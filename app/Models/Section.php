<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;
    public $timestamps = true;
    
    public function Post()
    {
        return $this->hasOne(Post::class, 'id', 'post_id');
    }
    public function Images()
    {
        return $this->hasMany(Images::class, 'section_id', 'id');
    }
}
