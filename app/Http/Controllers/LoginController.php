<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/admin');
        }
        
        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended('/admin');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    // ────────────────────────────────────────────────────────────
    //  Forgot Password — Match username + email, then force-reset
    // ────────────────────────────────────────────────────────────
    public function showForgotPasswordForm()
    {
        if (Auth::check()) {
            return redirect('/admin');
        }

        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255'],
        ]);

        $user = User::where('username', $request->input('username'))
                    ->where('email', $request->input('email'))
                    ->first();

        if (! $user) {
            return back()->withErrors([
                'username' => 'No account matches the provided Username and Email Address.',
            ])->withInput();
        }

        $user->update([
            'password' => Hash::make('12345678'),
        ]);

        return redirect()->route('login')
            ->with('status', "Password has been reset to default: '12345678'. Please login and change it immediately.");
    }

    // ────────────────────────────────────────────────────────────
    //  Renew / Change Password (authenticated users only)
    // ────────────────────────────────────────────────────────────
    public function showRenewPasswordForm()
    {
        return view('auth.renew-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password'          => ['required', 'string'],
            'new_password'              => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        if (! Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        return back()->with('status', 'Password updated successfully!');
    }
}
