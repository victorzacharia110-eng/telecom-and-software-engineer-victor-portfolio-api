<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * Get client dashboard statistics
     */
    public function stats(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Get projects for this client
            $projects = Project::where('client_id', $user->id)->count();
            
            // Get messages for this client (using email)
            $messages = Contact::where('email', $user->email)->count();
            
            // Get unread messages
            $unreadMessages = Contact::where('email', $user->email)
                ->whereNull('read_at')
                ->count();
            
            return response()->json([
                'success' => true,
                'message' => 'Client stats retrieved successfully',
                'data' => [
                    'projects' => $projects,
                    'messages' => $messages,
                    'unread_messages' => $unreadMessages,
                    'pending' => 0, // Placeholder for pending tasks
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch client stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get client projects
     */
    public function projects(Request $request)
    {
        try {
            $user = Auth::user();
            
            $projects = Project::where('client_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($project) {
                    return [
                        'id' => $project->id,
                        'name' => $project->name,
                        'description' => $project->description,
                        'status' => $project->status ?? 'pending',
                        'created_at' => $project->created_at,
                        'updated_at' => $project->updated_at,
                    ];
                });
            
            return response()->json([
                'success' => true,
                'message' => 'Client projects retrieved successfully',
                'data' => $projects
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch client projects',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific project
     */
    public function showProject($id)
    {
        try {
            $user = Auth::user();
            
            $project = Project::where('client_id', $user->id)
                ->where('id', $id)
                ->firstOrFail();
            
            return response()->json([
                'success' => true,
                'message' => 'Project retrieved successfully',
                'data' => $project
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get client messages
     */
    public function messages(Request $request)
    {
        try {
            $user = Auth::user();
            
            $messages = Contact::where('email', $user->email)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'from' => $message->name,
                        'email' => $message->email,
                        'company' => $message->company,
                        'service' => $message->service ?? 'General',
                        'budget' => $message->budget ?? '—',
                        'message' => $message->message,
                        'preview' => substr($message->message, 0, 100) . '...',
                        'date' => $message->created_at->format('M d, Y'),
                        'time' => $message->created_at->format('h:i A'),
                        'is_read' => $message->is_read, // Uses the computed attribute
                        'read_at' => $message->read_at,
                    ];
                });
            
            return response()->json([
                'success' => true,
                'message' => 'Client messages retrieved successfully',
                'data' => $messages
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch client messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific message
     */
    public function showMessage($id)
    {
        try {
            $user = Auth::user();
            
            $message = Contact::where('email', $user->email)
                ->where('id', $id)
                ->firstOrFail();
            
            // Mark as read if not already
            if (is_null($message->read_at)) {
                $message->update(['read_at' => now()]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Message retrieved successfully',
                'data' => [
                    'id' => $message->id,
                    'from' => $message->name,
                    'email' => $message->email,
                    'company' => $message->company,
                    'service' => $message->service,
                    'budget' => $message->budget,
                    'message' => $message->message,
                    'date' => $message->created_at->format('M d, Y'),
                    'time' => $message->created_at->format('h:i A'),
                    'is_read' => $message->is_read,
                    'read_at' => $message->read_at,
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Send a message (client to admin)
     */
    public function sendMessage(Request $request)
    {
        try {
            $user = Auth::user();
            
            $validator = Validator::make($request->all(), [
                'message' => 'required|string',
                'service' => 'nullable|string',
                'budget' => 'nullable|string',
                'company' => 'nullable|string',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $contact = Contact::create([
                'name' => $user->name,
                'email' => $user->email,
                'company' => $request->company ?? null,
                'service' => $request->service ?? null,
                'budget' => $request->budget ?? null,
                'message' => $request->message,
                'read_at' => null,
                'ip_address' => $request->ip(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $contact
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get client profile
     */
    public function profile(Request $request)
    {
        try {
            $user = Auth::user();
            
            return response()->json([
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update client profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();
            
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
                'password' => 'sometimes|string|min:8|confirmed',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $updateData = [];
            
            if ($request->has('name')) {
                $updateData['name'] = $request->name;
            }
            
            if ($request->has('email')) {
                $updateData['email'] = $request->email;
            }
            
            if ($request->has('password')) {
                $updateData['password'] = Hash::make($request->password);
            }
            
            $user->update($updateData);
            
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}