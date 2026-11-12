<?php

namespace App\Http\Controllers;

use App\Models\BusinessManager;
use App\Models\AdAccount;
use App\Services\FacebookAdsService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessManagerController extends Controller
{
    protected FacebookAdsService $fbService;

    public function __construct(FacebookAdsService $fbService)
    {
        $this->fbService = $fbService;
    }

    /**
     * Danh sách tất cả Business Managers của người dùng hiện tại (chỉ BM doanh nghiệp thật)
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $query = BusinessManager::where('user_id', $userId)
            ->realBms()
            ->with(['adAccounts'])
            ->latest();

        // Tìm kiếm theo tên hoặc BM ID
        $search = $request->get('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('bm_id', 'like', "%{$search}%");
            });
        }

        // Lọc theo trạng thái
        $statusFilter = $request->get('status');
        if ($statusFilter) {
            if ($statusFilter === 'has_debt') {
                $query->whereHas('adAccounts', function ($q) {
                    $q->where('account_status', 3)->orWhere('balance', '>', 0);
                });
            } elseif (in_array($statusFilter, ['active', 'expired', 'error', 'disabled'])) {
                $query->where('status', $statusFilter);
            }
        }

        $perPage = (int) $request->input('per_page', 20);
        if ($perPage <= 0 || $perPage > 500) {
            $perPage = 20;
        }

        $bms = $query->paginate($perPage)->withQueryString();

        // Tái sử dụng collection nếu không có lọc và chỉ có 1 trang để tránh query lặp
        if (!$search && !$statusFilter && $bms->currentPage() === 1 && !$bms->hasMorePages()) {
            $allUserBMs = $bms->getCollection();
        } else {
            $allUserBMs = BusinessManager::where('user_id', $userId)
                ->realBms()
                ->with('adAccounts')
                ->get();
        }

        $bmAccountQuery = AdAccount::whereHas('businessManager', function ($q) use ($userId) {
            $q->where('user_id', $userId)->realBms();
        });

        // Gom nhóm theo ad_account_id để tính tổng chi tiêu & nợ chính xác, không bị nhân bản
        $uniqueAccounts = (clone $bmAccountQuery)
            ->select('ad_account_id', \Illuminate\Support\Facades\DB::raw('MAX(amount_spent) as amount_spent'), \Illuminate\Support\Facades\DB::raw('MAX(balance) as balance'), \Illuminate\Support\Facades\DB::raw('MAX(account_status) as account_status'))
            ->groupBy('ad_account_id')
            ->get();

        // Tài khoản cá nhân (tách riêng)
        $personalBm = BusinessManager::where('user_id', $userId)
            ->personal()
            ->with('adAccounts')
            ->first();
        $personalAccounts = $personalBm ? $personalBm->adAccounts : collect();

        $stats = [
            'total_bms' => $allUserBMs->count(),
            'active_bms' => $allUserBMs->where('status', 'active')->count(),
            'total_accounts' => $uniqueAccounts->count(),
            'owned_accounts' => (clone $bmAccountQuery)->where('source_type', 'owned')->count(),
            'client_accounts' => (clone $bmAccountQuery)->where('source_type', 'client')->count(),
            'active_accounts' => $uniqueAccounts->where('account_status', 1)->count(),
            'disabled_accounts' => $uniqueAccounts->where('account_status', 2)->count(),
            'unsettled_accounts' => $uniqueAccounts->where('account_status', 3)->count(),
            'total_spent' => (float) $uniqueAccounts->sum('amount_spent'),
            'total_balance' => (float) $uniqueAccounts->where('account_status', 3)->sum('balance'),
            'personal_accounts_count' => $personalAccounts->count(),
        ];

        // Bộ lọc thời gian cho chi phí (Date preset hoặc custom range - Mặc định: last_30d)
        [
            'datePreset' => $datePreset,
            'since' => $since,
            'until' => $until,
            'dateLabel' => $dateLabel,
        ] = $this->resolveDateFilter($request);

        $needsApiSpend = !$this->fbService->isLifetimeSpendFilter($datePreset, $since)
            && !($datePreset === 'custom' && (empty($since) || empty($until)));

        $spendReady = true;
        if ($needsApiSpend) {
            // Chỉ đọc từ cache khi render HTML để mở trang tức thì; nếu chưa có cache sẽ tải song song qua AJAX
            $cachedByBmId = [];
            $allCached = true;
            foreach ($allUserBMs as $bmModel) {
                $cached = $this->fbService->getCachedBmSpend($bmModel, $datePreset, $since, $until);
                $cachedByBmId[$bmModel->id] = $cached;
                if ($cached === null) {
                    $allCached = false;
                }
            }

            foreach ($bms as $bmModel) {
                $cached = $cachedByBmId[$bmModel->id] ?? $this->fbService->getCachedBmSpend($bmModel, $datePreset, $since, $until);
                if ($cached !== null) {
                    $bmModel->filtered_spent = $cached['total_spend'];
                    $bmModel->filtered_owned_spend = $cached['owned_spend'];
                    $bmModel->filtered_client_spend = $cached['client_spend'];
                    $bmModel->spend_pending = false;
                } else {
                    $bmModel->filtered_spent = null;
                    $bmModel->spend_pending = true;
                }
            }

            if ($allCached) {
                $stats['total_spent'] = $this->sumUniqueAccountSpends($allUserBMs, $cachedByBmId);
            } else {
                $spendReady = false;
            }
        } else {
            foreach ($bms as $bmModel) {
                $bmModel->filtered_spent = $bmModel->total_spent;
                $bmModel->spend_pending = false;
            }
        }

        $latestUpdateTime = $allUserBMs->max('updated_at');
        $latestUpdate = $latestUpdateTime ? \Illuminate\Support\Carbon::parse($latestUpdateTime) : null;

        return view('bms.index', compact('bms', 'stats', 'search', 'statusFilter', 'personalAccounts', 'latestUpdate', 'datePreset', 'since', 'until', 'dateLabel', 'spendReady', 'needsApiSpend'));
    }

    /**
     * API AJAX lấy chi tiêu theo bộ lọc ngày cho toàn bộ BM của User (chạy song song Http::pool)
     */
    public function spendSummary(Request $request)
    {
        $userId = Auth::id();
        [
            'datePreset' => $datePreset,
            'since' => $since,
            'until' => $until,
            'dateLabel' => $dateLabel,
        ] = $this->resolveDateFilter($request);

        $forceRefresh = $request->boolean('refresh');

        $allUserBMs = BusinessManager::where('user_id', $userId)
            ->realBms()
            ->with('adAccounts')
            ->get();

        $spendByBmId = $this->fbService->getMultipleBmSpendsByDate($allUserBMs, $datePreset, $since, $until, $forceRefresh);
        $totalSpent = $this->sumUniqueAccountSpends($allUserBMs, $spendByBmId);

        $bmSpends = [];
        $hasErrors = false;
        foreach ($allUserBMs as $bmModel) {
            $data = $spendByBmId[$bmModel->id] ?? ['total_spend' => 0.0, 'has_error' => false];
            $hasError = !empty($data['has_error']);
            if ($hasError) {
                $hasErrors = true;
            }
            $amount = (float) ($data['total_spend'] ?? 0);
            $bmSpends[$bmModel->id] = [
                'total_spend' => $amount,
                'formatted' => number_format($amount, 0, ',', '.') . ' đ',
                'has_error' => $hasError,
            ];
        }

        return response()->json([
            'success' => true,
            'date_preset' => $datePreset,
            'date_label' => $dateLabel,
            'total_spent' => $totalSpent,
            'total_spent_formatted' => number_format($totalSpent, 0, ',', '.') . ' đ',
            'bm_spends' => $bmSpends,
            'has_errors' => $hasErrors,
        ]);
    }

    /**
     * Xem thông tin chi tiết một Business Manager và danh sách tài khoản của nó
     */
    public function show(Request $request, $id)
    {
        $userId = Auth::id();
        $bm = BusinessManager::where('user_id', $userId)
            ->with('adAccounts')
            ->findOrFail($id);

        $accountsQuery = $bm->adAccounts();

        // Lọc tài khoản theo tên hoặc ID
        if ($search = $request->get('search')) {
            $accountsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('ad_account_id', 'like', "%{$search}%");
            });
        }

        // Lọc theo nguồn tài khoản: owned (TK của BM) hoặc client (TK được share)
        $sourceFilter = $request->get('source');
        if ($sourceFilter && in_array($sourceFilter, ['owned', 'client'])) {
            $accountsQuery->where('source_type', $sourceFilter);
        }

        // Lọc theo trạng thái tài khoản
        $accountStatus = $request->get('account_status');
        if ($accountStatus !== null && $accountStatus !== '') {
            $accountsQuery->where('account_status', (int) $accountStatus);
        }

        // Bộ lọc thời gian cho chi phí (Date preset hoặc custom range - Mặc định: last_30d)
        [
            'datePreset' => $datePreset,
            'since' => $since,
            'until' => $until,
            'dateLabel' => $dateLabel,
        ] = $this->resolveDateFilter($request);

        $spendData = $this->fbService->getBmSpendByDate($bm, $datePreset, $since, $until);
        $bm->filtered_spent = $spendData['total_spend'];
        $accountSpends = $spendData['account_spends'];

        $perPage = (int) $request->input('per_page', 20);
        if ($perPage <= 0 || $perPage > 500) {
            $perPage = 20;
        }

        $accounts = $accountsQuery->latest()->paginate($perPage)->withQueryString();

        foreach ($accounts as $acc) {
            $acc->filtered_spend = $accountSpends[$acc->ad_account_id] ?? (float) $acc->amount_spent;
        }

        $partners = $bm->partners_data ?? [];
        $bmPages = $bm->facebookPages()->orderByDesc('is_hot')->latest('id')->get();

        $stats = [
            'total_accounts' => $bm->adAccounts->count(),
            'owned_accounts' => $bm->owned_accounts_count,
            'client_accounts' => $bm->client_accounts_count,
            'active_accounts' => $bm->active_accounts_count,
            'disabled_accounts' => $bm->disabled_accounts_count,
            'unsettled_accounts' => $bm->unsettled_accounts_count,
            'total_spent' => $spendData['total_spend'],
            'total_balance' => $bm->total_balance,
            'partners_count' => $bm->partners_count ?? count($partners),
            'pages_count' => $bmPages->count(),
        ];

        $userBms = BusinessManager::where('user_id', $userId)
            ->realBms()
            ->orderBy('name')
            ->get();

        return view('bms.show', compact('bm', 'accounts', 'stats', 'partners', 'bmPages', 'sourceFilter', 'datePreset', 'since', 'until', 'dateLabel', 'perPage', 'accountStatus', 'userBms'));
    }

    /**
     * Thêm mới Business Manager bằng Access Token
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bm_id' => 'nullable|string|max:100',
            'access_token' => 'required|string',
        ]);

        $bm = BusinessManager::create([
            'user_id' => Auth::id(),
            'name' => trim($validated['name']),
            'bm_id' => !empty($validated['bm_id']) ? trim($validated['bm_id']) : null,
            'access_token' => trim($validated['access_token']),
            'status' => 'active',
        ]);

        try {
            $this->performSync($bm);
            return redirect()->route('bms.index')->with('success', "Đã thêm BM [{$bm->name}] và đồng bộ tài khoản quảng cáo thành công!");
        } catch (Exception $e) {
            return redirect()->route('bms.index')->with('warning', "Đã lưu BM [{$bm->name}] nhưng đồng bộ tài khoản lỗi: " . $e->getMessage());
        }
    }

    /**
     * Cập nhật thông tin Business Manager hoặc thay thế Access Token mới
     */
    public function update(Request $request, $id)
    {
        $bm = BusinessManager::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bm_id' => 'nullable|string|max:100',
            'access_token' => 'nullable|string',
        ]);

        $bm->name = trim($validated['name']);
        $bm->bm_id = !empty($validated['bm_id']) ? trim($validated['bm_id']) : null;

        $tokenUpdated = false;
        if (!empty($validated['access_token'])) {
            $bm->access_token = trim($validated['access_token']);
            $bm->status = 'active';
            $tokenUpdated = true;
        }

        $bm->save();

        if ($tokenUpdated) {
            try {
                $this->performSync($bm);
                return back()->with('success', "Đã cập nhật BM [{$bm->name}], làm mới Access Token và đồng bộ tài khoản thành công!");
            } catch (Exception $e) {
                return back()->with('warning', "Đã cập nhật BM [{$bm->name}], nhưng đồng bộ tài khoản bị lỗi: " . $e->getMessage());
            }
        }

        return back()->with('success', "Đã cập nhật thông tin BM [{$bm->name}] thành công!");
    }

    /**
     * Xóa Business Manager cùng toàn bộ tài khoản quảng cáo liên kết
     */
    public function destroy($id)
    {
        $bm = BusinessManager::where('user_id', Auth::id())->findOrFail($id);
        $name = $bm->name;
        $bm->delete();

        return redirect()->route('bms.index')->with('success', "Đã xóa thành công BM [{$name}]!");
    }

    /**
     * Quét và đồng bộ toàn bộ các BM & tài khoản từ Facebook cho User hiện tại
     */
    public function syncAllFacebook(Request $request)
    {
        $userId = Auth::id();

        // Tìm BusinessManager có token khả dụng gần nhất của user
        $bmWithToken = BusinessManager::where('user_id', $userId)
            ->whereNotNull('access_token')
            ->latest()
            ->first();

        if (!$bmWithToken || empty($bmWithToken->access_token)) {
            return redirect()->route('auth.facebook')->with('warning', 'Chưa có Token Facebook khả dụng. Vui lòng bấm Kết nối Facebook để tải danh sách BM.');
        }

        try {
            $result = $this->fbService->syncAllBusinessesAndAccounts($userId, $bmWithToken->access_token);
            $bmCount = $result['bms_count'];
            $accCount = $result['accounts_count'];

            return redirect()->route('bms.index')->with('success', "Đã tìm thấy và đồng bộ thành công {$bmCount} Business Managers cùng {$accCount} tài khoản quảng cáo!");
        } catch (Exception $e) {
            return redirect()->route('bms.index')->with('error', 'Lỗi khi quét BM từ Facebook: ' . $e->getMessage());
        }
    }

    /**
     * Đồng bộ tài khoản quảng cáo từ Meta về cơ sở dữ liệu
     */
    public function sync($id)
    {
        $bm = BusinessManager::where('user_id', Auth::id())->findOrFail($id);

        try {
            $result = $this->performSync($bm);
            $owned = $result['owned'];
            $client = $result['client'];
            return back()->with('success', "Đã đồng bộ thành công BM [{$bm->name}]: {$owned} tài khoản của BM (sở hữu), {$client} tài khoản được share từ BM khác!");
        } catch (Exception $e) {
            return back()->with('error', "Không thể đồng bộ tài khoản cho BM [{$bm->name}]: " . $e->getMessage());
        }
    }

    /**
     * Kiểm tra trực tiếp trạng thái & hạn dùng của Access Token với Graph API
     */
    public function checkToken(Request $request, $id)
    {
        $bm = BusinessManager::where('user_id', Auth::id())->findOrFail($id);

        try {
            $tokenInfo = $this->fbService->inspectToken($bm->access_token);

            $bm->status = $tokenInfo['is_valid'] ? 'active' : 'expired';
            $bm->save();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'is_valid' => $tokenInfo['is_valid'],
                    'token_info' => $tokenInfo,
                    'bm_status' => $bm->status,
                ]);
            }

            if ($tokenInfo['is_valid']) {
                $msg = "Token của BM [{$bm->name}] hoàn toàn HỢP LỆ! Hạn dùng: {$tokenInfo['expires_at_text']}.";
                return back()->with('success', $msg);
            } else {
                $errMsg = $tokenInfo['error'] ?? 'Token đã hết hạn hoặc bị thu hồi.';
                return back()->with('error', "Token của BM [{$bm->name}] KHÔNG HỢP LỆ: {$errMsg}");
            }
        } catch (Exception $e) {
            $bm->status = 'error';
            $bm->save();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return back()->with('error', "Lỗi khi kiểm tra Token: " . $e->getMessage());
        }
    }

    /**
     * Lấy danh sách đối tác của BM (JSON)
     */
    public function partners($id)
    {
        $bm = BusinessManager::where('user_id', Auth::id())->findOrFail($id);

        return response()->json([
            'success' => true,
            'bm_id' => $bm->id,
            'bm_meta_id' => $bm->bm_id,
            'bm_name' => $bm->name,
            'partners_count' => $bm->partners_count,
            'partners' => $bm->partners_data ?? [],
        ]);
    }

    /**
     * Đồng bộ đối tác từ Graph API cho BM
     */
    public function syncPartners($id)
    {
        $bm = BusinessManager::where('user_id', Auth::id())->findOrFail($id);

        try {
            $partners = $this->fbService->syncPartnersForBm($bm);
            return response()->json([
                'success' => true,
                'message' => "Đã đồng bộ thành công " . count($partners) . " đối tác cho BM [{$bm->name}]!",
                'partners_count' => count($partners),
                'partners' => $partners,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Lỗi đồng bộ đối tác: " . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper thực hiện đồng bộ tài khoản quảng cáo từ Graph API
     */
    protected function performSync(BusinessManager $bm): array
    {
        return $this->fbService->syncBmAccounts($bm);
    }

    /**
     * Chuẩn hóa tham số lọc thời gian chi tiêu từ Request
     */
    protected function resolveDateFilter(Request $request): array
    {
        $datePreset = (string) $request->get('date_preset', 'last_30d');
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

        return [
            'datePreset' => $datePreset,
            'since' => $since,
            'until' => $until,
            'dateLabel' => $dateLabels[$datePreset] ?? '30 ngày qua',
        ];
    }

    /**
     * Tính tổng chi tiêu theo tài khoản duy nhất (tránh cộng trùng tài khoản share nhiều BM)
     */
    protected function sumUniqueAccountSpends(iterable $bms, array $spendByBmId): float
    {
        $uniqueAccountSpends = [];
        foreach ($bms as $bmModel) {
            $spendData = $spendByBmId[$bmModel->id] ?? null;
            if (!$spendData) {
                continue;
            }
            foreach ($spendData['account_spends'] ?? [] as $accId => $sp) {
                $uniqueAccountSpends[$accId] = max($uniqueAccountSpends[$accId] ?? 0, (float) $sp);
            }
        }

        return (float) array_sum($uniqueAccountSpends);
    }
}
