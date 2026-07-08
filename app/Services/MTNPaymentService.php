<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MTNPaymentService
{
    protected $apiUserId;
    protected $subscriptionKey;
    protected $apiKey;

    public function __construct()
    {
        // Access the API credentials from environment variables
        $this->apiUserId = env('MOMO_API_USER_ID');
        $this->subscriptionKey = env('MOMO_SUBSCRIPTION_KEY');
        $this->apiKey = env('MOMO_API_KEY');
    }

    public function getAccessToken()
    {
        $credentials = base64_encode("{$this->apiUserId}:{$this->apiKey}");

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
            'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
            'Content-Type' => 'application/json',
        ])->post('https://sandbox.momodeveloper.mtn.com/collection/token/');
        
        if ($response->successful()) {
            return $response->json()['access_token'];
        }

        return null;
    }

    public function requestToPay($accessToken){

        $url = 'https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay';
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
            'X-Reference-Id: ' . $this->apiUserId,
            'X-Target-Environment: sandbox',  // Ensure sandbox is selected
            'Ocp-Apim-Subscription-Key: ' . $this->subscriptionKey,
        ];


        $data = [
            'amount' => '1',            // The amount to charge
            'currency' => 'EUR',        // Currency type
            'externalId' => uniqid(),    // Reference number
            'payer' => [
                'partyIdType' => 'MSISDN',
                'partyId' => '+250785389111', // Test phone number
            ],
            'payerMessage' => 'Payment Request',
            'payeeNote' => 'Please confirm payment',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        // Log the raw cURL response
        if (curl_errno($ch)) {
            Log::error('cURL error: ' . curl_error($ch));  // Log cURL error
            return null;
        }

        // Check the HTTP response code
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($responseCode != 200) {
            Log::error('Request failed with status code ' . $responseCode, ['response' => $response]);
            return null;
        }

        curl_close($ch);

        // Log the successful response for further debugging
        Log::info('Payment request response', ['response' => $response]);

        return json_decode($response, true);  // Decode JSON response
    }

}
