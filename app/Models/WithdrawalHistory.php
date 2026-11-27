<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalHistory extends Model
{
    protected $fillable = [
        'withdrawal_id',
        'admin_id',
        'action',
        'note',
    ];

    public function withdrawal()
    {
        return $this->belongsTo(Withdrawal::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
