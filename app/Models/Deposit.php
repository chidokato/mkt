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

    public function getProofImageUrlAttribute(): ?string
    {
        $raw = trim((string) $this->proof_image);
        if ($raw === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $raw)) {
            return $raw;
        }

        $path = ltrim(str_replace('\\', '/', $raw), '/');
        $relative = preg_replace('#^(uploads|storage)/#', '', $path);

        if (is_file(public_path('uploads/' . $relative))) {
            return asset('uploads/' . $relative);
        }

        if (is_file(public_path('storage/' . $relative))) {
            return asset('storage/' . $relative);
        }

        if (is_file(public_path($path))) {
            return asset($path);
        }

        return asset('uploads/' . $relative);
    }
}
