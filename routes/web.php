<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Users\LoginController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\ChannelController;
use App\Http\Controllers\Admin\DuanController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\AjaxController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\Account\ExpenseController;
use App\Http\Controllers\Account\TaskCostPeriodController;
use App\Http\Controllers\Account\BulkMailController;
use App\Http\Controllers\Account\UserManagementController;

// Auth routes
Route::get('dangnhap', function (\Illuminate\Http\Request $request) {
    if (auth()->check()) {
        return (int) (auth()->user()->permission ?? 6) < 6
            ? redirect()->route('admin')
            : redirect()->route('account.dashboard.main');
    }
    if ($request->boolean('local')) {
        return app(AccountController::class)->dangnhap();
    }
    return app(\App\Http\Controllers\SsoClientController::class)->redirectToPortal();
})->name('dangnhap');
Route::post('dangnhap', [AccountController::class, 'postDangnhap'])->name('post.dangnhap');
Route::get('login', fn() => redirect()->route('dangnhap'))->name('login');
Route::get('sso/callback', [\App\Http\Controllers\SsoClientController::class, 'callback'])->name('sso.callback');
Route::get('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('google.callback');
Route::get('auth/google/complete-profile', [GoogleController::class, 'showCompleteProfileForm'])->name('google.complete.form');
Route::post('auth/google/complete-profile', [GoogleController::class, 'completeProfile'])->name('google.complete.store');

// Ajax helpers
Route::group(['prefix' => 'ajax'], function () {
    Route::get('change_province/{id}', [AjaxController::class, 'change_province']);
    Route::get('change_district/{id}', [AjaxController::class, 'change_district']);
    Route::get('change_district_street/{id}', [AjaxController::class, 'change_district_street']);
    Route::get('change_SortBy/{id}', [AjaxController::class, 'change_SortBy']);
    Route::get('change_parent/{id}', [AjaxController::class, 'change_parent']);
});

// Internal MKT Portal (root = dashboard)
Route::middleware(['user'])->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('account.dashboard.main');
    Route::get('account/main', fn() => redirect()->route('account.dashboard.main'));

    Route::get('opened', [AccountController::class, 'opened'])->name('account.guide.index');
    Route::get('edit', [AccountController::class, 'edit'])->name('account.profile.edit');
    Route::post('update', [AccountController::class, 'update'])->name('account.update');
    Route::post('change-password', [AccountController::class, 'changePassword'])->name('account.change-password');
    Route::post('update-secondary-email', [AccountController::class, 'updateSecondaryEmail'])->name('account.update-secondary-email');
    Route::post('verify-secondary-email-otp', [AccountController::class, 'verifySecondaryEmailOtp'])->name('account.verify-secondary-email-otp');

    // Learning SSO
    Route::get('learning', [AccountController::class, 'ssoLearning'])->name('account.learning');
    Route::get('izi', [AccountController::class, 'ssoIzi'])->name('account.izi');

    // MKT
    Route::get('mkt-register', [AccountController::class, 'mktregister'])->name('account.marketing.register');
    Route::post('mkt-tasksstore', [AccountController::class, 'storeTask'])->name('account.tasksstore');
    Route::get('tasks-stats', [AccountController::class, 'stats'])->name('account.tasks.stats');

    // Task
    Route::resource('task', TaskController::class);
    Route::post('tasks/delete/{id}', [AccountController::class, 'delete'])->name('account.tasks.delete');
    Route::post('task/toggle-approved/{task}', [TaskController::class, 'toggleApproved'])->name('task.toggleApproved');
    Route::post('tasks/update-rate', [TaskController::class, 'updateRate'])->name('tasks.updateRate');
    Route::post('task/update-kpi', [TaskController::class, 'updateKpi'])->name('task.updateKpi');
    Route::post('task/update-video', [TaskController::class, 'updateVideo'])->name('task.updateVideo');
    Route::post('task/update-livestream', [TaskController::class, 'updateLivestream'])->name('task.updateLivestream');
    Route::post('task/update-expected-cost', [TaskController::class, 'updateExpectedCost'])->name('task.updateExpectedCost');
    Route::post('tasks/bulk-update', [TaskController::class, 'bulkUpdateTasks'])->name('account.tasks.bulkUpdate');
    Route::post('tasks/bulk-approve', [TaskController::class, 'bulkApproveTasks'])->name('account.tasks.bulkApprove');
    Route::post('tasks/bulk-delete', [TaskController::class, 'bulkDeleteTasks'])->name('account.tasks.bulkDelete');
    Route::post('tasks/{task}/update-paid', [TaskController::class, 'updatePaid'])->name('tasks.updatePaid');
    Route::post('tasks/{task}/up', [TaskController::class, 'upTask'])->name('tasks.up');
    Route::put('tasks/{task}', [TaskController::class, 'updateall'])->name('tasks.update');

    // Expense management
    Route::get('actualcosts', [ExpenseController::class, 'actualcosts'])->name('tasks.actualcosts');
    Route::get('ns-debt', [ExpenseController::class, 'nsDebt'])->name('tasks.nsDebt');
    Route::post('tasks/bulk-import-actual-costs', [ExpenseController::class, 'bulkImportActualCosts'])->name('tasks.bulkImportActualCosts');
    Route::post('tasks/{task}/actual-costs', [ExpenseController::class, 'ajaxUpdateActualCosts'])->name('tasks.ajaxUpdateActualCosts');
    Route::post('tasks/store', [ExpenseController::class, 'store'])->name('account.task.store');
    Route::get('tasks/expense', [ExpenseController::class, 'expense'])->name('tasks.expense');
    Route::post('tasks/{task}/toggle-settled', [ExpenseController::class, 'toggleSettled'])->name('tasks.toggleSettled');

    // Report
    Route::resource('report', ReportController::class);
    Route::post('report-store', [ReportController::class, 'store'])->name('account.report.store');
    Route::post('report-update', [ReportController::class, 'update'])->name('account.report.update');
    Route::post('report-delete', [ReportController::class, 'delete'])->name('account.report.delete');
    Route::get('load-report', [ReportController::class, 'loadReport'])->name('account.loadReport');
    Route::post('report-active', [ReportController::class, 'active'])->name('account.report.active');
    Route::post('report/{report}/recalc-expected', [ReportController::class, 'recalcExpected'])->name('account.reports.recalcExpected');
    Route::post('report/{report}/recalc-actual', [ReportController::class, 'recalcActual'])->name('account.reports.recalcActual');
    Route::post('report/{report}/sync-kpi', [ReportController::class, 'syncKpi'])->name('account.reports.syncKpi');

    // Wallet, Deposit, Withdrawal
    Route::get('wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('wallet/deposit', [DepositController::class, 'depositForm'])->name('wallet.deposit.form');
    Route::post('wallet/deposit/create', [DepositController::class, 'depositCreate'])->name('wallet.deposit.create');
    Route::post('wallet/deposit/{deposit}/upload', [DepositController::class, 'depositUploadProof'])->name('wallet.deposit.upload');
    Route::post('wallet/deposit/{deposit}/expire', [DepositController::class, 'depositExpire'])->name('wallet.deposit.expire');
    Route::get('wallet/withdraw', [WithdrawalController::class, 'form'])->name('wallet.withdraw.form');
    Route::post('wallet/withdraw', [WithdrawalController::class, 'store'])->name('wallet.withdraw.store');

    Route::get('wallet/deposits', [WalletController::class, 'myDeposits'])->name('wallet.deposits');
    Route::get('wallet/transfer', [WalletController::class, 'bulkTransferForm'])->name('wallet.bulk.form');
    Route::post('wallet/transfer', [WalletController::class, 'bulkTransferSubmit'])->name('wallet.bulk.submit');
    Route::post('wallet/transactions/{id}/recall', [WalletController::class, 'recallTransfer'])->name('wallet.transactions.recall');

    Route::get('wallets', [WalletController::class, 'wallets'])->name('account.wallets');
    Route::get('wallets/{wallet}/histories', [WalletController::class, 'histories'])->name('wallets.histories');
    Route::get('wallets/{wallet}/detail', [WalletController::class, 'detail'])->name('wallets.detail');
    Route::post('wallets/{wallet}/update-held-balance', [WalletController::class, 'updateHeldBalance'])->name('wallets.updateHeldBalance');
    Route::post('wallets/{wallet}/update-balance', [WalletController::class, 'updateBalance'])->name('wallets.updateBalance');

    Route::get('deposits', [DepositController::class, 'index'])->name('deposits.index');
    Route::post('deposits/{deposit}/update-status', [DepositController::class, 'updateStatus'])->name('deposits.updateStatus');
    Route::post('deposits/{deposit}/bank-name', [DepositController::class, 'updateBankName'])->name('deposits.updateBankName');
    Route::post('deposits/{deposit}/upload', [DepositController::class, 'depositUploadProofAdmin'])->name('deposits.upload');
    Route::get('withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::post('withdrawals/{withdrawal}/process', [WithdrawalController::class, 'process'])->name('withdrawals.process');

    // Task Cost Period
    Route::get('statistical', [TaskCostPeriodController::class, 'index'])->name('task_cost_period.index');
    Route::post('task-cost-post/update', [TaskCostPeriodController::class, 'updateMonthly'])->name('task_cost_post.update');

    // Bulk Mail
    Route::get('bulk-mail', [BulkMailController::class, 'create'])->name('admin.bulk_mail.create');
    Route::post('bulk-mail/send', [BulkMailController::class, 'send'])->name('admin.bulk_mail.send');
    Route::post('bulk-mail/process-chunk', [BulkMailController::class, 'processChunk'])->name('admin.bulk_mail.process_chunk');

    // User management
    Route::get('users', [UserManagementController::class, 'index'])->name('account.users.index');
    Route::get('users/members', [UserManagementController::class, 'members'])->name('account.users.members');
    Route::get('users/create', [UserManagementController::class, 'create'])->name('account.users.create');
    Route::post('users', [UserManagementController::class, 'store'])->name('account.users.store');
    Route::get('users/{user}/edit', [UserManagementController::class, 'edit'])->name('account.users.edit');
    Route::put('users/{user}', [UserManagementController::class, 'update'])->name('account.users.update');
    Route::delete('users/{user}', [UserManagementController::class, 'destroy'])->name('account.users.destroy');
    Route::post('users/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('account.users.toggleStatus');
    Route::post('users/{user}/toggle-marketing', [UserManagementController::class, 'toggleMarketing'])->name('account.users.toggleMarketing');
    Route::post('users/import-kpi', [UserManagementController::class, 'importKpi'])->name('account.users.importKpi');

    // Invite
    Route::get('invite', [InviteController::class, 'create'])->name('invite.create');
    Route::post('invite', [InviteController::class, 'store'])->name('invite.store');
    Route::get('invite/{invite}/download', [InviteController::class, 'download'])->name('invite.download');
});

// Config & Categories (moved out of /admin, protected by admin:2)
Route::post('/upload', [UploadController::class, 'upload'])->name('upload');
Route::middleware(['user', 'admin:2'])->group(function () {
    Route::resource('departments', DepartmentController::class);
    Route::get('departments/{id}/duplicate', [DepartmentController::class, 'duplicate'])->name('departments.duplicate');
    Route::post('departments/{id}/update-name', [DepartmentController::class, 'updateName'])->name('departments.updateName');

    Route::resource('suppliers', SupplierController::class);
    Route::get('suppliers/{id}/duplicate', [SupplierController::class, 'duplicate'])->name('suppliers.duplicate');

    Route::resource('channels', ChannelController::class);
    Route::get('channels/{id}/duplicate', [ChannelController::class, 'duplicate'])->name('channels.duplicate');
    Route::post('channels/{id}/update-name', [ChannelController::class, 'updateName'])->name('channels.updateName');

    Route::resource('duan', DuanController::class);
    Route::post('duan/update-rate', [DuanController::class, 'updateRate'])->name('duan.updateRate');
    Route::post('duan/{id}/update-name', [DuanController::class, 'updateName'])->name('duan.updateName');
});
