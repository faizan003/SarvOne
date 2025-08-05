<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Live Video Verification - SarvOne</title>
    
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
                    <span class="text-sm text-gray-600">Step 2 of 3</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="mx-auto h-16 w-16 bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center mb-6">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Live Video Verification</h1>
            <p class="text-lg text-gray-600">
                Record a short video to complete your identity verification
            </p>
        </div>

        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center">
                <div class="flex items-center text-green-600">
                    <div class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-medium">✓</div>
                    <span class="ml-2 text-sm font-medium text-green-600">Live Selfie</span>
                </div>
                <div class="flex-1 mx-4 h-1 bg-blue-200 rounded"></div>
                <div class="flex items-center text-blue-600">
                    <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-medium">2</div>
                    <span class="ml-2 text-sm font-medium text-blue-600">Live Video</span>
                </div>
                <div class="flex-1 mx-4 h-1 bg-gray-200 rounded"></div>
                <div class="flex items-center text-gray-400">
                    <div class="w-8 h-8 bg-gray-200 text-gray-400 rounded-full flex items-center justify-center text-sm font-medium">3</div>
                    <span class="ml-2 text-sm font-medium text-gray-400">Upload Aadhaar</span>
                </div>
            </div>
        </div>

        <!-- Video Section -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Video Container -->
            <div class="relative">
                <div id="video-container" class="aspect-video bg-gray-900 flex items-center justify-center relative">
                    <!-- Video Stream -->
                    <video id="video-stream" class="w-full h-full object-cover" autoplay playsinline muted style="display: none;"></video>
                    
                    <!-- Video Overlay -->
                    <div id="video-overlay" class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center text-white">
                            <svg class="mx-auto h-16 w-16 mb-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-lg font-medium">Click "Start Camera" to begin</p>
                        </div>
                    </div>

                    <!-- Recording Indicator -->
                    <div id="recording-indicator" class="absolute top-4 left-4 bg-red-600 text-white px-3 py-1 rounded-full text-sm font-medium flex items-center" style="display: none;">
                        <div class="w-2 h-2 bg-white rounded-full mr-2 animate-pulse"></div>
                        Recording
                    </div>

                    <!-- Timer -->
                    <div id="timer" class="absolute top-4 right-4 bg-black/50 text-white px-3 py-1 rounded-lg text-sm font-medium" style="display: none;">
                        0:00
                    </div>

                    <!-- Instructions Overlay -->
                    <div id="instructions-overlay" class="absolute bottom-4 left-4 right-4 bg-black/70 text-white p-4 rounded-lg text-sm" style="display: none;">
                        <p id="current-instruction" class="text-center font-medium"></p>
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <div class="p-6">
                <div class="text-center space-y-4">
                    <!-- Start Camera Button -->
                    <button id="start-camera" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium py-4 px-6 rounded-xl hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]">
                        <svg class="inline h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Start Camera
                    </button>

                    <!-- Start Recording Button -->
                    <button id="start-recording" class="w-full bg-gradient-to-r from-red-500 to-pink-500 text-white font-medium py-4 px-6 rounded-xl hover:from-red-600 hover:to-pink-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transform transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]" style="display: none;">
                        <svg class="inline h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Start Recording (5 seconds)
                    </button>

                    <!-- Stop Recording Button -->
                    <button id="stop-recording" class="w-full bg-gradient-to-r from-gray-500 to-gray-600 text-white font-medium py-4 px-6 rounded-xl hover:from-gray-600 hover:to-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transform transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]" style="display: none;">
                        <svg class="inline h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9h6v6H9z"/>
                        </svg>
                        Stop Recording
                    </button>

                    <!-- Processing Button -->
                    <button id="processing" class="w-full bg-gray-400 text-white font-medium py-4 px-6 rounded-xl cursor-not-allowed" disabled style="display: none;">
                        <svg class="inline h-5 w-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Processing Video...
                    </button>

                    <!-- Retry Button -->
                    <button id="retry-recording" class="w-full border border-gray-300 text-gray-700 font-medium py-4 px-6 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200" style="display: none;">
                        Try Again
                    </button>
                </div>

                <!-- Instructions -->
                <div class="mt-6">
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-purple-900 mb-2">Head Movement Instructions:</h4>
                        <ul class="text-xs text-purple-800 space-y-1">
                            <li>• <strong>Step 1:</strong> Look directly at the camera (2 seconds)</li>
                            <li>• <strong>Step 2:</strong> Turn your head slowly to the LEFT (1 second)</li>
                            <li>• <strong>Step 3:</strong> Turn your head slowly to the RIGHT (1 second)</li>
                            <li>• <strong>Step 4:</strong> Return to center and look at camera (1 second)</li>
                            <li>• Keep your face visible throughout the recording</li>
                            <li>• Ensure good lighting and stay still except for head movement</li>
                        </ul>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                        <h4 class="text-sm font-medium text-blue-900 mb-2">Why we need this:</h4>
                        <p class="text-xs text-blue-800">The head movements help us verify that you are a real person and not a photo or video. This provides additional security for your identity verification.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let stream = null;
        let mediaRecorder = null;
        let recordedChunks = [];
        let recordingTimer = null;
        let recordingDuration = 0;

        const video = document.getElementById('video-stream');
        const videoOverlay = document.getElementById('video-overlay');
        const recordingIndicator = document.getElementById('recording-indicator');
        const timer = document.getElementById('timer');
        const instructionsOverlay = document.getElementById('instructions-overlay');
        const currentInstruction = document.getElementById('current-instruction');

        // Button elements
        const startCameraBtn = document.getElementById('start-camera');
        const startRecordingBtn = document.getElementById('start-recording');
        const stopRecordingBtn = document.getElementById('stop-recording');
        const processingBtn = document.getElementById('processing');
        const retryBtn = document.getElementById('retry-recording');

        // Check camera support on page load
        document.addEventListener('DOMContentLoaded', () => {
            checkCameraSupport();
        });

        // Check camera support and permissions (mobile-friendly)
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

        function updatePermissionStatus(state) {
            if (state === 'denied') {
                showPermissionDeniedHelp();
            }
        }

        function showPermissionDeniedHelp() {
            videoOverlay.innerHTML = `
                <div class="text-center text-white p-6">
                    <svg class="mx-auto h-16 w-16 mb-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <h3 class="text-lg font-medium mb-4">Camera Permission Denied</h3>
                    <div class="text-sm space-y-2 max-w-sm mx-auto">
                        <p>Please enable camera access to complete verification</p>
                        <div class="bg-black/20 rounded-lg p-3 text-left">
                            <p class="font-medium mb-2">How to enable:</p>
                            <ul class="text-xs space-y-1">
                                <li>• Click the camera icon in address bar</li>
                                <li>• Select "Allow" for camera</li>
                                <li>• Refresh this page</li>
                            </ul>
                        </div>
                    </div>
                </div>
            `;
            startCameraBtn.innerHTML = 'Retry Camera Access';
        }

        function showError(title, message) {
            videoOverlay.innerHTML = `
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
        }

        // Start Camera
        startCameraBtn.addEventListener('click', async () => {
            startCameraBtn.disabled = true;
            startCameraBtn.innerHTML = 'Starting Camera...';

            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { 
                        width: { ideal: 1280, min: 640 },
                        height: { ideal: 720, min: 480 },
                        facingMode: 'user'
                    },
                    audio: true // Need audio for video recording
                });

                video.srcObject = stream;
                video.style.display = 'block';
                videoOverlay.style.display = 'none';

                startCameraBtn.style.display = 'none';
                startRecordingBtn.style.display = 'block';

            } catch (error) {
                startCameraBtn.disabled = false;
                console.error('Camera error:', error);
                handleCameraError(error);
            }
        });

        function handleCameraError(error) {
            let errorTitle = 'Camera Access Failed';
            let errorMessage = '';

            switch (error.name) {
                case 'NotAllowedError':
                case 'PermissionDeniedError':
                    showPermissionDeniedHelp();
                    return;
                case 'NotFoundError':
                    errorTitle = 'No Camera Found';
                    errorMessage = 'No camera found. Please connect a camera and try again.';
                    break;
                case 'NotReadableError':
                    errorTitle = 'Camera Busy';
                    errorMessage = 'Camera is being used by another app. Please close other apps and try again.';
                    break;
                default:
                    errorMessage = 'Please check your camera and try again.';
            }

            showError(errorTitle, errorMessage);
            startCameraBtn.innerHTML = 'Retry Camera Access';
        }

        // Start Recording
        startRecordingBtn.addEventListener('click', () => {
            if (!stream) return;

            recordedChunks = [];
            recordingDuration = 0;

            try {
                mediaRecorder = new MediaRecorder(stream, { mimeType: 'video/webm' });
                
                mediaRecorder.ondataavailable = (event) => {
                    if (event.data.size > 0) {
                        recordedChunks.push(event.data);
                    }
                };

                mediaRecorder.onstop = () => {
                    processRecording();
                };

                mediaRecorder.start();
                
                // UI updates
                startRecordingBtn.style.display = 'none';
                stopRecordingBtn.style.display = 'block';
                recordingIndicator.style.display = 'flex';
                timer.style.display = 'block';
                instructionsOverlay.style.display = 'block';

                // Start timer and instructions
                startRecordingTimer();
                showInstructions();

                // Auto-stop after 5 seconds
                setTimeout(() => {
                    if (mediaRecorder && mediaRecorder.state === 'recording') {
                        stopRecording();
                    }
                }, 5000);

            } catch (error) {
                console.error('Recording error:', error);
                alert('Recording not supported. Please try a different browser.');
            }
        });

        // Stop Recording
        stopRecordingBtn.addEventListener('click', stopRecording);

        function stopRecording() {
            if (mediaRecorder && mediaRecorder.state === 'recording') {
                mediaRecorder.stop();
            }
            
            clearInterval(recordingTimer);
            recordingIndicator.style.display = 'none';
            timer.style.display = 'none';
            instructionsOverlay.style.display = 'none';
            stopRecordingBtn.style.display = 'none';
        }

        function startRecordingTimer() {
            recordingTimer = setInterval(() => {
                recordingDuration++;
                const minutes = Math.floor(recordingDuration / 60);
                const seconds = recordingDuration % 60;
                timer.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            }, 1000);
        }

        function showInstructions() {
            const instructions = [
                'Look directly at the camera',
                'Blink your eyes naturally',
                'Turn your head slightly left',
                'Turn your head slightly right',
                'Look straight ahead'
            ];

            let currentIndex = 0;
            currentInstruction.textContent = instructions[currentIndex];

            const instructionTimer = setInterval(() => {
                currentIndex++;
                if (currentIndex < instructions.length) {
                    currentInstruction.textContent = instructions[currentIndex];
                } else {
                    clearInterval(instructionTimer);
                }
            }, 1000);
        }

        // Process Recording
        async function processRecording() {
            if (recordedChunks.length === 0) {
                alert('No video recorded. Please try again.');
                resetRecording();
                return;
            }

            // Show processing state
            processingBtn.style.display = 'block';

            try {
                const blob = new Blob(recordedChunks, { type: 'video/webm' });
                const reader = new FileReader();
                
                reader.onload = async () => {
                    const videoData = reader.result;
                    await sendVideoToAPI(videoData);
                };
                
                reader.readAsDataURL(blob);

            } catch (error) {
                console.error('Processing error:', error);
                alert('Error processing video. Please try again.');
                resetRecording();
            }
        }

        // Send Video to API
        async function sendVideoToAPI(videoData) {
            try {
                const response = await fetch('{{ route("verification.video.process") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        video_data: videoData
                    })
                });

                const result = await response.json();

                if (result.success) {
                    // Success - redirect to dashboard with detailed results
                    let message = `Verification completed successfully!\n`;
                    message += `Trust Score: ${result.trust_score}%\n`;
                    if (result.face_match_score) {
                        message += `Face Match Score: ${(result.face_match_score * 100).toFixed(1)}%\n`;
                    }
                    if (result.face_verified !== undefined) {
                        message += `Face Verification: ${result.face_verified ? 'Passed' : 'Failed'}\n`;
                    }
                    message += `\nRedirecting to dashboard...`;
                    
                    alert(message);
                    window.location.href = result.redirect;
                } else {
                    alert(result.message);
                    resetRecording();
                }

            } catch (error) {
                console.error('API Error:', error);
                alert('Network error. Please try again.');
                resetRecording();
            }
        }

        // Reset Recording
        function resetRecording() {
            processingBtn.style.display = 'none';
            startRecordingBtn.style.display = 'block';
            retryBtn.style.display = 'block';
        }

        // Retry Recording
        retryBtn.addEventListener('click', () => {
            retryBtn.style.display = 'none';
            startRecordingBtn.style.display = 'block';
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        });
    </script>
</body>
</html> 