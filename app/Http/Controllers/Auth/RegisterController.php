<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    /**
     * Show the registration form
     */
    public function show(): View
    {
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            // Normalize phone number first (remove spaces, dashes, etc.)
            $rawPhone = $request->phone;
            $phone = preg_replace('/[^0-9+]/', '', $rawPhone);
            
            // Basic Indian phone number validation
            if (!preg_match('/^(\+91|91|0)?[6-9]\d{9}$/', $phone)) {
                throw ValidationException::withMessages([
                    'phone' => 'Please enter a valid Indian mobile number (10 digits starting with 6-9).'
                ]);
            }

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

            // Check if phone number already exists
            $existingUser = User::where('phone', $phone)->first();
            if ($existingUser) {
                $maskedPhone = substr($phone, 0, 7) . '***' . substr($phone, -3);
                throw ValidationException::withMessages([
                    'phone' => "This mobile number {$maskedPhone} is already registered. Please use a different number or try signing in."
                ]);
            }

            // Validate other fields
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255', 'min:2'],
            ]);

            // Create the user
            $user = User::create([
                'name' => $validated['name'],
                'phone' => $phone,
                'verification_status' => 'pending',
            ]);

            // Generate and save DID
            $user->did = $user->generateDID();
            $user->save();

            // Log the user in
            Auth::login($user);

            // Redirect to verification process
            return redirect()->route('verification.selfie')->with([
                'success' => 'Registration successful! Now let\'s verify your identity with OTP and then add your Aadhaar number.'
            ]);

        } catch (ValidationException $e) {
            // Re-throw validation exceptions to preserve the error messages
            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations
            if (str_contains($e->getMessage(), 'users_phone_unique')) {
                $maskedPhone = substr($phone, 0, 7) . '***' . substr($phone, -3);
                throw ValidationException::withMessages([
                    'phone' => "This mobile number {$maskedPhone} is already registered. Please use a different number or try signing in."
                ]);
            }
            
            return back()->withErrors([
                'registration' => 'Registration failed due to a database error. Please try again.'
            ])->withInput();
        } catch (\Exception $e) {
            return back()->withErrors([
                'registration' => 'Registration failed. Please try again. Error: ' . $e->getMessage()
            ])->withInput();
        }
    }
}
