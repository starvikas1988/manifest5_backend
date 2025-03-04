<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Mail\OtpMail;
use Carbon\Carbon; // Import Carbon for date comparisons
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;


class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password'])
            //'password' => bcrypt($validatedData['password']), 
        ]);

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }
    
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'No such user exists!!'], 404);
        }

        return response()->json(['message' => 'User exists!!'], 200);
    }

    public function generateOtpMail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
       
        $user = User::where('email', $request->email)->first();

        

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
       
        // Generate a 6-digit OTP
        $otp = rand(100000, 999999);
        $email = $request->email;
        // Store OTP with expiration time (e.g., 10 minutes)
        DB::table('password_resets')->updateOrInsert(
            ['email' => $email], // If email exists, update; otherwise, insert
            ['otp' => $otp, 'otp_expires_at' => Carbon::now()->addMinutes(10)]
        );
    
       // dd($user);
        Mail::to($user->email)->send(new OtpMail($otp));

        return response()->json([
            'message' => 'OTP generated successfully',
            'otp' => $otp, // Return OTP in JSON response
            'expires_in' => 600 // Expiration time in seconds (10 minutes)
        ]);
    }

    public function validateOtp(Request $request)
    {
        // dd($request->all());
        // Validate request input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:password_resets,email',
            'otp' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        

        $email = $request->email;
        $otp = $request->otp;

        // Retrieve the OTP record
        $otpRecord = DB::table('password_resets')->where('email', $email)->first();

        // Check if OTP exists
        if (!$otpRecord) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        //  Check if OTP is expired
        if (Carbon::now()->greaterThan(Carbon::parse($otpRecord->otp_expires_at))) {
            return response()->json(['message' => 'OTP has expired'], 400);
        }

        //  Check if OTP is correct
        if ($otpRecord->otp !== $otp) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        // OTP is valid, now delete it (one-time use)
        //DB::table('password_resets')->where('email', $email)->delete();

        return response()->json([
            'message' => 'OTP verified successfully',
        ]);
    }

    public function resetPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:password_resets,email',
            'newPassword' => 'required|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $email = $request->email;
        $newPassword = $request->newPassword;

        $otpRecord = DB::table('password_resets')->where('email', $email)->first();
        if(!$otpRecord){
            return response()->json(['message' => 'OTP verification required before resetting password'], 400);   
        }

        $user = User::where('email', $email)->first();
        if(!$user){
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        DB::table('password_resets')->where('email', $email)->delete();

        return response()->json([
            'message' => 'Password reset successfully',
        ]);

    }



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
        // dd($user->name);

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
            'user_name' => $user->name,
            'token' => $token,
            'device_id' => $user->device_id, // Return stored device ID
        ]);
    }

    public function operator_login(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
      

        $operator = User::where('email', $request->email)->where('role', 'operator')->first();
        $operator_id = $operator->id;
        

        if (!$operator) {
            return response()->json(['message' => 'No such Operator exists!!'], 401);
        }

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
            'user_name' => $user->name,
            'operator_id' => $operator_id,
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

    public function getAccessToken()
    {
        $cacheKey = 'vendor_api_token';

        // Check if token exists in cache
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // API URL and Credentials
        $authApiUrl = 'https://api.maniifest5.com/api/Auth/token';
        $credentials = [
            'email' => 'john@example.com', // Replace with actual email
            'password' => 'password123',  // Replace with actual password
        ];

        // Make API Request
        $response = Http::post($authApiUrl, $credentials);

        // Check if response is successful
        if ($response->successful()) {
            $data = $response->json();
            $accessToken = $data['response']['accessToken'] ?? null;
            $expiresAt = strtotime($data['response']['expiresAt'] ?? 'now');

            if ($accessToken) {
                // Store token in cache with expiration time (minus a buffer)
                Cache::put($cacheKey, $accessToken, $expiresAt - time() - 60);
                return $accessToken;
            }
        }

        return null; // Return null if authentication fails
    }
    
}
