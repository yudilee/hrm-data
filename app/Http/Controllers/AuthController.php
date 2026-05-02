<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('import.index');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $ip = $request->ip();
        $ua = $request->userAgent() ?? '';

        // Brute-force protection: 10+ failures in 15 min from same IP
        if (LoginAttempt::recentFailures($ip, 15) >= 10) {
            Log::channel('security')->warning('Login brute-force lockout triggered', [
                'ip' => $ip,
                'email' => $request->email,
            ]);

            return back()->withErrors([
                'email' => 'Too many failed login attempts from your IP. Please wait 15 minutes.',
            ])->onlyInput('email');
        }

        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $this->completeLogin($user, $request);
            LoginAttempt::record($request->email, true, $ip, $ua);

            return redirect()->intended(route('import.index'));
        }

        LoginAttempt::record($request->email, false, $ip, $ua, 'invalid_credentials');

        Log::channel('security')->info('Failed login attempt', [
            'email' => $request->email,
            'ip' => $ip,
        ]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    protected function completeLogin($user, Request $request): void
    {
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        UserSession::recordLogin(
            $user->id,
            session()->getId(),
            $request->ip(),
            $request->userAgent()
        );
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('import.index');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'auth_source' => 'local',
        ]);

        Auth::login($user);

        return redirect()->route('import.index');
    }

    public function logout(Request $request)
    {
        UserSession::where('session_id', session()->getId())
            ->update(['is_current' => false]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
