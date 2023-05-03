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
        Sign up - 
        a. Include name, email and password and returns signed JWT token
        b. Validations
        i. Validate if the email has been registered before
        c. Send to Messaging platform with the payload after signup:
            i. User attributes: email, current timestamp, user id
            ii. Event name: Sign Up
        d. Send a welcome mailer.
    */
    public function signup(Request $request) {
        \Log::info('Sign Up');

        /* Validation Parameters for Signup */
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6'
        ]);

        /* Check validation*/
        if($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        /* encrypt password before saving into database */
        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));
        
        /* logging message for anlaytics */
        \Log::info($user);

        /* Send welcome mail after signup is successful */
        Mail::to($request['email'])->send(new WelcomeMail($user));

        /* Send response to user after signup*/
        return response()->json([
            'message' => 'User sucessfully registered',
            'user' => $user 
        ], 200);
    }


    /*
        Login - 
        a. Accept email and password, and return a JWT token
        b. Send to Segment with the payload after login:
            i. User attributes: Email, current timestamp, user id.
            ii. Event name: Login
    */
    public function login(Request $request) {
        \Log::info('Login');

        /* validation for login which requires email and password */
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        /* Validation checks here if any error will send to the user */
        if($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 422);
        }

        /* Check if the token valid or not */
        if(!$token = auth()->attempt($validator->validated())){
            return response()->json(['error' => "Unauthorized"], 401);
        }

        /* logging error message for Analaytics */
        \Log::info($this->createNewToken($token));

        /* Generate new JWT token everytime login is successful */
        return $this->createNewToken($token);
    }

    /* 
    Create JWT Token -
    JSON Web Token is a proposed Internet standard for creating 
    data with optional signature and/or optional encryption whose 
    payload holds JSON that asserts some number of claims. 
    The tokens are signed either using a private secret 
    or a public/private key.
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
    Password Reset -
    a. Only logged in users can perform this update.
    b. Accept current password and new password
    c. Send to Analytics platform with the payload after reset:
        i. User attributes: Email, current timestamp, user id
        ii. Event name: Password Reset
    */
    public function passwordReset(Request $request) {

        \Log::info('Password Reset');

        /* Validation parameters check for password and new password */
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6',
            'newpassword' => 'required|string|min:6'
        ]);

        /*If validation fails send the error message here */
        if($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 422);
        }

        /* Check if the user is authenticated, means request having proper 
        JWT toekn with validate expiry time*/
        if(auth()->user()) {
            $id = auth()->user()->id;
            /* encrypt password before saving into database */
            $user = User::where('id', $id)->update(
                ['password' => bcrypt($request->newpassword)]
            );

            /* logging error message for Analaytics */
            \Log::info(auth()->user());

            /* Send the response back to user once user password set successful*/
            return response()->json([
                'message' => 'User password has been changed',
                'user' => auth()->user()
            ], 200);
        }
    }


    /*
    Update account details-
    a. Only logged in user can perform this update
    b. Accept name, phone number, address
    c. Send to Segment with payload after update:
        i. User attributes: email, current timestamp, user id
        ii. Event name: Account Updated
    d. Send to Klaviyo with payload after update:
        i. User attributes: fields that have been updated. Eg: if only name has been
        updated, then send name only
        ii. Event name: Account Updated

    */
    public function updateAccountDetails(Request $request) {

        \Log::info('Account Updated');
        /* Check validation requires name, phone and address */
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phonenumber' => 'required|numeric|min:10',
            'address' => 'required|string'
        ]);
        
        /** Send error message if validation check failed */
        if($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 422);
        }

        /* check if the user is valid 
        
        */
        if(auth()->user()) {
            $id = auth()->user()->id;
            /* if the user is valid then we can update the name, phone and address in database */
            $user = User::where('id', $id)->update(
                ['name' => $request->name, 'phonenumber' => $request->phonenumber, 'address' => $request->address]
            );
    
            /* logging error message for Analaytics */
            \Log::info(auth()->user());

            /* Send the response back to user once user Account updated successful*/
            return response()->json([
                'message' => 'User Account updated',
                'user' => auth()->user()
            ], 200);
        }
    }


}
