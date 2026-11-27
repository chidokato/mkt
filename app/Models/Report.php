<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'time_start',
        'time_end',
        'days',
        'status',
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class, 'report_id', 'id');
    }

    public function Task()
    {
        return $this->hasMany(Task::class, 'report_id', 'id');
    }
}
