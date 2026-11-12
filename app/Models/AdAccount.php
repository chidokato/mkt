<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_manager_id',
        'ad_account_id',
        'name',
        'currency',
        'account_status',
        'amount_spent',
        'balance',
        'spend_cap',
        'payment_method_display',
        'card_last_four',
        'source_type',
        'owner_business_id',
        'owner_business_name',
    ];

    protected $casts = [
        'account_status' => 'integer',
        'amount_spent' => 'decimal:2',
        'balance' => 'decimal:2',
        'spend_cap' => 'decimal:2',
    ];

    /**
     * Thuộc về Business Manager
     */
    public function businessManager(): BelongsTo
    {
        return $this->belongsTo(BusinessManager::class);
    }

    /**
     * Scope tài khoản thuộc sở hữu của BM
     */
    public function scopeOwned($query)
    {
        return $query->where('source_type', 'owned');
    }

    /**
     * Scope tài khoản được share từ BM khác
     */
    public function scopeClient($query)
    {
        return $query->where('source_type', 'client');
    }

    /**
     * Scope tài khoản cá nhân
     */
    public function scopePersonal($query)
    {
        return $query->where('source_type', 'personal');
    }

    /**
     * Kiểm tra có phải tài khoản sở hữu không
     */
    public function getIsOwnedAttribute(): bool
    {
        return ($this->source_type ?? 'owned') === 'owned';
    }

    /**
     * Kiểm tra có phải tài khoản được share từ BM khác không
     */
    public function getIsClientAttribute(): bool
    {
        return $this->source_type === 'client';
    }

    /**
     * Nhãn loại tài khoản
     */
    public function getSourceLabelAttribute(): string
    {
        return match ($this->source_type) {
            'owned' => 'TK của BM',
            'client' => 'TK được share',
            'personal' => 'TK cá nhân',
            default => 'TK của BM',
        };
    }

    /**
     * Tên trạng thái tài khoản theo chuẩn Meta
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->account_status) {
            1 => 'Hoạt động (Active)',
            2 => 'Vô hiệu hóa (Disabled)',
            3 => 'Chưa thanh toán (Unsettled)',
            7 => 'Đang xét duyệt rủi ro',
            8 => 'Chờ quyết toán',
            9 => 'Trong thời gian ân hạn',
            100 => 'Chờ đóng tài khoản',
            101 => 'Đã đóng (Closed)',
            default => 'Không xác định (' . $this->account_status . ')',
        };
    }

    /**
     * Kiểm tra tài khoản có đang hoạt động tốt không
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->account_status === 1;
    }

    /**
     * Lấy ID thuần số của Ad Account (bỏ tiền tố 'act_')
     */
    public function getNumericAccountIdAttribute(): string
    {
        return preg_replace('/^act_/', '', (string) $this->ad_account_id);
    }

    /**
     * Link dẫn trực tiếp tới trang Chi tiết thanh toán của tài khoản trên Facebook Ads Manager (Billing Hub)
     * Chuẩn Meta: https://adsmanager.facebook.com/adsmanager/billing_hub/accounts/details?asset_id={id}&business_id={bmId}&payment_account_id={id}&placement=BILLING_HUB
     */
    public function getBillingUrlAttribute(): string
    {
        $actId = $this->numeric_account_id;
        $params = [
            'asset_id' => $actId,
            'payment_account_id' => $actId,
        ];

        // Ưu tiên owner_business_id, nếu không có thì lấy bm_id của BM trực thuộc
        $bmId = $this->owner_business_id ?: ($this->businessManager->bm_id ?? null);
        if ($bmId && !str_starts_with($bmId, 'personal_') && !str_starts_with($bmId, 'fb_')) {
            $params['business_id'] = $bmId;
        }

        $params['placement'] = 'BILLING_HUB';

        return 'https://adsmanager.facebook.com/adsmanager/billing_hub/accounts/details?' . http_build_query($params);
    }

    /**
     * Link dẫn tới trang Quản lý Chiến dịch trên Facebook Ads Manager
     */
    public function getAdsManagerUrlAttribute(): string
    {
        $actId = $this->numeric_account_id;
        $params = ['act' => $actId];
        $bmId = $this->owner_business_id ?: ($this->businessManager->bm_id ?? null);
        if ($bmId && !str_starts_with($bmId, 'personal_') && !str_starts_with($bmId, 'fb_')) {
            $params['business_id'] = $bmId;
        }
        return 'https://adsmanager.facebook.com/adsmanager/manage/campaigns?' . http_build_query($params);
    }

    /**
     * Chuỗi hiển thị thẻ thanh toán (Ví dụ: "VISA *9701" hoặc "*9701")
     */
    public function getCardDisplayAttribute(): ?string
    {
        if (!empty($this->payment_method_display)) {
            return $this->payment_method_display;
        }
        if (!empty($this->card_last_four)) {
            return '*' . $this->card_last_four;
        }
        return null;
    }
}
