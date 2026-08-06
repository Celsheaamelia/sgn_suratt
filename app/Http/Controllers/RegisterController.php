<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class RegisterController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'username'              => 'required|string|max:255|unique:users,username',
            'email'                 => 'required|email|max:255|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
            // field password_confirmation otomatis dicek Laravel karena rule 'confirmed'
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    // Redirect ke Google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Callback dari Google
    public function handleGoogleCallback(Request $request)
    {
        try {
            /** @var \Laravel\Socialite\Two\GoogleProvider $driver */
            $driver = Socialite::driver('google');
            $googleUser = $driver->stateless()->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors([
                'username' => 'Login dengan Google gagal, coba lagi.',
            ]);
        }

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
        } else {
            $user = User::create([
                'username'  => Str::slug($googleUser->getName()) . '_' . Str::random(4),
                'email'     => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password'  => Hash::make(Str::random(24)),
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
