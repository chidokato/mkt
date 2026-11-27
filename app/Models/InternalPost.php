<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalPost extends Model
{
    use HasFactory;
    public $timestamps = true;

    public function Category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }
    public function User()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    public function Images()
    {
        return $this->hasMany(Images::class, 'post_id', 'id');
    }
    public function Order()
    {
        return $this->hasMany(Order::class, 'product_id', 'id');
    }
    public function Section()
    {
        return $this->hasMany(Section::class, 'post_id', 'id');
    }
    public function Ward()
    {
        return $this->hasOne(Ward::class, 'id', 'ward_id');
    }
    public function District()
    {
        return $this->hasOne(District::class, 'id', 'district_id');
    }
    public function Province()
    {
        return $this->hasOne(Province::class, 'id', 'province_id');
    }
    public function Street()
    {
        return $this->hasOne(Street::class, 'id', 'street_id');
    }
}
