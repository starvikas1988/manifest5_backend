<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

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


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/users/search', [UserController::class, 'search']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UserController::class, 'index']); // Get all users
    Route::post('/users', [UserController::class, 'store']); // Create new user
    Route::get('/users/{id}', [UserController::class, 'show']); // Get user by ID
    Route::put('/users/{id}', [UserController::class, 'update']); // Update user
    Route::delete('/users/{id}', [UserController::class, 'destroy']); // Delete user
    Route::put('/users/{id}/status', [UserController::class, 'updateStatus']); 

});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
