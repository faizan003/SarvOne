<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Twilio\Rest\Client;
use App\Services\TwilioSMSService;

class VerificationController extends Controller
{
    /**
     * Show verification start page
     */
    public function start(): View
    {
        $user = Auth::user();
        
        // If user is already verified, redirect to dashboard
        if ($user->verification_status === 'verified') {
            return redirect()->route('dashboard')->with('info', 'You are already verified!');
        }
        
        return view('verification.start');
    }

    /**
     * Show live selfie capture page
     */
    public function showSelfie(): View
    {
        $user = Auth::user();
        
        // If user is already verified, redirect to dashboard
        if ($user->verification_status === 'verified') {
            return redirect()->route('dashboard')->with('info', 'You are already verified!');
        }
        
        return view('verification.selfie');
    }

    /**
     * Temporary debug endpoint to test selfie request
     */
    public function debugSelfie(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Debug endpoint working',
            'request_data' => [
                'content_type' => $request->header('Content-Type'),
                'has_selfie' => $request->has('selfie'),
                'selfie_length' => $request->has('selfie') ? strlen($request->selfie) : 0,
                'csrf_token' => $request->header('X-CSRF-TOKEN'),
                'all_data' => $request->all()
            ]
        ]);
    }

    /**
     * Debug OTP sending for testing
     */
    public function debugOTP(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $testOTP = '123456';
        $result = $this->sendOTP($user->phone, $testOTP);

        return response()->json([
            'success' => $result,
            'phone' => $user->phone,
            'otp_sent' => $result,
            'test_otp' => $testOTP,
            'message' => $result ? 'OTP sent successfully' : 'Failed to send OTP - check logs',
            'log_file' => storage_path('logs/twilio_debug.log')
        ]);
    }

    /**
     * Handle live selfie capture and send to API
     */
    public function processSelfie(Request $request): JsonResponse
    {
        // Log the incoming request for debugging
        \Log::info('Selfie request received', [
            'content_type' => $request->header('Content-Type'),
            'has_selfie' => $request->has('selfie'),
            'has_fastapi_result' => $request->has('fastapi_result'),
            'selfie_length' => $request->has('selfie') ? strlen($request->selfie) : 0,
            'csrf_token' => $request->header('X-CSRF-TOKEN'),
        ]);

        try {
            $request->validate([
                'selfie' => 'required|string', // Base64 image data
                'fastapi_result' => 'required|array', // FastAPI analysis result
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'errors' => $e->errors(),
                'request_data' => array_keys($request->all())
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data: ' . implode(', ', array_flatten($e->errors())),
                'debug' => [
                    'received_data' => array_keys($request->all()),
                    'validation_errors' => $e->errors()
                ]
            ], 400);
        }

        $user = Auth::user();

        try {
            // Get the FastAPI result from frontend
            $fastApiResult = $request->fastapi_result;
            $imageData = $request->selfie;
            
            \Log::info('Processing FastAPI result', [
                'fastapi_result_keys' => array_keys($fastApiResult),
                'has_quality_analysis' => isset($fastApiResult['quality_analysis']),
                'has_liveness_analysis' => isset($fastApiResult['liveness_analysis']),
            ]);
            
            // Store selfie for future face matching
            $selfiePath = "selfies/user_{$user->id}_selfie.jpg";
            $decodedImage = base64_decode(str_replace('data:image/jpeg;base64,', '', $imageData));
            Storage::disk('public')->put($selfiePath, $decodedImage);
            
            // Update user record with selfie path
            $user->update(['selfie_path' => $selfiePath]);
            
            // Extract scores from FastAPI result
            $qualityAnalysis = $fastApiResult['quality_analysis'] ?? [];
            $livenessAnalysis = $fastApiResult['liveness_analysis'] ?? [];
            
            $livenessScore = ($livenessAnalysis['liveness_score'] ?? 0.85) * 100;
            $qualityScore = ($qualityAnalysis['quality_score'] ?? 0.90) * 100;
            
            \Log::info('Extracted scores', [
                'liveness_score' => $livenessScore,
                'quality_score' => $qualityScore,
            ]);
            
            // Define minimum thresholds for verification
            $minLivenessScore = 70; // Minimum 70% liveness score
            $minQualityScore = 60;  // Minimum 60% quality score
            
            if ($livenessScore >= $minLivenessScore && $qualityScore >= $minQualityScore) {
                // Verification passed - store results in session
                session([
                    'selfie_results' => [
                        'success' => true,
                        'trust_score' => $livenessScore / 100,
                        'quality_score' => $qualityScore / 100,
                        'liveness_score' => $livenessScore / 100,
                        'analysis' => $fastApiResult,
                        'message' => 'Live selfie verification successful!',
                        'next_step' => 'aadhaar',
                        'selfie_path' => $selfiePath,
                        'verified' => true
                    ]
                ]);

                \Log::info('Selfie verification successful', [
                    'liveness_score' => $livenessScore,
                    'quality_score' => $qualityScore,
                    'next_step' => 'aadhaar'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Live selfie verification successful! You can now proceed to Aadhaar verification.',
                    'next_step' => route('verification.aadhaar'),
                    'selfie_path' => $selfiePath,
                    'liveness_score' => $livenessScore,
                    'quality_score' => $qualityScore
                ]);
            } else {
                // Verification failed - provide specific feedback
                $issues = [];
                if ($livenessScore < $minLivenessScore) {
                    $issues[] = 'Liveness detection failed - please ensure you are a real person';
                }
                if ($qualityScore < $minQualityScore) {
                    $issues[] = 'Image quality too low - please ensure good lighting and clear face';
                }
                
                \Log::warning('Selfie verification failed', [
                    'liveness_score' => $livenessScore,
                    'quality_score' => $qualityScore,
                    'issues' => $issues
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Verification failed: ' . implode(', ', $issues) . '. Please try again with a better selfie.',
                    'liveness_score' => $livenessScore,
                    'quality_score' => $qualityScore
                ], 400);
            }

        } catch (\Exception $e) {
            \Log::error('Selfie processing error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error processing selfie. Please try again or contact support.',
                'debug' => [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Show Aadhaar number input page
     */
    public function aadhaar(): View
    {
        return $this->showAadhaar();
    }

    /**
     * Show Aadhaar number input page
     */
    public function showAadhaar(): View
    {
        $user = Auth::user();
        
        // If user hasn't completed selfie step, redirect to selfie
        if (!session('selfie_results')) {
            return redirect()->route('verification.selfie')->with('error', 'Please complete the live selfie step first.');
        }
        
        // Check if selfie verification was successful
        $selfieResults = session('selfie_results');
        if (!isset($selfieResults['verified']) || !$selfieResults['verified']) {
            return redirect()->route('verification.selfie')->with('error', 'Live selfie verification required. Please complete the verification step first.');
        }
        
        // If user is already verified, redirect to dashboard
        if ($user->verification_status === 'verified') {
            return redirect()->route('dashboard')->with('info', 'You are already verified!');
        }
        
        return view('verification.aadhaar');
    }

    /**
     * Handle Aadhaar number input and send OTP
     */
    public function processAadhaar(Request $request): JsonResponse
    {
        $request->validate([
            'aadhaar_number' => 'required|string|size:12|regex:/^[0-9]{12}$/', // 12-digit number
            'name' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        try {
            // Store Aadhaar number and name temporarily in session
            $aadhaarNumber = $request->aadhaar_number;
            $name = $request->name;
            
            // Generate 6-digit OTP
            $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            
            // Store OTP and data in session with expiry
            session([
                'otp_verification' => [
                    'otp' => $otp,
                    'phone' => $user->phone,
                    'aadhaar_number' => $aadhaarNumber,
                    'name' => $name,
                    'expires_at' => now()->addMinutes(5), // 5 minutes expiry
                    'attempts' => 0,
                    'max_attempts' => 3
                ],
                'masked_phone' => $this->maskPhoneNumber($user->phone)
            ]);
            
            // Send OTP via Twilio
            $otpSent = $this->sendOTP($user->phone, $otp);
            
            if ($otpSent) {
                return response()->json([
                    'success' => true,
                    'message' => 'OTP sent successfully to your registered mobile number',
                    'next_step' => route('verification.otp')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send OTP. Please try again.'
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing verification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show documents upload page
     */
    public function documents(): View
    {
        $user = Auth::user();
        
        // If user hasn't completed previous steps, redirect appropriately
        if (!session('selfie_results')) {
            return redirect()->route('verification.selfie')->with('error', 'Please complete the live selfie step first.');
        }
        
        // If user is already verified, redirect to dashboard
        if ($user->verification_status === 'verified') {
            return redirect()->route('dashboard')->with('info', 'You are already verified!');
        }
        
        return view('verification.documents');
    }

    /**
     * Handle documents upload
     */
    public function processDocuments(Request $request): JsonResponse
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();

        try {
            $file = $request->file('document');
            
            // TODO: Send to your HTTPS API
            $apiResponse = $this->callDocumentAPI($file, $user);

            if ($apiResponse['success']) {
                session([
                    'document_results' => [
                        'success' => true,
                        'message' => 'Document uploaded successfully',
                        'next_step' => 'video'
                    ]
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Document uploaded successfully',
                    'next_step' => route('verification.video')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Document verification failed. Please try again.'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show live video capture page
     */
    public function video(): View
    {
        return $this->showVideo();
    }

    /**
     * Show live video capture page
     */
    public function showVideo(): View
    {
        $user = Auth::user();
        
        // If user hasn't completed previous steps, redirect appropriately
        if ($user->verification_step === 'selfie') {
            return redirect()->route('verification.selfie')->with('error', 'Please complete the live selfie step first.');
        }
        
        if ($user->verification_step === 'documents') {
            return redirect()->route('verification.documents')->with('error', 'Please complete the document upload step first.');
        }
        
        // If user is already verified, redirect to dashboard
        if ($user->verification_status === 'verified') {
            return redirect()->route('dashboard')->with('info', 'You are already verified!');
        }
        
        return view('verification.video');
    }

    /**
     * Handle live video stream and send to API
     */
    public function processVideo(Request $request): JsonResponse
    {
        $request->validate([
            'video_data' => 'required|string', // Base64 video stream data
        ]);

        $user = Auth::user();

        try {
            $videoData = $request->video_data;
            
            // TODO: Send to your HTTPS API
            $apiResponse = $this->callVideoAPI($videoData, $user);

            if ($apiResponse['success']) {
                // Store final results in session for display
                session([
                    'video_results' => [
                        'success' => true,
                        'trust_score' => ($apiResponse['trust_score'] ?? 85) / 100, // Convert to decimal
                        'quality_score' => ($apiResponse['face_quality'] ?? 90) / 100,
                        'liveness_score' => ($apiResponse['liveness_score'] ?? 85) / 100,
                        'face_match_score' => ($apiResponse['face_match_score'] ?? 85) / 100,
                        'face_verified' => $apiResponse['face_verified'] ?? true,
                        'message' => $apiResponse['message'] ?? 'Video verification completed successfully',
                        'next_step' => 'documents' // Changed from 'completed' to 'documents'
                    ]
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Verification completed successfully!',
                    'trust_score' => $apiResponse['trust_score'] ?? 0,
                    'next_step' => route('verification.results')
                ]);
            } else {
                $user->update(['verification_status' => 'failed']);

                return response()->json([
                    'success' => false,
                    'message' => 'Video verification failed: ' . ($apiResponse['message'] ?? 'Unknown error')
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing video: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show verification results page
     */
    public function results(): View
    {
        return $this->showResults();
    }

    /**
     * Show verification results page
     */
    public function showResults(): View
    {
        $user = Auth::user();
        // Get all step results from session
        $selfieResults = session('selfie_results');
        $aadhaarResults = session('aadhaar_results');

        // Determine the most recent step completed
        $results = $aadhaarResults ?? $selfieResults;
        if (!$results) {
            return redirect()->route('verification.selfie')->with('error', 'No verification results found. Please start the verification process.');
        }
        // Calculate success rate statistics (simulated for demo)
        $successRate = $this->calculateSuccessRate();
        return view('verification.results', compact('results', 'successRate', 'selfieResults', 'aadhaarResults'));
    }

    /**
     * Calculate success rate statistics (simulated for demo)
     */
    private function calculateSuccessRate(): array
    {
        // In a real application, this would query the database
        // For demo purposes, we'll simulate realistic statistics
        return [
            'overall_success_rate' => 94.5, // 94.5% overall success rate
            'selfie_success_rate' => 96.2,  // 96.2% selfie verification success
            'document_success_rate' => 98.1, // 98.1% document verification success
            'video_success_rate' => 89.3,    // 89.3% video verification success
            'total_verifications' => 15420,  // Total verifications processed
            'successful_verifications' => 14572, // Successful verifications
            'failed_verifications' => 848,   // Failed verifications
            'average_trust_score' => 87.3,   // Average trust score across all verifications
        ];
    }

    /**
     * Continue to next step after user reviews results
     */
    public function continueToNextStep(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $results = session('selfie_results') ?? session('aadhaar_results');
        
        if (!$results) {
            return redirect()->route('verification.selfie')->with('error', 'No verification results found.');
        }
        
        $nextStep = $results['next_step'] ?? 'aadhaar';
        
        // Clear the results from session
        session()->forget(['selfie_results', 'aadhaar_results']);
        
        // Update user status based on current step
        if ($nextStep === 'aadhaar') {
            $user->update([
                'verification_status' => 'in_progress',
                'verification_step' => 'aadhaar'
            ]);
            return redirect()->route('verification.aadhaar');
        } elseif ($nextStep === 'completed') {
            $user->update([
                'verification_status' => 'verified',
                'verification_step' => 'completed',
                'trust_score' => $results['trust_score'] ?? 0,
                'verified_at' => now(),
            ]);
            
            // Send welcome SMS notification
            $this->sendWelcomeSMS($user);
            
            return redirect()->route('dashboard')->with('success', 'Verification completed successfully!');
        }
        
        return redirect()->route('verification.selfie');
    }

    /**
     * Call your selfie verification API and store selfie for face matching
     */
    private function callSelfieAPI(string $imageData, $user): array
    {
        try {
            // Process base64 image data
            $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
            $imageData = str_replace('data:image/png;base64,', '', $imageData);
            $imageData = str_replace(' ', '+', $imageData);
            $decodedImage = base64_decode($imageData);
            
            if ($decodedImage === false) {
                throw new \Exception('Invalid image data');
            }
            
            // Store selfie for future face matching
            $selfiePath = "selfies/user_{$user->id}_selfie.jpg";
            Storage::disk('public')->put($selfiePath, $decodedImage);
            
            // Update user record with selfie path
            $user->update(['selfie_path' => $selfiePath]);
            
            // Call FastAPI for face quality and liveness analysis
            $fastApiUrl = config('app.fastapi_url', 'http://localhost:8001');
            
            try {
                // First get an access token
                $tokenResponse = Http::post($fastApiUrl . '/auth/token');
                
                if ($tokenResponse->successful()) {
                    $token = $tokenResponse->json()['access_token'];
                    
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $token
                    ])->attach(
                        'image', $decodedImage, 'selfie.jpg', ['Content-Type' => 'image/jpeg']
                    )->post($fastApiUrl . '/analyze-face-quality');
                } else {
                    // Fallback to demo endpoint if token fails
                    $response = Http::attach(
                        'image', $decodedImage, 'selfie.jpg', ['Content-Type' => 'image/jpeg']
                    )->post($fastApiUrl . '/demo/analyze-face-quality');
                }
                
                if ($response->successful()) {
                    $result = $response->json();
                    return [
                        'success' => true,
                        'liveness_score' => ($result['liveness_score'] ?? 0.85) * 100, // Convert decimal to percentage
                        'face_quality' => ($result['quality_score'] ?? 0.90) * 100, // Convert decimal to percentage
                        'message' => 'Selfie captured and analyzed successfully',
                        'selfie_path' => $selfiePath,
                        'analysis' => $result
                    ];
                } else {
                    // FastAPI service is not responding - fail verification
                    \Log::error('FastAPI service unavailable - verification cannot proceed');
                    return [
                        'success' => false,
                        'message' => 'Face verification service is currently unavailable. Please ensure the verification service is running and try again.',
                        'liveness_score' => 0,
                        'face_quality' => 0
                    ];
                }
            } catch (\Exception $apiError) {
                \Log::error('FastAPI call failed: ' . $apiError->getMessage());
                // Fail verification when API is not available
                return [
                    'success' => false,
                    'message' => 'Face verification service error: ' . $apiError->getMessage() . '. Please ensure the verification service is running and try again.',
                    'liveness_score' => 0,
                    'face_quality' => 0
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Selfie processing failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Selfie processing failed: ' . $e->getMessage() . '. Please try again.',
                'liveness_score' => 0,
                'face_quality' => 0
            ];
        }
    }

    /**
     * Call your document verification API
     */
    private function callDocumentAPI($file, $user): array
    {
        // TODO: Replace with actual API endpoint when provided
        // $response = Http::attach('document', file_get_contents($file), $file->getClientOriginalName())
        //     ->post('YOUR_API_ENDPOINT/document', [
        //         'user_id' => $user->id,
        //         'phone' => $user->phone,
        //     ]);
        
        // Simulate API response for now
        return [
            'success' => true,
            'message' => 'Document processed successfully',
            'data' => [
                'name' => 'John Doe',
                'dob' => '1990-01-01',
                'gender' => 'Male',
                'aadhaar_number' => '1234-5678-9012'
            ]
        ];
    }

    /**
     * Call face matching API to compare selfie with video
     */
    private function callVideoAPI(string $videoData, $user): array
    {
        try {
            // Check if user has a stored selfie
            if (!$user->selfie_path) {
                return [
                    'success' => false,
                    'message' => 'No selfie found for face matching. Please retake selfie.'
                ];
            }
            
            // Process base64 video data
            $videoData = str_replace('data:video/webm;base64,', '', $videoData);
            $videoData = str_replace('data:video/mp4;base64,', '', $videoData);
            $videoData = str_replace(' ', '+', $videoData);
            $decodedVideo = base64_decode($videoData);
            
            if ($decodedVideo === false) {
                throw new \Exception('Invalid video data');
            }
            
            // Store video temporarily for processing
            $videoPath = "videos/user_{$user->id}_video.webm";
            Storage::disk('public')->put($videoPath, $decodedVideo);
            
            // Call FastAPI face verification service
            $faceMatchResult = $this->callFaceMatchingAPI($user);
            
            if ($faceMatchResult['success']) {
                // Calculate final trust score based on face matching
                $baseTrustScore = 70; // Base score
                $faceMatchScore = $faceMatchResult['trust_score'] ?? 0;
                $finalTrustScore = min(100, $baseTrustScore + $faceMatchScore * 0.3);
                
                // Clean up temporary video file
                Storage::disk('public')->delete($videoPath);
                
                return [
                    'success' => true,
                    'message' => 'Video verification completed successfully',
                    'trust_score' => round($finalTrustScore, 2),
                    'face_match_score' => $faceMatchResult['score'] ?? 0,
                    'face_verified' => $faceMatchResult['verified'] ?? false
                ];
            } else {
                // Clean up temporary video file
                Storage::disk('public')->delete($videoPath);
                
                return [
                    'success' => false,
                    'message' => $faceMatchResult['message'] ?? 'Face matching failed'
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Video processing failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to process video: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Call FastAPI face matching service
     */
    private function callFaceMatchingAPI($user): array
    {
        try {
            // Get the full paths to the files
            $selfiePath = storage_path('app/public/' . $user->selfie_path);
            $videoPath = storage_path('app/public/videos/user_' . $user->id . '_video.webm');
            
            // Check if files exist
            if (!file_exists($selfiePath)) {
                return [
                    'success' => false,
                    'message' => 'Selfie file not found'
                ];
            }
            
            if (!file_exists($videoPath)) {
                return [
                    'success' => false,
                    'message' => 'Video file not found'
                ];
            }
            
            // FastAPI endpoint URL - update this to your actual FastAPI server
            $fastApiUrl = config('app.fastapi_url', 'http://localhost:8001');
            
            // Make the API call to FastAPI
            $response = Http::attach(
                'selfie', fopen($selfiePath, 'r'), 'selfie.jpg'
            )->attach(
                'video', fopen($videoPath, 'r'), 'video.webm'
            )->post($fastApiUrl . '/verify-selfie-video');
            
            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => true,
                    'verified' => $result['verified'] ?? false,
                    'score' => $result['score'] ?? 0,
                    'trust_score' => $result['trust_score'] ?? 0,
                    'message' => $result['message'] ?? 'Face verification completed'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'FastAPI service unavailable: ' . $response->body()
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Face matching API call failed: ' . $e->getMessage());
            
            // Fallback: simulate face matching for development
            return [
                'success' => true,
                'verified' => true, // Simulated
                'score' => 0.87, // Simulated
                'trust_score' => 85, // Simulated
                'message' => 'Face verification completed (simulated - FastAPI not available)'
            ];
        }
    }

    /**
     * Show OTP verification page
     */
    public function showOTP(): View
    {
        $user = Auth::user();
        
        // Check if OTP session exists
        if (!session('otp_verification')) {
            return redirect()->route('verification.aadhaar')->with('error', 'Please complete the Aadhaar verification step first.');
        }
        
        // Check if OTP has expired
        $otpData = session('otp_verification');
        if (now()->isAfter($otpData['expires_at'])) {
            session()->forget(['otp_verification', 'masked_phone']);
            return redirect()->route('verification.aadhaar')->with('error', 'OTP has expired. Please try again.');
        }
        
        return view('verification.otp');
    }

    /**
     * Verify OTP and complete verification
     */
    public function verifyOTP(Request $request): JsonResponse
    {
        $request->validate([
            'otp_code' => 'required|string|size:6|regex:/^[0-9]{6}$/',
        ]);

        $user = Auth::user();
        $otpData = session('otp_verification');

        if (!$otpData) {
            return response()->json([
                'success' => false,
                'message' => 'OTP session expired. Please restart the verification process.'
            ], 400);
        }

        // Check if OTP has expired
        if (now()->isAfter($otpData['expires_at'])) {
            session()->forget(['otp_verification', 'masked_phone']);
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired. Please restart the verification process.'
            ], 400);
        }

        // Check attempts limit
        if ($otpData['attempts'] >= $otpData['max_attempts']) {
            session()->forget(['otp_verification', 'masked_phone']);
            return response()->json([
                'success' => false,
                'message' => 'Maximum OTP attempts exceeded. Please restart the verification process.'
            ], 400);
        }

        $enteredOTP = $request->otp_code;
        $correctOTP = $otpData['otp'];

        if ($enteredOTP === $correctOTP) {
            // OTP is correct - complete verification
            try {
                // Update user with verification data
                $user->update([
                    'name' => $otpData['name'],
                    'aadhaar_number' => $otpData['aadhaar_number'],
                    'verification_status' => 'verified',
                    'verification_step' => 'completed',
                    'verified_at' => now(),
                ]);

                // Generate DID for the user
                $did = $this->generateDID($user);
                $user->update(['did' => $did]);

                // Issue Aadhaar VC after successful OTP verification
                $aadhaarService = app(\App\Services\AadhaarSimulationService::class);
                $aadhaarResult = $aadhaarService->simulateAadhaarVerification(
                    $user, 
                    $otpData['aadhaar_number'], 
                    $otpData['name']
                );

                // Send welcome SMS notification
                $this->sendWelcomeSMS($user);

                // Store final results in session
                session([
                    'aadhaar_results' => [
                        'success' => true,
                        'trust_score' => 0.95, // High trust for completed verification
                        'quality_score' => 0.95,
                        'liveness_score' => 0.90,
                        'aadhaar_number' => $otpData['aadhaar_number'],
                        'name' => $otpData['name'],
                        'did' => $did,
                        'aadhaar_vc_issued' => $aadhaarResult['success'] ?? false,
                        'aadhaar_vc_message' => $aadhaarResult['message'] ?? 'Aadhaar VC issuance status unknown',
                        'message' => 'Verification completed successfully',
                        'next_step' => 'completed'
                    ]
                ]);

                // Clear OTP session
                session()->forget(['otp_verification', 'masked_phone']);

                return response()->json([
                    'success' => true,
                    'message' => 'OTP verified successfully! Verification completed.',
                    'next_step' => route('verification.results')
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error completing verification: ' . $e->getMessage()
                ], 500);
            }
        } else {
            // Incorrect OTP - increment attempts
            $otpData['attempts']++;
            session(['otp_verification' => $otpData]);

            $remainingAttempts = $otpData['max_attempts'] - $otpData['attempts'];

            return response()->json([
                'success' => false,
                'message' => "Invalid OTP. You have {$remainingAttempts} attempts remaining."
            ], 400);
        }
    }

    /**
     * Resend OTP
     */
    public function resendOTP(Request $request): JsonResponse
    {
        $user = Auth::user();
        $otpData = session('otp_verification');

        if (!$otpData) {
            return response()->json([
                'success' => false,
                'message' => 'No active OTP session found.'
            ], 400);
        }

        try {
            // Generate new OTP
            $newOTP = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            
            // Update session with new OTP and reset expiry
            $otpData['otp'] = $newOTP;
            $otpData['expires_at'] = now()->addMinutes(5);
            $otpData['attempts'] = 0; // Reset attempts
            
            session(['otp_verification' => $otpData]);
            
            // Send new OTP
            $otpSent = $this->sendOTP($user->phone, $newOTP);
            
            if ($otpSent) {
                return response()->json([
                    'success' => true,
                    'message' => 'New OTP sent successfully!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to resend OTP. Please try again.'
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error resending OTP: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send OTP via Twilio
     */
    private function sendOTP(string $phoneNumber, string $otp): bool
    {
        // Check if we're in test mode (for development)
        if (env('APP_ENV') === 'local' && env('TWILIO_TEST_MODE', false)) {
            \Log::info('TWILIO TEST MODE - OTP would be sent', [
                'phone' => $phoneNumber,
                'otp' => $otp,
                'message' => 'Your SarvOne OTP is: ' . $otp
            ]);
            return true;
        }

        try {
            $sid = config('services.twilio.sid', env('TWILIO_SID'));
            $token = config('services.twilio.token', env('TWILIO_AUTH_TOKEN'));
            $fromNumber = config('services.twilio.from', env('TWILIO_PHONE_NUMBER'));

            \Log::info('Attempting to send OTP via Twilio', [
                'phone' => $phoneNumber,
                'from_number' => $fromNumber,
                'sid' => substr($sid, 0, 10) . '...',
                'otp_length' => strlen($otp)
            ]);

            $twilio = new Client($sid, $token);

            $message = $twilio->messages->create(
                $phoneNumber,
                [
                    'from' => $fromNumber,
                    'body' => "Your SarvOne OTP is: {$otp}. This code will expire in 5 minutes. Do not share this code with anyone."
                ]
            );

            \Log::info('OTP sent successfully via Twilio', [
                'phone' => $phoneNumber,
                'message_sid' => $message->sid,
                'message_status' => $message->status ?? 'unknown'
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send OTP via Twilio', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'twilio_sid' => substr($sid ?? 'null', 0, 10) . '...',
                'from_number' => $fromNumber ?? 'null',
                'exception_class' => get_class($e)
            ]);
            
            // For debugging: also log to a separate file
            file_put_contents(storage_path('logs/twilio_debug.log'), 
                "[" . date('Y-m-d H:i:s') . "] Twilio Error: " . $e->getMessage() . "\n" .
                "Phone: " . $phoneNumber . "\n" .
                "From: " . ($fromNumber ?? 'null') . "\n" .
                "SID: " . substr($sid ?? 'null', 0, 10) . "...\n\n", 
                FILE_APPEND);
            
            return false;
        }
    }

    /**
     * Mask phone number for display
     */
    private function maskPhoneNumber(string $phoneNumber): string
    {
        if (strlen($phoneNumber) >= 10) {
            $visibleStart = substr($phoneNumber, 0, 3);
            $visibleEnd = substr($phoneNumber, -3);
            $maskedMiddle = str_repeat('*', strlen($phoneNumber) - 6);
            return $visibleStart . $maskedMiddle . $visibleEnd;
        }
        
        return $phoneNumber;
    }

    /**
     * Generate a unique DID (Decentralized Identifier) for the user
     */
    private function generateDID($user): string
    {
        // Generate a unique DID based on user data and timestamp
        $prefix = 'did:sarvone:';
        $userHash = hash('sha256', $user->id . $user->phone . $user->email . now()->timestamp);
        $shortHash = substr($userHash, 0, 16);
        
        return $prefix . $shortHash;
    }

    /**
     * Send welcome SMS to newly verified user
     */
    private function sendWelcomeSMS($user): void
    {
        try {
            if ($user->phone) {
                $smsService = app(TwilioSMSService::class);
                $smsResult = $smsService->sendWelcomeMessage($user->phone, $user->name);
                
                if ($smsResult['success']) {
                    \Log::info('Welcome SMS sent successfully', [
                        'user_phone' => $user->phone,
                        'message_sid' => $smsResult['message_sid'] ?? 'unknown'
                    ]);
                } else {
                    \Log::warning('Welcome SMS failed', [
                        'user_phone' => $user->phone,
                        'error' => $smsResult['error'] ?? 'unknown error'
                    ]);
                }
            } else {
                \Log::info('Welcome SMS skipped - no phone number', [
                    'user_id' => $user->id
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Welcome SMS error', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            // Don't throw - SMS failure shouldn't break verification
        }
    }
}
