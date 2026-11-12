<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessManager extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'bm_id',
        'access_token',
        'status',
        'partners_count',
        'partners_data',
    ];

    /**
     * Tự động mã hóa / giải mã Access Token khi tương tác với DB
     */
    protected $casts = [
        'access_token' => 'encrypted',
        'partners_count' => 'integer',
        'partners_data' => 'array',
    ];

    /**
     * Thuộc về một User (Người dùng sở hữu)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quan hệ 1-N với Ad Accounts
     */
    public function adAccounts(): HasMany
    {
        return $this->hasMany(AdAccount::class);
    }

    /**
     * Quan hệ với Ad Accounts thuộc sở hữu của BM
     */
    public function ownedAdAccounts(): HasMany
    {
        return $this->hasMany(AdAccount::class)->where('source_type', 'owned');
    }

    /**
     * Quan hệ với Ad Accounts được share từ BM khác
     */
    public function clientAdAccounts(): HasMany
    {
        return $this->hasMany(AdAccount::class)->where('source_type', 'client');
    }

    /**
     * Quan hệ 1-N với Facebook Pages
     */
    public function facebookPages(): HasMany
    {
        return $this->hasMany(FacebookPage::class);
    }

    /**
     * Scope chỉ lấy các Business Manager thật (loại trừ tài khoản cá nhân)
     */
    public function scopeRealBms($query)
    {
        return $query->where('bm_id', 'not like', 'personal_%');
    }

    /**
     * Scope chỉ lấy tài khoản cá nhân
     */
    public function scopePersonal($query)
    {
        return $query->where('bm_id', 'like', 'personal_%');
    }

    /**
     * Kiểm tra có phải tài khoản cá nhân không
     */
    public function getIsPersonalAttribute(): bool
    {
        return str_starts_with((string) $this->bm_id, 'personal_');
    }

    /**
     * Hiển thị chuỗi token đã che mờ bảo mật
     */
    public function getMaskedTokenAttribute(): string
    {
        $token = $this->access_token;
        if (empty($token)) {
            return '---';
        }
        $len = strlen($token);
        if ($len <= 15) {
            return '••••••••••••';
        }
        return substr($token, 0, 7) . '••••••••' . substr($token, -6);
    }

    /**
     * Kiểm tra có phải là kết nối bằng Facebook OAuth không
     */
    public function getIsOAuthAttribute(): bool
    {
        return str_starts_with((string)$this->bm_id, 'fb_') || str_starts_with((string)$this->name, 'FB:');
    }

    /**
     * Tổng chi tiêu của tất cả tài khoản trong BM này
     */
    public function getTotalSpentAttribute(): float
    {
        return (float) $this->adAccounts->sum('amount_spent');
    }

    /**
     * Tổng số dư nợ của các tài khoản NỢ TIỀN (status 3) trong BM này
     */
    public function getTotalBalanceAttribute(): float
    {
        return (float) $this->adAccounts->where('account_status', 3)->sum('balance');
    }

    /**
     * Tổng số dư nợ của các tài khoản NỢ TIỀN (status 3) trong BM này
     */
    public function getUnsettledBalanceAttribute(): float
    {
        return (float) $this->adAccounts->where('account_status', 3)->sum('balance');
    }

    /**
     * Số tài khoản đang hoạt động (Active)
     */
    public function getActiveAccountsCountAttribute(): int
    {
        return $this->adAccounts->where('account_status', 1)->count();
    }

    /**
     * Số tài khoản bị vô hiệu hóa (Disabled/Die)
     */
    public function getDisabledAccountsCountAttribute(): int
    {
        return $this->adAccounts->where('account_status', 2)->count();
    }

    /**
     * Số tài khoản nợ tiền (Unsettled / status 3)
     */
    public function getUnsettledAccountsCountAttribute(): int
    {
        return $this->adAccounts->where('account_status', 3)->count();
    }

    /**
     * Số tài khoản thuộc sở hữu của BM
     */
    public function getOwnedAccountsCountAttribute(): int
    {
        return $this->adAccounts->where('source_type', 'owned')->count();
    }

    /**
     * Số tài khoản sở hữu đang hoạt động
     */
    public function getOwnedActiveAccountsCountAttribute(): int
    {
        return $this->adAccounts->where('source_type', 'owned')->where('account_status', 1)->count();
    }

    /**
     * Số tài khoản sở hữu bị vô hiệu hóa
     */
    public function getOwnedDisabledAccountsCountAttribute(): int
    {
        return $this->adAccounts->where('source_type', 'owned')->where('account_status', 2)->count();
    }

    /**
     * Số tài khoản sở hữu đang nợ tiền (Unsettled / status 3)
     */
    public function getOwnedUnsettledAccountsCountAttribute(): int
    {
        return $this->adAccounts->where('source_type', 'owned')->where('account_status', 3)->count();
    }

    /**
     * Số tài khoản được share từ BM khác
     */
    public function getClientAccountsCountAttribute(): int
    {
        return $this->adAccounts->where('source_type', 'client')->count();
    }

    /**
     * Số tài khoản được share đang hoạt động
     */
    public function getClientActiveAccountsCountAttribute(): int
    {
        return $this->adAccounts->where('source_type', 'client')->where('account_status', 1)->count();
    }

    /**
     * Số tài khoản được share bị vô hiệu hóa
     */
    public function getClientDisabledAccountsCountAttribute(): int
    {
        return $this->adAccounts->where('source_type', 'client')->where('account_status', 2)->count();
    }

    /**
     * Số tài khoản được share đang nợ tiền (Unsettled / status 3)
     */
    public function getClientUnsettledAccountsCountAttribute(): int
    {
        return $this->adAccounts->where('source_type', 'client')->where('account_status', 3)->count();
    }

    /**
     * Link dẫn tới Trung tâm thanh toán của BM trên Facebook Ads Manager (Billing Hub)
     */
    public function getBillingHubUrlAttribute(): string
    {
        $bmId = (string) $this->bm_id;
        if ($bmId && !str_starts_with($bmId, 'personal_') && !str_starts_with($bmId, 'fb_')) {
            return "https://adsmanager.facebook.com/adsmanager/billing_hub/accounts?business_id={$bmId}";
        }
        return "https://adsmanager.facebook.com/adsmanager/billing_hub/accounts";
    }
}
