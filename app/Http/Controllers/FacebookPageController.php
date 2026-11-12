<?php

namespace App\Http\Controllers;

use App\Models\BusinessManager;
use App\Models\FacebookPage;
use App\Services\FacebookAdsService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FacebookPageController extends Controller
{
    protected FacebookAdsService $fbService;

    public function __construct(FacebookAdsService $fbService)
    {
        $this->fbService = $fbService;
    }

    /**
     * Danh sách Facebook Pages của người dùng
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        // 1. Lấy toàn bộ bản ghi Page của User để khử trùng lặp (1 Page thuộc BM và /me/accounts) và tính thống kê chuẩn
        $allUserPages = FacebookPage::where('user_id', $userId)
            ->with('businessManager')
            ->get();

        $sourceScore = ['owned' => 3, 'client' => 2, 'personal' => 1];
        $uniquePages = $allUserPages->groupBy('page_id')->map(function ($records) use ($sourceScore) {
            return $records->sort(function ($a, $b) use ($sourceScore) {
                $scoreA = $sourceScore[$a->source_type] ?? 0;
                $scoreB = $sourceScore[$b->source_type] ?? 0;
                if ($scoreA !== $scoreB) {
                    return $scoreB <=> $scoreA;
                }
                return $b->id <=> $a->id;
            })->first();
        });

        // Map các BM thật khác cùng sở hữu/quản lý 1 Page (dùng để hiển thị badge phụ)
        $otherBmsByPageId = [];
        foreach ($allUserPages as $p) {
            if ($p->businessManager && !$p->businessManager->is_personal) {
                $otherBmsByPageId[$p->page_id][] = [
                    'bm_id' => $p->business_manager_id,
                    'bm_name' => $p->businessManager->name,
                    'source_type' => $p->source_type,
                ];
            }
        }

        // 2. Xây dựng Query hiển thị (khử trùng lặp theo page_id khi xem tất cả BM)
        if ($request->filled('bm_id')) {
            $query = FacebookPage::where('user_id', $userId)
                ->where('business_manager_id', $request->get('bm_id'))
                ->with('businessManager');
        } else {
            $preferredIdsSubquery = FacebookPage::where('user_id', $userId)
                ->select('id', 'page_id')
                ->selectRaw("ROW_NUMBER() OVER (
                    PARTITION BY page_id
                    ORDER BY
                        CASE source_type
                            WHEN 'owned' THEN 1
                            WHEN 'client' THEN 2
                            WHEN 'personal' THEN 3
                            ELSE 4
                        END ASC,
                        id DESC
                ) as rn");

            $preferredIdsQuery = DB::table(DB::raw("({$preferredIdsSubquery->toSql()}) as ranked"))
                ->mergeBindings($preferredIdsSubquery->getQuery())
                ->where('rn', 1)
                ->select('id');

            $query = FacebookPage::whereIn('id', $preferredIdsQuery)
                ->with('businessManager');
        }

        // Tìm kiếm theo tên hoặc Page ID hoặc chuyên mục
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('page_id', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Lọc theo Nguồn (owned, client, personal)
        if ($request->filled('source_type')) {
            $query->where('source_type', $request->get('source_type'));
        }

        // Lọc theo trạng thái hoạt động (published)
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'published') {
                $query->where('is_published', true);
            } elseif ($status === 'unpublished') {
                $query->where('is_published', false);
            }
        }

        $pages = $query->orderByDesc('is_hot')->latest('id')->paginate(15)->withQueryString();

        // 3. Thống kê nhanh toàn bộ Pages duy nhất của User
        $stats = [
            'total' => $uniquePages->count(),
            'published' => $uniquePages->where('is_published', true)->count(),
            'total_fans' => $uniquePages->sum('fan_count'),
            'total_followers' => $uniquePages->sum('followers_count'),
            'owned_count' => $uniquePages->where('source_type', 'owned')->count(),
            'client_count' => $uniquePages->where('source_type', 'client')->count(),
            'personal_count' => $uniquePages->where('source_type', 'personal')->count(),
            'hot_count' => $uniquePages->where('is_hot', true)->count(),
        ];

        // Danh sách BM để đưa vào bộ lọc
        $bms = BusinessManager::where('user_id', $userId)
            ->realBms()
            ->orderBy('name')
            ->get();

        // Kiểm tra xem token cá nhân của user đã cấp quyền đọc Pages (pages_show_list) chưa
        $personalBm = BusinessManager::where('user_id', $userId)->personal()->latest('updated_at')->first();
        $hasPersonalPagePermission = false;
        if ($personalBm && !empty($personalBm->access_token)) {
            $hasPersonalPagePermission = $this->fbService->checkUserPagePermission($personalBm->access_token);
        }

        $hasPendingLastPostSync = $pages->getCollection()
            ->contains(fn ($p) => !empty($p->access_token) && $p->last_post_synced_at === null);

        return view('pages.index', compact('pages', 'stats', 'bms', 'hasPersonalPagePermission', 'personalBm', 'otherBmsByPageId', 'hasPendingLastPostSync'));
    }

    /**
     * Đồng bộ Pages từ Facebook về hệ thống
     */
    public function sync(Request $request)
    {
        $userId = Auth::id();

        try {
            $result = $this->fbService->syncAllPagesForUser($userId);
            $syncedCount = $result['synced_count'] ?? 0;
            $totalCount = $result['total_pages'] ?? 0;

            return back()->with('success', "Đã đồng bộ thành công {$syncedCount} lượt Page từ Facebook! Hiện có tổng cộng {$totalCount} Pages.");
        } catch (Exception $e) {
            return back()->with('error', "Không thể đồng bộ Pages: " . $e->getMessage());
        }
    }

    /**
     * API AJAX lấy thời gian bài đăng gần nhất cho danh sách Pages của User
     */
    public function syncLastPosts(Request $request)
    {
        $userId = Auth::id();
        $pageIds = $request->input('page_ids');
        $onlyUnsynced = $request->boolean('only_unsynced');

        $result = $this->fbService->syncLastPostTimesForUser(
            $userId,
            is_array($pageIds) ? $pageIds : null,
            $onlyUnsynced
        );

        return response()->json([
            'success' => true,
            'updated_count' => $result['updated_count'],
            'posts' => $result['posts'],
        ]);
    }

    /**
     * Đồng bộ Pages cá nhân bằng Access Token trực tiếp
     */
    public function storeToken(Request $request)
    {
        $request->validate([
            'access_token' => 'required|string',
        ]);

        $userId = Auth::id();
        $token = trim($request->access_token);

        try {
            // Lấy danh sách pages từ token này
            $userPages = $this->fbService->getUserPages($token);

            if (empty($userPages)) {
                return back()->with('warning', 'Không tìm thấy Page nào từ Token này hoặc Token thiếu quyền "pages_show_list". Vui lòng kiểm tra lại quyền hạn của Token.');
            }

            // Lưu/Cập nhật Token cho BM cá nhân
            $personalBm = BusinessManager::where('user_id', $userId)->personal()->latest('updated_at')->first();
            if ($personalBm) {
                $personalBm->update(['access_token' => $token]);
            } else {
                $userProfile = $this->fbService->getUserProfile($token);
                $fbId = $userProfile['id'] ?? $userId;
                $personalBm = BusinessManager::create([
                    'user_id' => $userId,
                    'name' => 'TK Cá nhân (' . ($userProfile['name'] ?? Auth::user()->name) . ')',
                    'bm_id' => 'personal_' . $fbId,
                    'access_token' => $token,
                    'status' => 'active',
                ]);
            }

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

            $count = 0;
            $syncedPageIds = [];
            foreach ($userPages as $p) {
                $pId = (string) ($p['id'] ?? '');
                if (empty($pId)) {
                    continue;
                }

                if (isset($bmPageIdSet[$pId])) {
                    if ($this->fbService->syncBmPageTokenIfPresent($userId, $pId, $p['access_token'] ?? null) > 0) {
                        $count++;
                    }
                    $syncedPageIds[] = $pId;
                    continue;
                }

                if ($this->fbService->upsertFacebookPage($userId, $personalBm->id, $p, 'personal')) {
                    $count++;
                    $syncedPageIds[] = $pId;
                }
            }

            if (!empty($syncedPageIds)) {
                $this->fbService->syncLastPostTimesForUser($userId, $syncedPageIds);
            }

            return back()->with('success', "Đã cập nhật Token & đồng bộ thành công {$count} Facebook Pages!");
        } catch (Exception $e) {
            return back()->with('error', "Lỗi xử lý Token cá nhân: " . $e->getMessage());
        }
    }

    /**
     * Thêm Trang cá nhân thủ công bằng Page ID
     */
    public function storePage(Request $request)
    {
        $request->validate([
            'page_id' => 'required|string|max:100',
            'name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
        ]);

        $userId = Auth::id();
        $pageId = trim($request->page_id);
        $name = trim($request->name ?? '');

        // Tìm personal BM
        $personalBm = BusinessManager::where('user_id', $userId)->personal()->first();

        // Thử lấy thông tin tự động từ Graph API nếu có token khả dụng
        $pictureUrl = null;
        $category = trim($request->category ?? '');
        $followersCount = 0;
        $fanCount = 0;
        $isPublished = true;

        if ($personalBm && !empty($personalBm->access_token)) {
            try {
                $version = config('services.meta.version', 'v21.0');
                $res = Http::withToken($personalBm->access_token)->get("https://graph.facebook.com/{$version}/{$pageId}", [
                    'fields' => 'id,name,category,link,picture{url},followers_count,fan_count,is_published'
                ]);
                if ($res->successful()) {
                    $fbData = $res->json();
                    $name = $fbData['name'] ?? $name;
                    $category = $fbData['category'] ?? $category;
                    $pictureUrl = $fbData['picture']['data']['url'] ?? null;
                    $followersCount = (int) ($fbData['followers_count'] ?? 0);
                    $fanCount = (int) ($fbData['fan_count'] ?? 0);
                    $isPublished = (bool) ($fbData['is_published'] ?? true);
                }
            } catch (Exception $e) {
                // bỏ qua lỗi nếu không query được
            }
        }

        if (empty($name)) {
            $name = "Trang cá nhân ({$pageId})";
        }

        $page = FacebookPage::updateOrCreate(
            [
                'user_id' => $userId,
                'page_id' => $pageId,
                'business_manager_id' => $personalBm?->id,
            ],
            [
                'name' => $name,
                'category' => $category ?: 'Trang cá nhân',
                'link' => "https://www.facebook.com/{$pageId}",
                'picture_url' => $pictureUrl,
                'followers_count' => $followersCount,
                'fan_count' => $fanCount,
                'is_published' => $isPublished,
                'source_type' => 'personal',
            ]
        );

        return back()->with('success', "Đã thêm Trang cá nhân [{$page->name}] (ID: {$page->page_id}) thành công!");
    }

    /**
     * Đánh dấu / Bỏ đánh dấu Hot cho Trang và đưa lên đầu danh sách
     */
    public function toggleHot(Request $request, $id)
    {
        $userId = Auth::id();
        $page = FacebookPage::where('user_id', $userId)->findOrFail($id);
        $newStatus = !$page->is_hot;

        FacebookPage::where('user_id', $userId)
            ->where('page_id', $page->page_id)
            ->update(['is_hot' => $newStatus]);

        $message = $newStatus
            ? "Đã tick Hot cho Page [{$page->name}] và đưa lên đầu danh sách!"
            : "Đã bỏ đánh dấu Hot cho Page [{$page->name}].";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_hot' => $newStatus,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Xóa Trang khỏi hệ thống
     */
    public function destroy($id)
    {
        $userId = Auth::id();
        $page = FacebookPage::where('user_id', $userId)->findOrFail($id);
        $name = $page->name;

        // Xóa toàn bộ bản ghi cùng page_id của user để tránh bản ghi trùng (nếu có) hiện lại
        FacebookPage::where('user_id', $userId)
            ->where('page_id', $page->page_id)
            ->delete();

        return back()->with('success', "Đã gỡ bỏ Page [{$name}] khỏi danh sách thành công!");
    }
}
