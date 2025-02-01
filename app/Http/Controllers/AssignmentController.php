<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Category;

class AssignmentController extends Controller
{
    public function assignMatch(Request $request) {
        $validatedData = $request->validate([
            'assignments' => 'required|array|min:1',
            'assignments.*.operator_id' => 'required|exists:users,id',
            'assignments.*.category_id' => 'required|exists:categories,id',
        ]);

        foreach ($validatedData['assignments'] as $assignment) {
            $operator = User::find($assignment['operator_id']);
            $operator->categories()->attach($assignment['category_id']); // Assign category to operator
        }

        return response()->json([
            'message' => 'Assignments created successfully'
        ], 201);
    }

    // Fetch assigned operators with categories
    public function getAssignments() {
        $assignments = User::with('categories')->get();

        return response()->json($assignments);
    }
}
