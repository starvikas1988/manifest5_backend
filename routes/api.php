<?php

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\MarketController;

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

Route::post('/generateOtpMail', [AuthController::class, 'generateOtpMail']);
Route::post('/validateOtp', [AuthController::class, 'validateOtp']);

Route::post('/verify-email', [AuthController::class, 'verifyEmail']);

Route::post('/change-password', [AuthController::class, 'changePassword']);
Route::post('/resetPassword', [AuthController::class, 'resetPassword']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

Route::post('/operator_login', [AuthController::class, 'operator_login']);


Route::get('/users/search', [UserController::class, 'search']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UserController::class, 'index']); // Get all users
    Route::post('/users', [UserController::class, 'store']); // Create new user
    Route::get('/users/{id}', [UserController::class, 'show']); // Get user by ID
    Route::put('/users/{id}', [UserController::class, 'update']); // Update user
    Route::delete('/users/{id}', [UserController::class, 'destroy']); // Delete user
    Route::put('/users/{id}/status', [UserController::class, 'updateStatus']); 
    Route::get('/category', [CategoryController::class, 'index']); 
    Route::post('/category', [CategoryController::class, 'store']);
    Route::get('/category/{id}', [CategoryController::class, 'show']); 
    Route::put('/category/{id}', [CategoryController::class, 'update']); 
    Route::delete('/category/{id}', [CategoryController::class, 'destroy']);

    Route::post('/assignments', [AssignmentController::class, 'assignMatch']);
    //Route::get('/getAssignmentsCount', [AssignmentController::class, 'getAssignmentsCount']);
    Route::get('/getAssignmentsCount/{operatorId?}', [AssignmentController::class, 'getUniqueMatchCount']);

    Route::get('/getAssignmentedMatchIds', [AssignmentController::class, 'getAssignmentedMatchIds']);
   
    Route::get('/operator/{operatorId}/assigned-matches', [AssignmentController::class, 'getOperatorAssignedMatchIds']);
   // Route::get('/assignments', [AssignmentController::class, 'getAssignments']);
    Route::get('/assignments/{userId?}', [AssignmentController::class, 'getAssignments']);

   
    Route::get('/tickets', [TicketController::class,  'index']);
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/tickets/{id}', [TicketController::class, 'show']);
    Route::post('/tickets/{id}', [TicketController::class, 'update']);
    Route::put('/tickets/{id}/status', [TicketController::class, 'updateStatus']); 
    Route::delete('/tickets/{id}', [TicketController::class, 'destroy']);

    Route::apiResource('markets', MarketController::class);
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
