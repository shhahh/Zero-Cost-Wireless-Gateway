<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsQueue extends Model
{
    protected $fillable = [
        'mobile',
        'message',
        'status'
    ];
}
