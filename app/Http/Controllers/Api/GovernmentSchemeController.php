<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\GovernmentScheme;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class GovernmentSchemeController extends Controller
{
    /**
     * Submit a new government scheme via API
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function submitScheme(Request $request): JsonResponse
    {
        try {
            // Validate API key
            $apiKey = $request->header('X-API-Key');
            if (!$this->validateApiKey($apiKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or missing API key',
                    'error_code' => 'INVALID_API_KEY'
                ], 401);
            }

            // Validate request data
            $validator = Validator::make($request->all(), [
                'scheme_name' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'category' => 'required|string|in:education,health,employment,financial,agriculture,housing,welfare,other',
                'max_income' => 'nullable|numeric|min:0',
                'min_age' => 'nullable|integer|min:0|max:120',
                'max_age' => 'nullable|integer|min:0|max:120',
                'required_credentials' => 'nullable|array',
                'required_credentials.*' => 'string|in:aadhaar_card,pan_card,voter_id,driving_license,passport,income_certificate,caste_certificate,domicile_certificate,ration_card,ayushman_card,pension_card,scholarship_approval,economic_weaker_section,property_land_records',
                'caste_criteria' => 'nullable|array',
                'caste_criteria.*' => 'string',
                'education_criteria' => 'nullable|array',
                'education_criteria.*' => 'string',
                'employment_criteria' => 'nullable|array',
                'employment_criteria.*' => 'string',
                'benefit_amount' => 'required|numeric|min:0',
                'benefit_type' => 'required|string|in:grant,loan,subsidy,scholarship,insurance,other',
                'application_deadline' => 'required|date|after:today',
                'organization_name' => 'required|string|max:255',
                'organization_did' => 'required|string|max:255',
                'contact_email' => 'nullable|email',
                'contact_phone' => 'nullable|string',
                'website_url' => 'nullable|url',
                'application_url' => 'nullable|url',
                'documents_required' => 'nullable|array',
                'documents_required.*' => 'string',
                'additional_info' => 'nullable|string|max:2000',
                'priority_level' => 'nullable|string|in:low,medium,high,urgent',
                'target_audience' => 'nullable|string|max:500',
                'implementation_phase' => 'nullable|string|in:planning,active,completed,discontinued'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'error_code' => 'VALIDATION_ERROR'
                ], 422);
            }

            // Create the scheme
            $scheme = GovernmentScheme::create([
                'scheme_name' => $request->scheme_name,
                'description' => $request->description,
                'category' => $request->category,
                'max_income' => $request->max_income,
                'min_age' => $request->min_age,
                'max_age' => $request->max_age,
                'required_credentials' => $request->required_credentials,
                'caste_criteria' => $request->caste_criteria,
                'education_criteria' => $request->education_criteria,
                'employment_criteria' => $request->employment_criteria,
                'benefit_amount' => $request->benefit_amount,
                'benefit_type' => $request->benefit_type,
                'application_deadline' => $request->application_deadline,
                'status' => 'active',
                'created_by' => 'api_submission',
                'organization_name' => $request->organization_name,
                'organization_did' => $request->organization_did,
                'contact_email' => $request->contact_email,
                'contact_phone' => $request->contact_phone,
                'website_url' => $request->website_url,
                'application_url' => $request->application_url,
                'documents_required' => $request->documents_required,
                'additional_info' => $request->additional_info,
                'priority_level' => $request->priority_level ?? 'medium',
                'target_audience' => $request->target_audience,
                'implementation_phase' => $request->implementation_phase ?? 'active'
            ]);

            // Log the submission
            Log::info('Government scheme submitted via API', [
                'scheme_id' => $scheme->id,
                'scheme_name' => $scheme->scheme_name,
                'organization' => $request->organization_name,
                'organization_did' => $request->organization_did,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Scheme submitted successfully',
                'data' => [
                    'scheme_id' => $scheme->id,
                    'scheme_name' => $scheme->scheme_name,
                    'status' => $scheme->status,
                    'created_at' => $scheme->created_at->toISOString(),
                    'application_deadline' => $scheme->application_deadline->toISOString()
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error submitting government scheme via API', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error_code' => 'INTERNAL_ERROR'
            ], 500);
        }
    }

    /**
     * Update an existing scheme via API
     * 
     * @param Request $request
     * @param int $schemeId
     * @return JsonResponse
     */
    public function updateScheme(Request $request, int $schemeId): JsonResponse
    {
        try {
            // Validate API key
            $apiKey = $request->header('X-API-Key');
            if (!$this->validateApiKey($apiKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or missing API key',
                    'error_code' => 'INVALID_API_KEY'
                ], 401);
            }

            // Find the scheme
            $scheme = GovernmentScheme::find($schemeId);
            if (!$scheme) {
                return response()->json([
                    'success' => false,
                    'message' => 'Scheme not found',
                    'error_code' => 'SCHEME_NOT_FOUND'
                ], 404);
            }

            // Validate request data (same as submit but all fields optional)
            $validator = Validator::make($request->all(), [
                'scheme_name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string|max:1000',
                'category' => 'sometimes|string|in:education,health,employment,financial,agriculture,housing,welfare,other',
                'max_income' => 'sometimes|numeric|min:0',
                'min_age' => 'sometimes|integer|min:0|max:120',
                'max_age' => 'sometimes|integer|min:0|max:120',
                'required_credentials' => 'sometimes|array',
                'required_credentials.*' => 'string|in:aadhaar_card,pan_card,voter_id,driving_license,passport,income_certificate,caste_certificate,domicile_certificate,ration_card,ayushman_card,pension_card,scholarship_approval,economic_weaker_section,property_land_records',
                'caste_criteria' => 'sometimes|array',
                'caste_criteria.*' => 'string',
                'education_criteria' => 'sometimes|array',
                'education_criteria.*' => 'string',
                'employment_criteria' => 'sometimes|array',
                'employment_criteria.*' => 'string',
                'benefit_amount' => 'sometimes|numeric|min:0',
                'benefit_type' => 'sometimes|string|in:grant,loan,subsidy,scholarship,insurance,other',
                'application_deadline' => 'sometimes|date|after:today',
                'status' => 'sometimes|string|in:active,inactive,draft,completed',
                'contact_email' => 'sometimes|email',
                'contact_phone' => 'sometimes|string',
                'website_url' => 'sometimes|url',
                'application_url' => 'sometimes|url',
                'documents_required' => 'sometimes|array',
                'documents_required.*' => 'string',
                'additional_info' => 'sometimes|string|max:2000',
                'priority_level' => 'sometimes|string|in:low,medium,high,urgent',
                'target_audience' => 'sometimes|string|max:500',
                'implementation_phase' => 'sometimes|string|in:planning,active,completed,discontinued'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'error_code' => 'VALIDATION_ERROR'
                ], 422);
            }

            // Update the scheme
            $scheme->update($request->only([
                'scheme_name', 'description', 'category', 'max_income', 'min_age', 'max_age',
                'required_credentials', 'caste_criteria', 'education_criteria', 'employment_criteria',
                'benefit_amount', 'benefit_type', 'application_deadline', 'status',
                'contact_email', 'contact_phone', 'website_url', 'application_url',
                'documents_required', 'additional_info', 'priority_level', 'target_audience',
                'implementation_phase'
            ]));

            // Log the update
            Log::info('Government scheme updated via API', [
                'scheme_id' => $scheme->id,
                'scheme_name' => $scheme->scheme_name,
                'updated_fields' => array_keys($request->all()),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Scheme updated successfully',
                'data' => [
                    'scheme_id' => $scheme->id,
                    'scheme_name' => $scheme->scheme_name,
                    'status' => $scheme->status,
                    'updated_at' => $scheme->updated_at->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating government scheme via API', [
                'error' => $e->getMessage(),
                'scheme_id' => $schemeId,
                'request_data' => $request->all(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error_code' => 'INTERNAL_ERROR'
            ], 500);
        }
    }

    /**
     * Get scheme status via API
     * 
     * @param Request $request
     * @param int $schemeId
     * @return JsonResponse
     */
    public function getSchemeStatus(Request $request, int $schemeId): JsonResponse
    {
        try {
            // Validate API key
            $apiKey = $request->header('X-API-Key');
            if (!$this->validateApiKey($apiKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or missing API key',
                    'error_code' => 'INVALID_API_KEY'
                ], 401);
            }

            // Find the scheme
            $scheme = GovernmentScheme::find($schemeId);
            if (!$scheme) {
                return response()->json([
                    'success' => false,
                    'message' => 'Scheme not found',
                    'error_code' => 'SCHEME_NOT_FOUND'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'scheme_id' => $scheme->id,
                    'scheme_name' => $scheme->scheme_name,
                    'status' => $scheme->status,
                    'created_at' => $scheme->created_at->toISOString(),
                    'updated_at' => $scheme->updated_at->toISOString(),
                    'application_deadline' => $scheme->application_deadline->toISOString(),
                    'total_applications' => 0, // TODO: Implement application tracking
                    'eligibility_checks' => 0 // TODO: Implement tracking
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting scheme status via API', [
                'error' => $e->getMessage(),
                'scheme_id' => $schemeId,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error_code' => 'INTERNAL_ERROR'
            ], 500);
        }
    }

    /**
     * Validate API key
     * 
     * @param string|null $apiKey
     * @return bool
     */
    private function validateApiKey(?string $apiKey): bool
    {
        if (!$apiKey) {
            return false;
        }

        // Get valid API keys from environment
        $validApiKeys = explode(',', env('GOVERNMENT_API_KEYS', ''));
        $validApiKeys = array_map('trim', $validApiKeys);
        $validApiKeys = array_filter($validApiKeys);

        return in_array($apiKey, $validApiKeys);
    }
} 