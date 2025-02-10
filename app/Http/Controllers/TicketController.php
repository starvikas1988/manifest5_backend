<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Match;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    // Method for getting all tickets
    public function index()
    {
        $tickets = Ticket::with(['match', 'user', 'assigned'])->get(); // Load relationships for better performance
        return response()->json($tickets);
    }

    // Method for getting a single ticket
    public function show($id)
    {
        $ticket = Ticket::with(['match', 'user', 'assigned'])->findOrFail($id);
        return response()->json($ticket);
    }

    // Method for creating a new ticket
    public function store(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'priority' => 'required|string',
            'username' => 'required|string',
            'subject' => 'required|string',
            'error_type' => 'required|string',
            'error_details' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'assigned_to' => 'nullable|exists:users,id', // Make sure user exists
            'match_id' => 'nullable|exists:matches,id', // Make sure match exists
            'user_id' => 'nullable|exists:users,id', // Make sure user exists
            'status' => 'required|in:Active,Inactive',
            'ticket_date' => 'nullable|date',
        ]);

        // If validation fails, return errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

          // Handle file upload (if a file is provided)
          $filePath = null;
          if ($request->hasFile('file')) {
              // Store the file on the 'public' disk (you can change the disk if needed)
              $filePath = $request->file('file')->store('tickets', 'public'); // Store in public/tickets directory
          }
        // Create new ticket
        $ticket = Ticket::create([
            'priority' => $request->priority,
            'username' => $request->username,
            'subject' => $request->subject,
            'error_type' => $request->error_type,
            'error_details' => $request->error_details,
            'file' => $filePath,  // Save the file path in the database
            'assigned_to' => $request->assigned_to,
            'match_id' => $request->match_id,
            'user_id' => $request->user_id,
            'status' => $request->status,
            'ticket_date' => $request->ticket_date,
        ]);

        return response()->json($ticket, 201); // Return the created ticket
    }

    // Method for updating a ticket
    public function update(Request $request, $id)
    {
        // Find the ticket
        $ticket = Ticket::find($id);
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }
        
        // Validate the request
         // Convert empty string values to NULL before validation
        $request->merge([
            'match_id' => $request->match_id === "" ? null : $request->match_id,
            'user_id' => $request->user_id === "" ? null : $request->user_id,
            'assigned_to' => $request->assigned_to === "" ? null : $request->assigned_to,
        ]);
       // dd($request->all());
        if ($request->hasFile('file')) {
            $validatedData = $request->validate([
                'priority' => 'sometimes|string',
                'username' => 'sometimes|string',
                'subject' => 'sometimes|string',
                'error_type' => 'sometimes|string',
                'error_details' => 'nullable|string',
                'file' => 'sometimes|file|mimes:jpg,jpeg,png,pdf|max:10240',
                'assigned_to' => 'nullable|exists:users,id',
                'match_id' => 'nullable|exists:matches,id',
                'user_id' => 'nullable|exists:users,id',
                'status' => 'sometimes|in:Active,Inactive',
                'ticket_date' => 'nullable|date',
            ]);
        }else{
            $validatedData = $request->validate([
                'priority' => 'sometimes|string',
                'username' => 'sometimes|string',
                'subject' => 'sometimes|string',
                'error_type' => 'sometimes|string',
                'error_details' => 'nullable|string',
                'assigned_to' => 'nullable|exists:users,id',
                'match_id' => 'nullable|exists:matches,id',
                'user_id' => 'nullable|exists:users,id',
                'status' => 'sometimes|in:Active,Inactive',
                'ticket_date' => 'nullable|date',
            ]);
        }
   
        // Handle file upload (if provided)
        if ($request->hasFile('file')) {
            // Delete old file if it exists
            if ($ticket->file && Storage::exists('public/' . $ticket->file)) {
                Storage::delete('public/' . $ticket->file);
            }
    
            // Store new file and update file path
            $filePath = $request->file('file')->store('tickets', 'public');
            $validatedData['file'] = $filePath;
        }
        
        // Update only provided fields
        $ticket->update($validatedData);
       // $ticket->update($request->all());
    
        // Return response
        return response()->json([
            'message' => 'Ticket updated successfully',
            'ticket' => $ticket
        ], 200);
    }
    

    // Method for deleting a ticket
    public function destroy($id)
    {
        // Find the ticket by ID and delete it
        $ticket = Ticket::findOrFail($id);
        $ticket->delete();

        return response()->json(['message' => 'Ticket deleted successfully']);
    }

    // Method for restoring a soft-deleted ticket
    public function restore($id)
    {
        $ticket = Ticket::onlyTrashed()->findOrFail($id);
        $ticket->restore();

        return response()->json(['message' => 'Ticket restored successfully']);
    }

    // Method for permanently deleting a ticket
    public function forceDelete($id)
    {
        $ticket = Ticket::onlyTrashed()->findOrFail($id);
        $ticket->forceDelete();

        return response()->json(['message' => 'Ticket permanently deleted']);
    }

    public function updateStatus(Request $request, $ticketId)
    {
        // Validate request
        $request->validate([
            'status' => 'required|string|in:Active,Inactive' // Add more statuses if needed
        ]);

        // Find the ticket by ID
        $ticket = Ticket::find($ticketId);

        // Check if ticket exists
        if (!$ticket) {
            return response()->json([
                'error' => 'Ticket not found'
            ], 404);
        }

        // Update the status
        $ticket->status = $request->status;
        $ticket->save();

        // Return response
        return response()->json([
            'message' => 'Ticket status updated successfully',
            'ticket' => $ticket
        ], 200);
    }

}
