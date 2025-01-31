<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/login', function () {
    return response()->json(['message' => 'Login route not implemented.'], 401);
})->name('login');
Route::get('/register', function () {
    return response()->json(['message' => 'Register route not implemented.'], 401);
})->name('register');
Route::get('/home', function () {
    return response()->json(['message' => 'Home route not implemented.'], 401);
})->name('home');
Route::get('/dashboard', function () {
    return response()->json(['message' => 'Dashboard route not implemented.'], 401);
})->name('dashboard');
Route::get('/logout', function () {
    return response()->json(['message' => 'Logout route not implemented.'], 401);
})->name('logout');
Route::get('/user', function () {
    return response()->json(['message' => 'User route not implemented.'], 401);
})->name('user');

// Route::get('/users/search', [UserController::class, 'search']); 
