<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invite extends Model
{
    protected $fillable = ['user_id','full_name','title','avatar_path','output_path', 'gender'];

}
