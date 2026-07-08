<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function store(Request $request)
    {
        $schedule = Schedule::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'start_at' => $request->start_at,
            'remind_at' => $request->remind_at,
            'source' => 'voice',
        ]);

        AiLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Schedule created',
            'source' => 'voice',
        ]);

        return response()->json($schedule, 201);
    }

    public function search(Request $request)
    {
        return Schedule::where('user_id', $request->user()->id)
            ->where('title', 'like', "%{$request->title}%")
            ->get();
    }

    public function destroy($id)
    {
        Schedule::where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();

        AiLog::create([
            'user_id' => auth()->id(),
            'action' => 'Schedule deleted',
            'source' => 'voice',
        ]);

        return response()->json(['message' => 'Deleted']);
    }
}

