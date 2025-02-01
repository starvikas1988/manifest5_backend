<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Http\Requests\RegisterRequest;
use Carbon\Carbon; // Import Carbon for date comparisons


class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']), 
        ]);

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }
    
    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required'
    //     ]);

    //     if (!Auth::attempt($request->only('email', 'password'))) {
    //         return response()->json(['error' => 'Invalid credentials'], 401);
    //     }

    //     $user = Auth::user();
    //     if (!$user) {
    //         return response()->json(['message' => 'User not authenticated'], 401);
    //     }
      
    //   // Generate a new unique device ID for this login attempt
    //   if($user->device_id){
    //     $newDeviceId = $user->device_id;
    //   }else{
    //     $newDeviceId = Str::uuid();
    //   }
    
    //     $token = $user->createToken('authToken')->plainTextToken;
    //     if (!$token) {
    //         return response()->json(['message' => 'Token missing!'], 401);
    //     }
    //     // Check if the frontend sends a device_id (stored from first login)
    //     $requestDeviceId = $request->header('Device-ID'); // Read from request header

    //     if ($user->device_id && $requestDeviceId && $user->device_id !== $requestDeviceId) {
    //         return response()->json(['error' => 'Unauthorized device'], 403);
    //     }

    //     // If first-time login, generate device ID
    //     if (!$user->device_id) {
    //         $user->device_id = $newDeviceId;
    //         $user->save();
    //     }

       
    //     return response()->json([
    //         'message' => 'Login successful',
    //         'token' => $token,
    //         'device_id' => $user->device_id, // Return stored device ID
    //     ]);
    // }




    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Retrieve authenticated user
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // 1. Check if user status is "Active"
        if ($user->status !== 'Active') {
            return response()->json(['message' => 'Your account is not active. Please contact support.'], 403);
        }

        //  2. Check if the current date is between `effective_date` and `cease_date`
        $currentDate = Carbon::now(); // Get current date
        if ($user->effective_date && $user->cease_date) {
            if ($currentDate->between(Carbon::parse($user->effective_date), Carbon::parse($user->cease_date))) {
                return response()->json(['message' => 'Access restricted. Contact admin.'], 403);
            }
        }

        //  3. Device ID Handling
        $newDeviceId = $user->device_id ?: Str::uuid(); // Use existing device_id or generate a new one

        //  4. Check if request contains a device_id and verify against stored device_id
        $requestDeviceId = $request->header('Device-ID'); // Read from request header

        if ($user->device_id && $requestDeviceId && $user->device_id !== $requestDeviceId) {
            return response()->json(['message' => 'Unauthorized device. Login allowed only from your registered device.'], 403);
        }

        //  5. If first-time login, store the generated device ID
        if (!$user->device_id) {
            $user->device_id = $newDeviceId;
            $user->save();
        }

        // ✅ 6. Generate authentication token
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'device_id' => $user->device_id, // Return stored device ID
        ]);
    }


    public function logout(Request $request)
    {
        $user = $request->user();
        //dd($user);
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
    
        // Attempt to delete the tokens
        $deleted = $user->tokens()->delete();

        if ($deleted) {
            return response()->json(['message' => 'Logged out successfully'], 200);
        } else {
            return response()->json(['message' => 'Failed to log out. Please try again.'], 500);
        }
    }
    
}
