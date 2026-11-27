<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailLog extends Model
{
    protected $fillable = [
        'batch_id','user_id','email','subject','status','sent_at','error'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
