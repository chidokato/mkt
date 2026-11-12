<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingController extends Controller
{
    /**
     * Màn hình trang Cấu hình hệ thống (Settings)
     */
    public function index()
    {
        $metaSettings = [
            'app_id' => Setting::get('meta_app_id', config('services.meta.app_id', '')),
            'app_secret' => Setting::get('meta_app_secret', config('services.meta.app_secret', '')),
            'version' => Setting::get('meta_graph_version', config('services.meta.version', 'v21.0')),
        ];

        $callbackUrl = route('auth.facebook.callback');

        return view('settings', compact('metaSettings', 'callbackUrl'));
    }

    /**
     * Lưu cấu hình Meta App
     */
    public function updateMeta(Request $request)
    {
        $validated = $request->validate([
            'app_id' => 'required|string|max:100',
            'app_secret' => 'required|string|max:100',
            'version' => 'required|string|max:20',
        ]);

        Setting::set('meta_app_id', trim($validated['app_id']), 'meta');
        Setting::set('meta_app_secret', trim($validated['app_secret']), 'meta');
        Setting::set('meta_graph_version', trim($validated['version']), 'meta');

        return back()->with('success', 'Đã cập nhật cấu hình Meta App thành công!');
    }

    /**
     * Cập nhật thông tin tài khoản Admin và mật khẩu
     */
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'current_password' => 'nullable|required_with:new_password|current_password',
            'new_password' => 'nullable|min:6|confirmed',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['new_password'])) {
            $user->password = Hash::make($validated['new_password']);
        }

        $user->save();

        return back()->with('success', 'Đã cập nhật thông tin tài khoản Admin thành công!');
    }
}
