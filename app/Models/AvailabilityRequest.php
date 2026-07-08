<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailabilityRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'visitor_name', 'intent', 'message', 'status'
    ];
}
