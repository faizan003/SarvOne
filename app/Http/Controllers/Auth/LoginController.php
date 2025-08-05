<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function show(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string'],
        ]);

        // Normalize phone number
        $phone = preg_replace('/[^0-9+]/', '', $validated['phone']);
        
        // Ensure phone starts with +91
        if (!str_starts_with($phone, '+91')) {
            if (str_starts_with($phone, '91')) {
                $phone = '+' . $phone;
            } elseif (str_starts_with($phone, '0')) {
                $phone = '+91' . substr($phone, 1);
            } else {
                $phone = '+91' . $phone;
            }
        }

        // Find user by phone
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'phone' => 'No account found with this phone number.'
            ]);
        }

        // Log the user in
        Auth::login($user);

        // Redirect based on verification status
        if ($user->verification_status === 'verified') {
            return redirect()->intended('dashboard');
        } else {
            return redirect()->route('verification.start');
        }
    }

    /**
     * Log the user out
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
