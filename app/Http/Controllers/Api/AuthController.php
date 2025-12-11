<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;
use App\Http\Resources\UserResource;


class AuthController extends Controller
{
    
    public function register(RegisterRequest $request)
    {
        // Get validated data from Form Request
        $validated = $request->validated();

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'User created successfully.',
            'data'    => [
                'user'  => new UserResource($user),
                'token' => $token
            ]
        ], 201);
    }

    
    public function login(LoginRequest $request)
    {
        // Get validated data from Form Request
        $validated = $request->validated();

        $email = $validated['email'];
        $ip    = $request->ip();
        $key   = 'login-attempts:' . $ip . '|' . $email;

        // Rate limiting check
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Too many login attempts. Try again in ' . RateLimiter::availableIn($key) . ' seconds.'
            ], 429);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            RateLimiter::hit($key, 60);
            return response()->json([
                'status'  => 'error',
                'message' => 'User not found with this email.'
            ], 404);
        }

        if (!Hash::check($validated['password'], $user->password)) {
            RateLimiter::hit($key, 60);
            return response()->json([
                'status'  => 'error',
                'message' => 'Incorrect password.'
            ], 401);
        }

        if (isset($user->is_active) && !$user->is_active) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Your account is not active. Please contact support.'
            ], 403);
        }

        $user->update(['last_login_at' => now()]);
        RateLimiter::clear($key);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'Logged in successfully.',
            'data'    => [
                'user'  => new UserResource($user),
                'token' => $token
            ]
        ], 200);
    }

    
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Logged out successfully.',
            'data'    => null
        ], 200);
    }
}
