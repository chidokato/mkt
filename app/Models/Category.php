<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    public $timestamps = true;

    public function User()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function Post()
    {
        return $this->hasMany(Post::class, 'category_id', 'id');
    }
    public function Option()
    {
        return $this->hasMany(Post::class, 'category_id', 'id');
    }

}
