<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\User;
use App\Models\QuestionAnswer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function adminInfo(){
        $AdminInfo = Admin::all();

        return response()->json([
            'admin_info' => $AdminInfo,
        ]);
    }

    public function add_question_and_answers(Request $request)
    {
        try {
            $validated = $request->validate([
                'question' => 'required|unique:question_answers,question|max:255',
                'answer' => 'required|string',
            ],[
                'question.unique' => 'Question has already been added !'
            ]);

            $qa = new QuestionAnswer();
            $qa->question = $validated['question'];
            $qa->answer = $validated['answer'];
            $qa->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Question & Answer saved successfully.',
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('QA Insertion error', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong.',
            ], 500);
        }
    }

    public function fetch_question_answers(Request $request){
        try {

            $qas = QuestionAnswer::orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'data'   => $qas,
            ], 200);

        } catch (\Exception $e) {
            \Log::error('QA Fetch error', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Could not retrieve questions & answers.',
            ], 500);
        }
    }

    public function user_registration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'firstname'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User registered successfully!',
            'user' => $user
        ], 201);
    }


}
