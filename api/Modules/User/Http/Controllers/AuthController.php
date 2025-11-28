<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Modules\User\Models\User;
use Modules\User\Http\Requests\RegisterRequest;
use Modules\User\Http\Requests\LoginRequest;

class AuthController
{
    public function register(RegisterRequest $request)
    {
        try {
            $user = User::create([
                // 'name'     => $request->name,
                'username'    => $request->mobile,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('api_token')->plainTextToken;

            return response()->json([
                'status'  => 1,
                'user'  => $user,
                'token' => $token,
            ], 201);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status'  => 0,
                'user'  => null,
                'token' => null,
            ], 201);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $user = User::where('username', $request->username)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['error' => 'Invalid credentials'], 422);
            }

            $token = $user->createToken('api_token')->plainTextToken;

            return response()->json([
                'status'  => 1,
                'user'  => $user,
                'token' => $token,
            ]);
        } catch (\Throwable $th) {
            throw $th;
            return response()->json([
                'status'  => 0,
                'user'  => null,
                'token' => null,
            ], 201);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'status' => 1
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status'  => 0,
            ], 201);
        }
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
