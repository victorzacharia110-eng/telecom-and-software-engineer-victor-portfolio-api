<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::paginate(15);
        
        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully',
            'data' => $users,
            'meta' => [
                'total' => User::count(),
                'verified' => User::whereNotNull('email_verified_at')->count(),
                'admins' => User::where('role', 'admin')->count(),
                'clients' => User::where('role', 'client')->count(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'sometimes|in:admin,client',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validatedData = $validator->validated();

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'phone_number' => $validatedData['phone_number'] ?? null,
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'] ?? 'client',
            'email_verified_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully!',
            'data' => [
                'user' => $user,
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'role' => $user->role,
                'created_at' => $user->created_at,
            ]
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'error' => 'User with ID ' . $id . ' does not exist'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'User retrieved successfully',
            'data' => [
                'user' => $user,
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'role' => $user->role,
                'created_at' => $user->created_at,
                'is_verified' => $user->is_verified,
                'account_age' => $user->account_age,
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
            'phone_number' => 'nullable|string|max:20', 
            'password' => 'sometimes|string|min:8|confirmed',
            'role' => 'sometimes|in:admin,client',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = [];
        $updatedFields = [];

        if ($request->has('name')) {
            $updateData['name'] = $request->name;
            $updatedFields[] = 'name';
        }

        if ($request->has('email')) {
            $updateData['email'] = $request->email;
            $updatedFields[] = 'email';
        }

        if ($request->has('phone_number')) {
            $updateData['phone_number'] = $request->phone_number;
            $updatedFields[] = 'phone_number';
        }

        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
            $updatedFields[] = 'password';
        }

        if ($request->has('role')) {
            $updateData['role'] = $request->role;
            $updatedFields[] = 'role';
        }

        $oldValues = [
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'role' => $user->role,
        ];

        $user->update($updateData);
        $user->refresh();

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully!',
            'data' => [
                'user' => $user,
                'updated_fields' => $updatedFields,
                'old_values' => $oldValues
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        //  Prevent self-deletion
        if (auth()->id() === (int) $id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account'
            ], 403);
        }

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'role' => $user->role,
        ];

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
            'data' => [
                'deleted_user' => $userData,
                'remaining_users' => User::count()
            ]
        ]);
    }
}