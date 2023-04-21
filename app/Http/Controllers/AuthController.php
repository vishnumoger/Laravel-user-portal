<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Validator;
use App\Models\User;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function _construct() {
        $this->middleware('auth:api', ['except' => ['login', 'signup']]);
    }

    /*
        Sign up
    */
    public function signup(Request $request) {
        \Log::info('Sign Up');

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));
        
        \Log::info($user);

        Mail::to($request['email'])->send(new WelcomeMail($user));

        return response()->json([
            'message' => 'User sucessfully registered',
            'user' => $user 
        ], 200);
    }


    /*
        Login
    */
    public function login(Request $request) {
        \Log::info('Login');

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 422);
        }

        if(!$token = auth()->attempt($validator->validated())){
            return response()->json(['error' => "Unauthorized"], 401);
        }

        \Log::info($this->createNewToken($token));

        return $this->createNewToken($token);
    }

    /* 
    Create JWT Token 
    */
    
    public function createNewToken($token) {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL()*60,
            'user' => auth()->user()
        ]);
    }


    /*
    Password Reset 
    */
    public function passwordReset(Request $request) {

        \Log::info('Password Reset');

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
            'newpassword' => 'required|string|min:6'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 422);
        }

        if(auth()->user()) {
            $id = auth()->user()->id;
            $user = User::where('id', $id)->update(
                ['password' => bcrypt($request->newpassword)]
            );

            \Log::info(auth()->user());

            return response()->json([
                'message' => 'User password has been changed',
                'user' => auth()->user()
            ], 200);
        }
    }


    /*
    Update account details
    */
    public function updateAccountDetails(Request $request) {

        \Log::info('Account Updated');

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phonenumber' => 'required|numeric|min:10',
            'address' => 'required|string'
        ]);
        
        if($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 422);
        }

        if(auth()->user()) {
            $id = auth()->user()->id;
            $user = User::where('id', $id)->update(
                ['name' => $request->name, 'phonenumber' => $request->phonenumber, 'address' => $request->address]
            );
    
            \Log::info(auth()->user());

            return response()->json([
                'message' => 'User Account updated',
                'user' => auth()->user()
            ], 200);
        }
    }


}
