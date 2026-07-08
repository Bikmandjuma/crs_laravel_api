<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SocialLoginController extends Controller
{
    public function auth_google()
    {
        return Socialite::driver('google')->stateless()->scopes(['email', 'profile', 'openid'])->redirect();
    }

    public function auth_google_callback()
    {
        try {
            
            $socialUser = Socialite::driver('google')->stateless()->user();
            Log::info('Google User ID:', ['id' => $socialUser->id]);
            $existingUser = User::where('email', $socialUser->email)->first();

            if ($existingUser) {
                Auth::login($existingUser);
                $token = JWTAuth::fromUser($existingUser);
                
                Log::info('Social User:', ['user' => $existingUser]);

                return response()->json([
                    'status' => 'login successfully !',
                    'user' => $existingUser,
                    'authorisation' => [
                        'token' => $token,
                        'type' => 'bearer',
                    ]
                ]);

            } else {

                //Create seeker_code
                $lastUserCode = DB::table('users')->max('user_code');
                if ($lastUserCode) {
                    preg_match('/\d+$/', $lastUserCode, $matches);
                    $sequenceNumber = isset($matches[0]) ? (int)$matches[0] + 1 : 1;
                } else {
                    $sequenceNumber = 1;
                }

                $prefix = date('y');
                $middle = 'JSR';
                $formattedNumber = str_pad($sequenceNumber, 5, '0', STR_PAD_LEFT);
                $seeker_code = $prefix . $middle . $formattedNumber;
            
                $newUser = User::create([
                    'user_code' => $seeker_code,
                    'user_name' => $socialUser->name,
                    'provider_name' => 'Google',
                    'provider_id' => $socialUser->id,
                    'email' => $socialUser->email,
                    'image' => $socialUser->avatar,
                    'provider_token' => $socialUser->token,
                ]);

                Auth::login($newUser);
                $token = JWTAuth::fromUser($newUser);

                Log::info('Social User:', ['user' => $newUser]);

                return response()->json([
                    'status' => 'login successfully !',
                    'user' => $newUser,
                    'authorisation' => [
                        'token' => $token,
                        'type' => 'bearer',
                    ]


                ]);
            } 

        } catch (Exception $e) {
            
            Log::error('Google login error: ' . $e->getMessage());
            return response()->json(['error' => 'Login again !' . $e->getMessage()], 500);

        }

            
    }
}
