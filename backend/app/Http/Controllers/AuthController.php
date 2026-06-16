<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => 'required|string|max:50|unique:users',
            'email'    => 'required|email|max:100|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'name'     => 'required|string|max:100',
            'phone'    => 'nullable|string|max:20',
            'role'     => 'required|in:buyer,seller,driver',
        ]);

        $user = User::create([
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'name'     => $data['name'],
            'phone'    => $data['phone'] ?? null,
        ]);

        UserRole::create(['user_id' => $user->id, 'role' => $data['role']]);

        if ($data['role'] === 'buyer') {
            Wallet::create(['user_id' => $user->id]);
            Cart::create(['user_id' => $user->id]);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'user'    => $user->load('roles'),
            'token'   => $token,
            'active_role' => $data['role'],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
            'role'     => 'required|in:admin,buyer,seller,driver',
        ]);

        $user = User::where('email', $data['login'])
            ->orWhere('username', $data['login'])
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['login' => 'Kredensial tidak valid.']);
        }

        if (! $user->hasRole($data['role'])) {
            throw ValidationException::withMessages(['role' => 'Akun tidak memiliki role ini.']);
        }

        $token = JWTAuth::claims(['active_role' => $data['role']])->fromUser($user);

        return response()->json([
            'user'        => $user->load('roles'),
            'token'       => $token,
            'active_role' => $data['role'],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user'        => $request->user()->load('roles'),
            'active_role' => JWTAuth::getPayload()->get('active_role'),
        ]);
    }

    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Logout berhasil.']);
    }

    public function switchRole(Request $request): JsonResponse
    {
        $data = $request->validate([
            'role' => 'required|in:admin,buyer,seller,driver',
        ]);

        $user = $request->user();

        if (! $user->hasRole($data['role'])) {
            return response()->json(['message' => 'Akun tidak memiliki role ini.'], 403);
        }

        JWTAuth::invalidate(JWTAuth::getToken());

        $token = JWTAuth::claims(['active_role' => $data['role']])->fromUser($user);

        return response()->json([
            'token'       => $token,
            'active_role' => $data['role'],
        ]);
    }
}
