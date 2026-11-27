<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'held_balance'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'held_balance' => 'decimal:2',
    ];

    /* ================== RELATIONSHIP ================== */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /* ================== BUSINESS LOGIC ================== */

    public function deposit(float $amount, string $description = null): void
    {
        $this->increment('balance', $amount);

        $this->transactions()->create([
            'type' => 'deposit',
            'amount' => $amount,
            'description' => $description,
        ]);
    }

    public function withdraw(float $amount, string $description = null): void
    {
        if ($this->balance < $amount) {
            throw new \Exception('Số dư ví không đủ');
        }

        $this->decrement('balance', $amount);

        $this->transactions()->create([
            'type' => 'withdraw',
            'amount' => $amount,
            'description' => $description,
        ]);
    }

    public function histories()
    {
        return $this->hasMany(WalletHistory::class, 'wallet_id')->latest();
    }

}
