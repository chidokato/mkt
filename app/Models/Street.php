<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Street extends Model
{
    use HasFactory;
    public $timestamps = true;
    
    public function Province()
    {
        return $this->hasOne(Province::class, 'id', 'province_id');
    }
    public function District()
    {
        return $this->hasOne(District::class, 'id', 'district_id');
    }
    public function Post()
    {
        return $this->hasOne(Post::class, 'id', 'street_id');
    }
}
