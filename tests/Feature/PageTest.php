<?php

namespace Tests\Feature;

use App\Models\BusinessManager;
use App\Models\FacebookPage;
use App\Models\User;
use App\Services\FacebookAdsService;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class PageTest extends TestCase
{
    public function test_guest_cannot_access_pages_page()
    {
        $response = $this->get('/pages');
        $response->assertRedirect('/login');
    }

    public function test_user_can_view_pages_index()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        // Tạo 1 page mẫu nếu chưa có
        FacebookPage::updateOrCreate(
            [
                'user_id' => $user->id,
                'page_id' => '1000999888777',
            ],
            [
                'name' => 'Trang Bất Động Sản Cao Cấp',
                'category' => 'Bất động sản',
                'followers_count' => 500,
                'fan_count' => 450,
                'is_published' => true,
                'source_type' => 'owned',
            ]
        );

        $response = $this->actingAs($user)->get('/pages');
        $response->assertStatus(200);
        $response->assertSee('Danh sách Trang Facebook');
        $response->assertSee('Trang Bất Động Sản Cao Cấp');
        $response->assertSee('Quản lý Pages');
    }

    public function test_user_can_filter_pages()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        FacebookPage::updateOrCreate(
            [
                'user_id' => $user->id,
                'page_id' => '999888111222',
            ],
            [
                'name' => 'Trang Độc Quyền ABC XYZ',
                'category' => 'Marketing',
                'followers_count' => 120,
                'fan_count' => 100,
                'is_published' => true,
                'source_type' => 'client',
            ]
        );

        // Filter by keyword that exists
        $response = $this->actingAs($user)->get('/pages?search=ABC+XYZ');
        $response->assertStatus(200);
        $response->assertSee('Trang Độc Quyền ABC XYZ');

        // Filter by source
        $responseSource = $this->actingAs($user)->get('/pages?source_type=client');
        $responseSource->assertStatus(200);
        $responseSource->assertSee('Trang Độc Quyền ABC XYZ');
    }

    public function test_user_can_trigger_pages_sync()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $mock = Mockery::mock(FacebookAdsService::class);
        $mock->shouldReceive('syncAllPagesForUser')
            ->once()
            ->with($user->id)
            ->andReturn(['synced_count' => 5, 'total_pages' => 5]);

        $this->app->instance(FacebookAdsService::class, $mock);

        $response = $this->actingAs($user)->post('/pages/sync');
        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_sidebar_displays_pages_link_and_count()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $count = FacebookPage::where('user_id', $user->id)->distinct('page_id')->count('page_id');

        $response = $this->actingAs($user)->get('/bms');
        $response->assertStatus(200);
        $response->assertSee('Quản lý Pages');
        $response->assertSee((string) $count);
    }

    public function test_bm_show_has_pages_tab()
    {
        $user = User::first();
        $bm = BusinessManager::where('user_id', $user->id)->first();
        if (!$bm) {
            $this->markTestSkipped('No BM found');
        }

        $response = $this->actingAs($user)->get('/bms/' . $bm->id);
        $response->assertStatus(200);
        $response->assertSee('tabBtn-pages', false);
        $response->assertSee('tabContent-pages', false);
    }

    public function test_user_can_store_personal_page_manually()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $response = $this->actingAs($user)->post('/pages/manual', [
            'page_id' => '112233445566',
            'name' => 'Trang Cá Nhân Thủ Công Mới',
            'category' => 'Bất động sản cá nhân',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('facebook_pages', [
            'user_id' => $user->id,
            'page_id' => '112233445566',
            'source_type' => 'personal',
        ]);
    }

    public function test_user_can_delete_page()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $page = FacebookPage::create([
            'user_id' => $user->id,
            'page_id' => '999000111222',
            'name' => 'Trang Cần Xóa',
            'source_type' => 'personal',
        ]);

        $response = $this->actingAs($user)->delete('/pages/' . $page->id);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('facebook_pages', [
            'id' => $page->id,
        ]);
    }

    public function test_facebook_oauth_url_uses_only_valid_scopes()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $response = $this->actingAs($user)->get('/auth/facebook');
        $response->assertRedirect();

        $targetUrl = $response->headers->get('Location');
        $this->assertStringContainsString('dialog/oauth', $targetUrl);

        parse_str(parse_url($targetUrl, PHP_URL_QUERY) ?? '', $query);
        $scopes = explode(',', $query['scope'] ?? '');

        $this->assertContains('ads_management', $scopes);
        $this->assertContains('ads_read', $scopes);
        $this->assertContains('business_management', $scopes);
        $this->assertContains('public_profile', $scopes);
        $this->assertContains('pages_show_list', $scopes);
        $this->assertContains('pages_read_engagement', $scopes);

        $this->assertNotContains('pages_manage_metadata', $scopes);
        $this->assertNotContains('pages_read_user_content', $scopes);
    }

    public function test_sync_all_pages_saves_personal_pages_via_fallback_oauth_token()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $testFbUserId = '999888777666_test';
        $testPageId = '888777666555_test';

        Http::fake([
            'graph.facebook.com/*/me/permissions*' => Http::response([
                'data' => [
                    ['permission' => 'pages_show_list', 'status' => 'granted'],
                ],
            ], 200),
            'graph.facebook.com/*/me/accounts*' => Http::response([
                'data' => [
                    [
                        'id' => $testPageId,
                        'name' => 'Test Personal Page OAuth',
                        'category' => 'Personal Blog',
                        'fan_count' => 12,
                        'followers_count' => 15,
                        'is_published' => true,
                    ],
                ],
            ], 200),
            'graph.facebook.com/*' => Http::response(['data' => []], 200),
        ]);

        try {
            $service = app(FacebookAdsService::class);
            $service->syncAllPagesForUser($user->id, 'EAA_TEST_FALLBACK_TOKEN', [
                'id' => $testFbUserId,
                'name' => 'Test Personal FB',
            ]);

            $this->assertDatabaseHas('business_managers', [
                'user_id' => $user->id,
                'bm_id' => 'personal_' . $testFbUserId,
            ]);

            $this->assertDatabaseHas('facebook_pages', [
                'user_id' => $user->id,
                'page_id' => $testPageId,
                'source_type' => 'personal',
            ]);
        } finally {
            FacebookPage::where('user_id', $user->id)->where('page_id', $testPageId)->delete();
            BusinessManager::where('user_id', $user->id)->where('bm_id', 'personal_' . $testFbUserId)->delete();
        }
    }

    public function test_pages_index_deduplicates_pages_in_both_bm_and_personal()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $bm = BusinessManager::where('user_id', $user->id)->realBms()->first();
        if (!$bm) {
            $this->markTestSkipped('No real BM found');
        }

        $personalBm = BusinessManager::where('user_id', $user->id)->personal()->first();
        $createdPersonalBm = false;
        if (!$personalBm) {
            $personalBm = BusinessManager::create([
                'user_id' => $user->id,
                'name' => 'TK Cá nhân Test Dedup',
                'bm_id' => 'personal_dedup_test',
                'access_token' => 'EAA_TEST',
                'status' => 'active',
            ]);
            $createdPersonalBm = true;
        }

        $dupPageId = '777666555444_dup_test';

        try {
            FacebookPage::create([
                'user_id' => $user->id,
                'business_manager_id' => $bm->id,
                'page_id' => $dupPageId,
                'name' => 'Page Trùng Lặp Test',
                'source_type' => 'owned',
                'fan_count' => 100,
                'followers_count' => 120,
                'is_published' => true,
            ]);

            FacebookPage::create([
                'user_id' => $user->id,
                'business_manager_id' => $personalBm->id,
                'page_id' => $dupPageId,
                'name' => 'Page Trùng Lặp Test',
                'source_type' => 'personal',
                'fan_count' => 100,
                'followers_count' => 120,
                'is_published' => true,
            ]);

            $response = $this->actingAs($user)->get('/pages?search=' . $dupPageId);
            $response->assertStatus(200);

            $pages = $response->viewData('pages');
            $this->assertEquals(1, $pages->total());
            $this->assertCount(1, $pages);
            $this->assertEquals('owned', $pages->first()->source_type);

            // Filtering by source_type=personal must not show the BM-owned page
            $personalResponse = $this->actingAs($user)->get('/pages?search=' . $dupPageId . '&source_type=personal');
            $personalResponse->assertStatus(200);
            $this->assertEquals(0, $personalResponse->viewData('pages')->total());
        } finally {
            FacebookPage::where('user_id', $user->id)->where('page_id', $dupPageId)->delete();
            if ($createdPersonalBm) {
                $personalBm->delete();
            }
        }
    }

    public function test_sync_all_pages_skips_and_cleans_personal_duplicate_of_bm_page()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $realBm = BusinessManager::create([
            'user_id' => $user->id,
            'name' => 'BM Sync Dedup Test',
            'bm_id' => '666555444333_test',
            'access_token' => 'EAA_BM_TEST',
            'status' => 'active',
        ]);

        $personalBm = BusinessManager::create([
            'user_id' => $user->id,
            'name' => 'TK Cá nhân Sync Dedup Test',
            'bm_id' => 'personal_666555444333_test',
            'access_token' => 'EAA_PERSONAL_TEST',
            'status' => 'active',
        ]);

        $sharedPageId = '555444333222_sync_test';
        $purePersonalPageId = '444333222111_sync_test';

        Http::fake([
            'graph.facebook.com/*/666555444333_test/owned_pages*' => Http::response([
                'data' => [
                    ['id' => $sharedPageId, 'name' => 'BM Shared Page', 'fan_count' => 10],
                ],
            ], 200),
            'graph.facebook.com/*/666555444333_test/client_pages*' => Http::response(['data' => []], 200),
            'graph.facebook.com/*/me/accounts*' => Http::response([
                'data' => [
                    ['id' => $sharedPageId, 'name' => 'BM Shared Page', 'fan_count' => 10],
                    ['id' => $purePersonalPageId, 'name' => 'Pure Personal Page', 'fan_count' => 5],
                ],
            ], 200),
            'graph.facebook.com/*' => Http::response(['data' => []], 200),
        ]);

        try {
            // Seed a stale personal duplicate for $sharedPageId
            FacebookPage::create([
                'user_id' => $user->id,
                'business_manager_id' => $personalBm->id,
                'page_id' => $sharedPageId,
                'name' => 'BM Shared Page',
                'source_type' => 'personal',
            ]);

            $service = app(FacebookAdsService::class);
            $service->syncAllPagesForUser($user->id);

            $this->assertDatabaseHas('facebook_pages', [
                'user_id' => $user->id,
                'business_manager_id' => $realBm->id,
                'page_id' => $sharedPageId,
                'source_type' => 'owned',
            ]);

            $this->assertDatabaseMissing('facebook_pages', [
                'user_id' => $user->id,
                'business_manager_id' => $personalBm->id,
                'page_id' => $sharedPageId,
                'source_type' => 'personal',
            ]);

            $this->assertDatabaseHas('facebook_pages', [
                'user_id' => $user->id,
                'business_manager_id' => $personalBm->id,
                'page_id' => $purePersonalPageId,
                'source_type' => 'personal',
            ]);
        } finally {
            FacebookPage::where('user_id', $user->id)->whereIn('page_id', [$sharedPageId, $purePersonalPageId])->delete();
            $realBm->delete();
            $personalBm->delete();
        }
    }

    public function test_pages_index_replaces_delete_button_with_hot_toggle_and_sorts_hot_first()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $olderHotCandidateId = '111000999888_hot_test';
        $newerNormalId = '222000999888_hot_test';

        try {
            $olderPage = FacebookPage::create([
                'user_id' => $user->id,
                'page_id' => $olderHotCandidateId,
                'name' => 'Hot Sort Page Alpha',
                'source_type' => 'personal',
                'is_hot' => false,
            ]);

            $newerPage = FacebookPage::create([
                'user_id' => $user->id,
                'page_id' => $newerNormalId,
                'name' => 'Hot Sort Page Beta',
                'source_type' => 'personal',
                'is_hot' => false,
            ]);

            // Before ticking hot: newerPage appears before olderPage (latest id first)
            $resBefore = $this->actingAs($user)->get('/pages?search=_hot_test');
            $resBefore->assertStatus(200);
            $resBefore->assertDontSee('fa-trash-can', false);
            $resBefore->assertDontSee('Gỡ khỏi danh sách', false);
            $resBefore->assertSee(route('pages.toggleHot', $olderPage->id), false);
            $this->assertEquals(
                [$newerPage->id, $olderPage->id],
                $resBefore->viewData('pages')->pluck('id')->all()
            );

            // Toggle hot on olderPage
            $toggleRes = $this->actingAs($user)->post(route('pages.toggleHot', $olderPage->id));
            $toggleRes->assertRedirect();
            $toggleRes->assertSessionHas('success');

            $this->assertDatabaseHas('facebook_pages', [
                'id' => $olderPage->id,
                'is_hot' => 1,
            ]);

            // After ticking hot: olderPage is sorted first!
            $resAfter = $this->actingAs($user)->get('/pages?search=_hot_test');
            $resAfter->assertStatus(200);
            $resAfter->assertSee('fa-square-check', false);
            $resAfter->assertSee('ID: ' . $olderHotCandidateId, false);
            $resAfter->assertDontSee('Vai trò / Quyền', false);
            $resAfter->assertDontSee('>Page ID</th>', false);
            $this->assertEquals(
                [$olderPage->id, $newerPage->id],
                $resAfter->viewData('pages')->pluck('id')->all()
            );

            // Toggle again -> un-ticks hot
            $this->actingAs($user)->post(route('pages.toggleHot', $olderPage->id))
                ->assertRedirect();
            $this->assertDatabaseHas('facebook_pages', [
                'id' => $olderPage->id,
                'is_hot' => 0,
            ]);
        } finally {
            FacebookPage::where('user_id', $user->id)
                ->whereIn('page_id', [$olderHotCandidateId, $newerNormalId])
                ->delete();
        }
    }

    public function test_pages_sync_last_posts_and_display_column()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $pageWithPostId = '333222111000_lastpost_test';
        $pageEmptyId = '444333222000_lastpost_test';

        Http::fake([
            "graph.facebook.com/*/{$pageWithPostId}/published_posts*" => Http::response([
                'data' => [
                    [
                        'id' => "{$pageWithPostId}_999",
                        'created_time' => '2026-08-13T07:05:07+0000',
                        'permalink_url' => 'https://www.facebook.com/post/999',
                    ],
                ],
            ], 200),
            "graph.facebook.com/*/{$pageEmptyId}/published_posts*" => Http::response([
                'data' => [],
            ], 200),
        ]);

        try {
            FacebookPage::create([
                'user_id' => $user->id,
                'page_id' => $pageWithPostId,
                'name' => 'Page Co Bai Dang',
                'access_token' => 'EAA_PAGE_TOKEN_1',
                'source_type' => 'personal',
            ]);

            FacebookPage::create([
                'user_id' => $user->id,
                'page_id' => $pageEmptyId,
                'name' => 'Page Chua Dang Bai',
                'access_token' => 'EAA_PAGE_TOKEN_2',
                'source_type' => 'personal',
            ]);

            $syncRes = $this->actingAs($user)->postJson(route('pages.syncLastPosts'), [
                'page_ids' => [$pageWithPostId, $pageEmptyId],
            ]);

            $syncRes->assertStatus(200);
            $syncRes->assertJson([
                'success' => true,
                'updated_count' => 2,
                'posts' => [
                    $pageWithPostId => [
                        'has_post' => true,
                        'date_text' => '13/08/2026 14:05',
                        'permalink_url' => 'https://www.facebook.com/post/999',
                    ],
                    $pageEmptyId => [
                        'has_post' => false,
                        'date_text' => 'Chưa có bài đăng',
                    ],
                ],
            ]);

            $indexRes = $this->actingAs($user)->get('/pages?search=_lastpost_test');
            $indexRes->assertStatus(200);
            $indexRes->assertSee('Lần đăng gần nhất');
            $indexRes->assertSee('13/08/2026 14:05');
            $indexRes->assertSee('https://www.facebook.com/post/999', false);
            $indexRes->assertSee('Chưa có bài đăng');
        } finally {
            FacebookPage::where('user_id', $user->id)
                ->whereIn('page_id', [$pageWithPostId, $pageEmptyId])
                ->delete();
        }
    }
}
