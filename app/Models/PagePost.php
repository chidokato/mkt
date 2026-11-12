<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagePost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'batch_id',
        'facebook_page_id',
        'page_id',
        'page_name',
        'post_type',
        'message',
        'link_url',
        'media_count',
        'media_paths',
        'publish_mode',
        'scheduled_at',
        'published_at',
        'fb_post_id',
        'permalink_url',
        'status',
        'error_message',
    ];

    protected $casts = [
        'media_count' => 'integer',
        'media_paths' => 'array',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    /**
     * Thuộc về một User sở hữu
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Thuộc về một FacebookPage (nếu còn tồn tại trong DB)
     */
    public function facebookPage(): BelongsTo
    {
        return $this->belongsTo(FacebookPage::class);
    }

    /**
     * Nhãn trạng thái hiển thị
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'published' => 'Đã đăng',
            'scheduled' => 'Đã hẹn giờ',
            'failed' => 'Thất bại',
            'cancelled' => 'Đã hủy',
            default => ucfirst((string) $this->status),
        };
    }

    /**
     * Class màu badge trạng thái
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'published' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'scheduled' => 'bg-sky-100 text-sky-800 border-sky-200',
            'failed' => 'bg-rose-100 text-rose-800 border-rose-200',
            'cancelled' => 'bg-slate-100 text-slate-600 border-slate-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    /**
     * Thông báo lỗi đã Việt hóa rõ ràng (hỗ trợ cả bản ghi lỗi cũ)
     */
    public function getLocalizedErrorAttribute(): ?string
    {
        if (empty($this->error_message)) {
            return null;
        }

        return \App\Services\FacebookAdsService::translatePublishError((string) $this->error_message);
    }
}
