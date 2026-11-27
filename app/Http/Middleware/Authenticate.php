<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    // protected function redirectTo($request)
    // {
    //     if (! $request->expectsJson()) {
    //         return route('login');
    //     }
    // }
    public function handle($request, Closure $next, ...$guards)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Bạn không có quyền truy cập');
        }

        $user = Auth::User(); // lấy thông tin user khi đã đăng nhập

        if ($user->permission == 1)
            return $next($request);
        else
            return redirect()->route('login')->with('error', 'Bạn không có quyền truy cập');

        // return $next($request);
    }
}
