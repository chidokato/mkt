<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessManager;
use App\Models\AdAccount;
use App\Models\User;
use App\Services\FacebookAdsService;
use Exception;
use Illuminate\Http\Request;

class AdminBMController extends Controller
{
    protected FacebookAdsService $fbService;

    public function __construct(FacebookAdsService $fbService)
    {
        $this->fbService = $fbService;
    }

    /**
     * Quản lý danh sách toàn bộ Business Managers trên toàn hệ thống
     */
    public function index(Request $request)
    {
        $query = BusinessManager::with(['user', 'adAccounts'])->latest();

        // Tìm kiếm theo tên BM, BM ID hoặc tên/email người dùng
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('bm_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Lọc theo người dùng cụ thể
        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        // Lọc theo trạng thái
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $bms = $query->paginate(15)->withQueryString();
        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        $stats = [
            'total_bms' => BusinessManager::count(),
            'active_bms' => BusinessManager::where('status', 'active')->count(),
            'total_accounts' => AdAccount::count(),
            'active_accounts' => AdAccount::where('account_status', 1)->count(),
            'total_spent' => AdAccount::sum('amount_spent'),
        ];

        return view('admin.bms.index', compact('bms', 'users', 'stats', 'search'));
    }

    /**
     * Quản trị viên xóa một BM
     */
    public function destroy($id)
    {
        $bm = BusinessManager::findOrFail($id);
        $name = $bm->name;
        $bm->delete();

        return back()->with('success', "Quản trị viên đã xóa BM [{$name}] cùng toàn bộ tài khoản quảng cáo liên kết!");
    }

    /**
     * Quản trị viên kích hoạt đồng bộ tài khoản cho một BM
     */
    public function sync($id)
    {
        $bm = BusinessManager::findOrFail($id);

        try {
            $result = $this->fbService->syncBmAccounts($bm);
            $owned = $result['owned'];
            $client = $result['client'];
            return back()->with('success', "Quản trị viên đã đồng bộ BM [{$bm->name}]: {$owned} tài khoản của BM, {$client} tài khoản được share!");
        } catch (Exception $e) {
            return back()->with('error', "Lỗi khi đồng bộ: " . $e->getMessage());
        }
    }

    /**
     * Quản trị viên kiểm tra trạng thái Token của một BM
     */
    public function checkToken(Request $request, $id)
    {
        $bm = BusinessManager::findOrFail($id);

        try {
            $tokenInfo = $this->fbService->inspectToken($bm->access_token);
            $bm->status = $tokenInfo['is_valid'] ? 'active' : 'expired';
            $bm->save();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'is_valid' => $tokenInfo['is_valid'],
                    'token_info' => $tokenInfo,
                ]);
            }

            return back()->with(
                $tokenInfo['is_valid'] ? 'success' : 'error',
                $tokenInfo['is_valid'] 
                    ? "Token của BM [{$bm->name}] hợp lệ! Hạn dùng: {$tokenInfo['expires_at_text']}."
                    : "Token của BM [{$bm->name}] không hợp lệ: " . ($tokenInfo['error'] ?? 'Hết hạn.')
            );
        } catch (Exception $e) {
            return back()->with('error', "Lỗi kiểm tra Token: " . $e->getMessage());
        }
    }
}
