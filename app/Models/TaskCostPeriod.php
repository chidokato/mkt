<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskCostPeriod extends Model
{
    protected $table = 'task_cost_period';

    protected $fillable = [
        'year_month',
        'period_no',
        'period_start',
        'period_end',
        'report_id',
        'department_id',
        'department_lv1',
        'department_lv2',
        'user_id',
        'channel_id',
        'sum_actual',
        'last_calc_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'sum_actual'   => 'decimal:2',
        'last_calc_at' => 'datetime',
    ];

    public function Post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
