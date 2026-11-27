<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    private const GOOGLE_PENDING_SESSION_KEY = 'google_pending_profile';

    public function redirectToGoogle(Request $request)
    {
        $google = Socialite::driver('google');

        if ($request->boolean('select_account')) {
            $google = $google->with([
                'prompt' => 'select_account',
            ]);
        }

        return $google->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('dangnhap')->with('error', 'Không thể đăng nhập bằng Google: ' . $e->getMessage());
        }

        $user = User::where('google_id', $googleUser->getId())->first();

        if (!$user) {
            $user = User::where('email', $googleUser->getEmail())
                ->orWhere('secondary_email', $googleUser->getEmail())
                ->first();
        }

        if ($user) {
            $this->syncGoogleData($user, $googleUser);
            $user->save();

            return $this->loginResolvedUser($user);
        }

        Session::put(self::GOOGLE_PENDING_SESSION_KEY, [
            'google_id' => $googleUser->getId(),
            'email' => $googleUser->getEmail(),
            'yourname' => $googleUser->getName() ?? $googleUser->getNickname() ?? '',
            'avatar' => $googleUser->getAvatar(),
            'token' => $googleUser->token ?? null,
            'refresh_token' => $googleUser->refreshToken ?? null,
            'expires_in' => $googleUser->expiresIn ?? null,
        ]);

        return redirect()->route('google.complete.form');
    }

    public function showCompleteProfileForm()
    {
        $pending = Session::get(self::GOOGLE_PENDING_SESSION_KEY);

        if (!$pending) {
            return redirect()->route('dangnhap')->with('warning', 'Phiên đăng nhập Google đã hết hạn. Vui lòng thử lại.');
        }

        $departments = Department::all();
        $departmentTree = $this->buildDepartmentTree($departments);

        return view('account.auth.google-complete-profile', [
            'pendingGoogle' => $pending,
            'departmentTree' => $departmentTree,
        ]);
    }

    public function completeProfile(Request $request)
    {
        $pending = Session::get(self::GOOGLE_PENDING_SESSION_KEY);

        if (!$pending) {
            return redirect()->route('dangnhap')->with('warning', 'Phiên đăng nhập Google đã hết hạn. Vui lòng thử lại.');
        }

        $validated = $request->validate(
            [
                'employee_code' => 'required|string|max:255|unique:users,employee_code',
                'yourname' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'department_id' => 'required|exists:departments,id',
            ],
            [
                'employee_code.unique' => 'Mã nhân viên đã tồn tại trong hệ thống.',
            ]
        );

        $existingUser = User::where('email', $pending['email'])
            ->orWhere('secondary_email', $pending['email'])
            ->first();

        if ($existingUser) {
            $this->syncGoogleData($existingUser, (object) [
                'id' => $pending['google_id'],
                'token' => $pending['token'],
                'refreshToken' => $pending['refresh_token'],
                'expiresIn' => $pending['expires_in'],
                'avatar' => $pending['avatar'],
            ]);
            $existingUser->save();

            Session::forget(self::GOOGLE_PENDING_SESSION_KEY);

            return $this->loginResolvedUser($existingUser);
        }

        $department = Department::with('parentDepartment.parentDepartment')->findOrFail($validated['department_id']);
        $status = Str::endsWith($pending['email'], '@dxmb.vn') ? 'active' : 'inactive';

        $user = new User();
        $user->employee_code = $validated['employee_code'];
        $user->yourname = $validated['yourname'];
        $user->email = $pending['email'];
        $user->phone = $validated['phone'];
        $user->department_id = $department->id;
        $user->department_lv2 = $department->parentDepartment?->id;
        $user->department_lv1 = $department->parentDepartment?->parentDepartment?->id;
        $user->permission = 6;
        $user->rank = 3;
        $user->status = $status;
        $user->password = bcrypt(Str::random(24));

        $this->syncGoogleData($user, (object) [
            'id' => $pending['google_id'],
            'token' => $pending['token'],
            'refreshToken' => $pending['refresh_token'],
            'expiresIn' => $pending['expires_in'],
            'avatar' => $pending['avatar'],
        ]);

        $user->save();
        Session::forget(self::GOOGLE_PENDING_SESSION_KEY);

        return $this->loginResolvedUser($user);
    }

    private function loginResolvedUser(User $user)
    {
        Auth::login($user, true);

        if ($user->permission < 6) {
            return redirect()->route('admin');
        }

        if ((int) $user->permission === 6) {
            if ($user->status === 'active') {
                return redirect()->route('account.profile.edit');
            }

            Auth::logout();

            return redirect()->route('dangnhap')->with(
                'center_warning',
                'Bạn đã kết nối vào hệ thống thành công. Do hệ thống chỉ lưu hành nội bộ. Bạn cần liên hệ Admin để cấp quyền truy cập cao hơn ! Admin: 0977572947'
            );
        }

        Auth::logout();

        return redirect()->route('dangnhap')->with('error', 'Tài khoản không hợp lệ');
    }

    private function syncGoogleData(User $user, $googleUser): void
    {
        $googleId = $googleUser->id ?? (method_exists($googleUser, 'getId') ? $googleUser->getId() : null);
        $avatar = $googleUser->avatar ?? (method_exists($googleUser, 'getAvatar') ? $googleUser->getAvatar() : null);

        if ($googleId) {
            $user->google_id = $googleId;
        }

        if ($avatar) {
            $user->avatar = $avatar;
        }

        if (!empty($googleUser->token ?? null)) {
            $user->google_token = encrypt($googleUser->token);
        }

        if (!empty($googleUser->refreshToken ?? null)) {
            $user->google_refresh_token = encrypt($googleUser->refreshToken);
        }

        if (!empty($googleUser->expiresIn ?? null)) {
            $user->google_token_expires_at = now()->addSeconds($googleUser->expiresIn);
        }
    }

    private function buildDepartmentTree($departments, $parentId = 0, $prefix = '', $level = 1)
    {
        $tree = [];

        foreach ($departments as $department) {
            if ((int) ($department->parent ?? 0) === (int) $parentId) {
                $department->name_with_prefix = $prefix . $department->name;
                $department->level = $level;
                $tree[] = $department;
                $tree = array_merge($tree, $this->buildDepartmentTree($departments, $department->id, $prefix . '-- ', $level + 1));
            }
        }

        return $tree;
    }
}
