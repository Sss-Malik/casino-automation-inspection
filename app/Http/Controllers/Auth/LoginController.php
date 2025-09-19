<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string'],
        ]);

        // Only allow super-admin@admin.com
        $user = User::where('email', $data['email'])->first();
        if (
            ! $user
            || $user->email !== 'admin@socialslots.games'
            || ! Hash::check($data['password'], $user->password)
        ) {
            return back()->withErrors([
                'email' => 'These credentials do not match our records.'
            ])->onlyInput('email');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $token = $user->createToken('admin-login')->plainTextToken;

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->tokens()->delete();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
