<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorReading;
use Illuminate\Http\Request;

class SensorController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'N'=>'required|numeric',
            'P'=>'required|numeric',
            'K'=>'required|numeric',
            'temperature'=>'required|numeric',
            'pH'=>'required|numeric',
            'soil_moisture'=>'required|numeric',
            'conductivity'=>'required|numeric',
        ]);

        $reading = SensorReading::create($validated);

        return response()->json([
            'success'=>true,
            'data'=>$reading
        ]);
    }

    public function latest()
    {
        return SensorReading::latest()->first();
    }
}
