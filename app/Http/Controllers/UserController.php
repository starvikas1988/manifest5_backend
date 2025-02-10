<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index()
    {
        // $users = User::where('role', '!=', 'admin')->get();
        $users = User::all();

        // Replace all null values with an empty string
        $modifiedUsers = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name ?? "",
                'email' => $user->email ?? "",
                'phoneno' => $user->phoneno ?? "",
                'role' => $user->role ?? "",
                'profile_image' => $user->profile_image ?? "",
                'device_id' => $user->device_id ?? "",
                'host_name' => $user->host_name ?? "",
                'status' => $user->status ?? "",
                'effective_date' => $user->effective_date ?? "",
                'cease_date' => $user->cease_date ?? "",
                'email_verified_at' => $user->email_verified_at ?? "",
                'created_at' => $user->created_at ?? "",
                'updated_at' => $user->updated_at ?? "",
                'deleted_at' => $user->deleted_at ?? "",
            ];
        });

        return response()->json($modifiedUsers, 200);
    }



    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'phoneno' => 'required|string|max:20',
                'role' => ['required', Rule::in(['admin', 'user', 'operator'])], 
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'device_id' => 'nullable|string|max:255',
                'host_name' => 'nullable|string|max:255',
                'status' => 'required|in:Active,Inactive',
                'effective_date' => 'nullable|date',
                'cease_date' => 'nullable|date|after_or_equal:effective_date',
            ]);
        
           // dd($validated);
        } catch (\Illuminate\Validation\ValidationException $e) {
            dd($e->errors()); // This will show exactly what is failing
        }

        // Handle profile image upload
        $profileImagePath = null;
        if ($request->hasFile('profile_image')) {
            $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phoneno' => $request->phoneno,
            'role' => $request->role,
            'profile_image' => $profileImagePath,
            'device_id' => $request->device_id,
            'host_name' => $request->host_name,
            'status' => $request->status,
            'effective_date' => $request->effective_date,
            'cease_date' => $request->cease_date,
            'password' => Hash::make('defaultpassword'), // Default password, can be changed later
        ]);

        return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
    }

     /**
     * Show details of a specific user.
     */
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user, 200);
    }

    /**
     * Update an existing user.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phoneno' => 'sometimes|string|max:20',
            'role' => ['sometimes', Rule::in(['admin', 'user', 'operator'])],
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'device_id' => 'nullable|string|max:255',
            'host_name' => 'nullable|string|max:255',
            'status' => 'sometimes|in:Active,Inactive',
            'effective_date' => 'nullable|date',
            'cease_date' => 'nullable|date|after_or_equal:effective_date',
        ]);
      //  dd($request->validate());

        if ($request->hasFile('profile_image')) {
            $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
            $user->profile_image = $profileImagePath;
        }

        $user->update($request->all());

        return response()->json(['message' => 'User updated successfully', 'user' => $user], 200);
    }

    /**
     * Delete a user.
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    // ✅ Update user status (Toggle Active/Inactive)
    public function updateStatus(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $newStatus = $request->status === "Active" ? "Active" : "Inactive";
        $user->status = $newStatus;
        $user->save();

        return response()->json(['message' => 'Status updated successfully', 'status' => $user->status]);
    }

    public function search(Request $request)
    {
        $query = User::query();

        if ($request->name) {
            $query->where('name', 'like', "%{$request->name}%");
        }
        if ($request->email) {
            $query->where('email', 'like', "%{$request->email}%");
        }
        if ($request->phoneno) {
            $query->where('phoneno', 'like', "%{$request->phoneno}%");
        }
        if ($request->role) {
            $query->where('role', $request->role);
        }
        if ($request->device_id) {
            $query->where('device_id', 'like', "%{$request->device_id}%");
        }
        if ($request->effective_date) {
            $query->whereDate('effective_date', '=', $request->effective_date);
        }
        if ($request->cease_date) {
            $query->whereDate('cease_date', '=', $request->cease_date);
        }

        // $sql = $query->toSql(); // Get the SQL query string
        // $bindings = $query->getBindings(); // Get the parameter bindings
        // dd($sql, $bindings);

        return response()->json($query->get(), 200);
    }

}
