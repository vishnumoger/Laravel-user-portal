<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
middleware is a feature that allows you to filter HTTP requests 
entering your application. In simple terms, middleware acts as a middleman
 between a request and your application. It can perform various tasks 
 such as verifying user authentication, checking user roles 
 and permissions, validating input data, logging requests, and more
*/

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function($router) {
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/passwordReset', [AuthController::class, 'passwordReset']);
    Route::post('/updateAccountDetails', [AuthController::class, 'updateAccountDetails']);
});
