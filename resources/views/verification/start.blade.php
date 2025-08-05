<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Start Verification - SarvOne</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <div class="h-8 w-8 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center mr-3">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-gray-900">SarvOne</span>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900 transition-colors duration-200">
                        Dashboard
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-600 hover:text-gray-900 transition-colors duration-200">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <div class="mx-auto h-20 w-20 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center mb-6">
                <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Let's Verify Your Identity</h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Follow these simple steps to create your blockchain-verified digital identity with AI-powered trust scoring
            </p>
        </div>

        <!-- Progress Steps -->
        <div class="mb-12">
            <div class="flex items-center justify-between">
                <div class="flex items-center text-blue-600">
                    <div class="flex items-center justify-center w-10 h-10 bg-blue-600 text-white rounded-full">
                        <span class="text-sm font-medium">1</span>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-blue-600">Live Selfie</p>
                        <p class="text-xs text-gray-500">AI Verification</p>
                    </div>
                </div>
                
                <div class="flex-1 mx-4 h-1 bg-gray-200 rounded"></div>
                
                <div class="flex items-center text-gray-400">
                    <div class="flex items-center justify-center w-10 h-10 bg-gray-200 text-gray-400 rounded-full">
                        <span class="text-sm font-medium">2</span>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium">Aadhaar Number</p>
                        <p class="text-xs text-gray-500">Identity Details</p>
                    </div>
                </div>
                
                <div class="flex-1 mx-4 h-1 bg-gray-200 rounded"></div>
                
                <div class="flex items-center text-gray-400">
                    <div class="flex items-center justify-center w-10 h-10 bg-gray-200 text-gray-400 rounded-full">
                        <span class="text-sm font-medium">3</span>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium">OTP Verification</p>
                        <p class="text-xs text-gray-500">Mobile Confirm</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Verification Steps -->
        <div class="grid md:grid-cols-3 gap-6 mb-12">
            <!-- Step 1: Live Selfie -->
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <div class="text-center mb-6">
                    <div class="mx-auto h-16 w-16 bg-blue-100 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Live Selfie Capture</h3>
                    <p class="text-gray-600 text-sm mt-2">AI-powered face verification & liveness detection</p>
                </div>
                
                <div class="space-y-3">
                    <div class="flex items-start">
                        <svg class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Liveness Detection</p>
                            <p class="text-xs text-gray-500">Confirms real person</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <svg class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Face Quality Check</p>
                            <p class="text-xs text-gray-500">Ensures clear image</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <svg class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Anti-Spoofing</p>
                            <p class="text-xs text-gray-500">Prevents fake attempts</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Aadhaar Details -->
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <div class="text-center mb-6">
                    <div class="mx-auto h-16 w-16 bg-green-100 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Aadhaar Number</h3>
                    <p class="text-gray-600 text-sm mt-2">Enter your identity details for verification</p>
                </div>
                
                <div class="space-y-3">
                    <div class="flex items-start">
                        <svg class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">12-Digit Number</p>
                            <p class="text-xs text-gray-500">Official Aadhaar ID</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <svg class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Name Verification</p>
                            <p class="text-xs text-gray-500">Match registered name</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <svg class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Demo Mode</p>
                            <p class="text-xs text-gray-500">Simulation for testing</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: OTP Verification -->
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <div class="text-center mb-6">
                    <div class="mx-auto h-16 w-16 bg-purple-100 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">OTP Verification</h3>
                    <p class="text-gray-600 text-sm mt-2">Confirm your mobile number with OTP</p>
                </div>
                
                <div class="space-y-3">
                    <div class="flex items-start">
                        <svg class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">SMS OTP</p>
                            <p class="text-xs text-gray-500">6-digit secure code</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <svg class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Twilio Integration</p>
                            <p class="text-xs text-gray-500">Reliable delivery</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <svg class="h-4 w-4 text-green-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">5 Min Expiry</p>
                            <p class="text-xs text-gray-500">Time-limited security</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Important Notes -->
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 mb-8">
            <div class="flex items-start">
                <svg class="h-6 w-6 text-amber-600 mt-1 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h4 class="text-lg font-medium text-amber-900 mb-2">Important Notes</h4>
                    <ul class="text-sm text-amber-800 space-y-1 list-disc list-inside">
                        <li>Ensure good lighting for selfie capture</li>
                        <li>Use the same mobile number registered with your Aadhaar</li>
                        <li>Keep your phone nearby for OTP verification</li>
                        <li>The entire process takes about 2-3 minutes to complete</li>
                        <li>Your data is encrypted and stored securely on blockchain</li>
                        <li>Demo mode is currently active for testing purposes</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Ready to Start -->
        <div class="text-center">
            <h3 class="text-2xl font-bold text-gray-900 mb-4">Ready to Start?</h3>
            <p class="text-gray-600 mb-8">The verification process will begin with taking a live selfie for identity verification</p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('verification.selfie') }}" class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium py-4 px-8 rounded-xl hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]">
                    Start Verification Process
                </a>
                
                <a href="{{ route('dashboard') }}" class="border border-gray-300 text-gray-700 font-medium py-4 px-8 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html> 