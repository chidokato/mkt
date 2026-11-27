<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    public $timestamps = true;

    public function Post()
    {
        return $this->hasOne(Post::class, 'id', 'product_id');
    }

}
