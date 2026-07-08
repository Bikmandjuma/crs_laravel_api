<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Subscription;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Exception;

class BillingController extends Controller
{
    public function verifyPayment(Request $request)
    {
        try {
            $request->validate([
                'transaction_id' => 'required|string',
                'plan_name' => 'required|string',
                'amount' => 'required|numeric',
            ]);

            $user = $request->user();

            // Verify Flutterwave
            $response = Http::withToken(config('services.flutterwave.secret_key'))
                ->get("https://api.flutterwave.com/v3/transactions/{$request->transaction_id}/verify");

            if (!$response->successful()) {
                return response()->json(['message' => 'Verification failed'], 400);
            }

            $data = $response->json('data');

            if (($data['status'] ?? '') !== 'successful') {
                return response()->json(['message' => 'Payment not successful'], 400);
            }

            // Plan periods
            $periodDays = match ($request->plan_name) {
                'Daily' => 1,
                'Weekly' => 7,
                'Monthly' => 30,
                '3 Months' => 90,
                '6 Months' => 180,
                'Yearly' => 365,
                default => throw ValidationException::withMessages([
                    'plan_name' => 'Invalid plan',
                ]),
            };

            $startsAt = Carbon::now();
            $endsAt = $startsAt->copy()->addDays($periodDays);

            // Create subscription
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_name' => $request->plan_name,
                'amount' => $request->amount,
                'period_days' => $periodDays,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'flutterwave_transaction_id' => $data['id'],
                'flutterwave_tx_ref' => $data['tx_ref'],
                'status' => 'active',
            ]);

            // Create invoice
            Invoice::create([
                'subscription_id' => $subscription->id,
                'invoice_number' => 'INV-' . strtoupper(uniqid()),
                'amount' => $subscription->amount,
                'currency' => 'USD',
                'paid_at' => now(),
            ]);


            return response()->json([
                'message' => 'Subscription activated',
                'subscription' => $subscription,
                'invoice' => $invoice,
                'days_left' => $user->subscriptionDaysLeft(),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Payment processing error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
