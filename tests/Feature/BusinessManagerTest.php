<?php

namespace Tests\Feature;

use App\Models\BusinessManager;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BusinessManagerTest extends TestCase
{
    public function test_user_can_view_bm_list_page()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $response = $this->actingAs($user)->get('/bms');
        $response->assertStatus(200);
        $response->assertSee('Đồng bộ tất cả');
        $response->assertSee('Quản lý BM');
    }

    public function test_user_can_view_bm_show_page()
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found');
        }

        $bm = BusinessManager::where('user_id', $user->id)->first();
        if (!$bm) {
            $bm = BusinessManager::create([
                'user_id' => $user->id,
                'name' => 'BM Test Demo',
                'bm_id' => '123456789',
                'access_token' => 'EAATestToken',
                'status' => 'active',
            ]);
        }

        $response = $this->actingAs($user)->get('/bms/' . $bm->id);
        $response->assertStatus(200);
        $response->assertSee($bm->name);
        $response->assertSee('Tài khoản QC');
        $response->assertSee('Đối tác');
    }

    public function test_user_can_update_bm()
    {
        $user = User::first();
        $bm = BusinessManager::where('user_id', $user->id)->first();

        $response = $this->actingAs($user)->put('/bms/' . $bm->id, [
            'name' => $bm->name . ' - Updated',
            'bm_id' => $bm->bm_id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('business_managers', [
            'id' => $bm->id,
            'name' => $bm->name . ' - Updated',
        ]);

        // Revert name back
        $bm->update(['name' => str_replace(' - Updated', '', $bm->name)]);
    }

    public function test_user_can_check_token_ajax()
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'data' => [
                    'is_valid' => true,
                    'app_id' => '123456',
                    'application' => 'Test App',
                    'type' => 'USER',
                    'expires_at' => 0,
                    'scopes' => ['ads_management', 'ads_read'],
                ]
            ], 200),
        ]);

        $user = User::first();
        $bm = BusinessManager::where('user_id', $user->id)->first();

        $response = $this->actingAs($user)->postJson('/bms/' . $bm->id . '/check-token');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_valid' => true,
        ]);
    }

    public function test_user_can_sync_all_bms_from_facebook()
    {
        Http::fake([
            'graph.facebook.com/*/me/businesses*' => Http::response([
                'data' => [
                    ['id' => '999901', 'name' => 'BM Test 1'],
                    ['id' => '999902', 'name' => 'BM Test 2'],
                ]
            ], 200),
            'graph.facebook.com/*/me/adaccounts*' => Http::response([
                'data' => [
                    [
                        'id' => 'act_111111',
                        'name' => 'TK QC 1',
                        'account_status' => 1,
                        'currency' => 'VND',
                        'amount_spent' => '10000',
                        'balance' => '0',
                        'spend_cap' => '0',
                        'business' => ['id' => '999901', 'name' => 'BM Test 1']
                    ]
                ]
            ], 200),
        ]);

        $user = User::first();
        $response = $this->actingAs($user)->post('/bms/sync-all-facebook');
        $response->assertSessionHas('success');
    }

    public function test_admin_can_view_admin_bms_page()
    {
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $this->markTestSkipped('No admin user');
        }

        $response = $this->actingAs($admin)->get('/admin/bms');
        $response->assertStatus(200);
        $response->assertSee('Danh sách Business Managers Toàn Sàn');
        $response->assertSee('QUẢN LÝ BM HỆ THỐNG');
    }

    public function test_user_can_view_personal_accounts_page()
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/personal-accounts');
        $response->assertStatus(200);
        $response->assertSee('Tài khoản Quảng cáo Cá nhân');
        $response->assertSee('Tài sản khác');
    }

    public function test_user_can_view_dashboard_with_separated_sections()
    {
        $user = User::where('email', 'tuan.nhadatvn@gmail.com')->first() ?? User::first();
        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
        $response->assertSee('Tổng Ad Accounts', false);
        if ($user->email === 'tuan.nhadatvn@gmail.com') {
            $response->assertSee('Tài sản khác (Tài khoản cá nhân)');
        }
    }

    public function test_bm_list_does_not_show_personal_account_in_table()
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/bms');
        $response->assertStatus(200);
        // Ensure personal_ prefix is not in the BM table
        $response->assertDontSee('ID: personal_');
    }

    public function test_flash_message_renders_in_bottom_left_toast()
    {
        $user = User::first();
        $response = $this->actingAs($user)->withSession(['success' => 'Đồng bộ thành công!'])->get('/bms');
        $response->assertStatus(200);
        $response->assertSee('id="toastContainer"', false);
        $response->assertSee('toast-item', false);
        $response->assertSee('Đồng bộ thành công!');
    }

    public function test_bm_list_has_partners_column()
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/bms');
        $response->assertStatus(200);
        $response->assertSee('Quản lý Đối tác');
        $response->assertDontSee('Tài sản khác: Có 1 tài khoản quảng cáo cá nhân');
    }

    public function test_user_can_get_bm_partners_ajax()
    {
        $user = User::first();
        $bm = BusinessManager::where('user_id', $user->id)->first();
        if ($bm) {
            $response = $this->actingAs($user)->getJson('/bms/' . $bm->id . '/partners');
            $response->assertStatus(200);
            $response->assertJson([
                'success' => true,
                'bm_id' => $bm->id,
            ]);
        }
    }

    public function test_bm_list_shows_debt_account_count()
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/bms');
        $response->assertStatus(200);
        $response->assertSee('TK nợ');

        $responseDebtFilter = $this->actingAs($user)->get('/bms?status=has_debt');
        $responseDebtFilter->assertStatus(200);
    }

    public function test_user_can_view_account_campaigns_and_partners_page()
    {
        $user = User::first();
        $bm = BusinessManager::where('user_id', $user->id)->first();
        if (!$bm) {
            $bm = BusinessManager::create([
                'user_id' => $user->id,
                'name' => 'BM Test Demo',
                'bm_id' => '123456789',
                'access_token' => 'EAATestToken',
                'status' => 'active',
            ]);
        }

        $account = \App\Models\AdAccount::where('business_manager_id', $bm->id)->first();
        if (!$account) {
            $account = \App\Models\AdAccount::create([
                'business_manager_id' => $bm->id,
                'ad_account_id' => 'act_1234567890',
                'name' => 'TKQC Test Demo',
                'account_status' => 1,
                'currency' => 'VND',
                'amount_spent' => 1000000,
                'balance' => 0,
                'payment_method_display' => 'VISA *4809',
            ]);
        }

        $response = $this->actingAs($user)->get('/accounts/' . $account->id . '/campaigns');
        $response->assertStatus(200);
        $response->assertSee($account->name);
        $response->assertSee('Chiến dịch');
        $response->assertSee('Đối tác');
        $response->assertSee('accountSwitcherContainer', false);
        $response->assertSee('Thanh toán FB');
    }

    public function test_user_can_view_accounts_index_page()
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/accounts');
        $response->assertStatus(200);
        $response->assertSee('Quản lý Tài khoản Quảng cáo');
        $response->assertSee('Danh sách Tài khoản Quảng cáo');
        $response->assertSee('Tổng TKQC');
    }

    public function test_sidebar_contains_quan_ly_tkqc_menu()
    {
        $user = User::first();
        $response = $this->actingAs($user)->get('/');
        $response->assertStatus(200);
        $response->assertSee('Quản lý TKQC');
        $response->assertSee(route('accounts.index'), false);
    }

    public function test_accounts_index_deduplicates_accounts_shared_across_multiple_bms()
    {
        $acc = \App\Models\AdAccount::where('ad_account_id', 'act_289343496800749')->with('businessManager')->first();
        if ($acc && $acc->businessManager) {
            $user = User::find($acc->businessManager->user_id);
        } else {
            $user = User::first();
        }

        // Check /accounts page with status=2 (Vô hiệu hóa)
        $response = $this->actingAs($user)->get('/accounts?account_status=2');
        $response->assertStatus(200);

        if ($acc) {
            // In User 3 database, before deduplication there were 4 rows (2 duplicates for each of the 2 disabled accounts)
            // After deduplication, there are exactly 2 unique accounts
            $accounts = $response->viewData('accounts');
            $this->assertEquals(2, $accounts->total(), 'Disabled accounts should be deduplicated to exactly 2 distinct accounts');
            $this->assertCount(2, $accounts);

            // Also check that all accounts in view have unique ad_account_id
            $ids = $accounts->pluck('ad_account_id')->toArray();
            $this->assertEquals(count($ids), count(array_unique($ids)), 'No duplicate ad_account_id should exist in the accounts index list');
        }
    }
}
