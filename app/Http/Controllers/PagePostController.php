<?php

namespace App\Http\Controllers;

use App\Models\FacebookPage;
use App\Models\PagePost;
use App\Models\Setting;
use App\Services\FacebookAdsService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PagePostController extends Controller
{
    protected FacebookAdsService $fbService;

    public function __construct(FacebookAdsService $fbService)
    {
        $this->fbService = $fbService;
    }

    /**
     * Màn hình Đăng bài hàng loạt & Hẹn giờ đăng bài lên Facebook Pages
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        // Tự động kiểm tra và cập nhật các bài hẹn giờ đã tới hạn (scheduled_at <= now)
        $this->fbService->syncScheduledPostsStatusForUser($userId, true);

        // Lấy danh sách Pages duy nhất theo page_id, ưu tiên bản ghi có Page Access Token và Hot lên đầu
        $allUserPages = FacebookPage::where('user_id', $userId)
            ->with('businessManager')
            ->get();

        $sourceScore = ['owned' => 3, 'client' => 2, 'personal' => 1];
        $pages = $allUserPages->groupBy('page_id')->map(function ($records) use ($sourceScore) {
            return $records->sort(function ($a, $b) use ($sourceScore) {
                $tokenA = !empty($a->access_token) ? 1 : 0;
                $tokenB = !empty($b->access_token) ? 1 : 0;
                if ($tokenA !== $tokenB) {
                    return $tokenB <=> $tokenA;
                }
                $scoreA = $sourceScore[$a->source_type] ?? 0;
                $scoreB = $sourceScore[$b->source_type] ?? 0;
                if ($scoreA !== $scoreB) {
                    return $scoreB <=> $scoreA;
                }
                return $b->id <=> $a->id;
            })->first();
        })->sort(function ($a, $b) {
            if ($a->is_hot !== $b->is_hot) {
                return $b->is_hot <=> $a->is_hot;
            }
            $tokenA = !empty($a->access_token) ? 1 : 0;
            $tokenB = !empty($b->access_token) ? 1 : 0;
            if ($tokenA !== $tokenB) {
                return $tokenB <=> $tokenA;
            }
            return strcasecmp((string) $a->name, (string) $b->name);
        })->values();

        $posts = PagePost::where('user_id', $userId)
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $allUserPosts = PagePost::where('user_id', $userId)->get(['status']);
        $postStats = [
            'published' => $allUserPosts->where('status', 'published')->count(),
            'scheduled' => $allUserPosts->where('status', 'scheduled')->count(),
            'failed' => $allUserPosts->where('status', 'failed')->count(),
            'publishable_pages' => $pages->filter(fn ($p) => !empty($p->access_token))->count(),
            'hot_pages' => $pages->filter(fn ($p) => $p->is_hot && !empty($p->access_token))->count(),
        ];

        $defaultScheduleLocal = Carbon::now('Asia/Ho_Chi_Minh')->addMinutes(20)->format('Y-m-d\TH:i');
        $minScheduleLocal = Carbon::now('Asia/Ho_Chi_Minh')->addMinutes(11)->format('Y-m-d\TH:i');
        $maxScheduleLocal = Carbon::now('Asia/Ho_Chi_Minh')->addDays(30)->format('Y-m-d\TH:i');
        $metaAppId = Setting::get('meta_app_id', config('services.meta.app_id'));

        $nextScheduledAtMs = PagePost::where('user_id', $userId)
            ->where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at')
            ->value('scheduled_at');
        $nextScheduledTimestampMs = $nextScheduledAtMs
            ? Carbon::parse($nextScheduledAtMs)->addSeconds(15)->getTimestampMs()
            : null;

        return view('pages.publish', compact(
            'pages',
            'posts',
            'postStats',
            'defaultScheduleLocal',
            'minScheduleLocal',
            'maxScheduleLocal',
            'metaAppId',
            'nextScheduledTimestampMs'
        ));
    }

    /**
     * Đồng bộ thủ công hoặc tự động trạng thái các bài đang hẹn giờ với Facebook
     */
    public function syncStatus(Request $request)
    {
        $userId = Auth::id();
        $onlyDue = $request->boolean('only_due', false);

        $result = $this->fbService->syncScheduledPostsStatusForUser($userId, $onlyDue);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'checked_count' => $result['checked_count'],
                'published_count' => $result['published_count'],
            ]);
        }

        if ($result['published_count'] > 0) {
            return back()->with('success', "Đã cập nhật trạng thái Đã đăng cho {$result['published_count']} bài viết!");
        }

        return back()->with('info', "Đã kiểm tra {$result['checked_count']} bài hẹn giờ: các bài vẫn đang chờ tới giờ đăng.");
    }

    /**
     * Thực thi đăng bài ngay hoặc hẹn giờ đăng bài hàng loạt lên nhiều Pages
     */
    public function store(Request $request)
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'page_ids' => 'required|array|min:1',
            'page_ids.*' => 'required|string|max:100',
            'attachment_type' => 'required|in:none,link,photo',
            'message' => 'nullable|string|max:63206',
            'link_url' => 'nullable|url|max:2000',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp,gif|max:10240',
            'image_urls' => 'nullable|string|max:10000',
            'publish_mode' => 'required|in:now,scheduled',
            'scheduled_at' => 'required_if:publish_mode,scheduled|nullable|date',
            'stagger_minutes' => 'nullable|integer|min:0|max:1440',
        ], [
            'page_ids.required' => 'Vui lòng chọn ít nhất 1 Fanpage để đăng bài.',
            'page_ids.min' => 'Vui lòng chọn ít nhất 1 Fanpage để đăng bài.',
            'link_url.url' => 'Đường dẫn liên kết đính kèm không đúng định dạng URL.',
            'images.max' => 'Chỉ hỗ trợ tối đa 10 hình ảnh trong một bài viết.',
            'images.*.image' => 'Tệp tải lên phải là hình ảnh hợp lệ.',
            'images.*.max' => 'Mỗi hình ảnh không được vượt quá 10MB.',
            'scheduled_at.required_if' => 'Vui lòng chọn ngày giờ hẹn đăng bài.',
        ]);

        $attachmentType = $validated['attachment_type'];
        $message = isset($validated['message']) ? trim($validated['message']) : null;
        $linkUrl = ($attachmentType === 'link' && !empty($validated['link_url'])) ? trim($validated['link_url']) : null;

        // Xử lý danh sách ảnh khi chọn chế độ kèm ảnh
        $batchId = (string) Str::uuid();
        $mediaSources = [];
        $persistedMediaList = [];
        $storedTempPaths = [];
        $keepFailedMedia = false;

        try {
            if ($attachmentType === 'photo') {
                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $file) {
                        $relativePath = $file->store("page_post_media/{$batchId}", 'local');
                        $storedTempPaths[] = $relativePath;
                        $mediaSources[] = [
                            'type' => 'file',
                            'path' => Storage::disk('local')->path($relativePath),
                            'name' => $file->getClientOriginalName(),
                        ];
                        $persistedMediaList[] = [
                            'type' => 'file',
                            'path' => $relativePath,
                            'name' => $file->getClientOriginalName(),
                        ];
                    }
                }

                if (!empty($validated['image_urls'])) {
                    $lines = preg_split('/\r\n|\r|\n/', $validated['image_urls']);
                    foreach ($lines as $rawUrl) {
                        $url = trim((string) $rawUrl);
                        if ($url === '') {
                            continue;
                        }
                        if (!filter_var($url, FILTER_VALIDATE_URL)) {
                            return back()->withInput()->with('error', "Link ảnh không hợp lệ: {$url}");
                        }
                        $mediaSources[] = [
                            'type' => 'url',
                            'url' => $url,
                        ];
                        $persistedMediaList[] = [
                            'type' => 'url',
                            'url' => $url,
                        ];
                    }
                }

                if (count($mediaSources) > 10) {
                    return back()->withInput()->with('error', 'Chỉ hỗ trợ tối đa 10 ảnh cho mỗi bài viết.');
                }
            }

            // Phải có ít nhất nội dung text, link hoặc ảnh
            if (empty($message) && empty($linkUrl) && empty($mediaSources)) {
                return back()->withInput()->with('error', 'Vui lòng nhập nội dung bài viết hoặc chọn link / hình ảnh đính kèm.');
            }

            $selectedPageIds = array_values(array_unique(array_map('strval', $validated['page_ids'])));
            $publishMode = $validated['publish_mode'];
            $staggerMinutes = ($publishMode === 'scheduled') ? (int) ($validated['stagger_minutes'] ?? 0) : 0;

            $baseScheduledAtUtc = null;
            if ($publishMode === 'scheduled') {
                $baseScheduledLocal = Carbon::parse($validated['scheduled_at'], 'Asia/Ho_Chi_Minh');
                $baseScheduledAtUtc = $baseScheduledLocal->copy()->utc();

                $minAllowed = Carbon::now('UTC')->addMinutes(10)->addSeconds(30);
                if ($baseScheduledAtUtc->lt($minAllowed)) {
                    return back()->withInput()->with('error', 'Thời gian hẹn giờ phải sau hiện tại ít nhất 11 phút (để trừ độ trễ gửi ảnh), vui lòng chọn giờ xa hơn.');
                }

                $lastScheduledAtUtc = $baseScheduledAtUtc->copy()->addMinutes(max(0, count($selectedPageIds) - 1) * $staggerMinutes);
                $maxAllowed = Carbon::now('UTC')->addDays(30);
                if ($lastScheduledAtUtc->gt($maxAllowed)) {
                    return back()->withInput()->with('error', 'Thời gian hẹn giờ (kể cả giãn cách đến Page cuối) không được vượt quá 30 ngày kể từ hiện tại.');
                }
            }

            // Lấy bản ghi đại diện có access_token cho từng page_id được chọn
            $candidatePages = FacebookPage::where('user_id', $userId)
                ->whereIn('page_id', $selectedPageIds)
                ->get()
                ->groupBy('page_id')
                ->map(function ($records) {
                    return $records->sortByDesc(fn ($p) => !empty($p->access_token) ? 1 : 0)->first();
                });

            if ($candidatePages->isEmpty()) {
                return back()->withInput()->with('error', 'Không tìm thấy Fanpage hợp lệ thuộc tài khoản của bạn.');
            }

            $postType = !empty($mediaSources) ? 'photo' : (!empty($linkUrl) ? 'link' : 'text');
            $successCount = 0;
            $failCount = 0;
            $firstError = null;
            $index = 0;

            foreach ($selectedPageIds as $pageId) {
                $page = $candidatePages->get($pageId);
                if (!$page) {
                    continue;
                }

                $targetScheduledAt = ($publishMode === 'scheduled' && $baseScheduledAtUtc)
                    ? $baseScheduledAtUtc->copy()->addMinutes($index * $staggerMinutes)
                    : null;
                $index++;

                try {
                    $result = $this->fbService->publishToPage(
                        $page,
                        $message,
                        $linkUrl,
                        $mediaSources,
                        $targetScheduledAt
                    );

                    $status = $publishMode === 'scheduled' ? 'scheduled' : 'published';
                    $publishedAt = $publishMode === 'now' ? now() : null;

                    PagePost::create([
                        'user_id' => $userId,
                        'batch_id' => $batchId,
                        'facebook_page_id' => $page->id,
                        'page_id' => $page->page_id,
                        'page_name' => $page->name,
                        'post_type' => $postType,
                        'message' => $message,
                        'link_url' => $linkUrl,
                        'media_count' => count($mediaSources),
                        'publish_mode' => $publishMode,
                        'scheduled_at' => $targetScheduledAt,
                        'published_at' => $publishedAt,
                        'fb_post_id' => $result['fb_post_id'],
                        'permalink_url' => $result['permalink_url'],
                        'status' => $status,
                    ]);

                    if ($status === 'published') {
                        FacebookPage::where('user_id', $userId)
                            ->where('page_id', $page->page_id)
                            ->update([
                                'last_post_at' => $publishedAt,
                                'last_post_url' => $result['permalink_url'],
                                'last_post_synced_at' => $publishedAt,
                            ]);
                    }

                    $successCount++;
                } catch (Exception $e) {
                    $failCount++;
                    $translatedError = FacebookAdsService::translatePublishError($e->getMessage());
                    $firstError = $firstError ?? $translatedError;
                    $keepFailedMedia = true;

                    PagePost::create([
                        'user_id' => $userId,
                        'batch_id' => $batchId,
                        'facebook_page_id' => $page->id,
                        'page_id' => $page->page_id,
                        'page_name' => $page->name,
                        'post_type' => $postType,
                        'message' => $message,
                        'link_url' => $linkUrl,
                        'media_count' => count($mediaSources),
                        'media_paths' => !empty($persistedMediaList) ? $persistedMediaList : null,
                        'publish_mode' => $publishMode,
                        'scheduled_at' => $targetScheduledAt,
                        'status' => 'failed',
                        'error_message' => $translatedError,
                    ]);
                }
            }

            $actionLabel = $publishMode === 'scheduled' ? 'lên lịch đăng bài' : 'đăng bài';
            if ($successCount > 0 && $failCount === 0) {
                return redirect()->route('pages.publish.index')
                    ->with('success', "Đã {$actionLabel} thành công lên {$successCount} Fanpage!");
            }

            if ($successCount > 0 && $failCount > 0) {
                return redirect()->route('pages.publish.index')
                    ->with('warning', "Đã {$actionLabel} thành công {$successCount} Page, nhưng có {$failCount} Page thất bại ({$firstError}).");
            }

            return redirect()->route('pages.publish.index')
                ->withInput()
                ->with('error', "Không thể {$actionLabel} ({$failCount} Page lỗi): {$firstError}");
        } finally {
            if (!$keepFailedMedia) {
                foreach ($storedTempPaths as $tempPath) {
                    Storage::disk('local')->delete($tempPath);
                }
            }
        }
    }

    /**
     * Thử lại một bài viết bị lỗi
     */
    public function retry(Request $request, $id)
    {
        $userId = Auth::id();
        $post = PagePost::where('user_id', $userId)->findOrFail($id);

        if ($post->status !== 'failed') {
            return back()->with('warning', 'Chỉ có thể thử lại các bài đăng đang ở trạng thái Thất bại.');
        }

        $validated = $request->validate([
            'publish_mode' => 'nullable|in:now,scheduled',
            'scheduled_at' => 'nullable|date',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,jpg,png,webp,gif|max:10240',
        ], [
            'images.max' => 'Chỉ hỗ trợ tối đa 10 hình ảnh trong một bài viết.',
            'images.*.image' => 'Tệp tải lên phải là hình ảnh hợp lệ.',
        ]);

        $publishMode = $validated['publish_mode'] ?? $post->publish_mode;
        $targetScheduledAt = null;

        if ($publishMode === 'scheduled') {
            if (!empty($validated['scheduled_at'])) {
                $targetScheduledAt = Carbon::parse($validated['scheduled_at'], 'Asia/Ho_Chi_Minh')->utc();
            } elseif ($post->scheduled_at) {
                $targetScheduledAt = $post->scheduled_at->copy()->utc();
            } else {
                return back()->with('error', 'Vui lòng chọn thời gian hẹn giờ hợp lệ trước khi thử lại.');
            }

            $minAllowed = Carbon::now('UTC')->addMinutes(10)->addSeconds(30);
            if ($targetScheduledAt->lt($minAllowed)) {
                return back()->with('error', 'Thời gian hẹn giờ cũ đã sát hoặc qua mốc 10 phút tối thiểu. Vui lòng chọn giờ hẹn mới (cách hiện tại ít nhất 11 phút) hoặc chọn Đăng ngay.');
            }

            $maxAllowed = Carbon::now('UTC')->addDays(30);
            if ($targetScheduledAt->gt($maxAllowed)) {
                return back()->with('error', 'Thời gian hẹn giờ không được vượt quá 30 ngày kể từ hiện tại.');
            }
        }

        $page = FacebookPage::where('user_id', $userId)
            ->where('page_id', $post->page_id)
            ->get()
            ->sortByDesc(fn ($p) => !empty($p->access_token) ? 1 : 0)
            ->first();

        if (!$page || empty($page->access_token)) {
            return back()->with('error', "Page [{$post->page_name}] chưa có Page Access Token khả dụng.");
        }

        $mediaSources = [];
        $newPersistedList = [];
        $oldMediaPaths = is_array($post->media_paths) ? $post->media_paths : [];
        $usedNewUpload = $request->hasFile('images');

        if ($usedNewUpload) {
            foreach ($request->file('images') as $file) {
                $relativePath = $file->store("page_post_media/{$post->batch_id}", 'local');
                $mediaSources[] = [
                    'type' => 'file',
                    'path' => Storage::disk('local')->path($relativePath),
                    'name' => $file->getClientOriginalName(),
                ];
                $newPersistedList[] = [
                    'type' => 'file',
                    'path' => $relativePath,
                    'name' => $file->getClientOriginalName(),
                ];
            }
        } elseif (!empty($oldMediaPaths)) {
            foreach ($oldMediaPaths as $item) {
                if (($item['type'] ?? '') === 'file' && !empty($item['path'])) {
                    $absPath = Storage::disk('local')->path($item['path']);
                    if (is_file($absPath)) {
                        $mediaSources[] = [
                            'type' => 'file',
                            'path' => $absPath,
                            'name' => $item['name'] ?? basename($absPath),
                        ];
                    }
                } elseif (($item['type'] ?? '') === 'url' && !empty($item['url'])) {
                    $mediaSources[] = [
                        'type' => 'url',
                        'url' => $item['url'],
                    ];
                }
            }
        }

        try {
            $result = $this->fbService->publishToPage(
                $page,
                $post->message,
                $post->link_url,
                $mediaSources,
                $targetScheduledAt
            );

            $status = $publishMode === 'scheduled' ? 'scheduled' : 'published';
            $publishedAt = $publishMode === 'now' ? now() : null;

            $post->update([
                'post_type' => !empty($mediaSources) ? 'photo' : (!empty($post->link_url) ? 'link' : 'text'),
                'media_count' => count($mediaSources),
                'media_paths' => null,
                'publish_mode' => $publishMode,
                'scheduled_at' => $targetScheduledAt,
                'published_at' => $publishedAt,
                'fb_post_id' => $result['fb_post_id'],
                'permalink_url' => $result['permalink_url'],
                'status' => $status,
                'error_message' => null,
            ]);

            if ($status === 'published') {
                FacebookPage::where('user_id', $userId)
                    ->where('page_id', $page->page_id)
                    ->update([
                        'last_post_at' => $publishedAt,
                        'last_post_url' => $result['permalink_url'],
                        'last_post_synced_at' => $publishedAt,
                    ]);
            }

            $this->cleanupOrphanedBatchMedia($post->batch_id, $oldMediaPaths);
            if ($usedNewUpload) {
                $this->cleanupOrphanedBatchMedia($post->batch_id, $newPersistedList);
            }

            $label = $status === 'scheduled' ? 'hẹn giờ đăng lại' : 'đăng lại';
            return back()->with('success', "Đã {$label} thành công lên Page [{$post->page_name}]!");
        } catch (Exception $e) {
            $translatedError = FacebookAdsService::translatePublishError($e->getMessage());

            $post->update([
                'publish_mode' => $publishMode,
                'scheduled_at' => $targetScheduledAt,
                'media_paths' => $usedNewUpload ? $newPersistedList : $post->media_paths,
                'media_count' => !empty($mediaSources) ? count($mediaSources) : $post->media_count,
                'error_message' => $translatedError,
            ]);

            if ($usedNewUpload) {
                $this->cleanupOrphanedBatchMedia($post->batch_id, $oldMediaPaths);
            }

            return back()->with('error', "Thử lại trên Page [{$post->page_name}] thất bại: {$translatedError}");
        }
    }

    /**
     * Xóa các file ảnh tạm của batch nếu không còn bản ghi lỗi nào tham chiếu tới
     */
    protected function cleanupOrphanedBatchMedia(string $batchId, array $mediaPaths): void
    {
        if (empty($mediaPaths)) {
            return;
        }

        $stillInUse = PagePost::where('batch_id', $batchId)
            ->where('status', 'failed')
            ->whereNotNull('media_paths')
            ->get(['media_paths'])
            ->flatMap(fn ($p) => is_array($p->media_paths) ? $p->media_paths : [])
            ->pluck('path')
            ->filter()
            ->flip();

        foreach ($mediaPaths as $item) {
            $relPath = $item['path'] ?? null;
            if (($item['type'] ?? '') === 'file' && $relPath && !$stillInUse->has($relPath)) {
                Storage::disk('local')->delete($relPath);
            }
        }
    }

    /**
     * Hủy bài viết đang hẹn giờ trên Facebook hoặc xóa log lịch sử
     */
    public function destroy($id)
    {
        $userId = Auth::id();
        $post = PagePost::where('user_id', $userId)->findOrFail($id);

        if ($post->status === 'scheduled' && !empty($post->fb_post_id)) {
            $pageWithToken = FacebookPage::where('user_id', $userId)
                ->where('page_id', $post->page_id)
                ->get()
                ->first(fn ($p) => !empty($p->access_token));

            if ($pageWithToken) {
                try {
                    $this->fbService->deletePagePost($post->fb_post_id, $pageWithToken->access_token);
                } catch (Exception $e) {
                    return back()->with('error', $e->getMessage());
                }
            }

            $post->update(['status' => 'cancelled']);
            return back()->with('success', "Đã hủy bài hẹn giờ trên Page [{$post->page_name}]!");
        }

        $oldMediaPaths = is_array($post->media_paths) ? $post->media_paths : [];
        $batchId = $post->batch_id;
        $post->delete();

        if (!empty($oldMediaPaths)) {
            $this->cleanupOrphanedBatchMedia($batchId, $oldMediaPaths);
        }

        return back()->with('success', 'Đã xóa bản ghi khỏi lịch sử đăng bài.');
    }
}
