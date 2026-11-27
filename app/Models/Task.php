<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user',
        'post_id',
        'expected_costs',
        'actual_costs',
        'rate',
        'days',
        'active',
        'channel_id',
        'department_id',
        'department_lv1',
        'department_lv2',
        'report_id',
        'cost_type',
        'content',
        'approved',
         // NEW
        'price_expected',
        'price_final',
        'hold_transaction_id',
        'capture_transaction_id',
        'status',
        'extra_money',
        'refund_money',
    ];

    protected $casts = [
        'expected_costs' => 'decimal:2',
        'actual_costs'   => 'decimal:2',
        'rate'           => 'decimal:2',
        'days'           => 'integer',
        'price_expected' => 'decimal:2',
        'price_final'    => 'decimal:2',
        'extra_money' => 'decimal:2',
        'refund_money' => 'decimal:2',
        'settled_at' => 'datetime',
        // nếu có:
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'user'); // người sử dụng tác vụ
    }

    public function Post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function Channel()
    {
        return $this->belongsTo(Channel::class, 'channel_id');
    }

    public function Report()
    {
        return $this->belongsTo(Report::class, 'report_id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function Department_lv2()
    {
        return $this->belongsTo(Department::class, 'department_lv2');
    }


    public function getGrossCostAttribute()
    {
        return $this->days * $this->expected_costs;
    }

    public function getNetCostAttribute()
    {
        return $this->days
            * $this->expected_costs
            * (1 - $this->rate / 100);
    }

}
