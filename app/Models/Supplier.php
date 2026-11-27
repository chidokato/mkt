<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'description', 'user_id', 'parent'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parentSupplier()
    {
        return $this->belongsTo(Supplier::class, 'parent');
    }

    public function children()
    {
        return $this->hasMany(Supplier::class, 'parent');
    }
}

