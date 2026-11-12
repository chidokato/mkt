<?php

namespace App\Http\Controllers;

use App\Models\BusinessManager;
use App\Models\AdAccount;
use App\Services\FacebookAdsService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdAccountController extends Controller
{
    protected FacebookAdsService $fbService;

    public function __construct(FacebookAdsService $fbService)
    {
        $this->fbService = $fbService;
    }

    /**
     * Màn hình chính Dashboard của Người dùng: Chỉ hiển thị tài khoản của chính họ
     */
    public function index()
    {
        $userId = Auth::id();

        $businessManagers = BusinessManager::where('user_id', $userId)
            ->realBms()
            ->with('adAccounts')
            ->latest()
            ->get();

        $personalBm = BusinessManager::where('user_id', $userId)
            ->personal()
            ->with('adAccounts')
            ->first();

        $userAccountQuery = AdAccount::whereHas('businessManager', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });

        $uniqueAccounts = (clone $userAccountQuery)
            ->select('ad_account_id', \Illuminate\Support\Facades\DB::raw('MAX(amount_spent) as amount_spent'), \Illuminate\Support\Facades\DB::raw('MAX(balance) as balance'), \Illuminate\Support\Facades\DB::raw('MAX(account_status) as account_status'))
            ->groupBy('ad_account_id')
            ->get();

        $stats = [
            'total_bms' => $businessManagers->count(),
            'total_accounts' => $uniqueAccounts->count(),
            'active_accounts' => $uniqueAccounts->where('account_status', 1)->count(),
            'disabled_accounts' => $uniqueAccounts->where('account_status', 2)->count(),
            'total_spent' => (float) $uniqueAccounts->sum('amount_spent'),
            'total_balance' => (float) $uniqueAccounts->where('account_status', 3)->sum('balance'),
            'personal_accounts_count' => $personalBm ? $personalBm->adAccounts->count() : 0,
        ];

        return view('dashboard', compact('businessManagers', 'personalBm', 'stats'));
    }

    /**
     * Màn hình Quản lý tất cả Tài khoản Quảng cáo (Quản lý TKQC) của User
     */
    public function accountsIndex(Request $request)
    {
        $userId = Auth::id();

        // Bộ lọc từ request
        $search = $request->get('search');
        $bmFilter = $request->get('bm_id');
        $statusFilter = $request->get('account_status');
        $sourceFilter = $request->get('source');
        $sort = $request->get('sort', 'latest');
        $perPage = (int) $request->input('per_page', 20);
        if ($perPage <= 0 || $perPage > 500) {
            $perPage = 20;
        }

        // 1. Lấy toàn bộ bản ghi tài khoản quảng cáo của User để tính thống kê và map BM liên kết
        $allUserAccounts = AdAccount::whereHas('businessManager', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with('businessManager')->get();

        // 2. Gom nhóm theo ad_account_id để lấy bản ghi chính thức duy nhất (khử lặp khi 1 TKQC thuộc nhiều BM)
        $groupedByActId = $allUserAccounts->groupBy('ad_account_id');

        $uniqueAccounts = $groupedByActId->map(function ($records) {
            // Ưu tiên bản ghi: owned (1) > personal (2) > client (3), tie-break: id mới nhất
            return $records->sort(function ($a, $b) {
                $sourceScore = ['owned' => 3, 'personal' => 2, 'client' => 1];
                $scoreA = $sourceScore[$a->source_type] ?? 0;
                $scoreB = $sourceScore[$b->source_type] ?? 0;
                if ($scoreA !== $scoreB) {
                    return $scoreB <=> $scoreA;
                }
                return $b->id <=> $a->id;
            })->first();
        });

        // 3. Map danh sách các BM cùng có quyền truy cập vào từng tài khoản (để hiển thị badge share phụ)
        $otherBmsByActId = [];
        foreach ($allUserAccounts as $acc) {
            if ($acc->businessManager) {
                $otherBmsByActId[$acc->ad_account_id][] = [
                    'bm_id' => $acc->business_manager_id,
                    'bm_name' => $acc->businessManager->name,
                    'is_personal' => $acc->businessManager->is_personal,
                    'source_type' => $acc->source_type,
                ];
            }
        }

        // 4. Thống kê tổng hợp (tính chính xác trên tài khoản duy nhất)
        $hasClientActIds = $allUserAccounts->where('source_type', 'client')->pluck('ad_account_id')->unique();
        $hasOwnedActIds = $allUserAccounts->where('source_type', 'owned')->pluck('ad_account_id')->unique();
        $hasPersonalActIds = $allUserAccounts->where('source_type', 'personal')->pluck('ad_account_id')->unique();

        $stats = [
            'total_accounts' => $uniqueAccounts->count(),
            'active_accounts' => $uniqueAccounts->where('account_status', 1)->count(),
            'disabled_accounts' => $uniqueAccounts->where('account_status', 2)->count(),
            'unsettled_accounts' => $uniqueAccounts->filter(fn($a) => $a->account_status == 3 || $a->balance > 0)->count(),
            'owned_accounts' => $hasOwnedActIds->count(),
            'client_accounts' => $hasClientActIds->count(),
            'personal_accounts' => $hasPersonalActIds->count(),
            'total_spent' => (float) $uniqueAccounts->sum('amount_spent'),
            'total_balance' => (float) $uniqueAccounts->where('account_status', 3)->sum('balance'),
        ];

        // 5. Xây dựng Query dữ liệu
        if ($bmFilter) {
            // Khi người dùng chỉ định xem 1 BM cụ thể (hoặc TK cá nhân)
            $query = AdAccount::whereHas('businessManager', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->with('businessManager');

            if ($bmFilter === 'personal') {
                $query->whereHas('businessManager', function ($q) {
                    $q->personal();
                });
            } else {
                $query->where('business_manager_id', $bmFilter);
            }

            if ($sourceFilter && in_array($sourceFilter, ['owned', 'client', 'personal'])) {
                $query->where('source_type', $sourceFilter);
            }
        } else {
            // Khi xem tất cả BM: TỰ ĐỘNG KHỬ TRÙNG LẶP theo ad_account_id
            // Nếu 1 tài khoản vừa thuộc BM sở hữu vừa được share tới BM khác (như Kế toán công ty),
            // ưu tiên hiển thị bản ghi BM sở hữu ('owned' > 'personal' > 'client')
            $preferredIdsSubquery = AdAccount::whereHas('businessManager', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });

            if ($sourceFilter && in_array($sourceFilter, ['owned', 'client', 'personal'])) {
                $preferredIdsSubquery->where('source_type', $sourceFilter);
            }

            $preferredIdsSubquery->select('id', 'ad_account_id')
                ->selectRaw("ROW_NUMBER() OVER (
                    PARTITION BY ad_account_id 
                    ORDER BY 
                        CASE source_type 
                            WHEN 'owned' THEN 1 
                            WHEN 'personal' THEN 2 
                            WHEN 'client' THEN 3 
                            ELSE 4 
                        END ASC,
                        id DESC
                ) as rn");

            $preferredIdsQuery = \Illuminate\Support\Facades\DB::table(
                \Illuminate\Support\Facades\DB::raw("({$preferredIdsSubquery->toSql()}) as ranked")
            )
            ->mergeBindings($preferredIdsSubquery->getQuery())
            ->where('rn', 1)
            ->select('id');

            $query = AdAccount::whereIn('id', $preferredIdsQuery)->with('businessManager');
        }

        // Tìm kiếm theo tên TKQC, ID hoặc thẻ
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('ad_account_id', 'like', "%{$search}%")
                  ->orWhere('payment_method_display', 'like', "%{$search}%")
                  ->orWhere('card_last_four', 'like', "%{$search}%");
            });
        }

        // Lọc theo trạng thái tài khoản
        if ($statusFilter !== null && $statusFilter !== '') {
            $query->where('account_status', (int) $statusFilter);
        }

        // Sắp xếp
        match ($sort) {
            'spent_desc' => $query->orderByDesc('amount_spent'),
            'spent_asc' => $query->orderBy('amount_spent'),
            'balance_desc' => $query->orderByDesc('balance'),
            'name_asc' => $query->orderBy('name'),
            'status' => $query->orderBy('account_status'),
            default => $query->latest('id'),
        };

        // Lấy danh sách BM cho bộ lọc dropdown
        $userBms = BusinessManager::where('user_id', $userId)
            ->realBms()
            ->withCount('adAccounts')
            ->orderBy('name')
            ->get();

        $personalBm = BusinessManager::where('user_id', $userId)
            ->personal()
            ->withCount('adAccounts')
            ->first();

        $accounts = $query->paginate($perPage)->withQueryString();

        $latestUpdate = $accounts->max('updated_at') ?? now();

        return view('accounts.index', compact(
            'accounts',
            'stats',
            'userBms',
            'personalBm',
            'otherBmsByActId',
            'search',
            'bmFilter',
            'statusFilter',
            'sourceFilter',
            'sort',
            'perPage',
            'latestUpdate'
        ));
    }

    /**
     * Thêm mới một Business Manager cùng System User Token cho Người dùng hiện tại
     */
    public function storeBM(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bm_id' => 'nullable|string|max:100',
            'access_token' => 'required|string',
        ]);

        $bm = BusinessManager::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'bm_id' => $validated['bm_id'],
            'access_token' => $validated['access_token'],
            'status' => 'active',
        ]);

        try {
            $this->syncAccounts($bm->id);
            return redirect()->route('dashboard')->with('success', "Đã thêm BM [{$bm->name}] và đồng bộ tài khoản thành công!");
        } catch (Exception $e) {
            return redirect()->route('dashboard')->with('warning', "Đã thêm BM [{$bm->name}] nhưng đồng bộ bị lỗi: " . $e->getMessage());
        }
    }

    /**
     * Xóa một Business Manager của Người dùng hiện tại
     */
    public function destroyBM($bmId)
    {
        $bm = BusinessManager::where('user_id', Auth::id())->findOrFail($bmId);
        $name = $bm->name;
        $bm->delete();

        return redirect()->route('dashboard')->with('success', "Đã xóa BM [{$name}] thành công!");
    }

    /**
     * Đồng bộ danh sách tài khoản quảng cáo từ Meta về Database cho User
     */
    public function syncAccounts($bmId)
    {
        $bm = BusinessManager::where('user_id', Auth::id())->findOrFail($bmId);

        try {
            $result = $this->fbService->syncBmAccounts($bm);
            $owned = $result['owned'];
            $client = $result['client'];
            return redirect()->route('dashboard')->with('success', "Đã đồng bộ BM [{$bm->name}]: {$owned} tài khoản của BM, {$client} tài khoản được share!");
        } catch (Exception $e) {
            return redirect()->route('dashboard')->with('error', "Lỗi khi đồng bộ tài khoản: " . $e->getMessage());
        }
    }

    /**
     * Xem danh sách các chiến dịch của 1 Ad Account (phải thuộc sở hữu của User)
     */
    /**
     * Xem danh sách các chiến dịch của 1 Ad Account (phải thuộc sở hữu của User)
     */
    public function campaigns(Request $request, $adAccountId)
    {
        $userId = Auth::id();
        $account = AdAccount::whereHas('businessManager', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with('businessManager')->findOrFail($adAccountId);

        $bm = $account->businessManager;
        // Danh sách tất cả tài khoản trong cùng BM để chuyển đổi ("chọn từng tkqc")
        $bmAccounts = $bm ? $bm->adAccounts()->orderBy('name')->get() : collect([$account]);

        // Bộ lọc thời gian cho chi phí (Date preset hoặc custom range - Mặc định: last_30d)
        $datePreset = $request->get('date_preset', 'last_30d');
        $since = $request->get('since');
        $until = $request->get('until');

        $dateLabels = [
            'last_30d' => '30 ngày qua',
            'today' => 'Hôm nay',
            'yesterday' => 'Hôm qua',
            'last_7d' => '7 ngày qua',
            'this_month' => 'Tháng này',
            'last_month' => 'Tháng trước',
            'this_year' => 'Năm nay',
            'last_year' => 'Năm trước',
            'maximum' => 'Toàn thời gian',
            'custom' => ($since && $until) ? ($since . ' đến ' . $until) : 'Tùy chọn ngày',
        ];
        $dateLabel = $dateLabels[$datePreset] ?? '30 ngày qua';

        // Danh sách đối tác liên kết với tài khoản này
        $allPartners = $bm->partners_data ?? [];
        $accountPartners = [];
        foreach ($allPartners as $p) {
            $matchedAccount = null;
            foreach ($p['accounts'] ?? [] as $a) {
                if (($a['account_id'] ?? '') === $account->ad_account_id) {
                    $matchedAccount = $a;
                    break;
                }
            }
            if ($matchedAccount) {
                $accountPartners[] = [
                    'id' => $p['id'] ?? null,
                    'name' => $p['name'] ?? 'Đối tác',
                    'status' => $matchedAccount['status'] ?? 'CONFIRMED',
                    'tasks' => $matchedAccount['tasks'] ?? [],
                    'type' => 'shared_with_partner',
                ];
            }
        }

        // Nếu là tài khoản client được share từ BM khác, thêm BM chủ sở hữu
        if ($account->source_type === 'client' && $account->owner_business_id) {
            array_unshift($accountPartners, [
                'id' => $account->owner_business_id,
                'name' => $account->owner_business_name ?: 'Doanh nghiệp chủ sở hữu',
                'status' => 'CONFIRMED',
                'tasks' => ['OWNER'],
                'type' => 'owner_business',
            ]);
        }

        $campaigns = [];
        $campaignError = null;
        $totalCampaignSpend = 0.0;

        try {
            if ($bm && !empty($bm->access_token)) {
                $campaigns = $this->fbService->getCampaigns(
                    $account->ad_account_id,
                    $bm->access_token,
                    $datePreset,
                    $since,
                    $until
                );

                // Tính tổng chi phí phát sinh trong khoảng thời gian lọc
                foreach ($campaigns as $camp) {
                    $totalCampaignSpend += (float) ($camp['spend'] ?? 0.0);
                }

                // SẮP XẾP CHIẾN DỊCH:
                // 1. Chiến dịch đang bật (ACTIVE) lên trên
                // 2. Chiến dịch phát sinh chi phí (spend > 0) lên trước (chi tiêu cao -> thấp)
                // 3. Fallback theo updated_time
                usort($campaigns, function ($a, $b) {
                    $aActive = (($a['status'] ?? '') === 'ACTIVE') ? 1 : 0;
                    $bActive = (($b['status'] ?? '') === 'ACTIVE') ? 1 : 0;
                    if ($aActive !== $bActive) {
                        return $bActive <=> $aActive; // 1 (ACTIVE) xếp trước 0 (PAUSED)
                    }

                    $aSpend = (float) ($a['spend'] ?? 0.0);
                    $bSpend = (float) ($b['spend'] ?? 0.0);
                    if ($aSpend != $bSpend) {
                        return $bSpend <=> $aSpend; // Chi phí cao xếp trước, chi phí 0 xếp sau
                    }

                    return strcmp($b['updated_time'] ?? '', $a['updated_time'] ?? '');
                });
            }
        } catch (Exception $e) {
            $campaignError = $e->getMessage();
        }

        return view('campaigns', compact(
            'account',
            'bm',
            'bmAccounts',
            'accountPartners',
            'campaigns',
            'campaignError',
            'datePreset',
            'since',
            'until',
            'dateLabel',
            'totalCampaignSpend'
        ));
    }

    /**
     * Bật / Tắt chiến dịch tức thì
     */
    public function toggleCampaign(Request $request, $campaignId)
    {
        $request->validate([
            'status' => 'required|in:ACTIVE,PAUSED',
            'ad_account_id' => 'required|exists:ad_accounts,id',
        ]);

        $userId = Auth::id();
        $account = AdAccount::whereHas('businessManager', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with('businessManager')->findOrFail($request->ad_account_id);

        try {
            $this->fbService->updateCampaignStatus(
                $campaignId,
                $request->status,
                $account->businessManager->access_token
            );

            // Xóa cache để cập nhật tức thì trạng thái mới
            $this->fbService->clearCampaignsCache($account->ad_account_id);

            $action = $request->status === 'ACTIVE' ? 'Bật' : 'Tắt';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Đã {$action} chiến dịch thành công!",
                    'status' => $request->status,
                ]);
            }

            return back()->with('success', "Đã {$action} chiến dịch thành công!");
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return back()->with('error', "Lỗi thao tác chiến dịch: " . $e->getMessage());
        }
    }

    /**
     * Bắt đầu luồng đăng nhập Facebook OAuth
     */
    public function redirectToFacebook(Request $request)
    {
        $state = bin2hex(random_bytes(16));
        $includePublish = $request->get('mode') === 'publish';

        session([
            'fb_oauth_state' => $state,
            'fb_oauth_user_id' => Auth::id(),
            'fb_oauth_return_to' => $includePublish ? 'pages.publish.index' : 'pages.index',
        ]);

        $redirectUri = route('auth.facebook.callback');
        $authUrl = $this->fbService->getAuthorizationUrl($redirectUri, $state, $includePublish);

        return redirect()->away($authUrl);
    }

    /**
     * Tiếp nhận callback từ Facebook sau khi User cấp quyền
     */
    public function handleFacebookCallback(Request $request)
    {
        if ($request->has('error')) {
            $desc = $request->get('error_description', $request->get('error'));
            return redirect()->route('dashboard')->with('error', 'Người dùng hủy hoặc Facebook từ chối: ' . $desc);
        }

        $code = $request->get('code');
        if (!$code) {
            return redirect()->route('dashboard')->with('error', 'Không nhận được mã xác thực từ Facebook.');
        }

        $userId = session('fb_oauth_user_id', Auth::id());
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.');
        }

        try {
            $redirectUri = route('auth.facebook.callback');

            // 1. Lấy short-lived token
            $shortLivedToken = $this->fbService->getAccessTokenFromCode($code, $redirectUri);

            // 2. Đổi lấy long-lived token (60 ngày)
            $longLivedToken = $this->fbService->getLongLivedToken($shortLivedToken);

            // 3. Lấy thông tin user Facebook
            $userProfile = $this->fbService->getUserProfile($longLivedToken);
            $fbUserId = $userProfile['id'] ?? $userId;
            $fbUserName = $userProfile['name'] ?? 'Tài khoản FB';

            $returnRoute = session()->pull('fb_oauth_return_to', 'pages.index');

            // Nếu user đi từ trang Đăng bài & Hẹn giờ để cấp quyền pages_manage_posts và đã có dữ liệu BM/Page,
            // chỉ làm mới token và đồng bộ nhanh Pages để tránh timeout / chờ lâu.
            if ($returnRoute === 'pages.publish.index' && BusinessManager::where('user_id', $userId)->exists()) {
                BusinessManager::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'bm_id' => 'personal_' . $fbUserId,
                    ],
                    [
                        'name' => 'TK Cá nhân (' . $fbUserName . ')',
                        'access_token' => $longLivedToken,
                        'status' => 'active',
                    ]
                );

                $pageResult = $this->fbService->syncAllPagesForUser($userId, $longLivedToken, $userProfile, false);
                $totalPages = $pageResult['total_pages'] ?? 0;

                return redirect()->route('pages.publish.index')->with(
                    'success',
                    "Cấp quyền đăng bài thành công cho nick [{$fbUserName}]! Đã cập nhật Page Access Token cho {$totalPages} Pages."
                );
            }

            // 4. Đồng bộ toàn bộ các Business Manager và tài khoản quảng cáo
            $result = $this->fbService->syncAllBusinessesAndAccounts($userId, $longLivedToken, $userProfile);

            $bmCount = $result['bms_count'];
            $accCount = $result['accounts_count'];

            // 5. Tự động đồng bộ toàn bộ Pages (BM + Trang cá nhân)
            $pagesCount = 0;
            try {
                $pageResult = $this->fbService->syncAllPagesForUser($userId, $longLivedToken, $userProfile, false);
                $pagesCount = $pageResult['synced_count'] ?? 0;
            } catch (Exception $e) {
                // bỏ qua lỗi nếu sync pages gặp trục trặc tạm thời
            }

            return redirect()->route($returnRoute)->with('success', "Kết nối Facebook thành công với nick [{$fbUserName}]! Đã đồng bộ {$bmCount} Business Managers, {$accCount} tài khoản QC cùng {$pagesCount} Facebook Pages.");
        } catch (Exception $e) {
            return redirect()->route('bms.index')->with('error', 'Lỗi khi xử lý đăng nhập Facebook: ' . $e->getMessage());
        }
    }
}
