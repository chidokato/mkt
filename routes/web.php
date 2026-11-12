<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdAccountController;
use App\Http\Controllers\BusinessManagerController;
use App\Http\Controllers\PersonalAccountController;
use App\Http\Controllers\FacebookPageController;
use App\Http\Controllers\PagePostController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminBMController;

/*
|--------------------------------------------------------------------------
| Web Routes - Facebook Ads SaaS Platform
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. DÀNH CHO NGƯỜI DÙNG (USER / MEMBER)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Khu vực Người dùng quản lý tài khoản quảng cáo
Route::middleware('auth')->group(function () {
    Route::get('/', [AdAccountController::class, 'index'])->name('dashboard');

    // Quản lý Business Manager (BM) của User
    Route::get('/bms', [BusinessManagerController::class, 'index'])->name('bms.index');
    Route::get('/bms/spend-summary', [BusinessManagerController::class, 'spendSummary'])->name('bms.spendSummary');
    Route::post('/bms', [BusinessManagerController::class, 'store'])->name('bms.store');
    Route::get('/bms/{bm}', [BusinessManagerController::class, 'show'])->name('bms.show');
    Route::put('/bms/{bm}', [BusinessManagerController::class, 'update'])->name('bms.update');
    Route::delete('/bms/{bm}', [BusinessManagerController::class, 'destroy'])->name('bms.destroy');
    Route::post('/bms/{bm}/sync', [BusinessManagerController::class, 'sync'])->name('bms.sync');
    Route::post('/bms/{bm}/check-token', [BusinessManagerController::class, 'checkToken'])->name('bms.checkToken');
    Route::get('/bms/{bm}/partners', [BusinessManagerController::class, 'partners'])->name('bms.partners');
    Route::post('/bms/{bm}/sync-partners', [BusinessManagerController::class, 'syncPartners'])->name('bms.syncPartners');
    Route::post('/bms/sync-all-facebook', [BusinessManagerController::class, 'syncAllFacebook'])->name('bms.syncAllFacebook');

    // Quản lý Ad Account & Chiến dịch của User
    Route::get('/accounts', [AdAccountController::class, 'accountsIndex'])->name('accounts.index');
    Route::get('/personal-accounts', [PersonalAccountController::class, 'index'])->name('personal-accounts.index');
    Route::post('/personal-accounts/sync', [PersonalAccountController::class, 'sync'])->name('personal-accounts.sync');

    // Quản lý Facebook Pages & Đăng bài hàng loạt
    Route::get('/pages', [FacebookPageController::class, 'index'])->name('pages.index');
    Route::get('/pages/publish', [PagePostController::class, 'index'])->name('pages.publish.index');
    Route::post('/pages/publish', [PagePostController::class, 'store'])->name('pages.publish.store');
    Route::post('/pages/posts/sync-status', [PagePostController::class, 'syncStatus'])->name('pages.posts.syncStatus');
    Route::post('/pages/posts/{post}/retry', [PagePostController::class, 'retry'])->name('pages.posts.retry');
    Route::delete('/pages/posts/{post}', [PagePostController::class, 'destroy'])->name('pages.posts.destroy');
    Route::post('/pages/sync', [FacebookPageController::class, 'sync'])->name('pages.sync');
    Route::post('/pages/sync-last-posts', [FacebookPageController::class, 'syncLastPosts'])->name('pages.syncLastPosts');
    Route::post('/pages/token', [FacebookPageController::class, 'storeToken'])->name('pages.token');
    Route::post('/pages/manual', [FacebookPageController::class, 'storePage'])->name('pages.manual');
    Route::post('/pages/{page}/toggle-hot', [FacebookPageController::class, 'toggleHot'])->name('pages.toggleHot');
    Route::delete('/pages/{page}', [FacebookPageController::class, 'destroy'])->name('pages.destroy');

    Route::get('/accounts/{account}/campaigns', [AdAccountController::class, 'campaigns'])->name('accounts.campaigns');
    Route::post('/campaigns/{campaign}/toggle', [AdAccountController::class, 'toggleCampaign'])->name('campaigns.toggle');

    // Đăng nhập kết nối Facebook OAuth của User
    Route::get('/auth/facebook', [AdAccountController::class, 'redirectToFacebook'])->name('auth.facebook');
    Route::get('/auth/facebook/callback', [AdAccountController::class, 'handleFacebookCallback'])->name('auth.facebook.callback');
});


// ==========================================
// 2. DÀNH RIÊNG CHO QUẢN TRỊ VIÊN (ADMIN)
// ==========================================
Route::prefix('admin')->as('admin.')->group(function () {
    // Đăng nhập Admin riêng biệt
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout')->middleware('auth');

    // Khu vực Quản trị bảo vệ bằng 'auth' và 'admin' middleware
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // Quản lý danh sách Người dùng
        Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');
        Route::delete('/users/{user}', [AdminDashboardController::class, 'deleteUser'])->name('users.delete');

        // Quản lý Business Managers toàn hệ thống
        Route::get('/bms', [AdminBMController::class, 'index'])->name('bms.index');
        Route::delete('/bms/{bm}', [AdminBMController::class, 'destroy'])->name('bms.destroy');
        Route::post('/bms/{bm}/sync', [AdminBMController::class, 'sync'])->name('bms.sync');
        Route::post('/bms/{bm}/check-token', [AdminBMController::class, 'checkToken'])->name('bms.checkToken');

        // Cấu hình nền tảng & Meta Developer App
        Route::get('/settings', [SettingController::class, 'index'])->name('settings');
        Route::post('/settings/meta', [SettingController::class, 'updateMeta'])->name('settings.meta');
        Route::post('/settings/profile', [SettingController::class, 'updateProfile'])->name('settings.profile');
    });
});
