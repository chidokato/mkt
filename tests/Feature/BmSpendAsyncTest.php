<?php

namespace Tests\Feature;

use App\Models\AdAccount;
use App\Models\BusinessManager;
use App\Models\User;
use App\Services\FacebookAdsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BmSpendAsyncTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_bms_index_does_not_call_facebook_api_when_cache_is_cold(): void
    {
        Http::fake();

        $user = User::factory()->create();
        $bm = BusinessManager::create([
            'user_id' => $user->id,
            'name' => 'BM Fast Load',
            'bm_id' => '1000001',
            'access_token' => 'EAAFakeToken1',
            'status' => 'active',
        ]);
        AdAccount::create([
            'business_manager_id' => $bm->id,
            'ad_account_id' => 'act_101',
            'name' => 'Acc 101',
            'account_status' => 1,
            'currency' => 'VND',
            'amount_spent' => 500000,
            'balance' => 0,
            'source_type' => 'owned',
        ]);

        $response = $this->actingAs($user)->get('/bms');

        $response->assertStatus(200);
        $response->assertSee('BM Fast Load');
        $response->assertSee('Đang tải...');
        $response->assertSee('loadBmSpends', false);
        Http::assertNothingSent();
    }

    public function test_spend_summary_skips_empty_edges_deduplicates_shared_accounts_and_caches(): void
    {
        Http::fake(function (Request $request) {
            $url = $request->url();
            if (str_contains($url, '1000001/owned_ad_accounts')) {
                if (str_contains($url, 'after=page2')) {
                    return Http::response([
                        'data' => [
                            [
                                'id' => 'act_owned_2',
                                'name' => 'Owned Acc 2',
                                'insights' => ['data' => [['spend' => '250000']]],
                            ],
                        ],
                    ], 200);
                }

                return Http::response([
                    'data' => [
                        [
                            'id' => 'act_shared_1',
                            'name' => 'Shared Acc 1',
                            'insights' => ['data' => [['spend' => '100000']]],
                        ],
                    ],
                    'paging' => [
                        'next' => 'https://graph.facebook.com/v21.0/1000001/owned_ad_accounts?after=page2',
                    ],
                ], 200);
            }

            if (str_contains($url, '1000002/client_ad_accounts')) {
                return Http::response([
                    'data' => [
                        [
                            'id' => 'act_shared_1',
                            'name' => 'Shared Acc 1',
                            'insights' => ['data' => [['spend' => '100000']]],
                        ],
                    ],
                ], 200);
            }

            return Http::response(['error' => ['message' => 'Unexpected request: ' . $url]], 500);
        });

        $user = User::factory()->create();

        // BM 1: chỉ có owned (không có client -> không được gọi client_ad_accounts)
        $bm1 = BusinessManager::create([
            'user_id' => $user->id,
            'name' => 'BM Owned Only',
            'bm_id' => '1000001',
            'access_token' => 'EAAFakeToken1',
            'status' => 'active',
        ]);
        AdAccount::create([
            'business_manager_id' => $bm1->id,
            'ad_account_id' => 'act_shared_1',
            'name' => 'Shared Acc 1',
            'account_status' => 1,
            'currency' => 'VND',
            'amount_spent' => 900000,
            'balance' => 0,
            'source_type' => 'owned',
        ]);
        AdAccount::create([
            'business_manager_id' => $bm1->id,
            'ad_account_id' => 'act_owned_2',
            'name' => 'Owned Acc 2',
            'account_status' => 1,
            'currency' => 'VND',
            'amount_spent' => 900000,
            'balance' => 0,
            'source_type' => 'owned',
        ]);

        // BM 2: chỉ có client (không có owned -> không được gọi owned_ad_accounts)
        $bm2 = BusinessManager::create([
            'user_id' => $user->id,
            'name' => 'BM Client Only',
            'bm_id' => '1000002',
            'access_token' => 'EAAFakeToken2',
            'status' => 'active',
        ]);
        AdAccount::create([
            'business_manager_id' => $bm2->id,
            'ad_account_id' => 'act_shared_1',
            'name' => 'Shared Acc 1',
            'account_status' => 1,
            'currency' => 'VND',
            'amount_spent' => 900000,
            'balance' => 0,
            'source_type' => 'client',
        ]);

        // BM 3: 0 tài khoản -> hoàn toàn không được gọi API
        $bm3 = BusinessManager::create([
            'user_id' => $user->id,
            'name' => 'BM Empty',
            'bm_id' => '1000003',
            'access_token' => 'EAAFakeToken3',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->getJson('/bms/spend-summary?date_preset=last_30d');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'date_preset' => 'last_30d',
                'total_spent' => 350000.0, // 100k (deduped) + 250k
                'total_spent_formatted' => '350.000 đ',
                'has_errors' => false,
                'bm_spends' => [
                    $bm1->id => ['total_spend' => 350000.0, 'formatted' => '350.000 đ', 'has_error' => false],
                    $bm2->id => ['total_spend' => 100000.0, 'formatted' => '100.000 đ', 'has_error' => false],
                    $bm3->id => ['total_spend' => 0.0, 'formatted' => '0 đ', 'has_error' => false],
                ],
            ]);

        // Chỉ gửi 3 HTTP request (page 1 owned BM1, page 2 owned BM1, client BM2)
        Http::assertSentCount(3);
        Http::assertNotSent(fn (Request $req) => str_contains($req->url(), '1000001/client_ad_accounts'));
        Http::assertNotSent(fn (Request $req) => str_contains($req->url(), '1000002/owned_ad_accounts'));
        Http::assertNotSent(fn (Request $req) => str_contains($req->url(), '1000003/'));

        // Lần mở /bms tiếp theo phải đọc từ cache (hiển thị ngay số liệu, không chờ AJAX)
        $warmPage = $this->actingAs($user)->get('/bms');
        $warmPage->assertStatus(200);
        $warmPage->assertSee('350.000 đ');
        $warmPage->assertDontSee('Đang tải...');

        // Gọi lại /bms/spend-summary không refresh -> dùng cache (vẫn tổng 3 request)
        $this->actingAs($user)->getJson('/bms/spend-summary?date_preset=last_30d')->assertStatus(200);
        Http::assertSentCount(3);

        // Gọi với refresh=1 -> bỏ qua cache, gọi lại 3 request
        $this->actingAs($user)->getJson('/bms/spend-summary?date_preset=last_30d&refresh=1')->assertStatus(200);
        Http::assertSentCount(6);
    }

    public function test_spend_summary_isolates_failed_bm_and_does_not_cache_errors(): void
    {
        Http::fake([
            'graph.facebook.com/*/2000001/owned_ad_accounts*' => Http::response([
                'data' => [
                    ['id' => 'act_ok', 'insights' => ['data' => [['spend' => '50000']]]],
                ],
            ], 200),
            'graph.facebook.com/*/2000002/owned_ad_accounts*' => Http::response([
                'error' => ['message' => 'Invalid OAuth access token'],
            ], 401),
        ]);

        $user = User::factory()->create();

        $bmOk = BusinessManager::create([
            'user_id' => $user->id,
            'name' => 'BM OK',
            'bm_id' => '2000001',
            'access_token' => 'EAAOk',
            'status' => 'active',
        ]);
        AdAccount::create([
            'business_manager_id' => $bmOk->id,
            'ad_account_id' => 'act_ok',
            'name' => 'OK',
            'account_status' => 1,
            'currency' => 'VND',
            'amount_spent' => 0,
            'balance' => 0,
            'source_type' => 'owned',
        ]);

        $bmFail = BusinessManager::create([
            'user_id' => $user->id,
            'name' => 'BM Fail',
            'bm_id' => '2000002',
            'access_token' => 'EAAExpired',
            'status' => 'active',
        ]);
        AdAccount::create([
            'business_manager_id' => $bmFail->id,
            'ad_account_id' => 'act_fail',
            'name' => 'Fail',
            'account_status' => 1,
            'currency' => 'VND',
            'amount_spent' => 0,
            'balance' => 0,
            'source_type' => 'owned',
        ]);

        $res = $this->actingAs($user)->getJson('/bms/spend-summary?date_preset=last_30d');
        $res->assertStatus(200)
            ->assertJson([
                'success' => true,
                'has_errors' => true,
                'total_spent' => 50000.0,
                'bm_spends' => [
                    $bmOk->id => ['total_spend' => 50000.0, 'has_error' => false],
                    $bmFail->id => ['total_spend' => 0.0, 'has_error' => true],
                ],
            ]);

        $fbService = app(FacebookAdsService::class);
        $this->assertNotNull($fbService->getCachedBmSpend($bmOk, 'last_30d'));
        $this->assertNull($fbService->getCachedBmSpend($bmFail, 'last_30d'));
    }

    public function test_clear_bm_spend_cache_invalidates_previous_cached_value(): void
    {
        Http::fake([
            'graph.facebook.com/*/3000001/owned_ad_accounts*' => Http::response([
                'data' => [
                    ['id' => 'act_301', 'insights' => ['data' => [['spend' => '120000']]]],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();
        $bm = BusinessManager::create([
            'user_id' => $user->id,
            'name' => 'BM Cache Clear',
            'bm_id' => '3000001',
            'access_token' => 'EAACache',
            'status' => 'active',
        ]);
        AdAccount::create([
            'business_manager_id' => $bm->id,
            'ad_account_id' => 'act_301',
            'name' => 'Acc 301',
            'account_status' => 1,
            'currency' => 'VND',
            'amount_spent' => 0,
            'balance' => 0,
            'source_type' => 'owned',
        ]);

        $fbService = app(FacebookAdsService::class);
        $data = $fbService->getBmSpendByDate($bm, 'last_30d');
        $this->assertSame(120000.0, $data['total_spend']);
        $this->assertNotNull($fbService->getCachedBmSpend($bm, 'last_30d'));

        $fbService->clearBmSpendCache($bm->id);
        $this->assertNull($fbService->getCachedBmSpend($bm, 'last_30d'));
    }
}
