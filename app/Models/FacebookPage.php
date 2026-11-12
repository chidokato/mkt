<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacebookPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_manager_id',
        'page_id',
        'name',
        'category',
        'category_list',
        'link',
        'picture_url',
        'access_token',
        'tasks',
        'followers_count',
        'fan_count',
        'is_published',
        'verification_status',
        'source_type',
        'is_hot',
        'last_post_at',
        'last_post_url',
        'last_post_synced_at',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'category_list' => 'array',
        'tasks' => 'array',
        'followers_count' => 'integer',
        'fan_count' => 'integer',
        'is_published' => 'boolean',
        'is_hot' => 'boolean',
        'last_post_at' => 'datetime',
        'last_post_synced_at' => 'datetime',
    ];

    /**
     * Thuộc về một User sở hữu
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Thuộc về một Business Manager (nếu có)
     */
    public function businessManager(): BelongsTo
    {
        return $this->belongsTo(BusinessManager::class);
    }

    /**
     * Link xem Page trên Facebook
     */
    public function getFacebookUrlAttribute(): string
    {
        if (!empty($this->link) && filter_var($this->link, FILTER_VALIDATE_URL)) {
            return $this->link;
        }
        return "https://www.facebook.com/{$this->page_id}";
    }

    /**
     * Ảnh đại diện Fanpage (hoặc avatar mặc định)
     */
    public function getDisplayAvatarAttribute(): string
    {
        if (!empty($this->picture_url) && filter_var($this->picture_url, FILTER_VALIDATE_URL)) {
            return $this->picture_url;
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=0284c7&color=ffffff&size=100';
    }

    /**
     * Tên nguồn Fanpage
     */
    public function getSourceLabelAttribute(): string
    {
        return match ($this->source_type) {
            'owned' => 'BM Sở hữu',
            'client' => 'Đối tác ủy quyền',
            'personal' => 'Trang cá nhân',
            default => 'Trang Facebook',
        };
    }

    /**
     * Badge màu theo nguồn
     */
    public function getSourceBadgeClassAttribute(): string
    {
        return match ($this->source_type) {
            'owned' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'client' => 'bg-blue-50 text-blue-700 border-blue-200',
            'personal' => 'bg-purple-50 text-purple-700 border-purple-200',
            default => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }

    /**
     * Kiểm tra trạng thái tích xanh/xám của trang
     */
    public function getIsVerifiedAttribute(): bool
    {
        return in_array($this->verification_status, ['blue_verified', 'grey_verified', 'verified']);
    }
}
