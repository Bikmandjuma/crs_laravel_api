<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'title', 'start_at', 'remind_at', 'source'
    ];

    protected $dates = ['start_at', 'remind_at'];
}
