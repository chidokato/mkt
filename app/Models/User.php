<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Kiểm tra người dùng có phải Quản trị viên (Admin) không
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Kiểm tra người dùng thông thường
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Quan hệ 1-N: Một User sở hữu nhiều Business Manager
     */
    public function businessManagers(): HasMany
    {
        return $this->hasMany(BusinessManager::class);
    }

    /**
     * Quan hệ 1-N xuyên bảng: Một User sở hữu nhiều Ad Accounts thông qua BM
     */
    public function adAccounts(): HasManyThrough
    {
        return $this->hasManyThrough(AdAccount::class, BusinessManager::class);
    }

    /**
     * Quan hệ 1-N: Một User có nhiều Facebook Pages
     */
    public function facebookPages(): HasMany
    {
        return $this->hasMany(FacebookPage::class);
    }
}
