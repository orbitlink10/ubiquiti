<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials)) {
            return back()
                ->withErrors(['email' => 'Invalid login credentials.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        if ($request->user()?->status !== 'active') {
            Auth::logout();
            return back()
                ->withErrors(['email' => 'Your account is suspended.'])
                ->onlyInput('email');
        }

        return redirect()
            ->intended($this->loginRedirectPath((string) $request->user()?->role))
            ->with('success', 'You are now logged in.');
    }

    public function showRegister(): View
    {
        return view('auth.register', [
            'pageTitle' => 'Create Account',
            'submitLabel' => 'Register',
            'formAction' => route('register.submit'),
            'loginPrompt' => 'Already registered?',
            'loginLinkLabel' => 'Login',
            'secondaryPrompt' => 'Need an admin account?',
            'secondaryLinkLabel' => 'Register as Admin',
            'secondaryLinkUrl' => route('admin.register'),
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        return $this->registerUser($request, 'customer');
    }

    public function showAdminRegister(): View
    {
        return view('auth.register', [
            'pageTitle' => 'Admin Registration',
            'submitLabel' => 'Create Admin Account',
            'formAction' => route('admin.register.submit'),
            'loginPrompt' => 'Already have admin access?',
            'loginLinkLabel' => 'Login',
            'secondaryPrompt' => 'Need a customer account instead?',
            'secondaryLinkLabel' => 'Create one',
            'secondaryLinkUrl' => route('register'),
        ]);
    }

    public function registerAdmin(Request $request): RedirectResponse
    {
        return $this->registerUser($request, 'admin');
    }

    private function registerUser(Request $request, string $role): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => $role,
            'status' => 'active',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->intended($this->loginRedirectPath($role))
            ->with('success', 'Account created successfully.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have logged out.');
    }

    private function loginRedirectPath(string $role): string
    {
        return match ($role) {
            'admin' => route('admin.dashboard'),
            'vendor' => route('vendor.dashboard'),
            default => route('home'),
        };
    }
}
