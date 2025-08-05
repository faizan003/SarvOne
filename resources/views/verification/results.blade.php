<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Verification Results - SarvOne</title>
    
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
                
                <div class="flex items-center">
                    <span class="text-sm text-gray-600">Verification Results</span>
                </div>
            </div>
        </div>
    </nav>

@php
    // Determine which step was just completed
    $step = isset($videoResults) ? 'video' : (isset($documentResults) ? 'document' : 'selfie');
@endphp

<!-- Main Content -->
<div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- Step Results: Most Recent -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
        <div class="p-8">
            <div class="text-center mb-8">
                @if($results['success'])
                    <div class="inline-flex items-center px-6 py-3 bg-green-100 text-green-800 rounded-full text-lg font-medium">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Verification Successful
                    </div>
                @else
                    <div class="inline-flex items-center px-6 py-3 bg-red-100 text-red-800 rounded-full text-lg font-medium">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Verification Failed
                    </div>
                @endif
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2 text-center">
                @if($step === 'selfie') Selfie Verification @elseif($step === 'document') Document Verification @else Live Video Verification @endif
            </h2>
            <div class="mb-8 text-center text-gray-500 text-sm">
                @if($step === 'selfie')
                    This is your selfie verification result.
                @elseif($step === 'document')
                    This is your document verification result.
                @else
                    This is your live video verification result.
                @endif
            </div>
            <!-- Trust Score, Quality, Liveness, etc. (as before) -->
            <div class="mb-8">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Trust Score</h3>
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-lg font-medium text-gray-700">Overall Trust Score</span>
                        <span class="text-3xl font-bold text-blue-600">{{ round(($results['trust_score'] ?? 0.85) * 100) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-gradient-to-r from-blue-500 to-indigo-500 h-3 rounded-full" style="width: {{ round(($results['trust_score'] ?? 0.85) * 100) }}%"></div>
                    </div>
                    <div class="mt-2 text-sm text-gray-600">
                        @if(round(($results['trust_score'] ?? 0.85) * 100) >= 80)
                            <span class="text-green-600">✓ High confidence level</span>
                        @elseif(round(($results['trust_score'] ?? 0.85) * 100) >= 60)
                            <span class="text-yellow-600">⚠ Medium confidence level</span>
                        @else
                            <span class="text-red-600">✗ Low confidence level</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-blue-50 rounded-xl p-6">
                    <div class="flex items-center mb-3">
                        <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        <h4 class="text-lg font-semibold text-blue-900">Quality Score</h4>
                    </div>
                    <div class="text-2xl font-bold text-blue-600 mb-2">{{ round(($results['quality_score'] ?? 0.90) * 100) }}%</div>
                    <p class="text-sm text-blue-700">Image clarity and face detection quality</p>
                </div>
                <div class="bg-green-50 rounded-xl p-6">
                    <div class="flex items-center mb-3">
                        <svg class="w-6 h-6 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <h4 class="text-lg font-semibold text-green-900">Liveness Score</h4>
                    </div>
                    <div class="text-2xl font-bold text-green-600 mb-2">{{ round(($results['liveness_score'] ?? 0.85) * 100) }}%</div>
                    <p class="text-sm text-green-700">Real person detection and anti-spoofing</p>
                </div>
            </div>
            @if(isset($results['face_match_score']))
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-purple-50 rounded-xl p-6">
                    <div class="flex items-center mb-3">
                        <svg class="w-6 h-6 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <h4 class="text-lg font-semibold text-purple-900">Face Match Score</h4>
                    </div>
                    <div class="text-2xl font-bold text-purple-600 mb-2">{{ round(($results['face_match_score'] ?? 0.85) * 100) }}%</div>
                    <p class="text-sm text-purple-700">Selfie to video face comparison</p>
                </div>
                <div class="bg-{{ $results['face_verified'] ? 'green' : 'red' }}-50 rounded-xl p-6">
                    <div class="flex items-center mb-3">
                        <svg class="w-6 h-6 text-{{ $results['face_verified'] ? 'green' : 'red' }}-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <h4 class="text-lg font-semibold text-{{ $results['face_verified'] ? 'green' : 'red' }}-900">Face Verification</h4>
                    </div>
                    <div class="text-2xl font-bold text-{{ $results['face_verified'] ? 'green' : 'red' }}-600 mb-2">
                        {{ $results['face_verified'] ? 'Verified' : 'Failed' }}
                    </div>
                    <p class="text-sm text-{{ $results['face_verified'] ? 'green' : 'red' }}-700">
                        {{ $results['face_verified'] ? 'Face matches successfully' : 'Face verification failed' }}
                    </p>
                </div>
            </div>
            @endif
            @if(isset($results['extracted_data']) && !empty($results['extracted_data']))
            <div class="mb-8">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Extracted Document Data</h3>
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($results['extracted_data'] as $key => $value)
                        <div class="bg-white rounded-lg p-4">
                            <h5 class="font-medium text-gray-900 mb-1 capitalize">{{ str_replace('_', ' ', $key) }}</h5>
                            <p class="text-sm text-gray-600">{{ $value }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
            @if(isset($results['analysis']) && !empty($results['analysis']))
            <div class="mb-8">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Detailed Analysis</h3>
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="space-y-4">
                        @if(isset($results['analysis']['quality_analysis']))
                        <div>
                            <h5 class="font-medium text-gray-900 mb-2">Quality Analysis</h5>
                            <div class="text-sm text-gray-600">
                                <p>Brightness: {{ $results['analysis']['quality_analysis']['brightness'] ?? 'N/A' }}</p>
                                <p>Contrast: {{ $results['analysis']['quality_analysis']['contrast'] ?? 'N/A' }}</p>
                                <p>Image Size: {{ $results['analysis']['quality_analysis']['image_size'] ?? 'N/A' }} pixels</p>
                            </div>
                        </div>
                        @endif
                        @if(isset($results['analysis']['liveness_analysis']))
                        <div>
                            <h5 class="font-medium text-gray-900 mb-2">Liveness Analysis</h5>
                            <div class="text-sm text-gray-600">
                                <p>Brightness: {{ $results['analysis']['liveness_analysis']['brightness'] ?? 'N/A' }}</p>
                                <p>Contrast: {{ $results['analysis']['liveness_analysis']['contrast'] ?? 'N/A' }}</p>
                                <p>Sharpness: {{ $results['analysis']['liveness_analysis']['sharpness'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
                <div class="flex">
                    <svg class="w-5 h-5 text-blue-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-blue-900">Analysis Complete</h4>
                        <p class="text-sm text-blue-700 mt-1">{{ $results['message'] ?? 'Verification analysis completed successfully.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Previous Steps Summary -->
    @if($step === 'document' && isset($selfieResults))
        <div class="bg-gray-50 rounded-2xl shadow mb-8 p-6">
            <h3 class="text-lg font-semibold mb-4">Previous Step: Selfie Verification</h3>
            <div class="flex flex-col md:flex-row gap-6">
                <div class="flex-1">
                    <div class="font-medium text-gray-700 mb-1">Trust Score</div>
                    <div class="text-2xl font-bold text-blue-600 mb-1">{{ round(($selfieResults['trust_score'] ?? 0.85) * 100) }}%</div>
                </div>
                <div class="flex-1">
                    <div class="font-medium text-gray-700 mb-1">Quality Score</div>
                    <div class="text-2xl font-bold text-blue-600 mb-1">{{ round(($selfieResults['quality_score'] ?? 0.90) * 100) }}%</div>
                </div>
                <div class="flex-1">
                    <div class="font-medium text-gray-700 mb-1">Liveness Score</div>
                    <div class="text-2xl font-bold text-green-600 mb-1">{{ round(($selfieResults['liveness_score'] ?? 0.85) * 100) }}%</div>
                </div>
            </div>
        </div>
    @elseif($step === 'video')
        @if(isset($documentResults))
        <div class="bg-gray-50 rounded-2xl shadow mb-8 p-6">
            <h3 class="text-lg font-semibold mb-4">Previous Step: Document Verification</h3>
            <div class="flex flex-col md:flex-row gap-6">
                <div class="flex-1">
                    <div class="font-medium text-gray-700 mb-1">Trust Score</div>
                    <div class="text-2xl font-bold text-purple-600 mb-1">{{ round(($documentResults['trust_score'] ?? 0.90) * 100) }}%</div>
                </div>
                <div class="flex-1">
                    <div class="font-medium text-gray-700 mb-1">Quality Score</div>
                    <div class="text-2xl font-bold text-blue-600 mb-1">{{ round(($documentResults['quality_score'] ?? 0.95) * 100) }}%</div>
                </div>
                <div class="flex-1">
                    <div class="font-medium text-gray-700 mb-1">Liveness Score</div>
                    <div class="text-2xl font-bold text-green-600 mb-1">{{ round(($documentResults['liveness_score'] ?? 0.85) * 100) }}%</div>
                </div>
            </div>
        </div>
        @endif
        @if(isset($selfieResults))
        <div class="bg-gray-50 rounded-2xl shadow mb-8 p-6">
            <h3 class="text-lg font-semibold mb-4">Previous Step: Selfie Verification</h3>
            <div class="flex flex-col md:flex-row gap-6">
                <div class="flex-1">
                    <div class="font-medium text-gray-700 mb-1">Trust Score</div>
                    <div class="text-2xl font-bold text-blue-600 mb-1">{{ round(($selfieResults['trust_score'] ?? 0.85) * 100) }}%</div>
                </div>
                <div class="flex-1">
                    <div class="font-medium text-gray-700 mb-1">Quality Score</div>
                    <div class="text-2xl font-bold text-blue-600 mb-1">{{ round(($selfieResults['quality_score'] ?? 0.90) * 100) }}%</div>
                </div>
                <div class="flex-1">
                    <div class="font-medium text-gray-700 mb-1">Liveness Score</div>
                    <div class="text-2xl font-bold text-green-600 mb-1">{{ round(($selfieResults['liveness_score'] ?? 0.85) * 100) }}%</div>
                </div>
            </div>
        </div>
        @endif
    @endif
    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        @if($results['success'])
            <a href="{{ route('dashboard') }}" class="flex-1">
                <button type="button" class="w-full bg-gradient-to-r from-green-500 to-teal-500 text-white font-medium py-4 px-8 rounded-xl hover:from-green-600 hover:to-teal-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transform transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]">
                    <svg class="inline h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                    Go to Dashboard
                </button>
            </a>
        @else
            <a href="{{ route('verification.selfie') }}" class="flex-1 bg-gradient-to-r from-red-500 to-pink-500 text-white font-medium py-4 px-8 rounded-xl hover:from-red-600 hover:to-pink-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transform transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] text-center">
                <svg class="inline h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Retry Verification
            </a>
        @endif
        <a href="{{ route('dashboard') }}" class="flex-1 border border-gray-300 text-gray-700 font-medium py-4 px-8 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 text-center">
            <svg class="inline h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Back to Dashboard
        </a>
    </div>
</div>
</body>
</html> 