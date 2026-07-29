<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Services\ActivityLogger;

class AuthController extends Controller
{
    /**
     * Register new User
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email|max:255',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        ActivityLogger::use('auth')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties(['email' => $user->email, 'name' => $user->name])
            ->log('User registered');

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], Response::HTTP_CREATED);
    }

    /**
     * Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Delete previous tokens of the user
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        ActivityLogger::use('auth')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties(['email' => $user->email])
            ->log('User logged in');

        return response()->json([
            'message' => 'User logged in successfully',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], Response::HTTP_OK);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            ActivityLogger::use('auth')
                ->performedOn($user)
                ->causedBy($user)
                ->withProperties(['email' => $user->email])
                ->log('User logged out');
        }

        $request->user()->currentAccessToken()->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Authenticated User Data
     */
    public function user(Request $request)
    {
        return response()->json($request->user(), Response::HTTP_OK);
    }
}
