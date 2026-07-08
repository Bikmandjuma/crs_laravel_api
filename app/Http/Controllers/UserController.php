<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Admin;
use App\Mail\CodeToRegisterMail;
use App\Models\Visit;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\ResetCodePassword;
use App\Mail\SendCodeResetPasswordMail;
use Illuminate\Support\Facades\Validator;
use App\Models\UserDeletedAccount;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class UserController extends Controller
{

	public function view_user_information(){
        $user = Auth::guard('user')->user();

        if ($user) {

            return response()->json([
                'status' => 'success',
                'user_info' => $user
            ], 200);

        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function edit_info(Request $request){
        try {

            $userId = Auth::guard('user')->user()->id;

            $validatedData = $request->validate([
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',

                'gender' => 'required|string|in:Male,Female',

                'email' => [
                    'required',
                    'string',
                    'unique:users,email,' . $userId,
                    'unique:admins,email',
                ],

                'phone' => [
                    'required',
                    'numeric',
                    'unique:users,phone,' . $userId,
                    'unique:admins,phone',
                ],

                'country' => 'required|string|max:255',

                'birthdate' => [
                    'required',
                    'date',
                ],
            ]);

            $user = User::find($userId);

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found.'
                ], 404);
            }

            $user->update($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully!',
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors occurred.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {

            \Log::error('Error occurred while editing user info: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred. Please try again later.',
            ], 500);
        }
    }


    public function profile_picture(){
        return 'profile public';
    }

    public function verify_code_to_register(Request $request, $email){
        try{
            $request->validate([
                'code' => 'required|numeric|digits:6'
            ]);

            if (is_array($request->code)) {
                $code = implode('', $request->code);
            } else {
                $code = $request->code;
            }

            $register_Code = CodeToRegister::where('email', $email)->where('code', $code)->first();

            if ($register_Code) {
                if ($register_Code->created_at->diffInMinutes(now()) > 60) {
                    $register_Code->delete();
                    return response()->json(['error' => 'Your code is expired!'], 400);
                } else {
                    $register_Code->delete();
                    return response()->json(['info' => 'Now fill missed info!'], 200);
                }
            
            }else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The code is not valid. Please try again.',
                ], 400);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors occurred.',
                'error' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Code verification failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request. ' . $e->getMessage(),
            ], 500);
        }

    }

    public function getVisitCount()
    {
        $today = now()->toDateString();
        $visit = Visit::where('date', $today)->first();
        $count = $visit ? $visit->count : 0;
        return response()->json(['count' => $this->formatNumber($count)]);
    }

    public function getTotalVisits(){
        $total = Visit::sum('count');
        return response()->json(['total' => $this->formatNumber($total)]);
    }

    private function formatNumber($number)
    {
        if ($number >= 1000000000) {
            return round($number / 1000000000, 1) . 'B';
        } elseif ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        } elseif ($number >= 1000) {
            return round($number / 1000, 1) . 'k';
        }

        return $number;
    }

    public function incrementVisitCount(){
        $today = now()->toDateString();

        $visit = Visit::where('date', $today)->first();

        if (!$visit) {
            Visit::create([
                'count' => 1,
                'date' => $today,
            ]);
        } else {
            $visit->increment('count');
        }

        return response()->json(['message' => 'Visit count incremented']);
    }

    public function change_password(Request $request){
        try {
            $validated = $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:6 |confirmed',
            ]);

            $user = Auth::guard('user')->user();

            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Current password is incorrect.',
                ], 422);
            }

            $user->password = Hash::make($validated['new_password']);
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Password updated successfully.',
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating your password.',
            ], 500);
        }
    }

    public function deleteAccount(Request $request){
        
        try {

            $request->validate([
                'password' => 'required|string',
            ]);

            $user = Auth::guard('user')->user();

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'errors' => [
                        'password' => ['The provided password is incorrect.']
                    ]
                ], 422);
            }

            UserDeletedAccount::create([
                'user_id'   => $user->id,
                'firstname' => $user->firstname,
                'lastname'  => $user->lastname,
                'email'     => $user->email,
                'phone'     => $user->phone,
                'country'   => $user->country,
                'deleted_at'=> now(),
            ]);

            $user->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Account deleted successfully.'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
        
            return response()->json([
                'status' => 'error',
                'errors' => $e->errors(),
            ], 422);
        
            \Log::error('errors: ' . $e->errors());

        } catch (\Exception $e) {
        
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while deleting your account.',
            ], 500);

            \Log::error('errors: ' . $e->errors());
            
        }
    
    }

    public function updateProfileImage(Request $request)
    {
        $user = Auth::guard('user')->user();

        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:5048',
        ]);

        if ($user->image && file_exists(public_path('images/User/' . $user->image))) {
            unlink(public_path('images/User/' . $user->image));
        }

        $fileName = time() . '_' . uniqid() . '.jpg';

        $request->file('image')->move(public_path('images/User'), $fileName);

        $user->image = $fileName;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile image updated successfully',
            'image_url' => asset('images/User/' . $fileName)
        ], 200);
    }


    public function code_to_reset_pswd(Request $request){

        try {
            $request->validate([
                'code' => 'required|numeric|digits:6',
                'email' => 'required|email',
            ]);

            $code = $request->input('code');
            $email = $request->input('email');

            $register_Code = ResetCodePassword::where('email', $email)->where('code', $code)->first();

            if ($register_Code) {
                if ($register_Code->created_at->diffInMinutes(now()) > 60) {
                    $register_Code->delete();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Your code is expired.',
                    ], 400);
                } else {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Code is valid, reset password now!',
                    ], 200);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The code is not valid. Please try again.',
                ], 400);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors occurred.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Code verification failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request. ' . $e->getMessage(),
            ], 500);
        }
    
    }

    public function resetPassword(Request $request,$email,$code){

        try {

            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);

            $password = $request->password;

            $passwordResetCode = ResetCodePassword::where('code', $code)
                ->where('email', $email)
                ->first();

            if (!$passwordResetCode) {

                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired reset code.',
                ], 400);

            }

            $user = User::where('email', $passwordResetCode->email)->first();
            $admin = Admin::where('email', $passwordResetCode->email)->first();

            if ($user) {
            
                $user->update(['password' => bcrypt($password)]);
                $passwordResetCode->delete();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Password updated successfully!',
                ], 200);
            } elseif ($admin) {
                
                $admin->update(['password' => bcrypt($password)]);
                $passwordResetCode->delete();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Password updated successfully!',
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Email\'s owner not found.',
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
  
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors occurred.',
                'error' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            
            \Log::error('Password reset failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing your request. ' . $e->getMessage(),
            ], 500);
        }

    }


    public function Updateimage(Request $request){
        try {

            $request->validate([

                'image' => 'required|image|mimes:jpg,jpeg,png|max:5048',

            ]);

            $user = Auth::guard('user')->user();

            if (
                $user->image &&
                File::exists(
                    public_path($user->image)
                )
            ) {

                File::delete(
                    public_path($user->image)
                );

            }

            $image = $request->file('image');

            $imageName =
                time() .
                "_" .
                rand(1000, 9999) .
                "." .
                $image->getClientOriginalExtension();

            $image->move(
                public_path('uploads/users'),
                $imageName
            );

            $user->image =
                "uploads/users/" .
                $imageName;

            $user->save();

            return response()->json([

                'success' => true,

                'message' =>
                    'image updated successfully',

                'image' =>
                    asset($user->image),

            ], 200);

        } catch (\Throwable $th) {

            return response()->json([

                'success' => false,

                'message' => $th->getMessage(),

            ], 500);

        }
    }


}
