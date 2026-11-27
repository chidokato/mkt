<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'bank_name',
        'transaction_code',
        'status',
        'approved_by',
        'approved_at',
        'proof_image',
        'expires_at',
        'proof_uploaded_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* ================== RELATIONSHIP ================== */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /* ================== HELPER ================== */

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function histories()
    {
        return $this->hasMany(DepositHistory::class);
    }
}
