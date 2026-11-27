<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use App\Models\Menu;
use App\Models\Task;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
        // Nếu bạn dùng BS4 thì đổi thành:
        // Paginator::useBootstrapFour();
        DB::statement("SET time_zone = '+07:00'");

        View::composer('*', function ($view) {
            $setting = Setting::find(1); // có thể dùng số luôn
            $menu = Menu::orderBy('view', 'asc')->get();

            $sumPrice = Task::where('extra_money', '>', 0)
                ->where('user', Auth::id())
                ->where('settled', 0)
                ->sum('extra_money');


            view()->share([
                'setting' => $setting,
                'menu'    => $menu,
                'sumPrice'    => $sumPrice,
            ]);
        });
    }
}
