<?php

namespace Modules\User\Http\Controllers;

use Modules\User\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'mobile'   => ['required', 'digits_between:10,15', 'unique:users,mobile'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'mobile'   => $data['mobile'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'status' => true,
            'user'   => $user,
            'token'  => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'mobile'   => ['required', 'digits_between:10,15'],
            'password' => ['required', 'string'],
        ]);

        $key = $request->ip().'|'.$data['mobile'];

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'status'  => false,
                'message' => 'تعداد تلاش‌های ورود زیاد است.',
                'retry_in'=> RateLimiter::availableIn($key),
            ], 429);
        }

        $user = User::where('mobile', $data['mobile'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($key, 60);

            return response()->json([
                'status'  => false,
                'message' => 'اطلاعات ورود اشتباه است.',
            ], 401);
        }

        RateLimiter::clear($key);

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'status' => true,
            'user'   => $user,
            'token'  => $token,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'status' => true,
            'user'   => $request->user(),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'با موفقیت خارج شدید.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'mobile'   => ['required', 'digits_between:10,15'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::where('mobile', $data['mobile'])->first();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'کاربر یافت نشد.',
            ], 404);
        }

        $user->update([
            'password' => Hash::make($data['password']),
            'remember_token' => Str::random(60),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'رمز عبور با موفقیت تغییر کرد.',
        ]);
    }
}
