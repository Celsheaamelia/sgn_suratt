<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {

            $request->session()->regenerate();

            return $this->redirectByRole();
        }

        return back()->withErrors([
            'username' => 'Username atau Password salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function index()
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }

        return view('login');
    }

    private function redirectByRole()
    {
        $user = Auth::user();

        return match ($user->role) {
            'satpam'     => redirect()->route('patroli.index'),
            'supervisor' => redirect()->route('patroli.monitoring.index'),
            default      => redirect()->route('dashboard'),
        };
    }
}
