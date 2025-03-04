<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Category;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AssignmentController extends Controller
{
    // public function assignMatch(Request $request) {
    //     try {
    //         // ✅ Validate the incoming request
    //         $validatedData = $request->validate([
    //             'assignments' => 'required|array|min:1',
    //             'assignments.*.operator_id' => 'required|exists:users,id',
    //             'assignments.*.category_id' => 'required|exists:categories,id',
    //             'assignments.*.match_id' => 'required|exists:matches,id',
    //         ]);

    //         // ✅ Prepare data for batch insert
    //         $assignmentsToCreate = [];

    //         foreach ($validatedData['assignments'] as $assignment) {
    //             $assignmentsToCreate[] = [
    //                 'operator_id' => $assignment['operator_id'],
    //                 'category_id' => $assignment['category_id'],
    //                 'match_id' => $assignment['match_id'],
    //                 'created_at' => now(),
    //                 'updated_at' => now()
    //             ];
    //         }

    //         // ✅ Insert multiple records at once
    //         Assignment::insert($assignmentsToCreate);

    //         // ✅ Log for debugging
    //         Log::info('Assignments Created:', $assignmentsToCreate);

    //         return response()->json(['message' => 'Assignments created successfully'], 201);
    //     } catch (ValidationException $e) {
    //         return response()->json(['error' => $e->errors()], 400);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }
    // data format for assignMatch
    // {
    //     "assignments": [
    //       { "operator_id": 1, "category_id": 2 ,"match_id":208},
    //       { "operator_id": 1, "category_id": 3,"match_id":208 }
    //     ]
    //   }

    public function assignMatch(Request $request)
    {
        // ✅ Validate request data
        $validator = Validator::make($request->all(), [
            'categoryId' => 'required|integer',
            'marketIds' => 'required|array',
            'marketIds.*' => 'integer', // Ensure each market ID is an integer
            'operatorId' => 'required|integer',
            'matchId' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // ✅ Insert multiple records in the assignments table
        foreach ($request->marketIds as $marketId) {
            Assignment::create([
                'category_id' => $request->categoryId,
                'market_id' => $marketId,
                'operator_id' => $request->operatorId,
                'match_id' => $request->matchId,
            ]);
        }

        return response()->json([
            'message' => 'Assignments successfully created',
        ], 201);
    }

    public function bulkAssign(Request $request)
    {
        // ✅ Validate request data
        $validator = Validator::make($request->all(), [
            'assignments' => 'required|array|min:1',
            'assignments.*.categoryId' => 'required|integer',
            'assignments.*.marketIds' => 'required|array|min:1',
            'assignments.*.marketIds.*' => 'integer',
            'assignments.*.operatorId' => 'required|integer',
            'assignments.*.matchId' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // ✅ Prepare bulk insert data
        $insertData = [];
        foreach ($request->assignments as $assignment) {
            foreach ($assignment['marketIds'] as $marketId) {
                $insertData[] = [
                    'category_id' => $assignment['categoryId'],
                    'market_id' => $marketId,
                    'operator_id' => $assignment['operatorId'],
                    'match_id' => $assignment['matchId'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (empty($insertData)) {
            return response()->json(['message' => 'No valid assignments found'], 400);
        }

        // ✅ Use Transaction for Safe Insert
        try {
            DB::beginTransaction();
            Assignment::insert($insertData);
            DB::commit();
            return response()->json(['message' => 'Assignments successfully created'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating assignments', 'error' => $e->getMessage()], 500);
        }
    }



    // public function getAssignments(Request $request, $userId = null)
    // {
    //     // Check if a userId is provided; if so, filter by that userId
    //     if ($userId) {
    //         $assignments = User::with(['assignments.category', 'assignments.match'])
    //             ->where('id', $userId) // Filter by userId
    //             ->get();

    //         if ($assignments->isEmpty()) {
    //             return response()->json(['error' => 'User not found or no assignments'], 404);
    //         }
    //     } else {
    //         // If no userId is provided, return all assignments
    //         $assignments = User::with(['assignments.category', 'assignments.match'])->get();
    //     }

    //     return response()->json($assignments->toArray(), 200);
    // }

    public function getAssignments(Request $request, $userId = null)
{
    // If a userId is provided, filter by that userId
    if ($userId) {
        $assignments = User::with(['assignments.category', 'assignments.match' => function ($query) {
            $query->select('*'); // Fetch all columns from vendor_matches
        }])
        ->where('id', $userId)
        ->get();

        if ($assignments->isEmpty()) {
            return response()->json(['error' => 'User not found or no assignments'], 404);
        }
    } else {
        // If no userId is provided, return all assignments
        $assignments = User::with(['assignments.category', 'assignments.match'])->get();
    }

    return response()->json($assignments->toArray(), 200);
}


    public function getAssignmentedMatchIds()
    {
        $assignments = User::with(['assignments.match'])->get();

        // Extract match_id values from assignments
        $assignedMatchIds = $assignments->flatMap(function ($user) {
            return $user->assignments->pluck('match_id');
        })->unique()->values(); // Ensure unique match_ids and reset array indexes

        return response()->json([
            'assigned_match_ids' => $assignedMatchIds
        ]);
    }

    public function getOperatorAssignedMatchIds($operatorId)
    {
        $operator = User::with(['assignments.match'])->find($operatorId);

        if (!$operator) {
            return response()->json(['error' => 'Operator not found'], 404);
        }

        $assignedMatchIds = $operator->assignments->pluck('match_id');

        return response()->json([
            'assigned_match_ids' => $assignedMatchIds
        ]);
    }


    // public function getAssignmentsCount(){
    //     $assignments = Assignment::all();
    //     $assignmentsCount = $assignments->count();
    //     return response()->json($assignmentsCount);
    // }

    public function getUniqueMatchCount($operatorId = null)
    {
        // Query assignments, filter by operatorId if provided
        $query = Assignment::query();

        if ($operatorId) {
            $query->where('operator_id', $operatorId);
        }

        // Get unique match_ids and count them
        $uniqueMatchCount = $query->distinct('match_id')->count('match_id');
        return response()->json($uniqueMatchCount);
        //return response()->json(['unique_match_count' => $uniqueMatchCount], 200);
    }


    // public function getAssignmentsCount($operatorId = null) {
    //     // If operatorId is provided, filter assignments by operator_id
    //     if ($operatorId) {
    //         $assignmentsCount = Assignment::where('operator_id', $operatorId)->count();
    //     } else {
    //         // If no operatorId is provided, return count of all assignments
    //         $assignmentsCount = Assignment::count();
    //     }
    //     return response()->json($assignmentsCount);
    //     //return response()->json(['count' => $assignmentsCount], 200);
    // }


}
