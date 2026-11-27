<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WardTranslation extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = [
    	'ward_id',
    	'province_id',
    	'content',
    	'district_id',
    	'name','prefix',
    	'locale',
    ];

    public function DistrictTranslation()
    {
        return $this->belongsTo(DistrictTranslation::class, 'district_id', 'id');
    }

    public function ProvinceTranslation()
    {
        return $this->belongsTo(ProvinceTranslation::class, 'province_id', 'id');
    }

    public function PostTranslation()
    {
        return $this->hasMany(PostTranslation::class, 'ward_id', 'id');
    }
}
