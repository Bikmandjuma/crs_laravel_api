<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Subscription;
use Carbon\Carbon;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, \Closure $next)
    {
        $subscription = Subscription::where('user_id', auth()->id())
            ->where('status', 'active')
            ->latest()
            ->first();

        if (!$subscription || Carbon::now()->greaterThan($subscription->ends_at)) {
            return response()->json(['message' => 'Subscription expired'], 403);
        }

        return $next($request);
    }

}
