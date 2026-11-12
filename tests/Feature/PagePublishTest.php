<?php

namespace Tests\Feature;

use App\Models\FacebookPage;
use App\Models\PagePost;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PagePublishTest extends TestCase
{
    public function test_guest_cannot_access_page_publish()
    {
        $this->get('/pages/publish')->assertRedirect('/login');
        $this->post('/pages/publish', [])->assertRedirect('/login');
    }

    public function test_user_can_view_bulk_publish_page()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        Http::fake([
            'graph.facebook.com/*' => Http::response([], 200),
        ]);

        $response = $this->actingAs($user)->get('/pages/publish');
        $response->assertStatus(200);
        $response->assertSee('Soạn nội dung & Lịch đăng bài', false);
        $response->assertSee('Chọn Fanpage đăng bài');
    }

    public function test_oauth_publish_mode_requests_pages_manage_posts_scope()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $response = $this->actingAs($user)->get('/auth/facebook?mode=publish');
        $response->assertRedirect();

        $targetUrl = $response->headers->get('Location');
        parse_str(parse_url($targetUrl, PHP_URL_QUERY) ?? '', $query);
        $scopes = explode(',', $query['scope'] ?? '');

        $this->assertContains('pages_manage_posts', $scopes);
        $this->assertContains('pages_show_list', $scopes);
        $this->assertContains('pages_read_engagement', $scopes);
    }

    public function test_user_can_bulk_publish_text_and_link_immediately_across_multiple_pages()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $pageId1 = '1010101010_pub_test';
        $pageId2 = '2020202020_pub_test';

        Http::fake([
            "graph.facebook.com/*/{$pageId1}/feed*" => Http::response(['id' => "{$pageId1}_111"], 200),
            "graph.facebook.com/*/{$pageId2}/feed*" => Http::response(['id' => "{$pageId2}_222"], 200),
        ]);

        try {
            FacebookPage::create([
                'user_id' => $user->id,
                'page_id' => $pageId1,
                'name' => 'Publish Page 1',
                'access_token' => 'EAA_PUB_1',
                'source_type' => 'personal',
            ]);

            FacebookPage::create([
                'user_id' => $user->id,
                'page_id' => $pageId2,
                'name' => 'Publish Page 2',
                'access_token' => 'EAA_PUB_2',
                'source_type' => 'personal',
            ]);

            $response = $this->actingAs($user)->post('/pages/publish', [
                'page_ids' => [$pageId1, $pageId2],
                'attachment_type' => 'link',
                'message' => 'Tin mới dự án bất động sản tháng 11!',
                'link_url' => 'https://example.com/du-an',
                'publish_mode' => 'now',
            ]);

            $response->assertRedirect(route('pages.publish.index'));
            $response->assertSessionHas('success');

            $this->assertDatabaseHas('page_posts', [
                'user_id' => $user->id,
                'page_id' => $pageId1,
                'status' => 'published',
                'fb_post_id' => "{$pageId1}_111",
                'post_type' => 'link',
            ]);

            $this->assertDatabaseHas('page_posts', [
                'user_id' => $user->id,
                'page_id' => $pageId2,
                'status' => 'published',
                'fb_post_id' => "{$pageId2}_222",
                'post_type' => 'link',
            ]);

            // last_post_at on FacebookPage should be updated immediately
            $this->assertNotNull(
                FacebookPage::where('user_id', $user->id)->where('page_id', $pageId1)->value('last_post_at')
            );
        } finally {
            PagePost::where('user_id', $user->id)->whereIn('page_id', [$pageId1, $pageId2])->delete();
            FacebookPage::where('user_id', $user->id)->whereIn('page_id', [$pageId1, $pageId2])->delete();
        }
    }

    public function test_user_can_schedule_multi_photo_post_with_stagger_and_cancel_it()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $pageId1 = '3030303030_sched_test';
        $pageId2 = '4040404040_sched_test';

        $capturedFeedPayloads = [];

        Http::fake(function ($request) use (&$capturedFeedPayloads, $pageId1, $pageId2) {
            $url = $request->url();
            if (str_contains($url, '/photos')) {
                return Http::response(['id' => '999888777'], 200);
            }
            if (str_contains($url, "{$pageId1}/feed")) {
                $capturedFeedPayloads[$pageId1] = $request->data();
                return Http::response(['id' => "{$pageId1}_sched_1"], 200);
            }
            if (str_contains($url, "{$pageId2}/feed")) {
                $capturedFeedPayloads[$pageId2] = $request->data();
                return Http::response(['id' => "{$pageId2}_sched_2"], 200);
            }
            if ($request->method() === 'DELETE' && str_contains($url, "{$pageId1}_sched_1")) {
                return Http::response(['success' => true], 200);
            }
            return Http::response([], 200);
        });

        try {
            FacebookPage::create([
                'user_id' => $user->id,
                'page_id' => $pageId1,
                'name' => 'Schedule Page 1',
                'access_token' => 'EAA_SCHED_1',
                'source_type' => 'personal',
            ]);

            FacebookPage::create([
                'user_id' => $user->id,
                'page_id' => $pageId2,
                'name' => 'Schedule Page 2',
                'access_token' => 'EAA_SCHED_2',
                'source_type' => 'personal',
            ]);

            $scheduledLocal = Carbon::now('Asia/Ho_Chi_Minh')->addHours(2)->format('Y-m-d\TH:i');
            $fakePhoto = UploadedFile::fake()->image('du-an.jpg', 600, 400);

            $response = $this->actingAs($user)->post('/pages/publish', [
                'page_ids' => [$pageId1, $pageId2],
                'attachment_type' => 'photo',
                'message' => 'Bài hẹn giờ kèm ảnh',
                'images' => [$fakePhoto],
                'publish_mode' => 'scheduled',
                'scheduled_at' => $scheduledLocal,
                'stagger_minutes' => 15,
            ]);

            $response->assertRedirect(route('pages.publish.index'));
            $response->assertSessionHas('success');

            // Verify Meta Graph API payload for scheduled photo post
            $this->assertFalse($capturedFeedPayloads[$pageId1]['published']);
            $this->assertEquals('SCHEDULED', $capturedFeedPayloads[$pageId1]['unpublished_content_type']);
            $this->assertEquals([['media_fbid' => '999888777']], $capturedFeedPayloads[$pageId1]['attached_media']);

            // Verify 15-minute stagger between page 1 and page 2
            $this->assertEquals(
                900,
                $capturedFeedPayloads[$pageId2]['scheduled_publish_time'] - $capturedFeedPayloads[$pageId1]['scheduled_publish_time']
            );

            $scheduledPost = PagePost::where('user_id', $user->id)
                ->where('page_id', $pageId1)
                ->firstOrFail();

            $this->assertEquals('scheduled', $scheduledPost->status);
            $this->assertEquals(1, $scheduledPost->media_count);

            // Cancel scheduled post
            $cancelRes = $this->actingAs($user)->delete(route('pages.posts.destroy', $scheduledPost->id));
            $cancelRes->assertRedirect();
            $cancelRes->assertSessionHas('success');

            $this->assertDatabaseHas('page_posts', [
                'id' => $scheduledPost->id,
                'status' => 'cancelled',
            ]);
        } finally {
            PagePost::where('user_id', $user->id)->whereIn('page_id', [$pageId1, $pageId2])->delete();
            FacebookPage::where('user_id', $user->id)->whereIn('page_id', [$pageId1, $pageId2])->delete();
        }
    }

    public function test_schedule_rejects_time_less_than_10_minutes_ahead()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $pageId = '5050505050_val_test';

        try {
            FacebookPage::create([
                'user_id' => $user->id,
                'page_id' => $pageId,
                'name' => 'Validation Page',
                'access_token' => 'EAA_VAL_1',
                'source_type' => 'personal',
            ]);

            $tooSoonLocal = Carbon::now('Asia/Ho_Chi_Minh')->addMinutes(3)->format('Y-m-d\TH:i');

            $response = $this->actingAs($user)->post('/pages/publish', [
                'page_ids' => [$pageId],
                'attachment_type' => 'none',
                'message' => 'Quá gần',
                'publish_mode' => 'scheduled',
                'scheduled_at' => $tooSoonLocal,
            ]);

            $response->assertRedirect();
            $response->assertSessionHas('error');
            $this->assertDatabaseMissing('page_posts', [
                'user_id' => $user->id,
                'page_id' => $pageId,
            ]);
        } finally {
            PagePost::where('user_id', $user->id)->where('page_id', $pageId)->delete();
            FacebookPage::where('user_id', $user->id)->where('page_id', $pageId)->delete();
        }
    }

    public function test_store_token_updates_access_token_for_existing_bm_pages()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $bm = \App\Models\BusinessManager::where('user_id', $user->id)->realBms()->first();
        if (!$bm) {
            $this->markTestSkipped('No real BM found');
        }

        $bmPageId = '6060606060_token_upgrade_test';

        Http::fake([
            'graph.facebook.com/*/me/accounts*' => Http::response([
                'data' => [
                    [
                        'id' => $bmPageId,
                        'name' => 'BM Page Upgraded Token',
                        'access_token' => 'EAA_NEW_PUBLISH_PAGE_TOKEN',
                    ],
                ],
            ], 200),
            "graph.facebook.com/*/{$bmPageId}/published_posts*" => Http::response(['data' => []], 200),
            'graph.facebook.com/*' => Http::response(['id' => $user->id, 'name' => 'User'], 200),
        ]);

        try {
            $bmPage = FacebookPage::create([
                'user_id' => $user->id,
                'business_manager_id' => $bm->id,
                'page_id' => $bmPageId,
                'name' => 'BM Page Upgraded Token',
                'access_token' => 'EAA_OLD_READONLY_TOKEN',
                'source_type' => 'owned',
            ]);

            $res = $this->actingAs($user)->post('/pages/token', [
                'access_token' => 'EAA_USER_WITH_MANAGE_POSTS',
            ]);

            $res->assertRedirect();
            $res->assertSessionHas('success');

            $bmPage->refresh();
            $this->assertEquals('EAA_NEW_PUBLISH_PAGE_TOKEN', $bmPage->access_token);
        } finally {
            FacebookPage::where('user_id', $user->id)->where('page_id', $bmPageId)->delete();
        }
    }

    public function test_failed_post_displays_vietnamese_error_and_can_retry_with_saved_media()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $pageId = '7070707070_retry_test';
        $attempt = 0;

        Http::fake(function ($request) use (&$attempt, $pageId) {
            $url = $request->url();
            if (str_contains($url, '/photos')) {
                return Http::response(['id' => '555666777'], 200);
            }
            if (str_contains($url, "{$pageId}/feed")) {
                $attempt++;
                if ($attempt === 1) {
                    return Http::response([
                        'error' => ['message' => '(#100) The specified scheduled publish time is invalid.'],
                    ], 400);
                }
                return Http::response(['id' => "{$pageId}_retry_ok"], 200);
            }
            return Http::response([], 200);
        });

        try {
            FacebookPage::create([
                'user_id' => $user->id,
                'page_id' => $pageId,
                'name' => 'Retry Test Page',
                'access_token' => 'EAA_RETRY_TOKEN',
                'source_type' => 'personal',
            ]);

            $scheduledLocal = Carbon::now('Asia/Ho_Chi_Minh')->addMinutes(30)->format('Y-m-d\TH:i');
            $fakePhoto = UploadedFile::fake()->image('retry-photo.jpg', 500, 500);

            // 1. Initial attempt fails with Meta English error -> saved & displayed in Vietnamese
            $resFail = $this->actingAs($user)->post('/pages/publish', [
                'page_ids' => [$pageId],
                'attachment_type' => 'photo',
                'message' => 'Test retry photo post',
                'images' => [$fakePhoto],
                'publish_mode' => 'scheduled',
                'scheduled_at' => $scheduledLocal,
            ]);

            $resFail->assertRedirect(route('pages.publish.index'));
            $resFail->assertSessionHas('error');

            $failedPost = PagePost::where('user_id', $user->id)->where('page_id', $pageId)->firstOrFail();
            $this->assertEquals('failed', $failedPost->status);
            $this->assertStringContainsString('Thời gian hẹn giờ không hợp lệ', $failedPost->error_message);
            $this->assertNotEmpty($failedPost->media_paths);

            $savedRelPath = $failedPost->media_paths[0]['path'] ?? null;
            $this->assertNotNull($savedRelPath);
            $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('local')->exists($savedRelPath));

            // Index displays full Vietnamese error and "Thử lại" button
            $indexRes = $this->actingAs($user)->get('/pages/publish');
            $indexRes->assertStatus(200);
            $indexRes->assertSee('Thời gian hẹn giờ không hợp lệ');
            $indexRes->assertSee('Thử lại');

            // 2. Retry succeeds using the saved photo and updates status to scheduled
            $retryRes = $this->actingAs($user)->post(route('pages.posts.retry', $failedPost->id));
            $retryRes->assertRedirect();
            $retryRes->assertSessionHas('success');

            $failedPost->refresh();
            $this->assertEquals('scheduled', $failedPost->status);
            $this->assertNull($failedPost->error_message);
            $this->assertNull($failedPost->media_paths);
            $this->assertEquals("{$pageId}_retry_ok", $failedPost->fb_post_id);
            $this->assertFalse(\Illuminate\Support\Facades\Storage::disk('local')->exists($savedRelPath));
        } finally {
            PagePost::where('user_id', $user->id)->where('page_id', $pageId)->delete();
            FacebookPage::where('user_id', $user->id)->where('page_id', $pageId)->delete();
        }
    }

    public function test_scheduled_post_auto_updates_to_published_when_facebook_publishes_it()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $pageId = '8080808080_autosync_test';
        $fbPostId = "{$pageId}_999888";

        Http::fake([
            "graph.facebook.com/*/{$fbPostId}*" => Http::response([
                'id' => $fbPostId,
                'is_published' => true,
                'created_time' => '2026-11-11T04:15:00+0000',
                'permalink_url' => "https://www.facebook.com/{$pageId}/posts/999888",
            ], 200),
            'graph.facebook.com/*' => Http::response([], 200),
        ]);

        try {
            $page = FacebookPage::create([
                'user_id' => $user->id,
                'page_id' => $pageId,
                'name' => 'Auto Sync Published Page',
                'access_token' => 'EAA_AUTOSYNC_TOKEN',
                'source_type' => 'personal',
            ]);

            $post = PagePost::create([
                'user_id' => $user->id,
                'batch_id' => 'batch-autosync-test',
                'facebook_page_id' => $page->id,
                'page_id' => $pageId,
                'page_name' => $page->name,
                'post_type' => 'photo',
                'message' => 'Scheduled auto sync post',
                'media_count' => 1,
                'publish_mode' => 'scheduled',
                'scheduled_at' => now()->subMinutes(5),
                'fb_post_id' => $fbPostId,
                'status' => 'scheduled',
            ]);

            // Visiting /pages/publish automatically checks due scheduled posts and marks it published
            $indexRes = $this->actingAs($user)->get('/pages/publish');
            $indexRes->assertStatus(200);
            $indexRes->assertSee('Đã đăng theo lịch');
            $indexRes->assertSee("https://www.facebook.com/{$pageId}/posts/999888", false);

            $post->refresh();
            $page->refresh();
            $this->assertEquals('published', $post->status);
            $this->assertEquals("https://www.facebook.com/{$pageId}/posts/999888", $post->permalink_url);
            $this->assertNotNull($post->published_at);
            $this->assertNotNull($page->last_post_at);

            // Manual sync-status endpoint also returns JSON cleanly
            $syncRes = $this->actingAs($user)->postJson(route('pages.posts.syncStatus'));
            $syncRes->assertStatus(200)->assertJson(['success' => true]);
        } finally {
            PagePost::where('user_id', $user->id)->where('page_id', $pageId)->delete();
            FacebookPage::where('user_id', $user->id)->where('page_id', $pageId)->delete();
        }
    }
}
