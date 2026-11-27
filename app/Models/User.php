<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'yourname',
        'email',
        'secondary_email',
        'phone',
        'address',
        'password',
        'google_id',
        'avatar',
        'google_token',
        'google_refresh_token',
        'google_token_expires_at',
        'permission', 
        'status', 
        'department_id',
        'department_lv1',
        'department_lv2',
        'rank',
        'employee_code',
        'kpi',
        'allow_marketing',
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

    public function Category()
    {
        return $this->hasMany(Category::class, 'user_id', 'id');
    }
    public function Post()
    {
        return $this->hasMany(Post::class, 'user_id', 'id');
    }
    public function Slider()
    {
        return $this->hasMany(Slider::class, 'user_id', 'id');
    }
    public function Cart()
    {
        return $this->hasMany(Cart::class, 'user_id', 'id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    public function departmentlv2()
    {
        return $this->belongsTo(Department::class, 'department_lv2');
    }
    public function departmentlv1()
    {
        return $this->belongsTo(Department::class, 'department_lv1');
    }

    // public function tasks()
    // {
    //     return $this->hasMany(Department::class, 'user_id', 'id'); 
    // }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'user');
    }

    
}
