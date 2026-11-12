<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BusinessManager;
use App\Models\AdAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    /**
     * Bảng điều khiển Quản trị viên (Admin Dashboard)
     */
    public function index()
    {
        $stats = [
            'total_users' => User::where('role', 'user')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_bms' => BusinessManager::count(),
            'total_accounts' => AdAccount::count(),
            'active_accounts' => AdAccount::where('account_status', 1)->count(),
            'disabled_accounts' => AdAccount::where('account_status', 2)->count(),
            'total_spent' => AdAccount::sum('amount_spent'),
            'total_balance' => AdAccount::sum('balance'),
        ];

        $recentUsers = User::withCount(['businessManagers', 'adAccounts'])
            ->latest()
            ->take(8)
            ->get();

        $recentBMs = BusinessManager::with(['user', 'adAccounts'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentBMs'));
    }

    /**
     * Danh sách tất cả người dùng trong hệ thống
     */
    public function users()
    {
        $users = User::withCount(['businessManagers', 'adAccounts'])
            ->latest()
            ->paginate(15);

        return view('admin.users', compact('users'));
    }

    /**
     * Xóa một người dùng
     */
    public function deleteUser($id)
    {
        if (Auth::id() == $id) {
            return back()->with('error', 'Bạn không thể tự xóa tài khoản Quản trị viên của chính mình!');
        }

        $user = User::findOrFail($id);
        $name = $user->name;
        $user->delete();

        return back()->with('success', "Đã xóa người dùng [{$name}] cùng toàn bộ dữ liệu liên kết thành công!");
    }
}
