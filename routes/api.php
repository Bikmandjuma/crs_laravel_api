<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialLoginController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\Api\SensorController;

Route::post('login',[AuthController::class,'login']);
Route::post('register',[AuthController::class,'register']);
Route::post('forgot_password',[AuthController::class,'forgot_password']);
Route::post('fill-missed-info',[AuthController::class,'FillMissedInfo']);

Route::post('visits/increment', [UserController::class, 'incrementVisitCount']);
Route::post('verify-register-reset-code', [AuthController::class, 'verifyRegisterCode']);
Route::post('resend-register-reset-code',[AuthController::class,'resendRegisterResetCode']);

Route::post('/verify-reset-code', [AuthController::class, 'verifyResetCode']);
Route::post('/resend-reset-code', [AuthController::class, 'resendResetCode']);
Route::post('/change-forgotten-password', [AuthController::class, 'changeForgottenPassword']);


Route::group(['prefix'=>'user','middleware'=>'UserAuth'],function(){
	Route::get('view-user-info',[UserController::class,'view_user_information']);
	Route::post('edit_info',[UserController::class,'edit_info']);
	Route::post('change-password',[UserController::class,'change_password']);
	Route::post('account/delete', [UserController::class, 'deleteAccount']);
	Route::post('update/profile/image', [UserController::class, 'updateProfileImage']);
    Route::post('/billing/verify-payment', [BillingController::class, 'verifyPayment']);
    Route::post('/schedules', [ScheduleController::class, 'store']);
    Route::post('/schedules/search', [ScheduleController::class, 'search']);
    Route::delete('/schedules/{id}', [ScheduleController::class, 'destroy']);
    Route::post('/userDeletedAccount', [UserController::class, 'deleteAccount']);
    Route::post('/UserUpdateimage', [UserController::class, 'Updateimage']);

});

Route::post('/sensor-data', [SensorController::class,'store']);
Route::get('/sensor-data/latest', [SensorController::class,'latest']);

Route::post('/availability/{slug}', [AvailabilityController::class, 'request']);

