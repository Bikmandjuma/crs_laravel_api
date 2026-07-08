<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function request(Request $request, $slug)
    {
        $user = User::where('public_slug', $slug)->firstOrFail();

        $req = AvailabilityRequest::create([
            'user_id' => $user->id,
            'visitor_name' => $request->visitor_name,
            'intent' => $request->intent,
            'message' => $request->message,
        ]);

        return response()->json([
            'message' => 'Request sent',
            'status' => $req->status,
        ]);
    }
}
