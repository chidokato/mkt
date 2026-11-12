<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Truy cập bị từ chối. Chỉ dành cho Quản trị viên.'], 403);
            }

            return redirect()->route('admin.login')->with('error', 'Bạn cần đăng nhập bằng tài khoản Quản trị viên để truy cập khu vực này.');
        }

        return $next($request);
    }
}
