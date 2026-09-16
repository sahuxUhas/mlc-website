<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** সিকিউর অ্যাডমিন লগইন — রেট লিমিট ও অ্যাক্টিভিটি লগ সহ */
class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email', 'max:190'],
            'password' => ['required', 'string', 'min:6'],
        ], [], ['email' => 'ইমেইল', 'password' => 'পাসওয়ার্ড']);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            ActivityLogger::log('login_failed', 'auth', 'ব্যর্থ লগইন চেষ্টা: '.($credentials['email']));

            return back()
                ->withInput($request->only('email', 'remember'))
                ->with('error', 'ইমেইল বা পাসওয়ার্ড সঠিক নয়।');
        }

        $user = Auth::user();

        // নিষ্ক্রিয় অ্যাকাউন্টে প্রবেশ বন্ধ
        if (! $user->is_active) {
            Auth::logout();

            return back()->with('error', 'আপনার অ্যাকাউন্টটি নিষ্ক্রিয় করা হয়েছে।');
        }

        $request->session()->regenerate();

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->saveQuietly();

        ActivityLogger::log('login', 'auth', $user->name.' লগইন করেছেন');

        return redirect()->intended(route('admin.dashboard'))
            ->with('success', 'স্বাগতম, '.$user->name.'!');
    }

    public function logout(Request $request)
    {
        ActivityLogger::log('logout', 'auth', $request->user()?->name.' লগআউট করেছেন');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'সফলভাবে লগআউট হয়েছে।');
    }
}
