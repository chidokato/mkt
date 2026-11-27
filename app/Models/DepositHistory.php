<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositHistory extends Model
{
    protected $fillable = [
        'deposit_id',
        'admin_id',
        'action',
        'note'
    ];

    public function deposit()
    {
        return $this->belongsTo(Deposit::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
