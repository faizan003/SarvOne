<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\VerifiableCredential;
use App\Models\AccessLog;
use App\Models\Organization;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ONDCController extends Controller
{
    /**
     * Lookup VC by VC ID (Privacy-preserving)
     * 
     * @param string $vcId
     * @return JsonResponse
     */
    public function lookupVC(string $vcId): JsonResponse
    {
        try {
            // Validate VC ID format
            if (!$this->isValidVCId($vcId)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid VC ID format',
                    'message' => 'VC ID must be a valid UUID'
                ], 400);
            }

            // Find the VC
            $vc = VerifiableCredential::where('vc_id', $vcId)
                ->with('issuer')
                ->first();

            if (!$vc) {
                return response()->json([
                    'success' => false,
                    'error' => 'VC not found',
                    'message' => 'No verifiable credential found with the provided VC ID'
                ], 404);
            }

            // Check if VC is active
            if (!$vc->isActive()) {
                return response()->json([
                    'success' => false,
                    'error' => 'VC not active',
                    'message' => 'This verifiable credential is not active',
                    'status' => $vc->isExpired() ? 'expired' : 'revoked'
                ], 400);
            }

            // Log the lookup for audit trail
            $this->logLookup($vc, 'ondc_lookup');

            // Return VC data (privacy-preserving - no DID exposure)
            return response()->json([
                'success' => true,
                'data' => [
                    'vc_id' => $vc->vc_id,
                    'vc_type' => $vc->vc_type,
                    'subject_name' => $vc->subject_name,
                    'issuer' => [
                        'name' => $vc->issuer->name ?? 'Unknown',
                        'type' => $vc->issuer->type ?? 'unknown'
                    ],
                    'issued_at' => $vc->issued_at->toISOString(),
                    'expires_at' => $vc->expires_at ? $vc->expires_at->toISOString() : null,
                    'credential_data' => $vc->credential_data,
                    'ipfs_hash' => $vc->ipfs_hash,
                    'blockchain_tx_hash' => $vc->blockchain_tx_hash,
                    'status' => 'active',
                    'verification_urls' => [
                        'ipfs' => $vc->getIPFSUrl(),
                        'blockchain' => $vc->getBlockchainExplorerUrl()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('ONDC VC lookup failed', [
                'vc_id' => $vcId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'message' => 'Failed to lookup verifiable credential'
            ], 500);
        }
    }

    /**
     * Verify VC by VC ID and signature
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyVC(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'vc_id' => 'required|string',
            'signature' => 'required|string',
            'public_key' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'message' => 'Missing required fields: vc_id, signature, public_key'
            ], 400);
        }

        try {
            $vcId = $request->input('vc_id');
            $signature = $request->input('signature');
            $publicKey = $request->input('public_key');

            // Find the VC
            $vc = VerifiableCredential::where('vc_id', $vcId)->first();

            if (!$vc) {
                return response()->json([
                    'success' => false,
                    'error' => 'VC not found',
                    'message' => 'No verifiable credential found with the provided VC ID'
                ], 404);
            }

            // Verify the signature (simplified - in production, use proper cryptographic verification)
            $isValid = $this->verifySignature($vc, $signature, $publicKey);

            if (!$isValid) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid signature',
                    'message' => 'The provided signature is not valid for this VC'
                ], 400);
            }

            // Log the verification
            $this->logLookup($vc, 'ondc_verification');

            return response()->json([
                'success' => true,
                'data' => [
                    'vc_id' => $vc->vc_id,
                    'verified' => true,
                    'verification_timestamp' => now()->toISOString(),
                    'status' => $vc->isActive() ? 'active' : 'inactive'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('ONDC VC verification failed', [
                'vc_id' => $request->input('vc_id'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'message' => 'Failed to verify verifiable credential'
            ], 500);
        }
    }

    /**
     * Get VC metadata (public info only)
     * 
     * @param string $vcId
     * @return JsonResponse
     */
    public function getVCMetadata(string $vcId): JsonResponse
    {
        try {
            // Find the VC
            $vc = VerifiableCredential::where('vc_id', $vcId)
                ->with('issuer')
                ->first();

            if (!$vc) {
                return response()->json([
                    'success' => false,
                    'error' => 'VC not found',
                    'message' => 'No verifiable credential found with the provided VC ID'
                ], 404);
            }

            // Return only public metadata
            return response()->json([
                'success' => true,
                'data' => [
                    'vc_id' => $vc->vc_id,
                    'vc_type' => $vc->vc_type,
                    'issuer_name' => $vc->issuer->name ?? 'Unknown',
                    'issued_at' => $vc->issued_at->toISOString(),
                    'expires_at' => $vc->expires_at ? $vc->expires_at->toISOString() : null,
                    'status' => $vc->isActive() ? 'active' : ($vc->isExpired() ? 'expired' : 'revoked'),
                    'verification_urls' => [
                        'ipfs' => $vc->getIPFSUrl(),
                        'blockchain' => $vc->getBlockchainExplorerUrl()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('ONDC VC metadata lookup failed', [
                'vc_id' => $vcId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'message' => 'Failed to get VC metadata'
            ], 500);
        }
    }

    /**
     * Health check for ONDC integration
     * 
     * @return JsonResponse
     */
    public function health(): JsonResponse
    {
        try {
            // Check database connectivity
            $vcCount = VerifiableCredential::count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'status' => 'healthy',
                    'timestamp' => now()->toISOString(),
                    'total_vcs' => $vcCount,
                    'version' => '1.0.0',
                    'endpoints' => [
                        'vc_lookup' => route('ondc.vc.lookup', ['vcId' => 'example-vc-id']),
                        'vc_verify' => route('ondc.vc.verify'),
                        'vc_metadata' => route('ondc.vc.metadata', ['vcId' => 'example-vc-id'])
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Service unhealthy',
                'message' => 'ONDC service is not responding properly'
            ], 503);
        }
    }

    /**
     * Validate VC ID format
     * 
     * @param string $vcId
     * @return bool
     */
    private function isValidVCId(string $vcId): bool
    {
        // VC ID should be a valid UUID or similar format
        return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $vcId) ||
               preg_match('/^[a-zA-Z0-9]{20,}$/', $vcId);
    }

    /**
     * Verify signature (simplified implementation)
     * 
     * @param VerifiableCredential $vc
     * @param string $signature
     * @param string $publicKey
     * @return bool
     */
    private function verifySignature(VerifiableCredential $vc, string $signature, string $publicKey): bool
    {
        // In production, implement proper cryptographic signature verification
        // For now, return true if signature is not empty
        return !empty($signature) && !empty($publicKey);
    }

    /**
     * Log lookup for audit trail
     * 
     * @param VerifiableCredential $vc
     * @param string $action
     * @return void
     */
    private function logLookup(VerifiableCredential $vc, string $action): void
    {
        try {
            AccessLog::create([
                'user_id' => null, // ONDC lookups don't have user context
                'organization_id' => null, // ONDC lookups don't have organization context
                'vc_id' => $vc->vc_id,
                'action' => $action,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'access_reason' => 'ONDC API lookup',
                'data_accessed' => json_encode([
                    'vc_type' => $vc->vc_type,
                    'issuer_name' => $vc->issuer->name ?? 'Unknown'
                ])
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log ONDC lookup', [
                'vc_id' => $vc->vc_id,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }
} 