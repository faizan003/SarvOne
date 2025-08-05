<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Register - SarvOne</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center mb-6">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Join SarvOne</h2>
                <p class="text-sm text-gray-600 max-w-sm mx-auto">
                    Create your digital identity with blockchain-verified documents and AI-powered trust scoring
                </p>
            </div>

            <!-- Registration Form -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf

                    <!-- Display general errors -->
                    @if ($errors->has('registration'))
                        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
                            {{ $errors->first('registration') }}
                        </div>
                    @endif

                    <!-- Display phone number conflict error with suggestion -->
                    @if ($errors->has('phone') && str_contains($errors->first('phone'), 'already registered'))
                        <div class="bg-orange-50 border border-orange-200 text-orange-800 px-4 py-3 rounded-lg text-sm">
                            <div class="flex items-start">
                                <svg class="h-5 w-5 text-orange-400 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                                <div>
                                    <p class="font-medium">{{ $errors->first('phone') }}</p>
                                    <p class="text-xs mt-1">Already have an account? <a href="{{ route('login') }}" class="underline font-medium">Sign in here</a></p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Name Field -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            value="{{ old('name') }}"
                            required
                            autocomplete="name"
                            placeholder="Enter your full name"
                            class="block w-full px-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 @error('name') border-red-300 focus:ring-red-500 @enderror"
                        />
                        @error('name')
                            <div class="mt-1 flex items-center">
                                <svg class="h-4 w-4 text-red-500 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            </div>
                        @enderror
                    </div>

                    <!-- Phone Field -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Mobile Number <span class="text-red-500">*</span>
                            <span class="text-xs text-blue-600 font-normal">(Same as registered with Aadhaar)</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 text-sm">🇮🇳 +91</span>
                            </div>
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone" 
                                value="{{ old('phone') }}"
                                required
                                autocomplete="tel"
                                placeholder="9876543210"
                                maxlength="15"
                                pattern="[6-9][0-9]{9}"
                                class="block w-full pl-20 pr-4 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200 @error('phone') border-red-300 focus:ring-red-500 @enderror"
                            />
                        </div>
                        @error('phone')
                            <div class="mt-1 flex items-center">
                                <svg class="h-4 w-4 text-red-500 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            </div>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            Use the same mobile number registered with your Aadhaar card. We'll send an OTP for verification.
                        </p>
                    </div>



                    <!-- Info Box -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="h-5 w-5 text-blue-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-sm text-blue-800">
                                <p class="font-medium mb-1">What happens next?</p>
                                <ul class="text-xs space-y-1 list-disc list-inside ml-2">
                                    <li>Verify your mobile number with OTP</li>
                                    <li>Record a live video for face matching</li>
                                    <li>Add your Aadhaar number for credential issuance</li>
                                    <li>Get your trust score and DID</li>
                                    <li>Start using your verified digital identity</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium py-3 px-4 rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]"
                    >
                        Create Account & Start Verification
                    </button>

                    <!-- Login Link -->
                    <div class="text-center pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-600">
                            Already have an account? 
                            <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500 transition-colors duration-200">
                                Sign in here
                            </a>
                        </p>
                    </div>
                </form>
            </div>

            <!-- Features -->
            <div class="grid grid-cols-3 gap-4 mt-8">
                <div class="text-center">
                    <div class="bg-white rounded-xl p-3 shadow-md mx-auto w-12 h-12 flex items-center justify-center mb-2">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-xs text-gray-600 font-medium">AI Verified</p>
                </div>
                <div class="text-center">
                    <div class="bg-white rounded-xl p-3 shadow-md mx-auto w-12 h-12 flex items-center justify-center mb-2">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                    </div>
                    <p class="text-xs text-gray-600 font-medium">Blockchain</p>
                </div>
                <div class="text-center">
                    <div class="bg-white rounded-xl p-3 shadow-md mx-auto w-12 h-12 flex items-center justify-center mb-2">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <p class="text-xs text-gray-600 font-medium">Secure</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Phone number and Aadhaar validation script -->
    <script>
        const phoneInput = document.getElementById('phone');

        
        // Phone number formatting and validation
        phoneInput.addEventListener('input', function(e) {
            // Remove all non-digits
            let value = e.target.value.replace(/\D/g, '');
            
            // Limit to 10 digits
            if (value.length > 10) {
                value = value.slice(0, 10);
            }
            
            e.target.value = value;
            
            // Real-time validation feedback
            validatePhoneNumber(value);
        });

        // Prevent paste of invalid characters
        phoneInput.addEventListener('paste', function(e) {
            e.preventDefault();
            let paste = (e.clipboardData || window.clipboardData).getData('text');
            let digits = paste.replace(/\D/g, '').slice(0, 10);
            e.target.value = digits;
            validatePhoneNumber(digits);
        });

        function validatePhoneNumber(value) {
            const input = phoneInput;
            const isValid = /^[6-9]\d{9}$/.test(value);
            
            // Update input styling
            if (value.length === 0) {
                input.classList.remove('border-red-300', 'border-green-300');
                input.classList.add('border-gray-300');
            } else if (isValid) {
                input.classList.remove('border-red-300', 'border-gray-300');
                input.classList.add('border-green-300');
            } else {
                input.classList.remove('border-green-300', 'border-gray-300');
                input.classList.add('border-red-300');
            }
        }



        // Form submission validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const phone = phoneInput.value;
            const name = document.getElementById('name').value.trim();
            
            let hasError = false;
            
            // Validate name
            if (name.length < 2) {
                hasError = true;
            }
            
            // Validate phone
            if (!/^[6-9]\d{9}$/.test(phone)) {
                hasError = true;
            }
            
            if (hasError) {
                e.preventDefault();
                // Show error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm mt-4';
                errorDiv.innerHTML = 'Please fix the errors above before submitting.';
                
                const existingError = document.querySelector('.bg-red-50');
                if (existingError) {
                    existingError.remove();
                }
                
                document.querySelector('form').insertBefore(errorDiv, document.querySelector('button[type="submit"]'));
            }
        });
    </script>
</body>
</html> 