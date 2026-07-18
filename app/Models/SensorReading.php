<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    protected $fillable = [
        'N',
        'P',
        'K',
        'temperature',
        'pH',
        'soil_moisture',
        'conductivity',
        'weather_temperature',
        'weather_humidity',
    ];
}
