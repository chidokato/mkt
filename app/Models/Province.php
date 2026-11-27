<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;
    public $timestamps = true;

    public function District()
    {
        return $this->hasMany(District::class, 'province_id', 'id');
    }
    public function Ward()
    {
        return $this->hasMany(Ward::class, 'province_id', 'id');
    }
    public function Street()
    {
        return $this->hasMany(Street::class, 'province_id', 'id');
    }
    public function Post()
    {
        return $this->hasMany(Post::class, 'province_id', 'id');
    }
}
