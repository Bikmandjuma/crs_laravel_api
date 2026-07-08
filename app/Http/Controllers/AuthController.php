<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Admin;
use App\Models\ResetCodePassword;
use App\Models\CodeToRegister;
use App\Services\SendGridService;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordCodeMail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    public function login(Request $request){
        
        try {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ], [
                'username.required' => 'The username is required.',
                'password.required' => 'The password is required.',
            ]);

            $loginField = filter_var($request->input('username'), FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

            if ($token = Auth::guard('user')->attempt([
                $loginField => $request->input('username'),
                'password' => $request->input('password'),
            ])) {
                $user = Auth::guard('user')->user();

                return response()->json([
                    'status' => 'success',
                    'message' => 'User login successfully!',
                    'user' => $user,
                    'role' => 'user',
                    'authorisation' => [
                        'token' => $token,
                        'type' => 'bearer',
                    ],
                ]);
            }

            if ($token = Auth::guard('admin')->attempt([
                $loginField => $request->input('username'),
                'password' => $request->input('password'),
            ])) {
                $user = Auth::guard('admin')->user();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Admin login successfully!',
                    'user' => $user,
                    'role' => 'admin',
                    'authorisation' => [
                        'token' => $token,
                        'type' => 'bearer',
                    ],
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials, try again!',
            ], 401);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors occurred.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Login failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred. Please try again later.',
            ], 500);
        }
    }
    
    public function register(Request $request){
        try {
            $validatedData = $request->validate([
                'firstname' => 'required|string|max:255',
                'lastname'  => 'required|string|max:255',
                'email'     => 'required|email|max:100|unique:users,email|unique:admins,email',
                'phone'     => [
                    'required',
                    'numeric',
                    'unique:users,phone',
                    'unique:admins,phone',
                ],
                'country'   => 'required|string|max:100',
                'gender'    => 'required|string|in:Male,Female,Other',
                'password'    => 'nullable|string|in:Male,Female,Other',
                'password_confirmation' => 'nullable|string|confirmed',
            ]);

            // Create User
            $user = User::create([
                'firstname' => $request->firstname,
                'lastname'  => $request->lastname,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'country'   => $request->country,
                'gender'    => $request->gender,
                'image'     => 'user.png',
            ]);

            \Log::info('User created: ' . $user->id);

            $token = Auth::guard('user')->login($user);
            \Log::info('Auth token generated: ' . $token);

            CodeToRegister::where('email', $user->email)->delete();

            $data = [
                'email' => $user->email,
                'code'  => mt_rand(100000, 999999),
            ];

            $got_data = CodeToRegister::create($data);

            $html = view('emails.code_to_register', ['code' => $got_data->code])->render();

            SendGridService::send(
                $got_data->email,
                'Code to Register',
                $html
            );

            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type'  => 'bearer',
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors occurred.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Registration failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed. ' . $e->getMessage()
            ], 500);
        }
    }

    public function UserNotReceiveCodeRegister(Request $request){
        try {
            $validatedData = $request->validate([
                'email' => 'required|email',
            ]);

            $email = $validatedData['email'];

            $existingCode = CodeToRegister::where('email', $email)->first();

            $newCode = mt_rand(100000, 999999);

            if ($existingCode) {
                $existingCode->update(['code' => $newCode]);
            } else {
                $existingCode = CodeToRegister::create([
                    'email' => $email,
                    'code' => $newCode,
                ]);
            }

            $html = view('emails.UserNotReceiveCodeRegister', ['code' => $existingCode->code])->render();

            SendGridService::send(
                $email,
                'New code to Register',
                $html
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Check again on your email',
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors occurred.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Registration failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed. ' . $e->getMessage()
            ], 500);
        }
    }

    public function FillMissedInfo(Request $request){
        try {
            
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found.'
                ], 404);
            }

            $user->birthdate = date('Y-m-d', strtotime($request->birthdate));
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Information updated successfully. Now you can login.'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors occurred.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Registration failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed. ' . $e->getMessage()
            ], 500);
        }
    }


    public function verifyRegisterCode(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'code' => 'required|digits:6',
            ]);

            $codeData = \App\Models\CodeToRegister::where('email', $request->email)
                ->where('code', $request->code)
                ->first();

            if (!$codeData) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid code.',
                ], 400);
            }

            $createdAt = $codeData->created_at;
            if ($createdAt->diffInHours(now()) >= 1) {
                $codeData->delete();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Code has expired.',
                ], 400);
            }

            // Find the user
            $user = \App\Models\User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found.',
                ], 404);
            }

            $user->update(['check' => 'yes']);

            $codeData->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Code verified successfully. Account activated.',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors occurred.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Code verification failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred. Please try again later.',
            ], 500);
        }
    }

    public function forgot_password(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Please enter an email!',
            'email.email' => 'Please enter a valid email address!',
        ]);

        $email = strtolower(trim($request->email));

        // Check if email exists
        $exists = Admin::whereRaw('LOWER(email)=?', [$email])->exists() ||
                  User::whereRaw('LOWER(email)=?', [$email])->exists();

        if (!$exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'This email is not registered in our system.',
            ], 404);
        }

        // Rate limit per email (max 3 requests per 5 minutes)
        $key = 'forgot-password:' . $email;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Too many reset attempts. Please try again later.',
            ], 429);
        }

        RateLimiter::hit($key, 300); // 5 minutes

        // Delete old codes
        ResetCodePassword::where('email', $email)->delete();

        // Generate OTP
        $otp = random_int(100000, 999999);

        ResetCodePassword::create([
            'email' => $email,
            'code' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,
        ]);

        Mail::to($email)->send(new ResetPasswordCodeMail($otp));

        return response()->json([
            'status' => 'success',
            'message' => 'A reset code has been sent to your email.',
        ], 200);
    }

    // public function verifyResetCode(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'email' => 'required|email',
    //             'code'  => 'required|digits:6',
    //         ], [
    //             'email.required' => 'Email is required.',
    //             'email.email'    => 'Invalid email format.',
    //             'code.required'  => 'Reset code is required.',
    //             'code.digits'    => 'Reset code must be exactly 6 digits.',
    //         ]);

    //         $email = trim(strtolower($request->email));

    //         $exists =
    //             User::where('email', $email)->exists() ||
    //             Admin::where('email', $email)->exists();

    //         if (!$exists) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'The email does not exist in our system.',
    //             ], 404);
    //         }

    //         // verify code
    //         $reset = ResetCodePassword::where('email', $email)
    //             ->where('code', $request->code)
    //             ->first();

    //         if (!$reset) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Invalid or expired reset code.',
    //             ], 400);
    //         }

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Reset code verified successfully.',
    //         ], 200);

    //     } catch (\Illuminate\Validation\ValidationException $e) {

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Validation errors occurr.',
    //             'errors' => $e->errors(),
    //         ], 422);

    //     } catch (\Exception $e) {

    //         Log::error('Verify reset code failed: ' . $e->getMessage());

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'An error occurred while processing your request. ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function verifyResetCode(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'code'  => 'required|digits:6',
            ]);

            $email = strtolower(trim($request->email));

            $reset = ResetCodePassword::where('email', $email)->first();

            if (!$reset) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired reset code.',
                ], 400);
            }

            // 🔒 Check if locked (5 wrong attempts)
            if ($reset->attempts >= 5) {
                return response()->json([
                    'status' => 'locked',
                    'message' => 'Too many failed attempts. Please request a new code.',
                ], 423);
            }

            // ⏳ Check expiry
            if (now()->greaterThan($reset->expires_at)) {
                $reset->delete();

                return response()->json([
                    'status' => 'expired',
                    'message' => 'Reset code has expired. Please request a new one.',
                ], 410);
            }

            // 🔐 Verify hashed OTP
            if (!Hash::check($request->code, $reset->code)) {

                $reset->increment('attempts');

                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid reset code.',
                ], 400);
            }

            // ✅ Success → delete code
            $reset->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Reset code verified successfully.',
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status' => 'error',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {

            Log::error('Verify reset code failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Server error.',
            ], 500);
        }
    }
    public function resendRegisterResetCode(Request $request){
        try {
            $request->validate([
                'email' => 'required|email',
            ], [
                'email.required' => 'Email is required.',
                'email.email'    => 'Invalid email format.',
            ]);

            $email = trim(strtolower($request->email));

            $exists =
                User::where('email', $email)->exists() ||
                Admin::where('email', $email)->exists();

            if (!$exists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The email does not exist in our system.',
                ], 404);
            }

            CodeToRegister::where('email', $email)->delete();

            $code = rand(100000, 999999);

            CodeToRegister::create([
                'email' => $email,
                'code'  => $code,
            ]);

            $html = view('emails.send-code-reset-password', [
                'code' => $code
            ])->render();

            SendGridService::send(
                $email,
                'Password Reset Code',
                $html
            );

            return response()->json([
                'status' => 'success',
                'message' => 'A new reset code has been sent to your email.',
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors occurr.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {

            Log::error('Resend reset code failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function resendResetCode(Request $request){
        try {
            $request->validate([
                'email' => 'required|email',
            ], [
                'email.required' => 'Email is required.',
                'email.email'    => 'Invalid email format.',
            ]);

            $email = trim(strtolower($request->email));

            // check User or Admin
            $exists =
                User::where('email', $email)->exists() ||
                Admin::where('email', $email)->exists();

            if (!$exists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The email does not exist in our system.',
                ], 404);
            }

            // delete old codes
            ResetCodePassword::where('email', $email)->delete();

            // generate new code
            $code = rand(100000, 999999);

            ResetCodePassword::create([
                'email' => $email,
                'code'  => $code,
            ]);

            // send email
            $html = view('emails.send-code-reset-password', [
                'code' => $code
            ])->render();

            SendGridService::send(
                $email,
                'Password Reset Code',
                $html
            );

            return response()->json([
                'status' => 'success',
                'message' => 'A new reset code has been sent to your email.',
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors occurr.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {

            Log::error('Resend reset code failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function changeForgottenPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:8|confirmed',
            ], [
                'email.required' => 'Email is required.',
                'email.email' => 'Invalid email format.',
                'password.required' => 'Password is required.',
                'password.min' => 'Password must be at least 8 characters.',
                'password.confirmed' => 'Passwords do not match.',
            ]);

            $email = trim(strtolower($request->email));

            // 🔍 Find user or admin
            $user = User::where('email', $email)->first();
            $admin = Admin::where('email', $email)->first();

            if (!$user && !$admin) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The email does not exist in our system.',
                ], 404);
            }

            // 🔐 Update password
            if ($user) {
                $user->password = Hash::make($request->password);
                $user->save();
            }

            if ($admin) {
                $admin->password = Hash::make($request->password);
                $admin->save();
            }

            // 🧹 Remove reset codes
            ResetCodePassword::where('email', $email)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Password updated successfully. You can now login.',
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors occurr.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {

            \Log::error('Change forgotten password failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request. ' . $e->getMessage(),
            ], 500);
        }
    }


    public function logout(Request $request){
        
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([
            'status', 'Success.',
            'message', 'You have been logged out.'
        ]);

    }

}