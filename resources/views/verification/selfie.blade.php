<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Live Selfie - SarvOne</title>
    
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
                    <div class="text-sm text-gray-500">Step 1 of 2</div>
                    <div class="w-20 h-2 bg-gray-200 rounded-full">
                        <div class="w-1/2 h-2 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full"></div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        
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
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="mx-auto h-16 w-16 bg-gradient-to-r from-green-500 to-teal-500 rounded-2xl flex items-center justify-center mb-6">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Take a Live Selfie</h1>
            <p class="text-lg text-gray-600">
                We'll use this to verify your identity throughout the process
            </p>
        </div>

        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center">
                <div class="flex items-center text-blue-600">
                    <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-medium">1</div>
                    <span class="ml-2 text-sm font-medium text-blue-600">Live Selfie</span>
                </div>
                <div class="flex-1 mx-4 h-1 bg-gray-200 rounded"></div>
                <div class="flex items-center text-gray-400">
                    <div class="w-8 h-8 bg-gray-200 text-gray-400 rounded-full flex items-center justify-center text-sm font-medium">2</div>
                    <span class="ml-2 text-sm font-medium text-gray-400">Aadhaar Number</span>
                </div>
            </div>
        </div>

        <!-- Live Selfie Requirements -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-6">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Live Selfie Verification</h3>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                    <div class="flex items-start">
                        <svg class="h-5 w-5 text-yellow-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div>
                            <h4 class="text-sm font-medium text-yellow-800 mb-1">Important Notice</h4>
                            <p class="text-sm text-yellow-700">Only live selfie capture is allowed. Uploaded images will not be accepted for verification. Our AI system will verify that you are a real person before proceeding.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-blue-900 mb-2">Verification Process:</h4>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• <strong>Live Detection:</strong> AI will verify you are a real person</li>
                        <li>• <strong>Face Quality:</strong> Ensures clear, well-lit face capture</li>
                        <li>• <strong>Liveness Check:</strong> Confirms you are present and alert</li>
                        <li>• <strong>Security:</strong> Only verified users can proceed to Aadhaar step</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Mode Toggle -->
        <div class="mb-6">
            <div class="flex bg-gray-100 rounded-xl p-1">
                <button id="camera-mode-btn" class="flex-1 py-3 px-4 rounded-lg text-sm font-medium transition-all duration-200 bg-blue-600 text-white">
                    <svg class="inline h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Live Camera
                </button>
                <button id="upload-mode-btn" class="flex-1 py-3 px-4 rounded-lg text-sm font-medium transition-all duration-200 text-gray-600 hover:text-gray-900">
                    <svg class="inline h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Upload Photo
                </button>
            </div>
            <p class="text-center text-xs text-gray-500 mt-2">Choose your preferred method for selfie verification</p>
        </div>

        <!-- Camera/Upload Section -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Camera Container -->
            <div id="camera-section" class="relative">
                <div id="camera-container" class="aspect-square bg-gray-900 flex items-center justify-center relative">
                    <!-- Video Stream -->
                    <video id="camera-stream" class="w-full h-full object-cover" autoplay playsinline muted style="display: none;"></video>
                    
                    <!-- Camera Preview Overlay -->
                    <div id="camera-overlay" class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center text-white">
                            <svg class="mx-auto h-16 w-16 mb-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p class="text-lg font-medium">Click "Start Camera" to begin</p>
                        </div>
                    </div>

                    <!-- Face Detection Guide -->
                    <div id="face-guide" class="absolute inset-0 flex items-center justify-center pointer-events-none" style="display: none;">
                        <div class="w-64 h-80 border-4 border-white border-dashed rounded-3xl opacity-70"></div>
                    </div>

                    <!-- Capture Flash -->
                    <div id="capture-flash" class="absolute inset-0 bg-white opacity-0 pointer-events-none"></div>
                </div>

                <!-- Status Messages -->
                <div id="status-message" class="absolute top-4 left-4 right-4 text-center">
                    <div class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium" style="display: none;">
                        Position your face within the guide
                    </div>
                </div>
            </div>

            <!-- Upload Section -->
            <div id="upload-section" class="relative" style="display: none;">
                <div class="aspect-square bg-gray-50 flex items-center justify-center relative border-2 border-dashed border-gray-300">
                    <!-- Upload Preview -->
                    <div id="upload-preview" class="w-full h-full flex items-center justify-center" style="display: none;">
                        <img id="uploaded-image" class="w-full h-full object-cover" />
                    </div>
                    
                    <!-- Upload Placeholder -->
                    <div id="upload-placeholder" class="text-center text-gray-500">
                        <svg class="mx-auto h-16 w-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-lg font-medium">Choose a selfie photo</p>
                        <p class="text-sm text-gray-400 mt-2">PNG, JPG up to 5MB</p>
                        <input type="file" id="selfie-upload" accept="image/*" class="hidden">
                        <button id="upload-trigger" class="mt-4 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            Browse Files
                        </button>
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <div class="p-6">
                <div class="text-center space-y-4">
                    <!-- Camera Mode Controls -->
                    <div id="camera-controls">
                        <!-- Start Camera Button -->
                        <button id="start-camera" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium py-4 px-6 rounded-xl hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]">
                            <svg class="inline h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            Start Camera
                        </button>

                        <!-- Capture Button -->
                        <button id="capture-selfie" class="w-full bg-gradient-to-r from-green-500 to-teal-500 text-white font-medium py-4 px-6 rounded-xl hover:from-green-600 hover:to-teal-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transform transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]" style="display: none;">
                            <svg class="inline h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Capture Selfie
                        </button>
                    </div>

                    <!-- Upload Mode Controls -->
                    <div id="upload-controls" style="display: none;">
                        <!-- Verify Upload Button -->
                        <button id="verify-upload" class="w-full bg-gradient-to-r from-green-500 to-teal-500 text-white font-medium py-4 px-6 rounded-xl hover:from-green-600 hover:to-teal-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transform transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]" style="display: none;">
                            <svg class="inline h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Verify Photo
                        </button>

                        <!-- Change Photo Button -->
                        <button id="change-photo" class="w-full border border-gray-300 text-gray-700 font-medium py-4 px-6 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200" style="display: none;">
                            <svg class="inline h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Change Photo
                        </button>
                    </div>

                    <!-- Processing Button -->
                    <button id="processing" class="w-full bg-gray-400 text-white font-medium py-4 px-6 rounded-xl cursor-not-allowed" disabled style="display: none;">
                        <svg class="inline h-5 w-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Processing...
                    </button>

                    <!-- Retake Button -->
                    <button id="retake-selfie" class="w-full border border-gray-300 text-gray-700 font-medium py-4 px-6 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200" style="display: none;">
                        Retake Selfie
                    </button>
                </div>

                <!-- Instructions -->
                <div class="mt-6 text-center">
                    <div id="camera-instructions" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-blue-900 mb-2">Tips for a good live selfie:</h4>
                        <ul class="text-xs text-blue-800 space-y-1">
                            <li>• Look directly at the camera</li>
                            <li>• Ensure good lighting on your face</li>
                            <li>• Remove glasses if possible</li>
                            <li>• Keep your face within the guide</li>
                        </ul>
                    </div>
                    
                    <div id="upload-instructions" class="bg-green-50 border border-green-200 rounded-lg p-4" style="display: none;">
                        <h4 class="text-sm font-medium text-green-900 mb-2">Tips for a good photo upload:</h4>
                        <ul class="text-xs text-green-800 space-y-1">
                            <li>• Use a clear, recent photo of yourself</li>
                            <li>• Face should be clearly visible and well-lit</li>
                            <li>• No sunglasses or face masks</li>
                            <li>• JPG or PNG format, under 5MB</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Canvas for Capture -->
    <canvas id="capture-canvas" style="display: none;"></canvas>

    <script>
        let stream = null;
        const video = document.getElementById('camera-stream');
        const canvas = document.getElementById('capture-canvas');
        const ctx = canvas.getContext('2d');

        // Button elements
        const startCameraBtn = document.getElementById('start-camera');
        const captureSelfieBtn = document.getElementById('capture-selfie');
        const processingBtn = document.getElementById('processing');
        const retakeBtn = document.getElementById('retake-selfie');

        // UI elements
        const cameraOverlay = document.getElementById('camera-overlay');
        const faceGuide = document.getElementById('face-guide');
        const captureFlash = document.getElementById('capture-flash');
        const statusMessage = document.getElementById('status-message');

        // Mode toggle elements
        const cameraModeBtn = document.getElementById('camera-mode-btn');
        const uploadModeBtn = document.getElementById('upload-mode-btn');
        const cameraSection = document.getElementById('camera-section');
        const uploadSection = document.getElementById('upload-section');
        const cameraControls = document.getElementById('camera-controls');
        const uploadControls = document.getElementById('upload-controls');

        // Upload elements
        const uploadTrigger = document.getElementById('upload-trigger');
        const selfieUpload = document.getElementById('selfie-upload');
        const uploadPreview = document.getElementById('upload-preview');
        const uploadPlaceholder = document.getElementById('upload-placeholder');
        const uploadedImage = document.getElementById('uploaded-image');
        const verifyUpload = document.getElementById('verify-upload');
        const changePhoto = document.getElementById('change-photo');

        let currentMode = 'camera'; // 'camera' or 'upload'
        let uploadedImageData = null;

        // Check camera permissions on page load
        document.addEventListener('DOMContentLoaded', () => {
            checkHTTPSRequirement();
            checkCameraSupport();
            initializeModeToggle();
            initializeUpload();
        });

        // Initialize mode toggle functionality
        function initializeModeToggle() {
            cameraModeBtn.addEventListener('click', () => switchMode('camera'));
            uploadModeBtn.addEventListener('click', () => switchMode('upload'));
        }

        // Switch between camera and upload modes
        function switchMode(mode) {
            currentMode = mode;
            
            if (mode === 'camera') {
                // Switch to camera mode
                cameraModeBtn.classList.add('bg-blue-600', 'text-white');
                cameraModeBtn.classList.remove('text-gray-600');
                uploadModeBtn.classList.remove('bg-blue-600', 'text-white');
                uploadModeBtn.classList.add('text-gray-600');
                
                cameraSection.style.display = 'block';
                uploadSection.style.display = 'none';
                cameraControls.style.display = 'block';
                uploadControls.style.display = 'none';
                
                // Toggle instructions
                document.getElementById('camera-instructions').style.display = 'block';
                document.getElementById('upload-instructions').style.display = 'none';
                
                // Reset upload state
                resetUploadState();
            } else {
                // Switch to upload mode
                uploadModeBtn.classList.add('bg-blue-600', 'text-white');
                uploadModeBtn.classList.remove('text-gray-600');
                cameraModeBtn.classList.remove('bg-blue-600', 'text-white');
                cameraModeBtn.classList.add('text-gray-600');
                
                cameraSection.style.display = 'none';
                uploadSection.style.display = 'block';
                cameraControls.style.display = 'none';
                uploadControls.style.display = 'block';
                
                // Toggle instructions
                document.getElementById('camera-instructions').style.display = 'none';
                document.getElementById('upload-instructions').style.display = 'block';
                
                // Stop camera if running
                if (stream) {
                    stopCamera();
                }
            }
        }

        // Initialize upload functionality
        function initializeUpload() {
            uploadTrigger.addEventListener('click', () => selfieUpload.click());
            
            selfieUpload.addEventListener('change', handleFileUpload);
            
            verifyUpload.addEventListener('click', processUploadedImage);
            
            changePhoto.addEventListener('click', () => {
                resetUploadState();
                selfieUpload.click();
            });
        }

        // Handle file upload
        function handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Validate file type
            if (!file.type.startsWith('image/')) {
                showError('Invalid file type', 'Please select an image file (JPG, PNG, etc.)');
                return;
            }

            // Validate file size (5MB limit)
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (file.size > maxSize) {
                showError('File too large', 'Please select an image smaller than 5MB');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                uploadedImageData = e.target.result;
                showUploadPreview(uploadedImageData);
            };
            reader.readAsDataURL(file);
        }

        // Show upload preview
        function showUploadPreview(imageData) {
            uploadedImage.src = imageData;
            uploadPlaceholder.style.display = 'none';
            uploadPreview.style.display = 'block';
            verifyUpload.style.display = 'block';
            changePhoto.style.display = 'block';
        }

        // Reset upload state
        function resetUploadState() {
            uploadedImageData = null;
            uploadPlaceholder.style.display = 'block';
            uploadPreview.style.display = 'none';
            verifyUpload.style.display = 'none';
            changePhoto.style.display = 'none';
            selfieUpload.value = '';
        }

        // Process uploaded image (same as camera capture)
        function processUploadedImage() {
            if (!uploadedImageData) {
                showError('No image selected', 'Please select an image first');
                return;
            }

            processSelfie(uploadedImageData);
        }

        // Check if HTTPS is required for camera access
        function checkHTTPSRequirement() {
            const isHTTPS = location.protocol === 'https:';
            const isLocalhost = location.hostname === 'localhost' || location.hostname === '127.0.0.1';
            const isLocalNetwork = location.hostname.startsWith('192.168.') || location.hostname.startsWith('10.') || location.hostname.startsWith('172.');
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            
            if (!isHTTPS && !isLocalhost && !isLocalNetwork && isMobile) {
                showHTTPSWarning();
            }
        }

        // Show HTTPS warning for mobile
        function showHTTPSWarning() {
            const warningDiv = document.createElement('div');
            warningDiv.className = 'bg-amber-100 border border-amber-400 text-amber-800 px-4 py-3 rounded-lg mb-4';
            warningDiv.innerHTML = `
                <div class="flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <p class="font-medium">Camera may require HTTPS</p>
                        <p class="text-sm">Some mobile browsers require HTTPS for camera access. If camera doesn't work, try using ngrok for HTTPS.</p>
                    </div>
                </div>
            `;
            
            const container = document.querySelector('.max-w-2xl');
            container.insertBefore(warningDiv, container.firstChild);
        }

        // Check if camera is supported and permissions
        async function checkCameraSupport() {
            // More lenient camera support detection for mobile
            if (!navigator.mediaDevices) {
                showError('Camera not supported', 'Camera access is not available. Please use HTTPS or a modern browser.');
                return;
            }

            if (!navigator.mediaDevices.getUserMedia) {
                // Try older getUserMedia API for compatibility
                navigator.mediaDevices.getUserMedia = navigator.mediaDevices.getUserMedia ||
                    navigator.webkitGetUserMedia ||
                    navigator.mozGetUserMedia ||
                    navigator.msGetUserMedia;
                
                if (!navigator.mediaDevices.getUserMedia) {
                    showError('Camera not supported', 'Please use Chrome, Firefox, Safari, or Edge browser.');
                    return;
                }
            }

            try {
                // Check permissions without requesting access (only if API is available)
                if (navigator.permissions && navigator.permissions.query) {
                    const permissions = await navigator.permissions.query({ name: 'camera' });
                    updatePermissionStatus(permissions.state);
                    
                    permissions.addEventListener('change', () => {
                        updatePermissionStatus(permissions.state);
                    });
                } else {
                    console.log('Permissions API not supported - will check on camera request');
                }
            } catch (error) {
                console.log('Permissions API not supported or failed:', error);
                // Don't show error - just continue without permission pre-check
            }
        }

        // Update UI based on permission status
        function updatePermissionStatus(state) {
            const permissionInfo = document.getElementById('permission-info');
            
            switch (state) {
                case 'denied':
                    showPermissionDeniedHelp();
                    break;
                case 'granted':
                    // Permission already granted, can start camera directly
                    break;
                case 'prompt':
                    // Permission will be requested when camera is accessed
                    break;
            }
        }

        // Show permission denied help
        function showPermissionDeniedHelp() {
            cameraOverlay.innerHTML = `
                <div class="text-center text-white p-6">
                    <svg class="mx-auto h-16 w-16 mb-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <h3 class="text-lg font-medium mb-4">Camera Permission Denied</h3>
                    <div class="text-sm space-y-2 max-w-sm mx-auto">
                        <p>To continue, please enable camera access:</p>
                        <div class="bg-black/20 rounded-lg p-3 text-left">
                            <p class="font-medium mb-2">Desktop:</p>
                            <ul class="text-xs space-y-1">
                                <li>• Click the camera icon in your browser's address bar</li>
                                <li>• Select "Allow" for camera access</li>
                                <li>• Refresh this page</li>
                            </ul>
                            <p class="font-medium mb-2 mt-3">Mobile:</p>
                            <ul class="text-xs space-y-1">
                                <li>• Go to browser settings → Site permissions</li>
                                <li>• Find this website and enable camera</li>
                                <li>• Refresh this page</li>
                            </ul>
                        </div>
                    </div>
                </div>
            `;
            startCameraBtn.innerHTML = '<svg class="inline h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>Retry Camera Access';
        }

        // Show error message
        function showError(title, message) {
            cameraOverlay.innerHTML = `
                <div class="text-center text-white p-6">
                    <svg class="mx-auto h-16 w-16 mb-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-lg font-medium mb-2">${title}</h3>
                    <p class="text-sm opacity-90">${message}</p>
                </div>
            `;
            startCameraBtn.disabled = true;
            startCameraBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
            startCameraBtn.classList.remove('bg-gradient-to-r', 'from-blue-600', 'to-indigo-600', 'hover:from-blue-700', 'hover:to-indigo-700');
        }

        // Start Camera with enhanced error handling
        startCameraBtn.addEventListener('click', async () => {
            startCameraBtn.disabled = true;
            startCameraBtn.innerHTML = '<svg class="inline h-5 w-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>Starting Camera...';

            try {
                // Request camera access with specific constraints
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        width: { ideal: 1280, min: 640 },
                        height: { ideal: 720, min: 480 },
                        facingMode: 'user'
                    },
                    audio: false
                });
                
                video.srcObject = stream;
                video.style.display = 'block';
                cameraOverlay.style.display = 'none';
                faceGuide.style.display = 'flex';
                
                startCameraBtn.style.display = 'none';
                captureSelfieBtn.style.display = 'block';

                // Show status message
                statusMessage.querySelector('div').style.display = 'block';
                
            } catch (error) {
                startCameraBtn.disabled = false;
                console.error('Camera error:', error);
                
                let errorTitle = 'Camera Access Failed';
                let errorMessage = '';
                
                switch (error.name) {
                    case 'NotAllowedError':
                    case 'PermissionDeniedError':
                        showPermissionDeniedHelp();
                        return;
                    
                    case 'NotFoundError':
                    case 'DevicesNotFoundError':
                        errorTitle = 'No Camera Found';
                        errorMessage = 'No camera device found on your device. Please connect a camera and try again.';
                        break;
                    
                    case 'NotReadableError':
                    case 'TrackStartError':
                        errorTitle = 'Camera Busy';
                        errorMessage = 'Camera is being used by another application. Please close other apps using the camera and try again.';
                        break;
                    
                    case 'OverconstrainedError':
                    case 'ConstraintNotSatisfiedError':
                        errorTitle = 'Camera Not Compatible';
                        errorMessage = 'Your camera does not meet the requirements. Please try with a different device.';
                        break;
                    
                    case 'NotSupportedError':
                        errorTitle = 'Browser Not Supported';
                        errorMessage = 'Your browser does not support camera access. Please use Chrome, Firefox, Safari, or Edge.';
                        break;
                    
                    case 'AbortError':
                        errorTitle = 'Camera Access Interrupted';
                        errorMessage = 'Camera access was interrupted. Please try again.';
                        break;
                    
                    default:
                        errorTitle = 'Camera Error';
                        errorMessage = 'An unexpected error occurred while accessing the camera. Please try again or contact support.';
                }
                
                showError(errorTitle, errorMessage);
                startCameraBtn.innerHTML = '<svg class="inline h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>Retry Camera Access';
            }
        });

        // Capture Selfie
        captureSelfieBtn.addEventListener('click', () => {
            // Flash effect
            captureFlash.style.opacity = '0.8';
            setTimeout(() => {
                captureFlash.style.opacity = '0';
            }, 150);

            // Capture image
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0);
            
            const imageData = canvas.toDataURL('image/jpeg', 0.8);
            
            // Send to API
            processSelfie(imageData);
        });

        // Retake Selfie
        retakeBtn.addEventListener('click', () => {
            captureSelfieBtn.style.display = 'block';
            retakeBtn.style.display = 'none';
            processingBtn.style.display = 'none';
        });

        // Process Selfie via API with Enhanced Verification
        async function processSelfie(imageData) {
            console.log('🚀 Starting selfie processing...');
            console.log('📸 Image data length:', imageData.length);
            
            // Show processing state
            captureSelfieBtn.style.display = 'none';
            processingBtn.style.display = 'block';

            try {
                // Step 1: Get auth token from FastAPI
                console.log('🔑 Step 1: Getting auth token from FastAPI...');
                const tokenResponse = await fetch('http://localhost:8001/auth/token', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                if (!tokenResponse.ok) {
                    throw new Error(`Token request failed: ${tokenResponse.status}`);
                }
                
                const tokenData = await tokenResponse.json();
                console.log('✅ Auth token received:', tokenData.access_token ? 'Token present' : 'No token');
                
                // Step 2: Convert base64 to blob for file upload
                console.log('🔄 Step 2: Converting image data...');
                const base64Data = imageData.split(',')[1]; // Remove data:image/jpeg;base64, prefix
                const byteCharacters = atob(base64Data);
                const byteNumbers = new Array(byteCharacters.length);
                for (let i = 0; i < byteCharacters.length; i++) {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                const byteArray = new Uint8Array(byteNumbers);
                const blob = new Blob([byteArray], { type: 'image/jpeg' });
                console.log('✅ Image converted to blob, size:', blob.size, 'bytes');
                
                // Step 3: Create FormData for file upload
                console.log('📁 Step 3: Creating FormData...');
                const formData = new FormData();
                formData.append('image', blob, 'selfie.jpg');
                console.log('✅ FormData created with image file');
                
                // Step 4: Send to FastAPI for analysis
                console.log('🚀 Step 4: Sending to FastAPI for analysis...');
                const analysisResponse = await fetch('http://localhost:8001/analyze-face-quality', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${tokenData.access_token}`
                    },
                    body: formData
                });
                
                console.log('📊 Analysis response status:', analysisResponse.status);
                
                if (!analysisResponse.ok) {
                    const errorText = await analysisResponse.text();
                    console.error('❌ Analysis failed:', errorText);
                    throw new Error(`Analysis failed: ${analysisResponse.status} - ${errorText}`);
                }
                
                const analysisResult = await analysisResponse.json();
                console.log('✅ Analysis completed:', analysisResult);
                
                // Step 5: Send results to Laravel for storage
                console.log('💾 Step 5: Sending results to Laravel...');
                const laravelResponse = await fetch('{{ route("verification.selfie.process") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        selfie: imageData,
                        fastapi_result: analysisResult
                    })
                });
                
                console.log('📊 Laravel response status:', laravelResponse.status);
                const laravelResult = await laravelResponse.json();
                console.log('✅ Laravel processing completed:', laravelResult);

                if (laravelResult.success) {
                    console.log('🎉 SUCCESS: Verification completed!');
                    // Show success message before redirect
                    const successMessage = document.createElement('div');
                    successMessage.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                    successMessage.innerHTML = `
                        <div class="flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Verification successful! Redirecting to Aadhaar step...</span>
                        </div>
                    `;
                    document.body.appendChild(successMessage);
                    
                    // Redirect to next step after a short delay
                    setTimeout(() => {
                        window.location.href = laravelResult.next_step;
                    }, 2000);
                } else {
                    console.error('❌ Laravel processing failed:', laravelResult.message);
                    // Show detailed error message
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                    errorMessage.innerHTML = `
                        <div class="flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span>${laravelResult.message}</span>
                        </div>
                    `;
                    document.body.appendChild(errorMessage);
                    
                    // Remove error message after 5 seconds
                    setTimeout(() => {
                        errorMessage.remove();
                    }, 5000);
                    
                    processingBtn.style.display = 'none';
                    retakeBtn.style.display = 'block';
                }

            } catch (error) {
                console.error('💥 ERROR in selfie processing:', error);
                
                const errorMessage = document.createElement('div');
                errorMessage.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                errorMessage.innerHTML = `
                    <div class="flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span>Error: ${error.message}</span>
                    </div>
                `;
                document.body.appendChild(errorMessage);
                
                setTimeout(() => {
                    errorMessage.remove();
                }, 5000);
                
                processingBtn.style.display = 'none';
                retakeBtn.style.display = 'block';
            }
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        });
    </script>
</body>
</html> 