<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

     protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'description',
        'ref_type',
        'ref_id',
        'idempotency_key',
        'meta',
        'balance_before',
        'balance_after',
        'held_before',
        'held_after',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'held_before' => 'decimal:2',
        'held_after' => 'decimal:2',
        'meta' => 'array',
    ];


    /* ================== RELATIONSHIP ================== */

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    /* ================== SCOPE (TIỆN DÙNG) ================== */

    public function scopeDeposit($query)
    {
        return $query->where('type', 'deposit');
    }

    public function scopeWithdraw($query)
    {
        return $query->where('type', 'withdraw');
    }
}
