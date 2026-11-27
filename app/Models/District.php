<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;
    public $timestamps = true;

    
    
    public function Province()
    {
        return $this->hasOne(Province::class, 'id', 'province_id');
    }

    public function Ward()
    {
        return $this->hasMany(Ward::class, 'district_id', 'id');
    }
    public function Street()
    {
        return $this->hasMany(Street::class, 'district_id', 'id');
    }
}
