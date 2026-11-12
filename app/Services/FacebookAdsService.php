<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\BusinessManager;
use App\Models\FacebookPage;
use App\Models\PagePost;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookAdsService
{
    public const BM_SPEND_CACHE_TTL = 900; // 15 phút

    protected string $baseUrl;

    public function __construct()
    {
        $version = Setting::get('meta_graph_version', config('services.meta.version', 'v21.0'));
        $this->baseUrl = "https://graph.facebook.com/{$version}";
    }

    /**
     * Lấy hệ số chia tiền tệ theo chuẩn Meta Marketing API
     * Meta quy định:
     * - Các đồng tiền không dùng cent (offset = 1): VND, JPY, KRW, CLP, COP, CRC, HUF, IDR, ISK, PYG, TWD -> hệ số chia là 1 (không chia 100)
     * - Các đồng tiền dùng cent (offset = 100): USD, EUR, GBP... -> hệ số chia là 100
     */
    public static function getCurrencyDivisor(?string $currency): float
    {
        $zeroDecimalCurrencies = [
            'VND', 'JPY', 'KRW', 'CLP', 'COP', 'CRC', 'HUF', 'IDR', 'ISK', 'PYG', 'TWD'
        ];

        $curr = strtoupper(trim((string) $currency));
        return in_array($curr, $zeroDecimalCurrencies) ? 1.0 : 100.0;
    }

    /**
     * Lấy danh sách các Business Managers (Doanh nghiệp) mà User sở hữu hoặc quản lý
     */
    public function getBusinesses(string $accessToken): array
    {
        $businesses = [];
        $url = "{$this->baseUrl}/me/businesses";
        $params = [
            'fields' => 'id,name,verification_status,created_time',
            'limit' => 100,
        ];

        do {
            $response = Http::withToken($accessToken)->get($url, $params);
            if ($response->failed()) {
                $error = $response->json()['error']['message'] ?? $response->body();
                Log::warning('Facebook API getBusinesses: ' . $error);
                break;
            }

            $json = $response->json();
            $data = $json['data'] ?? [];
            foreach ($data as $item) {
                $businesses[] = $item;
            }

            $url = $json['paging']['next'] ?? null;
            $params = [];
        } while ($url);

        return $businesses;
    }

    /**
     * Lấy danh sách các Ad Account gắn liền với Token (kèm thông tin BM sở hữu)
     */
    public function getAssignedAdAccounts(string $accessToken): array
    {
        $accounts = [];
        $url = "{$this->baseUrl}/me/adaccounts";
        $params = [
            'fields' => 'id,name,account_status,currency,amount_spent,balance,spend_cap,business{id,name},funding_source_details',
            'limit' => 100,
        ];

        do {
            $response = Http::withToken($accessToken)->get($url, $params);
            if ($response->failed()) {
                $error = $response->json()['error']['message'] ?? $response->body();
                Log::error('Facebook API Error getAssignedAdAccounts: ' . $error);
                throw new Exception("Lỗi lấy danh sách tài khoản: {$error}");
            }

            $json = $response->json();
            $data = $json['data'] ?? [];
            foreach ($data as $acc) {
                $accounts[] = $acc;
            }

            $url = $json['paging']['next'] ?? null;
            $params = [];
        } while ($url);

        return $accounts;
    }

    /**
     * Lấy danh sách tài khoản quảng cáo thuộc sở hữu của Business Manager (owned_ad_accounts)
     */
    public function getBmOwnedAdAccounts(string $bmId, string $accessToken): array
    {
        if (str_starts_with($bmId, 'personal_') || str_starts_with($bmId, 'fb_')) {
            return [];
        }

        $accounts = [];
        $url = "{$this->baseUrl}/{$bmId}/owned_ad_accounts";
        $params = [
            'fields' => 'id,name,account_status,currency,amount_spent,balance,spend_cap,business{id,name},funding_source_details',
            'limit' => 100,
        ];

        do {
            $response = Http::withToken($accessToken)->get($url, $params);
            if ($response->failed()) {
                $error = $response->json()['error']['message'] ?? $response->body();
                Log::warning("Facebook API getBmOwnedAdAccounts ({$bmId}) error: {$error}");
                break;
            }

            $json = $response->json();
            $data = $json['data'] ?? [];
            foreach ($data as $acc) {
                $accounts[] = $acc;
            }

            $url = $json['paging']['next'] ?? null;
            $params = [];
        } while ($url);

        return $accounts;
    }

    /**
     * Lấy danh sách tài khoản quảng cáo được đối tác chia sẻ cho Business Manager (client_ad_accounts)
     */
    public function getBmClientAdAccounts(string $bmId, string $accessToken): array
    {
        if (str_starts_with($bmId, 'personal_') || str_starts_with($bmId, 'fb_')) {
            return [];
        }

        $accounts = [];
        $url = "{$this->baseUrl}/{$bmId}/client_ad_accounts";
        $params = [
            'fields' => 'id,name,account_status,currency,amount_spent,balance,spend_cap,business{id,name},funding_source_details',
            'limit' => 100,
        ];

        do {
            $response = Http::withToken($accessToken)->get($url, $params);
            if ($response->failed()) {
                $error = $response->json()['error']['message'] ?? $response->body();
                Log::warning("Facebook API getBmClientAdAccounts ({$bmId}) error: {$error}");
                break;
            }

            $json = $response->json();
            $data = $json['data'] ?? [];
            foreach ($data as $acc) {
                $accounts[] = $acc;
            }

            $url = $json['paging']['next'] ?? null;
            $params = [];
        } while ($url);

        return $accounts;
    }

    /**
     * Đồng bộ tài khoản quảng cáo (cả TK sở hữu và TK được share) cho 1 Business Manager cụ thể
     */
    public function syncBmAccounts(BusinessManager $bm): array
    {
        $token = $bm->access_token;
        if (empty($token)) {
            throw new Exception("BM [{$bm->name}] chưa có Access Token khả dụng.");
        }

        $syncedIds = [];
        $ownedCount = 0;
        $clientCount = 0;

        if ($bm->is_personal) {
            // Tài khoản cá nhân
            $allAssigned = $this->getAssignedAdAccounts($token);
            foreach ($allAssigned as $acc) {
                if (empty($acc['business']['id'])) {
                    $currency = $acc['currency'] ?? 'VND';
                    $divisor = self::getCurrencyDivisor($currency);
                    $cardInfo = self::extractCardDetails($acc['funding_source_details'] ?? null);

                    \App\Models\AdAccount::updateOrCreate(
                        [
                            'business_manager_id' => $bm->id,
                            'ad_account_id' => $acc['id'],
                        ],
                        [
                            'name' => $acc['name'] ?? 'Chưa đặt tên',
                            'currency' => $currency,
                            'account_status' => $acc['account_status'] ?? 1,
                            'amount_spent' => isset($acc['amount_spent']) ? ((float) $acc['amount_spent'] / $divisor) : 0,
                            'balance' => isset($acc['balance']) ? ((float) $acc['balance'] / $divisor) : 0,
                            'spend_cap' => isset($acc['spend_cap']) ? ((float) $acc['spend_cap'] / $divisor) : null,
                            'payment_method_display' => $cardInfo['payment_method_display'],
                            'card_last_four' => $cardInfo['card_last_four'],
                            'source_type' => 'personal',
                            'owner_business_id' => null,
                            'owner_business_name' => null,
                        ]
                    );
                    $syncedIds[] = $acc['id'];
                }
            }
        } elseif (!empty($bm->bm_id)) {
            // 1. Đồng bộ tài khoản thuộc sở hữu của BM (owned)
            $ownedAccounts = $this->getBmOwnedAdAccounts($bm->bm_id, $token);
            foreach ($ownedAccounts as $acc) {
                $currency = $acc['currency'] ?? 'VND';
                $divisor = self::getCurrencyDivisor($currency);
                $cardInfo = self::extractCardDetails($acc['funding_source_details'] ?? null);

                \App\Models\AdAccount::updateOrCreate(
                    [
                        'business_manager_id' => $bm->id,
                        'ad_account_id' => $acc['id'],
                    ],
                    [
                        'name' => $acc['name'] ?? 'Chưa đặt tên',
                        'currency' => $currency,
                        'account_status' => $acc['account_status'] ?? 1,
                        'amount_spent' => isset($acc['amount_spent']) ? ((float) $acc['amount_spent'] / $divisor) : 0,
                        'balance' => isset($acc['balance']) ? ((float) $acc['balance'] / $divisor) : 0,
                        'spend_cap' => isset($acc['spend_cap']) ? ((float) $acc['spend_cap'] / $divisor) : null,
                        'payment_method_display' => $cardInfo['payment_method_display'],
                        'card_last_four' => $cardInfo['card_last_four'],
                        'source_type' => 'owned',
                        'owner_business_id' => $acc['business']['id'] ?? $bm->bm_id,
                        'owner_business_name' => $acc['business']['name'] ?? $bm->name,
                    ]
                );
                $syncedIds[] = $acc['id'];
                $ownedCount++;
            }

            // 2. Đồng bộ tài khoản được share từ BM khác (client)
            $clientAccounts = $this->getBmClientAdAccounts($bm->bm_id, $token);
            foreach ($clientAccounts as $acc) {
                $currency = $acc['currency'] ?? 'VND';
                $divisor = self::getCurrencyDivisor($currency);
                $cardInfo = self::extractCardDetails($acc['funding_source_details'] ?? null);

                \App\Models\AdAccount::updateOrCreate(
                    [
                        'business_manager_id' => $bm->id,
                        'ad_account_id' => $acc['id'],
                    ],
                    [
                        'name' => $acc['name'] ?? 'Chưa đặt tên',
                        'currency' => $currency,
                        'account_status' => $acc['account_status'] ?? 1,
                        'amount_spent' => isset($acc['amount_spent']) ? ((float) $acc['amount_spent'] / $divisor) : 0,
                        'balance' => isset($acc['balance']) ? ((float) $acc['balance'] / $divisor) : 0,
                        'spend_cap' => isset($acc['spend_cap']) ? ((float) $acc['spend_cap'] / $divisor) : null,
                        'payment_method_display' => $cardInfo['payment_method_display'],
                        'card_last_four' => $cardInfo['card_last_four'],
                        'source_type' => 'client',
                        'owner_business_id' => $acc['business']['id'] ?? null,
                        'owner_business_name' => $acc['business']['name'] ?? null,
                    ]
                );
                $syncedIds[] = $acc['id'];
                $clientCount++;
            }
        }

        // Xóa sạch các tài khoản rác/gán nhầm trước đây cho BM này
        \App\Models\AdAccount::where('business_manager_id', $bm->id)
            ->whereNotIn('ad_account_id', $syncedIds)
            ->delete();

        $bm->touch();
        $this->clearBmSpendCache($bm->id);

        return [
            'owned' => $ownedCount,
            'client' => $clientCount,
            'total' => count($syncedIds),
        ];
    }

    /**
     * Đồng bộ toàn bộ các Business Manager và tài khoản quảng cáo của User từ Facebook
     *
     * @param int $userId
     * @param string $accessToken
     * @param array|null $userProfile
     * @return array ['bms_count' => int, 'accounts_count' => int]
     */
    public function syncAllBusinessesAndAccounts(int $userId, string $accessToken, ?array $userProfile = null): array
    {
        if ($userProfile === null) {
            try {
                $userProfile = $this->getUserProfile($accessToken);
            } catch (Exception $e) {
                $userProfile = ['id' => $userId, 'name' => 'Facebook User'];
            }
        }

        $fbUserId = $userProfile['id'] ?? $userId;
        $fbUserName = $userProfile['name'] ?? 'Facebook User';

        // 1. Quét danh sách tất cả các Business Managers (BM) được phân quyền
        $businesses = $this->getBusinesses($accessToken);
        $bmMap = []; // bm_id (Facebook) => BusinessManager (Database model)

        foreach ($businesses as $biz) {
            $bm = \App\Models\BusinessManager::updateOrCreate(
                [
                    'user_id' => $userId,
                    'bm_id' => (string) $biz['id'],
                ],
                [
                    'name' => $biz['name'] ?? ('BM ' . $biz['id']),
                    'access_token' => $accessToken,
                    'status' => 'active',
                ]
            );
            $bmMap[(string) $biz['id']] = $bm;
        }

        // 2. Đồng bộ Ad Accounts chi tiết cho từng BM (phân tách rõ TK sở hữu và TK được share)
        $totalOwned = 0;
        $totalClient = 0;

        foreach ($bmMap as $bmKey => $bmModel) {
            if (!str_starts_with((string)$bmKey, 'personal_') && !empty($bmModel->bm_id)) {
                try {
                    $res = $this->syncBmAccounts($bmModel);
                    $totalOwned += $res['owned'];
                    $totalClient += $res['client'];
                } catch (Exception $e) {
                    Log::warning("Lỗi đồng bộ tài khoản BM {$bmModel->name}: " . $e->getMessage());
                }
            }
        }

        // 3. Quét tài khoản cá nhân nếu có (hoặc cập nhật token nếu đã từng lưu TK cá nhân này)
        $personalBm = BusinessManager::where('user_id', $userId)
            ->where('bm_id', 'personal_' . $fbUserId)
            ->first();
        $personalAccounts = 0;
        try {
            $assigned = $this->getAssignedAdAccounts($accessToken);
            $personalAccs = array_filter($assigned, fn($a) => empty($a['business']['id']));
            if (!empty($personalAccs) || $personalBm) {
                $personalBm = BusinessManager::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'bm_id' => 'personal_' . $fbUserId,
                    ],
                    [
                        'name' => 'TK Cá nhân (' . $fbUserName . ')',
                        'access_token' => $accessToken,
                        'status' => 'active',
                    ]
                );
                $personalRes = $this->syncBmAccounts($personalBm);
                $personalAccounts = $personalRes['total'];
            }
        } catch (Exception $e) {
            Log::warning("Lỗi quét tài khoản cá nhân: " . $e->getMessage());
        }

        // 4. Xóa BM placeholder cũ (fb_...) nếu nó không còn tài khoản nào
        $legacyBm = \App\Models\BusinessManager::where('user_id', $userId)
            ->where('bm_id', 'fb_' . $fbUserId)
            ->first();
        if ($legacyBm && $legacyBm->adAccounts()->count() === 0) {
            $legacyBm->delete();
        }

        // 5. Đồng bộ đối tác (Partner Agencies) cho từng BM
        foreach ($bmMap as $bmKey => $bmModel) {
            if (!str_starts_with((string)$bmKey, 'personal_') && !empty($bmModel->bm_id)) {
                $partners = $this->getBmPartners($bmModel->bm_id, $accessToken);
                $bmModel->update([
                    'partners_count' => count($partners),
                    'partners_data' => $partners,
                ]);
            }
        }

        $realBmsCount = count(array_filter(array_keys($bmMap), fn($k) => !str_starts_with((string)$k, 'personal_')));

        return [
            'bms_count' => $realBmsCount,
            'real_bms_count' => $realBmsCount,
            'owned_accounts_count' => $totalOwned,
            'client_accounts_count' => $totalClient,
            'personal_accounts_count' => $personalAccounts,
            'accounts_count' => $totalOwned + $totalClient + $personalAccounts,
        ];
    }

    /**
     * Lấy danh sách đối tác (Agencies / Client partners) của một Business Manager từ Meta Graph API
     */
    public function getBmPartners(string $bmId, string $accessToken): array
    {
        if (str_starts_with($bmId, 'personal_') || str_starts_with($bmId, 'fb_')) {
            return [];
        }

        try {
            $response = Http::get("{$this->baseUrl}/{$bmId}/agencies", [
                'access_token' => $accessToken,
                'fields' => 'id,name,adaccount_permissions',
                'limit' => 100,
            ]);

            if ($response->successful()) {
                $data = $response->json('data') ?? [];
                return array_map(function ($item) {
                    $permissions = $item['adaccount_permissions'] ?? [];
                    return [
                        'id' => $item['id'] ?? '',
                        'name' => $item['name'] ?? 'Đối tác chưa đặt tên',
                        'accounts_count' => count($permissions),
                        'accounts' => array_map(function ($p) {
                            return [
                                'account_id' => $p['id'] ?? '',
                                'tasks' => $p['permitted_tasks'] ?? [],
                                'status' => $p['access_status'] ?? '',
                            ];
                        }, $permissions),
                    ];
                }, $data);
            }
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Không thể lấy đối tác của BM {$bmId}: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Đồng bộ danh sách đối tác cho 1 BM cụ thể
     */
    public function syncPartnersForBm(\App\Models\BusinessManager $bm): array
    {
        if (!$bm->bm_id || $bm->is_personal || empty($bm->access_token)) {
            return [];
        }

        $partners = $this->getBmPartners($bm->bm_id, $bm->access_token);
        $bm->update([
            'partners_count' => count($partners),
            'partners_data' => $partners,
        ]);

        return $partners;
    }

    /**
     * Lấy chi tiết một Ad Account cụ thể
     */
    public function getAdAccount(string $adAccountId, string $accessToken): array
    {
        $accountId = $this->formatAccountId($adAccountId);

        $response = Http::withToken($accessToken)->get("{$this->baseUrl}/{$accountId}", [
            'fields' => 'id,name,account_status,currency,amount_spent,balance,spend_cap',
        ]);

        if ($response->failed()) {
            $error = $response->json()['error']['message'] ?? $response->body();
            throw new Exception("Lỗi đọc tài khoản {$accountId}: {$error}");
        }

        return $response->json();
    }

    /**
     * Lấy danh sách chiến dịch của 1 Ad Account
     */
    /**
     * Lấy danh sách chiến dịch của 1 Ad Account kèm chi phí theo khoảng thời gian lọc
     */
    public function getCampaigns(string $adAccountId, string $accessToken, string $datePreset = 'last_30d', ?string $since = null, ?string $until = null): array
    {
        $accountId = $this->formatAccountId($adAccountId);

        // Tạo cache key ngắn hạn (60 giây) để giảm tải và tăng tốc độ load tức thì khi chuyển trang / lọc
        $cacheKey = "fb_camps_{$accountId}_{$datePreset}_" . md5("{$since}_{$until}");
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($accountId, $accessToken, $datePreset, $since, $until) {
            // Xây dựng trường truy vấn insights cho chi phí
            $insightField = "insights.date_preset({$datePreset}){spend}";
            if ($datePreset === 'custom' && $since && $until) {
                $insightField = "insights.time_range({'since':'{$since}','until':'{$until}'}){spend}";
            } elseif ($datePreset === 'maximum') {
                $insightField = "insights.date_preset(maximum){spend}";
            }

            $allCampaigns = [];
            $seenCampaignIds = [];
            $seenUrls = [];
            $page = 0;
            $maxPages = 10; // Giới hạn tối đa 10 trang để tránh treo nếu API lặp vô tận

            $url = "{$this->baseUrl}/{$accountId}/campaigns";
            $params = [
                'fields' => "id,name,status,objective,daily_budget,lifetime_budget,start_time,updated_time,{$insightField}",
                'limit' => 250, // Lấy tới 250 chiến dịch trong 1 request để tối ưu tốc độ
            ];

            try {
                do {
                    $page++;
                    $seenUrls[$url] = true;

                    $response = Http::timeout(15)->withToken($accessToken)->get($url, $params);

                    if ($response->failed()) {
                        $error = $response->json()['error']['message'] ?? $response->body();
                        Log::error("Facebook API Error getCampaigns {$accountId}: " . $error);

                        // Nếu lỗi do insightField (ví dụ permission hoặc định dạng), thử lại không kèm insights
                        if (str_contains($insightField, 'insights')) {
                            return $this->getCampaignsWithoutInsights($accountId, $accessToken);
                        }
                        throw new Exception("Lỗi lấy danh sách chiến dịch: {$error}");
                    }

                    $json = $response->json();
                    $data = $json['data'] ?? [];
                    if (empty($data)) {
                        break;
                    }

                    $newPageNewItems = 0;
                    foreach ($data as $item) {
                        $cid = (string) ($item['id'] ?? '');
                        if ($cid && !isset($seenCampaignIds[$cid])) {
                            $seenCampaignIds[$cid] = true;
                            $spend = isset($item['insights']['data'][0]['spend']) ? (float) $item['insights']['data'][0]['spend'] : 0.0;
                            $item['spend'] = $spend;
                            $allCampaigns[] = $item;
                            $newPageNewItems++;
                        }
                    }

                    // Nếu trang tiếp theo không chứa chiến dịch mới nào (Facebook lặp lại cursor) -> ngắt ngay
                    if ($newPageNewItems === 0) {
                        break;
                    }

                    $nextUrl = $json['paging']['next'] ?? null;
                    // Nếu không có next URL hoặc URL tiếp theo đã từng gọi -> ngắt vòng lặp
                    if (!$nextUrl || isset($seenUrls[$nextUrl])) {
                        break;
                    }

                    $url = $nextUrl;
                    $params = [];
                } while ($url && $page < $maxPages);

                return $allCampaigns;
            } catch (Exception $e) {
                Log::warning("Lỗi getCampaigns có insights ({$accountId}): " . $e->getMessage() . " -> Thử lại chế độ cơ bản");
                return $this->getCampaignsWithoutInsights($accountId, $accessToken);
            }
        });
    }

    /**
     * Fallback lấy danh sách chiến dịch cơ bản khi truy vấn insights gặp sự cố
     */
    protected function getCampaignsWithoutInsights(string $accountId, string $accessToken): array
    {
        $allCampaigns = [];
        $seenCampaignIds = [];
        $seenUrls = [];
        $page = 0;
        $maxPages = 10;

        $url = "{$this->baseUrl}/{$accountId}/campaigns";
        $params = [
            'fields' => 'id,name,status,objective,daily_budget,lifetime_budget,start_time,updated_time',
            'limit' => 250,
        ];

        do {
            $page++;
            $seenUrls[$url] = true;

            $response = Http::timeout(15)->withToken($accessToken)->get($url, $params);
            if ($response->failed()) {
                $error = $response->json()['error']['message'] ?? $response->body();
                throw new Exception("Lỗi lấy danh sách chiến dịch: {$error}");
            }
            $json = $response->json();
            $data = $json['data'] ?? [];
            if (empty($data)) {
                break;
            }

            $newPageNewItems = 0;
            foreach ($data as $item) {
                $cid = (string) ($item['id'] ?? '');
                if ($cid && !isset($seenCampaignIds[$cid])) {
                    $seenCampaignIds[$cid] = true;
                    $item['spend'] = 0.0;
                    $allCampaigns[] = $item;
                    $newPageNewItems++;
                }
            }

            if ($newPageNewItems === 0) {
                break;
            }

            $nextUrl = $json['paging']['next'] ?? null;
            if (!$nextUrl || isset($seenUrls[$nextUrl])) {
                break;
            }

            $url = $nextUrl;
            $params = [];
        } while ($url && $page < $maxPages);

        return $allCampaigns;
    }

    /**
     * Xóa cache danh sách chiến dịch của 1 tài khoản
     */
    public function clearCampaignsCache(string $adAccountId): void
    {
        $accountId = $this->formatAccountId($adAccountId);
        $presets = ['last_30d', 'today', 'yesterday', 'last_7d', 'this_month', 'last_month', 'this_year', 'last_year', 'maximum'];
        foreach ($presets as $p) {
            \Illuminate\Support\Facades\Cache::forget("fb_camps_{$accountId}_{$p}_" . md5("_"));
        }
    }

    /**
     * Bật / Tắt chiến dịch tức thì
     *
     * @param string $campaignId
     * @param string $status 'ACTIVE' hoặc 'PAUSED'
     * @param string $accessToken
     * @return bool
     */
    public function updateCampaignStatus(string $campaignId, string $status, string $accessToken): bool
    {
        $status = strtoupper($status);
        if (!in_array($status, ['ACTIVE', 'PAUSED'])) {
            throw new Exception("Trạng thái không hợp lệ. Chỉ chấp nhận ACTIVE hoặc PAUSED.");
        }

        $response = Http::withToken($accessToken)->post("{$this->baseUrl}/{$campaignId}", [
            'status' => $status,
        ]);

        if ($response->failed()) {
            $error = $response->json()['error']['message'] ?? $response->body();
            Log::error("Facebook API Error updateCampaignStatus {$campaignId}: " . $error);
            throw new Exception("Lỗi cập nhật chiến dịch: {$error}");
        }

        return $response->successful();
    }

    /**
     * Cập nhật ngân sách chiến dịch (đơn vị: tiền tệ thực tế, service tự quy đổi ra cent)
     */
    public function updateCampaignBudget(string $campaignId, float $dailyBudget, string $accessToken): bool
    {
        // Meta nhận đơn vị cent (nhân 100)
        $budgetInCents = (int) ($dailyBudget * 100);

        $response = Http::withToken($accessToken)->post("{$this->baseUrl}/{$campaignId}", [
            'daily_budget' => $budgetInCents,
        ]);

        return $response->successful();
    }

    /**
     * Kiểm tra thông tin & quyền của Access Token (Debug Token)
     */
    public function debugToken(string $accessToken): array
    {
        $appId = Setting::get('meta_app_id', config('services.meta.app_id'));
        $appSecret = Setting::get('meta_app_secret', config('services.meta.app_secret'));

        $appAccessToken = ($appId && $appSecret) ? "{$appId}|{$appSecret}" : $accessToken;

        $response = Http::get("{$this->baseUrl}/debug_token", [
            'input_token' => $accessToken,
            'access_token' => $appAccessToken,
        ]);

        if ($response->failed()) {
            $err = $response->json()['error']['message'] ?? $response->body();
            throw new Exception("Không thể kiểm tra token: {$err}");
        }

        return $response->json()['data'] ?? [];
    }

    /**
     * Kiểm tra chi tiết và toàn diện trạng thái của Access Token
     */
    public function inspectToken(string $accessToken): array
    {
        $debugData = [];
        $errorMessage = null;

        try {
            $debugData = $this->debugToken($accessToken);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            Log::warning("debugToken notice: " . $errorMessage);
        }

        // Lấy thêm thông tin đối tượng sở hữu qua /me
        $meData = null;
        try {
            $res = Http::withToken($accessToken)->get("{$this->baseUrl}/me", [
                'fields' => 'id,name',
            ]);
            if ($res->successful()) {
                $meData = $res->json();
            }
        } catch (Exception $e) {
            // bỏ qua
        }

        $isValid = false;
        if (!empty($debugData)) {
            $isValid = !empty($debugData['is_valid']);
        } elseif ($meData !== null && !empty($meData['id'])) {
            $isValid = true;
        }

        $expiresAt = $debugData['expires_at'] ?? null;
        $expiresAtText = 'Không rõ';
        if ($expiresAt !== null) {
            $expiresAtText = $expiresAt === 0 ? 'Vĩnh viễn (Never Expires)' : date('d/m/Y H:i:s', $expiresAt);
        }

        return [
            'is_valid' => $isValid,
            'name' => $meData['name'] ?? ($debugData['application'] ?? 'Meta Business Entity'),
            'account_id' => $meData['id'] ?? ($debugData['user_id'] ?? null),
            'type' => $debugData['type'] ?? 'USER / SYSTEM_USER',
            'application' => $debugData['application'] ?? null,
            'app_id' => $debugData['app_id'] ?? null,
            'expires_at' => $expiresAt,
            'expires_at_text' => $expiresAtText,
            'scopes' => $debugData['scopes'] ?? ['ads_management', 'ads_read'],
            'error' => $isValid ? null : ($debugData['error']['message'] ?? $errorMessage ?? 'Token không hợp lệ hoặc đã hết hạn.'),
        ];
    }

    /**
     * Tạo đường dẫn OAuth chuyển hướng người dùng sang trang cấp quyền của Facebook
     */
    public function getAuthorizationUrl(string $redirectUri, string $state, bool $includePublishScope = false): string
    {
        $appId = Setting::get('meta_app_id', config('services.meta.app_id'));
        $version = Setting::get('meta_graph_version', config('services.meta.version', 'v21.0'));

        $scopes = [
            'ads_management',
            'ads_read',
            'business_management',
            'public_profile',
            'pages_show_list',
            'pages_read_engagement',
        ];

        if ($includePublishScope) {
            $scopes[] = 'pages_manage_posts';
        }

        $params = http_build_query([
            'client_id' => $appId,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'response_type' => 'code',
            'auth_type' => 'rerequest',
            'scope' => implode(',', $scopes),
        ]);

        return "https://www.facebook.com/{$version}/dialog/oauth?{$params}";
    }

    /**
     * Đổi Authorization Code lấy User Access Token (Short-lived token)
     */
    public function getAccessTokenFromCode(string $code, string $redirectUri): string
    {
        $appId = Setting::get('meta_app_id', config('services.meta.app_id'));
        $appSecret = Setting::get('meta_app_secret', config('services.meta.app_secret'));

        $response = Http::get("{$this->baseUrl}/oauth/access_token", [
            'client_id' => $appId,
            'client_secret' => $appSecret,
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);

        if ($response->failed()) {
            $error = $response->json()['error']['message'] ?? $response->body();
            throw new Exception("Lỗi đổi mã truy cập Facebook: {$error}");
        }

        return $response->json()['access_token'];
    }

    /**
     * Nâng cấp Short-lived token thành Long-lived token (hạn 60 ngày)
     */
    public function getLongLivedToken(string $shortLivedToken): string
    {
        $appId = Setting::get('meta_app_id', config('services.meta.app_id'));
        $appSecret = Setting::get('meta_app_secret', config('services.meta.app_secret'));

        $response = Http::get("{$this->baseUrl}/oauth/access_token", [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $appId,
            'client_secret' => $appSecret,
            'fb_exchange_token' => $shortLivedToken,
        ]);

        if ($response->failed()) {
            // Nếu không nâng cấp được thì dùng tạm short-lived token
            Log::warning('Không thể nâng cấp Long-lived Token: ' . $response->body());
            return $shortLivedToken;
        }

        return $response->json()['access_token'] ?? $shortLivedToken;
    }

    /**
     * Lấy thông tin tài khoản Facebook cá nhân
     */
    public function getUserProfile(string $accessToken): array
    {
        $response = Http::withToken($accessToken)->get("{$this->baseUrl}/me", [
            'fields' => 'id,name,email',
        ]);

        if ($response->failed()) {
            throw new Exception("Không thể lấy thông tin người dùng Facebook: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Kiểm tra xem Token người dùng đã được cấp một quyền cụ thể trên /me/permissions chưa
     */
    public function checkUserPermission(string $accessToken, string $permission): bool
    {
        try {
            $res = Http::withToken($accessToken)->get("{$this->baseUrl}/me/permissions");
            if ($res->successful()) {
                $perms = collect($res->json('data') ?? []);
                return $perms->where('permission', $permission)->where('status', 'granted')->isNotEmpty();
            }
        } catch (Exception $e) {
            // Bỏ qua lỗi kết nối
        }
        return false;
    }

    /**
     * Kiểm tra xem Token người dùng đã được cấp quyền đọc Pages (pages_show_list) chưa
     */
    public function checkUserPagePermission(string $accessToken): bool
    {
        return $this->checkUserPermission($accessToken, 'pages_show_list');
    }

    /**
     * Kiểm tra xem Token người dùng đã được cấp quyền đăng bài Pages (pages_manage_posts) chưa
     */
    public function checkUserPublishPermission(string $accessToken): bool
    {
        return $this->checkUserPermission($accessToken, 'pages_manage_posts');
    }

    /**
     * Lấy danh sách Pages trực tiếp thuộc tài khoản người dùng (/me/accounts)
     */
    public function getUserPages(string $accessToken): array
    {
        $pages = [];
        $url = "{$this->baseUrl}/me/accounts";
        $params = [
            'fields' => 'id,name,category,category_list,link,picture{url},access_token,tasks,followers_count,fan_count,is_published,verification_status',
            'limit' => 100,
        ];

        do {
            $response = Http::withToken($accessToken)->get($url, $params);
            if ($response->failed()) {
                $error = $response->json()['error']['message'] ?? $response->body();
                Log::warning("Facebook API getUserPages error: {$error}");
                break;
            }

            $json = $response->json();
            $data = $json['data'] ?? [];
            foreach ($data as $item) {
                $pages[] = $item;
            }

            $url = $json['paging']['next'] ?? null;
            $params = [];
        } while ($url);

        return $pages;
    }

    /**
     * Lấy danh sách Pages do Business Manager sở hữu (owned_pages)
     */
    public function getBmOwnedPages(string $bmId, string $accessToken): array
    {
        $pages = [];
        $url = "{$this->baseUrl}/{$bmId}/owned_pages";
        $params = [
            'fields' => 'id,name,category,category_list,link,picture{url},access_token,tasks,followers_count,fan_count,is_published,verification_status',
            'limit' => 100,
        ];

        do {
            $response = Http::withToken($accessToken)->get($url, $params);
            if ($response->failed()) {
                $error = $response->json()['error']['message'] ?? $response->body();
                Log::warning("Facebook API getBmOwnedPages ({$bmId}) error: {$error}");
                break;
            }

            $json = $response->json();
            $data = $json['data'] ?? [];
            foreach ($data as $item) {
                $pages[] = $item;
            }

            $url = $json['paging']['next'] ?? null;
            $params = [];
        } while ($url);

        return $pages;
    }

    /**
     * Lấy danh sách Pages do đối tác/khách hàng ủy quyền cho BM (client_pages)
     */
    public function getBmClientPages(string $bmId, string $accessToken): array
    {
        $pages = [];
        $url = "{$this->baseUrl}/{$bmId}/client_pages";
        $params = [
            'fields' => 'id,name,category,category_list,link,picture{url},access_token,tasks,followers_count,fan_count,is_published,verification_status',
            'limit' => 100,
        ];

        do {
            $response = Http::withToken($accessToken)->get($url, $params);
            if ($response->failed()) {
                $error = $response->json()['error']['message'] ?? $response->body();
                Log::warning("Facebook API getBmClientPages ({$bmId}) error: {$error}");
                break;
            }

            $json = $response->json();
            $data = $json['data'] ?? [];
            foreach ($data as $item) {
                $pages[] = $item;
            }

            $url = $json['paging']['next'] ?? null;
            $params = [];
        } while ($url);

        return $pages;
    }

    /**
     * Đồng bộ toàn bộ các Pages của người dùng từ BMs và Tài khoản cá nhân
     *
     * @param int $userId
     * @param string|null $fallbackUserToken Token cá nhân từ OAuth phòng trường hợp user chưa có TKQC cá nhân
     * @param array|null $userProfile
     * @return array
     */
    public function syncAllPagesForUser(int $userId, ?string $fallbackUserToken = null, ?array $userProfile = null, bool $syncLastPosts = true): array
    {
        $bms = BusinessManager::where('user_id', $userId)
            ->whereNotNull('access_token')
            ->get();

        // Nếu user kết nối OAuth có quyền pages_show_list nhưng chưa có TKQC cá nhân (chưa tạo personal_* BM tương ứng),
        // vẫn lưu token cá nhân để đồng bộ được các Fanpage cá nhân (/me/accounts)
        if ($fallbackUserToken) {
            $expectedPersonalBmId = !empty($userProfile['id']) ? 'personal_' . $userProfile['id'] : null;
            $hasPersonalBm = $expectedPersonalBmId
                ? $bms->contains(fn ($bm) => $bm->bm_id === $expectedPersonalBmId)
                : $bms->contains(fn ($bm) => $bm->is_personal);

            if (!$hasPersonalBm && $this->checkUserPagePermission($fallbackUserToken)) {
                $profile = $userProfile;
                if ($profile === null) {
                    try {
                        $profile = $this->getUserProfile($fallbackUserToken);
                    } catch (Exception $e) {
                        $profile = ['id' => $userId, 'name' => 'Facebook User'];
                    }
                }
                $fbUserId = $profile['id'] ?? $userId;
                $fbUserName = $profile['name'] ?? 'Facebook User';

                $personalBm = BusinessManager::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'bm_id' => 'personal_' . $fbUserId,
                    ],
                    [
                        'name' => 'TK Cá nhân (' . $fbUserName . ')',
                        'access_token' => $fallbackUserToken,
                        'status' => 'active',
                    ]
                );
                $bms->push($personalBm);
            }
        }

        $syncedCount = 0;

        // 1. Đồng bộ toàn bộ Pages thuộc các Business Manager thật trước (owned_pages + client_pages)
        $realBms = $bms->filter(fn ($bm) => !$bm->is_personal);
        $personalBms = $bms->filter(fn ($bm) => $bm->is_personal);

        foreach ($realBms as $bm) {
            $token = $bm->access_token;
            if (empty($token)) {
                continue;
            }

            // Pages BM sở hữu
            try {
                foreach ($this->getBmOwnedPages($bm->bm_id, $token) as $p) {
                    if ($this->upsertFacebookPage($userId, $bm->id, $p, 'owned')) {
                        $syncedCount++;
                    }
                }
            } catch (Exception $e) {
                Log::warning("Lỗi sync BM owned pages ({$bm->name}): " . $e->getMessage());
            }

            // Pages đối tác chia sẻ cho BM
            try {
                foreach ($this->getBmClientPages($bm->bm_id, $token) as $p) {
                    if ($this->upsertFacebookPage($userId, $bm->id, $p, 'client')) {
                        $syncedCount++;
                    }
                }
            } catch (Exception $e) {
                Log::warning("Lỗi sync BM client pages ({$bm->name}): " . $e->getMessage());
            }
        }

        // Tập hợp các Page ID đã thuộc BM (owned / client) để không tạo bản ghi 'personal' trùng lặp
        $bmPageIds = FacebookPage::where('user_id', $userId)
            ->whereIn('source_type', ['owned', 'client'])
            ->pluck('page_id')
            ->unique()
            ->values()
            ->all();

        if (!empty($bmPageIds)) {
            FacebookPage::where('user_id', $userId)
                ->where('source_type', 'personal')
                ->whereIn('page_id', $bmPageIds)
                ->delete();
        }

        $bmPageIdSet = array_flip($bmPageIds);

        // 2. Đồng bộ Pages cá nhân (/me/accounts), bỏ qua những Page đã nằm trong BM
        foreach ($personalBms as $bm) {
            $token = $bm->access_token;
            if (empty($token)) {
                continue;
            }

            try {
                foreach ($this->getUserPages($token) as $p) {
                    $pId = (string) ($p['id'] ?? '');
                    if (empty($pId)) {
                        continue;
                    }

                    if (isset($bmPageIdSet[$pId])) {
                        // Cập nhật lại Page Access Token mới cho bản ghi BM (hữu ích khi vừa cấp thêm quyền đăng bài)
                        $this->syncBmPageTokenIfPresent($userId, $pId, $p['access_token'] ?? null);
                        continue;
                    }

                    if ($this->upsertFacebookPage($userId, $bm->id, $p, 'personal')) {
                        $syncedCount++;
                    }
                }
            } catch (Exception $e) {
                Log::warning("Lỗi sync user pages: " . $e->getMessage());
            }
        }

        // 3. Cập nhật thời gian bài đăng gần nhất cho các Pages có Page Access Token (khi yêu cầu)
        if ($syncLastPosts) {
            $this->syncLastPostTimesForUser($userId);
        }

        return [
            'synced_count' => $syncedCount,
            'total_pages' => FacebookPage::where('user_id', $userId)->distinct('page_id')->count('page_id'),
        ];
    }

    /**
     * Lưu hoặc cập nhật một bản ghi FacebookPage từ dữ liệu Graph API
     */
    public function upsertFacebookPage(int $userId, int $businessManagerId, array $pageData, string $sourceType): bool
    {
        $pageId = (string) ($pageData['id'] ?? '');
        if (empty($pageId)) {
            return false;
        }

        $pictureUrl = $pageData['picture']['data']['url'] ?? ($pageData['picture']['url'] ?? null);

        $attributes = [
            'name' => $pageData['name'] ?? 'Facebook Page',
            'category' => $pageData['category'] ?? null,
            'category_list' => $pageData['category_list'] ?? null,
            'link' => $pageData['link'] ?? "https://www.facebook.com/{$pageId}",
            'picture_url' => $pictureUrl,
            'tasks' => $pageData['tasks'] ?? null,
            'followers_count' => (int) ($pageData['followers_count'] ?? 0),
            'fan_count' => (int) ($pageData['fan_count'] ?? 0),
            'is_published' => (bool) ($pageData['is_published'] ?? true),
            'verification_status' => $pageData['verification_status'] ?? 'not_verified',
            'source_type' => $sourceType,
        ];

        if (!empty($pageData['access_token'])) {
            $attributes['access_token'] = $pageData['access_token'];
        }

        FacebookPage::updateOrCreate(
            [
                'user_id' => $userId,
                'page_id' => $pageId,
                'business_manager_id' => $businessManagerId,
            ],
            $attributes
        );

        return true;
    }

    /**
     * Cập nhật Page Access Token mới cho các bản ghi Page thuộc BM nếu /me/accounts trả về token mới
     */
    public function syncBmPageTokenIfPresent(int $userId, string $pageId, ?string $pageAccessToken): int
    {
        if (empty($pageAccessToken)) {
            return 0;
        }

        $bmPages = FacebookPage::where('user_id', $userId)
            ->where('page_id', $pageId)
            ->whereIn('source_type', ['owned', 'client'])
            ->get();

        $updated = 0;
        foreach ($bmPages as $bmPage) {
            if ($bmPage->access_token !== $pageAccessToken) {
                $bmPage->update(['access_token' => $pageAccessToken]);
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Lấy thời gian bài đăng gần nhất (published_posts) cho các Pages của User qua Http::pool()
     *
     * @param int $userId
     * @param array|null $pageIds Giới hạn theo danh sách page_id cụ thể (nếu có)
     * @param bool $onlyUnsynced Chỉ lấy các Page chưa từng đồng bộ thời gian đăng bài
     * @return array{updated_count: int, posts: array<string, array>}
     */
    public function syncLastPostTimesForUser(int $userId, ?array $pageIds = null, bool $onlyUnsynced = false): array
    {
        $query = FacebookPage::where('user_id', $userId);

        if (!empty($pageIds)) {
            $query->whereIn('page_id', array_values(array_unique(array_map('strval', $pageIds))));
        }

        if ($onlyUnsynced) {
            $query->whereNull('last_post_synced_at');
        }

        $uniquePages = $query->get()
            ->filter(fn ($p) => !empty($p->access_token))
            ->unique('page_id')
            ->values();

        if ($uniquePages->isEmpty()) {
            return ['updated_count' => 0, 'posts' => []];
        }

        $updatedCount = 0;
        $posts = [];

        foreach ($uniquePages->chunk(20) as $chunk) {
            try {
                $responses = Http::pool(function (Pool $pool) use ($chunk) {
                    foreach ($chunk as $page) {
                        $pool->as((string) $page->page_id)
                            ->withToken($page->access_token)
                            ->timeout(8)
                            ->connectTimeout(4)
                            ->get("{$this->baseUrl}/{$page->page_id}/published_posts", [
                                'fields' => 'id,created_time,permalink_url',
                                'limit' => 1,
                            ]);
                    }
                });
            } catch (\Throwable $e) {
                Log::warning('Lỗi Http::pool syncLastPostTimesForUser: ' . $e->getMessage());
                continue;
            }

            foreach ($chunk as $page) {
                $pId = (string) $page->page_id;
                $res = $responses[$pId] ?? null;

                if ($res instanceof \Throwable || !$res || $res->failed()) {
                    continue;
                }

                $post = $res->json('data.0');
                $lastPostAt = null;
                $lastPostUrl = null;

                if (is_array($post) && !empty($post['created_time'])) {
                    try {
                        $lastPostAt = Carbon::parse($post['created_time'])->utc();
                    } catch (\Throwable $e) {
                        $lastPostAt = null;
                    }
                    $lastPostUrl = $post['permalink_url'] ?? null;
                }

                $syncedAt = now();

                FacebookPage::where('user_id', $userId)
                    ->where('page_id', $pId)
                    ->update([
                        'last_post_at' => $lastPostAt,
                        'last_post_url' => $lastPostUrl,
                        'last_post_synced_at' => $syncedAt,
                    ]);

                $posts[$pId] = self::formatLastPostPayload($lastPostAt, $lastPostUrl);
                $updatedCount++;
            }
        }

        return [
            'updated_count' => $updatedCount,
            'posts' => $posts,
        ];
    }

    /**
     * Chuẩn hóa dữ liệu hiển thị Lần đăng gần nhất
     */
    public static function formatLastPostPayload(?Carbon $lastPostAt, ?string $lastPostUrl = null): array
    {
        if (!$lastPostAt) {
            return [
                'has_post' => false,
                'date_text' => 'Chưa có bài đăng',
                'relative_text' => '',
                'permalink_url' => null,
            ];
        }

        return [
            'has_post' => true,
            'date_text' => $lastPostAt->copy()->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i'),
            'relative_text' => $lastPostAt->copy()->locale('vi')->diffForHumans(),
            'permalink_url' => $lastPostUrl,
        ];
    }

    /**
     * Đăng bài hoặc hẹn giờ đăng bài lên một Facebook Page qua Graph API
     *
     * @param FacebookPage $page
     * @param string|null $message
     * @param string|null $linkUrl
     * @param array $mediaSources Danh sách ảnh: ['type' => 'file', 'path' => ..., 'name' => ...] hoặc ['type' => 'url', 'url' => ...]
     * @param Carbon|null $scheduledAt Thời gian hẹn giờ (UTC). Null = đăng ngay.
     * @return array{fb_post_id: string|null, permalink_url: string|null}
     * @throws Exception
     */
    public function publishToPage(
        FacebookPage $page,
        ?string $message,
        ?string $linkUrl = null,
        array $mediaSources = [],
        ?Carbon $scheduledAt = null
    ): array {
        if (empty($page->access_token)) {
            throw new Exception("Page [{$page->name}] chưa có Page Access Token khả dụng.");
        }

        $pageToken = $page->access_token;
        $attachedMedia = [];

        if (!empty($mediaSources)) {
            foreach ($mediaSources as $source) {
                $photoRes = $this->uploadUnpublishedPhotoToPage($page->page_id, $pageToken, $source);
                $photoId = $photoRes['id'] ?? null;
                if (empty($photoId)) {
                    throw new Exception('Facebook không trả về ID ảnh sau khi tải lên.');
                }
                $attachedMedia[] = ['media_fbid' => (string) $photoId];
            }
        }

        $payload = [];
        if ($message !== null && trim($message) !== '') {
            $payload['message'] = $message;
        }

        if (!empty($attachedMedia)) {
            $payload['attached_media'] = $attachedMedia;
        } elseif (!empty($linkUrl)) {
            $payload['link'] = $linkUrl;
        }

        if ($scheduledAt !== null) {
            $payload['published'] = false;
            $payload['scheduled_publish_time'] = $scheduledAt->timestamp;
            $payload['unpublished_content_type'] = 'SCHEDULED';
        }

        $response = Http::withToken($pageToken)
            ->timeout(25)
            ->post("{$this->baseUrl}/{$page->page_id}/feed", $payload);

        if ($response->failed()) {
            $error = $response->json()['error']['message'] ?? $response->body();
            throw new Exception(self::translatePublishError((string) $error));
        }

        $fbPostId = $response->json('id');
        $permalinkUrl = null;
        if (!empty($fbPostId) && $scheduledAt === null) {
            $permalinkUrl = "https://www.facebook.com/{$fbPostId}";
        }

        return [
            'fb_post_id' => $fbPostId ? (string) $fbPostId : null,
            'permalink_url' => $permalinkUrl,
        ];
    }

    /**
     * Tải ảnh lên Page ở trạng thái unpublished để đính kèm vào bài viết (hỗ trợ cả đăng ngay & hẹn giờ)
     *
     * @throws Exception
     */
    protected function uploadUnpublishedPhotoToPage(string $pageId, string $pageToken, array $source): array
    {
        $endpoint = "{$this->baseUrl}/{$pageId}/photos";

        if (($source['type'] ?? '') === 'file' && !empty($source['path']) && is_file($source['path'])) {
            $handle = fopen($source['path'], 'r');
            try {
                $response = Http::withToken($pageToken)
                    ->timeout(30)
                    ->attach('source', $handle, $source['name'] ?? basename($source['path']))
                    ->post($endpoint, [
                        'published' => 'false',
                        'temporary' => 'true',
                    ]);
            } finally {
                if (is_resource($handle)) {
                    fclose($handle);
                }
            }
        } elseif (($source['type'] ?? '') === 'url' && !empty($source['url'])) {
            $response = Http::withToken($pageToken)
                ->timeout(30)
                ->post($endpoint, [
                    'url' => $source['url'],
                    'published' => false,
                    'temporary' => true,
                ]);
        } else {
            throw new Exception('Nguồn ảnh tải lên không hợp lệ.');
        }

        if ($response->failed()) {
            $error = $response->json()['error']['message'] ?? $response->body();
            throw new Exception(self::translatePublishError((string) $error));
        }

        return $response->json() ?? [];
    }

    /**
     * Hủy (xóa) bài viết đang hẹn giờ trên Facebook Page
     *
     * @throws Exception
     */
    public function deletePagePost(string $fbPostId, string $pageAccessToken): bool
    {
        $response = Http::withToken($pageAccessToken)
            ->timeout(15)
            ->delete("{$this->baseUrl}/{$fbPostId}");

        if ($response->failed()) {
            $error = $response->json()['error']['message'] ?? $response->body();
            throw new Exception(self::translatePublishError((string) $error));
        }

        return true;
    }

    /**
     * Kiểm tra và cập nhật trạng thái thực tế từ Facebook cho các bài đang ở trạng thái 'scheduled'
     *
     * @param int $userId
     * @param bool $onlyDue Chỉ kiểm tra các bài đã tới/qua giờ hẹn đăng (scheduled_at <= now)
     * @return array{checked_count: int, published_count: int}
     */
    public function syncScheduledPostsStatusForUser(int $userId, bool $onlyDue = true): array
    {
        $query = PagePost::where('user_id', $userId)
            ->where('status', 'scheduled')
            ->whereNotNull('fb_post_id');

        if ($onlyDue) {
            $query->where('scheduled_at', '<=', now());
        }

        $scheduledPosts = $query->get();
        if ($scheduledPosts->isEmpty()) {
            return ['checked_count' => 0, 'published_count' => 0];
        }

        $pageTokens = FacebookPage::where('user_id', $userId)
            ->whereIn('page_id', $scheduledPosts->pluck('page_id')->unique()->all())
            ->get()
            ->filter(fn ($p) => !empty($p->access_token))
            ->keyBy('page_id');

        $eligiblePosts = $scheduledPosts->filter(fn ($p) => $pageTokens->has($p->page_id))->values();
        if ($eligiblePosts->isEmpty()) {
            return ['checked_count' => 0, 'published_count' => 0];
        }

        $checkedCount = 0;
        $publishedCount = 0;

        foreach ($eligiblePosts->chunk(20) as $chunk) {
            try {
                $responses = Http::pool(function (Pool $pool) use ($chunk, $pageTokens) {
                    foreach ($chunk as $post) {
                        $token = $pageTokens->get($post->page_id)->access_token;
                        $pool->as((string) $post->id)
                            ->withToken($token)
                            ->timeout(8)
                            ->connectTimeout(4)
                            ->get("{$this->baseUrl}/{$post->fb_post_id}", [
                                'fields' => 'id,is_published,created_time,permalink_url',
                            ]);
                    }
                });
            } catch (\Throwable $e) {
                Log::warning('Lỗi Http::pool syncScheduledPostsStatusForUser: ' . $e->getMessage());
                continue;
            }

            foreach ($chunk as $post) {
                $res = $responses[(string) $post->id] ?? null;
                if ($res instanceof \Throwable || !$res) {
                    continue;
                }

                $checkedCount++;

                if ($res->successful()) {
                    $json = $res->json() ?? [];
                    $isPublished = $json['is_published'] ?? null;
                    $permalinkUrl = $json['permalink_url'] ?? $post->permalink_url;

                    if ($isPublished === true) {
                        $publishedAt = $post->scheduled_at ?? now();
                        if (!empty($json['created_time'])) {
                            try {
                                $publishedAt = Carbon::parse($json['created_time'])->utc();
                            } catch (\Throwable $e) {
                                // giữ nguyên fallback
                            }
                        }

                        $finalPermalink = $permalinkUrl ?: "https://www.facebook.com/{$post->fb_post_id}";

                        $post->update([
                            'status' => 'published',
                            'published_at' => $publishedAt,
                            'permalink_url' => $finalPermalink,
                        ]);

                        $matchingPages = FacebookPage::where('user_id', $userId)
                            ->where('page_id', $post->page_id)
                            ->get();

                        foreach ($matchingPages as $fp) {
                            if ($fp->last_post_at === null || $fp->last_post_at->lt($publishedAt)) {
                                $fp->update([
                                    'last_post_at' => $publishedAt,
                                    'last_post_url' => $finalPermalink,
                                    'last_post_synced_at' => now(),
                                ]);
                            }
                        }

                        $publishedCount++;
                    } elseif (!empty($permalinkUrl) && empty($post->permalink_url)) {
                        $post->update(['permalink_url' => $permalinkUrl]);
                    }
                } elseif (in_array($res->status(), [400, 404], true)) {
                    $errorJson = $res->json('error') ?? [];
                    $errorCode = (int) ($errorJson['code'] ?? 0);
                    $errorMsg = mb_strtolower((string) ($errorJson['message'] ?? ''));

                    if ($errorCode === 100 && str_contains($errorMsg, 'does not exist')) {
                        $post->update(['status' => 'cancelled']);
                    }
                }
            }
        }

        return [
            'checked_count' => $checkedCount,
            'published_count' => $publishedCount,
        ];
    }

    /**
     * Chuyển đổi thông báo lỗi tiếng Anh từ Meta Graph API sang tiếng Việt rõ ràng
     */
    public static function translatePublishError(string $rawError): string
    {
        $clean = trim($rawError);
        $lower = mb_strtolower($clean);

        if (str_contains($lower, 'scheduled publish time') || str_contains($lower, 'scheduled_publish_time')) {
            return 'Thời gian hẹn giờ không hợp lệ: Facebook yêu cầu thời điểm hẹn đăng phải sau hiện tại ít nhất 10 phút và tối đa 30 ngày.';
        }

        if (str_contains($lower, 'pages_manage_posts') || str_contains($lower, 'permissions error') || str_contains($lower, '(#200)') || str_contains($lower, 'has not authorized') || str_contains($lower, "hasn't authorized")) {
            return 'Thiếu quyền đăng bài (pages_manage_posts) hoặc tài khoản chưa cấp quyền cho Trang này. Vui lòng bấm "Cấp quyền đăng bài" và chọn lại Trang.';
        }

        if (str_contains($lower, 'error validating access token') || str_contains($lower, 'session has expired') || str_contains($lower, 'invalid oauth access token')) {
            return 'Access Token đã hết hạn hoặc bị thu hồi. Vui lòng cấp quyền lại qua Facebook.';
        }

        if (str_contains($lower, 'a page access token is required') || str_contains($lower, '(#210)') || str_contains($lower, 'chưa có page access token')) {
            return 'Trang này chưa có Page Access Token hợp lệ. Vui lòng đồng bộ lại Pages hoặc cấp quyền lại qua Facebook.';
        }

        if (str_contains($lower, 'could not be downloaded') || str_contains($lower, 'missing or invalid image') || str_contains($lower, 'invalid image')) {
            return 'Không thể tải hoặc xử lý hình ảnh đính kèm. Vui lòng kiểm tra lại file ảnh hoặc đường dẫn ảnh.';
        }

        if (str_contains($lower, '(#368)') || str_contains($lower, 'action blocked') || str_contains($lower, 'spam') || str_contains($lower, 'rate limit') || str_contains($lower, 'too many')) {
            return 'Facebook tạm giới hạn do thao tác quá nhanh hoặc nghi ngờ spam. Vui lòng thử lại sau vài phút hoặc tăng giãn cách giữa các Trang.';
        }

        if (str_contains($lower, 'does not have permission') || str_contains($lower, 'restrict')) {
            return 'Tài khoản Facebook hiện tại không đủ quyền đăng bài lên Trang này (hoặc Trang đang bị hạn chế).';
        }

        if (preg_match('/^(Thời gian|Thiếu quyền|Access Token|Trang này|Không thể|Facebook tạm|Tài khoản|Page \[)/u', $clean) && !str_contains($lower, '(#')) {
            return $clean;
        }

        return 'Lỗi từ Facebook API: ' . $clean;
    }

    /**
     * Kiểm tra bộ lọc có đang xem Toàn thời gian (đọc từ DB) hay không
     */
    public function isLifetimeSpendFilter(string $datePreset = 'maximum', ?string $since = null): bool
    {
        return $datePreset === 'maximum' || (empty($datePreset) && empty($since));
    }

    /**
     * Tạo cache key theo version của từng BM để có thể xóa cache theo BM trên mọi Cache driver
     */
    public function getBmSpendCacheKey(int $bmId, string $datePreset = 'maximum', ?string $since = null, ?string $until = null): string
    {
        $version = (int) Cache::get("bm_spend_ver_{$bmId}", 1);
        $sinceNorm = $since ?? '';
        $untilNorm = $until ?? '';

        return "bm_spend_v{$version}_{$bmId}_{$datePreset}_{$sinceNorm}_{$untilNorm}";
    }

    /**
     * Tăng version cache chi tiêu cho BM sau khi đồng bộ dữ liệu mới
     */
    public function clearBmSpendCache(int $bmId): void
    {
        $verKey = "bm_spend_ver_{$bmId}";
        $nextVersion = ((int) Cache::get($verKey, 1)) + 1;
        Cache::put($verKey, $nextVersion, 86400 * 30);
    }

    /**
     * Trả về dữ liệu chi tiêu BM đã lưu trong cache (nếu có) mà không gọi API
     */
    public function getCachedBmSpend(BusinessManager $bm, string $datePreset = 'maximum', ?string $since = null, ?string $until = null): ?array
    {
        if ($this->isLifetimeSpendFilter($datePreset, $since)) {
            return $this->getBmSpendFromDatabase($bm);
        }

        if ($datePreset === 'custom' && (empty($since) || empty($until))) {
            return $this->emptyBmSpendResult();
        }

        if ($this->isIneligibleForSpendApi($bm)) {
            return $this->emptyBmSpendResult();
        }

        $cached = Cache::get($this->getBmSpendCacheKey($bm->id, $datePreset, $since, $until));

        return is_array($cached) ? $cached : null;
    }

    /**
     * Lấy chi phí quảng cáo theo khoảng thời gian cho 1 BM (tái sử dụng logic pool + cache)
     */
    public function getBmSpendByDate(BusinessManager $bm, string $datePreset = 'maximum', ?string $since = null, ?string $until = null, bool $forceRefresh = false): array
    {
        $results = $this->getMultipleBmSpendsByDate([$bm], $datePreset, $since, $until, $forceRefresh);

        return $results[$bm->id] ?? $this->emptyBmSpendResult();
    }

    /**
     * Lấy chi phí quảng cáo theo khoảng thời gian cho nhiều BM đồng thời qua Http::pool()
     *
     * @param iterable<BusinessManager> $bms
     * @return array<int, array>
     */
    public function getMultipleBmSpendsByDate(iterable $bms, string $datePreset = 'maximum', ?string $since = null, ?string $until = null, bool $forceRefresh = false): array
    {
        $results = [];

        if ($this->isLifetimeSpendFilter($datePreset, $since)) {
            foreach ($bms as $bm) {
                $results[$bm->id] = $this->getBmSpendFromDatabase($bm);
            }
            return $results;
        }

        if ($datePreset === 'custom' && (empty($since) || empty($until))) {
            foreach ($bms as $bm) {
                $results[$bm->id] = $this->emptyBmSpendResult();
            }
            return $results;
        }

        $insightField = ($datePreset === 'custom' && $since && $until)
            ? "insights.time_range({'since':'{$since}','until':'{$until}'}){spend}"
            : "insights.date_preset({$datePreset}){spend}";

        $pendingRequests = [];
        $accumulators = [];
        $bmNames = [];

        foreach ($bms as $bm) {
            $cacheKey = $this->getBmSpendCacheKey($bm->id, $datePreset, $since, $until);

            if (!$forceRefresh) {
                $cached = Cache::get($cacheKey);
                if (is_array($cached)) {
                    $results[$bm->id] = $cached;
                    continue;
                }
            }

            if ($this->isIneligibleForSpendApi($bm)) {
                $results[$bm->id] = $this->emptyBmSpendResult();
                continue;
            }

            // Bỏ qua các nhánh (owned / client) có 0 tài khoản trong DB để tránh request thừa
            $edgeMap = [
                'owned' => ['has_accounts' => $bm->owned_accounts_count > 0, 'endpoint' => 'owned_ad_accounts'],
                'client' => ['has_accounts' => $bm->client_accounts_count > 0, 'endpoint' => 'client_ad_accounts'],
            ];

            $hasAnyEdge = false;
            foreach ($edgeMap as $type => $edge) {
                if (!$edge['has_accounts']) {
                    continue;
                }
                $hasAnyEdge = true;
                $pendingRequests["{$bm->id}:{$type}"] = [
                    'bm_id' => $bm->id,
                    'type' => $type,
                    'token' => $bm->access_token,
                    'url' => "{$this->baseUrl}/{$bm->bm_id}/{$edge['endpoint']}",
                    'params' => ['fields' => "id,name,{$insightField}", 'limit' => 100],
                ];
            }

            if (!$hasAnyEdge) {
                $empty = $this->emptyBmSpendResult();
                Cache::put($cacheKey, $empty, self::BM_SPEND_CACHE_TTL);
                $results[$bm->id] = $empty;
                continue;
            }

            $bmNames[$bm->id] = $bm->name;
            $accumulators[$bm->id] = [
                'owned_spend' => 0.0,
                'client_spend' => 0.0,
                'account_spends' => [],
                'has_error' => false,
            ];
        }

        // Thực thi song song toàn bộ request (và xử lý phân trang paging.next nếu có)
        $round = 0;
        $maxPages = 10;
        $visitedUrls = [];
        while (!empty($pendingRequests) && $round < $maxPages) {
            $round++;
            $currentBatch = $pendingRequests;
            $pendingRequests = [];

            try {
                $responses = Http::pool(function (Pool $pool) use ($currentBatch) {
                    foreach ($currentBatch as $key => $req) {
                        $pending = $pool->as($key)
                            ->withToken($req['token'])
                            ->timeout(12)
                            ->connectTimeout(5);

                        // Chỉ truyền $params ở trang 1; từ paging.next giữ nguyên URL đầy đủ để tránh ghi đè query string
                        if (!empty($req['params'])) {
                            $pending->get($req['url'], $req['params']);
                        } else {
                            $pending->get($req['url']);
                        }
                    }
                });
            } catch (\Throwable $e) {
                Log::warning('Lỗi Http::pool getMultipleBmSpendsByDate: ' . $e->getMessage());
                foreach ($currentBatch as $req) {
                    $accumulators[$req['bm_id']]['has_error'] = true;
                }
                break;
            }

            foreach ($currentBatch as $key => $req) {
                $bmId = $req['bm_id'];
                $type = $req['type'];
                $visitedUrls[$key][$req['url']] = true;
                $res = $responses[$key] ?? null;

                if ($res instanceof \Throwable || !$res || $res->failed()) {
                    $errorDetail = $res instanceof \Throwable
                        ? $res->getMessage()
                        : ($res ? ($res->json()['error']['message'] ?? ('HTTP ' . $res->status())) : 'Empty response');
                    Log::warning("Lỗi getBmSpendByDate {$type} ({$bmNames[$bmId]}): {$errorDetail}");
                    $accumulators[$bmId]['has_error'] = true;
                    continue;
                }

                $json = $res->json();
                foreach ($json['data'] ?? [] as $acc) {
                    $spend = isset($acc['insights']['data'][0]['spend']) ? (float) $acc['insights']['data'][0]['spend'] : 0.0;
                    $accumulators[$bmId]['account_spends'][$acc['id']] = $spend;
                    if ($type === 'client') {
                        $accumulators[$bmId]['client_spend'] += $spend;
                    } else {
                        $accumulators[$bmId]['owned_spend'] += $spend;
                    }
                }

                $nextUrl = $json['paging']['next'] ?? null;
                if (!empty($nextUrl) && empty($visitedUrls[$key][$nextUrl])) {
                    $pendingRequests[$key] = [
                        'bm_id' => $bmId,
                        'type' => $type,
                        'token' => $req['token'],
                        'url' => $nextUrl,
                        'params' => [],
                    ];
                }
            }
        }

        foreach ($accumulators as $bmId => $acc) {
            $data = [
                'total_spend' => $acc['owned_spend'] + $acc['client_spend'],
                'owned_spend' => $acc['owned_spend'],
                'client_spend' => $acc['client_spend'],
                'account_spends' => $acc['account_spends'],
                'is_lifetime' => false,
                'has_error' => $acc['has_error'],
            ];

            if (!$acc['has_error']) {
                Cache::put(
                    $this->getBmSpendCacheKey($bmId, $datePreset, $since, $until),
                    $data,
                    self::BM_SPEND_CACHE_TTL
                );
            }

            $results[$bmId] = $data;
        }

        return $results;
    }

    /**
     * Lấy chi tiêu Toàn thời gian trực tiếp từ DB local
     */
    protected function getBmSpendFromDatabase(BusinessManager $bm): array
    {
        $accountSpends = [];
        $ownedSpend = 0.0;
        $clientSpend = 0.0;

        foreach ($bm->adAccounts as $acc) {
            $spend = (float) $acc->amount_spent;
            $accountSpends[$acc->ad_account_id] = $spend;
            if ($acc->source_type === 'client') {
                $clientSpend += $spend;
            } else {
                $ownedSpend += $spend;
            }
        }

        return [
            'total_spend' => $ownedSpend + $clientSpend,
            'owned_spend' => $ownedSpend,
            'client_spend' => $clientSpend,
            'account_spends' => $accountSpends,
            'is_lifetime' => true,
            'has_error' => false,
        ];
    }

    /**
     * Kiểm tra BM có đủ điều kiện gọi API chi tiêu không
     */
    protected function isIneligibleForSpendApi(BusinessManager $bm): bool
    {
        return empty($bm->access_token)
            || empty($bm->bm_id)
            || $bm->is_personal
            || str_starts_with((string) $bm->bm_id, 'fb_');
    }

    /**
     * Cấu trúc dữ liệu chi tiêu rỗng
     */
    protected function emptyBmSpendResult(): array
    {
        return [
            'total_spend' => 0.0,
            'owned_spend' => 0.0,
            'client_spend' => 0.0,
            'account_spends' => [],
            'is_lifetime' => false,
            'has_error' => false,
        ];
    }

    /**
     * Trích xuất thông tin thẻ thanh toán từ funding_source_details của Meta API
     */
    public static function extractCardDetails(?array $fundingSourceDetails): array
    {
        $displayString = is_array($fundingSourceDetails) ? ($fundingSourceDetails['display_string'] ?? null) : null;
        $lastFour = null;
        if ($displayString && preg_match('/\*(\d{4})$/', $displayString, $matches)) {
            $lastFour = $matches[1];
        } elseif ($displayString && preg_match('/(\d{4})$/', $displayString, $matches)) {
            $lastFour = $matches[1];
        }

        return [
            'payment_method_display' => $displayString,
            'card_last_four' => $lastFour,
        ];
    }

    /**
     * Đảm bảo Ad Account ID luôn có tiền tố act_
     */
    protected function formatAccountId(string $adAccountId): string
    {
        return str_starts_with($adAccountId, 'act_') ? $adAccountId : 'act_' . $adAccountId;
    }
}
