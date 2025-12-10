<?php

namespace Modules\User\Http\Controllers;

use Modules\User\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function registerForm()
    {
        return view('user::register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'digits_between:10,15', 'unique:users,mobile'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'mobile'   => $request->mobile,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard.home');
    }

    public function loginForm()
    {
        return view('user::login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'mobile'   => ['required', 'digits_between:10,15'],
            'password' => ['required', 'string'],
        ]);

        $key = $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'mobile' => "تعداد تلاش‌های ورود زیاد بوده. لطفاً بعد از {$seconds} ثانیه دوباره امتحان کنید."
            ])->onlyInput('mobile');
        }

        $credentials = $request->only('mobile', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($key);
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard.home'));
        }

        RateLimiter::hit($key, 60);

        return back()->withErrors([
            'mobile' => 'اطلاعات ورود اشتباه است.',
        ])->onlyInput('mobile');
    }

    public function me()
    {
        return view('dashboard.me', [
            'user' => auth()->user()
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('dashboard.login.form');
    }

    public function forgotPasswordForm()
    {
        return view('user::forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'mobile' => ['required', 'digits_between:10,15'],
        ]);

        // اگر از SMS برای ریست رمز استفاده می‌کنید، این قسمت باید با سرویس پیامک جایگزین بشه
        return back()->with('status', 'لینک بازیابی رمز به شماره شما ارسال شد (پیاده‌سازی SMS نیاز دارد).');
    }

    public function resetPasswordForm(string $token)
    {
        return view('user::reset-password', [
            'token' => $token
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'mobile'   => ['required', 'digits_between:10,15'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        // ریست رمز بدون ایمیل نیازمند پیاده‌سازی custom است
        $user = User::where('mobile', $request->mobile)->first();
        if (!$user) {
            return back()->withErrors(['mobile' => 'شماره موبایل یافت نشد.']);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        return redirect()->route('dashboard.login.form')->with('status', 'رمز عبور با موفقیت تغییر کرد.');
    }
}
