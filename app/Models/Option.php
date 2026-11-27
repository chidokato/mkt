<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;
    public $timestamps = true;

    public function User()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function Category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }

}
