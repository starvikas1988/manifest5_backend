<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Category;
use App\Models\Assignment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AssignmentController extends Controller
{
    public function assignMatch(Request $request) {
        try {
            // ✅ Validate the incoming request
            $validatedData = $request->validate([
                'assignments' => 'required|array|min:1',
                'assignments.*.operator_id' => 'required|exists:users,id',
                'assignments.*.category_id' => 'required|exists:categories,id',
                'assignments.*.match_id' => 'required|exists:matches,id',
            ]);
    
            // ✅ Prepare data for batch insert
            $assignmentsToCreate = [];
    
            foreach ($validatedData['assignments'] as $assignment) {
                $assignmentsToCreate[] = [
                    'operator_id' => $assignment['operator_id'],
                    'category_id' => $assignment['category_id'],
                    'match_id' => $assignment['match_id'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
    
            // ✅ Insert multiple records at once
            Assignment::insert($assignmentsToCreate);
    
            // ✅ Log for debugging
            Log::info('Assignments Created:', $assignmentsToCreate);
    
            return response()->json(['message' => 'Assignments created successfully'], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    // Fetch assigned operators with categories
    // public function getAssignments() {
    //     $assignments = User::with('categories','MatchModel')->get();

    //     return response()->json($assignments);
    // }

    public function getAssignments() {
        $assignments = User::with(['assignments.category', 'assignments.match'])->get();
        
        return response()->json($assignments);
    }

    public function getAssignmentedMatchIds() {
        $assignments = User::with(['assignments.match'])->get();
    
        // Extract match_id values from assignments
        $assignedMatchIds = $assignments->flatMap(function ($user) {
            return $user->assignments->pluck('match_id');
        })->unique()->values(); // Ensure unique match_ids and reset array indexes
    
        return response()->json([
            'assigned_match_ids' => $assignedMatchIds
        ]);
    }
    

    public function getAssignmentsCount(){
        $assignments = Assignment::all();
        $assignmentsCount = $assignments->count();
        return response()->json($assignmentsCount);
    }
    
}
