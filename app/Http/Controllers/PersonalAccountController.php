<?php

namespace App\Http\Controllers;

use App\Models\BusinessManager;
use App\Models\AdAccount;
use App\Services\FacebookAdsService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PersonalAccountController extends Controller
{
    protected FacebookAdsService $fbService;

    public function __construct(FacebookAdsService $fbService)
    {
        $this->fbService = $fbService;
    }

    /**
     * Danh sách Tài khoản Quảng cáo Cá nhân (Tài sản khác không thuộc BM)
     */
    public function index()
    {
        $userId = Auth::id();

        $personalBm = BusinessManager::where('user_id', $userId)
            ->personal()
            ->with('adAccounts')
            ->first();

        $accounts = $personalBm ? $personalBm->adAccounts : collect();

        $stats = [
            'total_accounts' => $accounts->count(),
            'active_accounts' => $accounts->where('account_status', 1)->count(),
            'disabled_accounts' => $accounts->where('account_status', 2)->count(),
            'total_spent' => $accounts->sum('amount_spent'),
            'total_balance' => (float) $accounts->where('account_status', 3)->sum('balance'),
        ];

        return view('personal_accounts.index', compact('personalBm', 'accounts', 'stats'));
    }

    /**
     * Đồng bộ lại tài khoản cá nhân từ Facebook
     */
    public function sync()
    {
        $userId = Auth::id();

        $bmWithToken = BusinessManager::where('user_id', $userId)
            ->whereNotNull('access_token')
            ->latest()
            ->first();

        if (!$bmWithToken || empty($bmWithToken->access_token)) {
            return redirect()->route('auth.facebook')->with('warning', 'Vui lòng kết nối tài khoản Facebook để lấy dữ liệu.');
        }

        try {
            $this->fbService->syncAllBusinessesAndAccounts($userId, $bmWithToken->access_token);
            return back()->with('success', 'Đã đồng bộ lại tài khoản cá nhân thành công!');
        } catch (Exception $e) {
            return back()->with('error', 'Lỗi đồng bộ tài khoản cá nhân: ' . $e->getMessage());
        }
    }
}
