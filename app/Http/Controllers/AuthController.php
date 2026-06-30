<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8|confirmed',
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
            'phone_number' => $validatedData['phone_number'],
            'password' => Hash::make($validatedData['password']),
            'role' => 'client',
            'email_verified_at' => null,
        ]);

        // Create API token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully!',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ], 201);
    }

    /**
     * Login user (Token-based)
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required_without:phone_number|string|email|nullable',
            'phone_number' => 'required_without:email|string|nullable',
            'password' => 'required|string',
            'remember' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $remember = $request->boolean('remember', false);

        // Determine login field
        $loginField = $request->has('email') ? 'email' : 'phone_number';
        $loginValue = $request->input($loginField);

        // Fetch the user
        $user = User::where($loginField, $loginValue)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid login credentials',
                'errors' => [
                    $loginField => ['The provided credentials do not match our records.']
                ]
            ], 401);
        }

        // Attempt login
        if (!Auth::attempt([$loginField => $loginValue, 'password' => $request->password], $remember)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid login credentials',
                'errors' => [
                    'password' => ['The provided password is incorrect.']
                ]
            ], 401);
        }

        // Update last login timestamp
        $user->update(['last_login_at' => now()]);

        // Revoke old tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful!',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'role' => $user->role,
                    'is_verified' => $user->email_verified_at !== null,
                    'last_login' => $user->last_login_at ?? null,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
                'remember_me' => $remember,
            ]
        ], 200);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'User retrieved successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                    'email_verified_at' => $user->email_verified_at,
                    'is_verified' => $user->email_verified_at !== null,
                    'account_age' => $user->created_at->diffForHumans(),
                ]
            ]
        ], 200);
    }

    /**
     * Logout user (Token-based)
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully!',
            'data' => [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'logged_out_at' => now(),
            ]
        ], 200);
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
                'errors' => [
                    'current_password' => ['The current password is incorrect.']
                ]
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully!',
            'data' => [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'password_updated_at' => now(),
            ]
        ], 200);
    }

    /**
     * Send password reset link
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json([
                'success' => true,
                'message' => 'Reset link sent to your email.'
            ])
            : response()->json([
                'success' => false,
                'message' => 'Unable to send reset link.'
            ], 500);
    }
}