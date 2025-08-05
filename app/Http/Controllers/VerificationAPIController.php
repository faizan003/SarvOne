<?php

namespace App\Http\Controllers;

use App\Models\VerifiableCredential;
use App\Models\Organization;
use App\Services\IPFSService;
use App\Services\BlockchainService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VerificationAPIController extends Controller
{
    protected $ipfsService;
    protected $blockchainService;

    public function __construct(IPFSService $ipfsService, BlockchainService $blockchainService)
    {
        $this->ipfsService = $ipfsService;
        $this->blockchainService = $blockchainService;
    }

    /**
     * Verify a verifiable credential by ID
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyByVCId(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'vc_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request parameters',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $vcId = $request->input('vc_id');
            
            // Find VC in database
            $vc = VerifiableCredential::where('vc_id', $vcId)->first();
            
            if (!$vc) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verifiable credential not found'
                ], 404);
            }

            // Perform comprehensive verification
            $verificationResult = $this->performComprehensiveVerification($vc);
            
            return response()->json([
                'success' => true,
                'data' => $verificationResult
            ]);

        } catch (\Exception $e) {
            Log::error('VC verification by ID failed', [
                'error' => $e->getMessage(),
                'vc_id' => $request->input('vc_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Verification failed due to system error'
            ], 500);
        }
    }

    /**
     * Verify a verifiable credential by IPFS hash
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyByIPFSHash(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ipfs_hash' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request parameters',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $ipfsHash = $request->input('ipfs_hash');
            
            // Validate IPFS hash format
            if (!$this->ipfsService->isValidHash($ipfsHash)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid IPFS hash format'
                ], 400);
            }

            // Retrieve VC from IPFS
            $vcData = $this->ipfsService->retrieveVC($ipfsHash);
            
            if (!$vcData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to retrieve VC from IPFS'
                ], 404);
            }

            // Find corresponding VC in database
            $vc = VerifiableCredential::where('ipfs_hash', $ipfsHash)->first();
            
            if (!$vc) {
                return response()->json([
                    'success' => false,
                    'message' => 'VC not found in registry'
                ], 404);
            }

            // Verify VC integrity and authenticity
            $verificationResult = $this->verifyVCIntegrity($vcData, $vc);
            
            return response()->json([
                'success' => true,
                'data' => $verificationResult
            ]);

        } catch (\Exception $e) {
            Log::error('VC verification by IPFS hash failed', [
                'error' => $e->getMessage(),
                'ipfs_hash' => $request->input('ipfs_hash')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Verification failed due to system error'
            ], 500);
        }
    }

    /**
     * Verify a credential hash on blockchain
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyOnBlockchain(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'credential_hash' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request parameters',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $credentialHash = $request->input('credential_hash');
            
            // Verify hash on blockchain
            $blockchainResult = $this->blockchainService->verifyCredentialHash($credentialHash);
            
            if (!$blockchainResult) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to verify on blockchain'
                ], 500);
            }

            // Find corresponding VC in database
            $vc = VerifiableCredential::where('credential_hash', $credentialHash)->first();
            
            $response = [
                'success' => true,
                'data' => [
                    'blockchain_verification' => $blockchainResult,
                    'exists_on_chain' => $blockchainResult['exists'],
                    'is_revoked' => $blockchainResult['is_revoked'],
                    'issuer_did' => $blockchainResult['issuer_did'],
                    'timestamp' => $blockchainResult['timestamp'],
                    'block_number' => $blockchainResult['block_number']
                ]
            ];

            // Add database information if available
            if ($vc) {
                $response['data']['database_record'] = [
                    'vc_id' => $vc->vc_id,
                    'vc_type' => $vc->vc_type,
                    'subject_name' => $vc->subject_name,
                    'issued_at' => $vc->issued_at,
                    'expires_at' => $vc->expires_at,
                    'status' => $vc->status
                ];
            }

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Blockchain verification failed', [
                'error' => $e->getMessage(),
                'credential_hash' => $request->input('credential_hash')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Blockchain verification failed'
            ], 500);
        }
    }

    /**
     * Get VC status information
     *
     * @param string $vcId
     * @return JsonResponse
     */
    public function getVCStatus(string $vcId): JsonResponse
    {
        try {
            $vc = VerifiableCredential::where('vc_id', $vcId)->first();
            
            if (!$vc) {
                return response()->json([
                    'success' => false,
                    'message' => 'VC not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'vc_id' => $vc->vc_id,
                    'status' => $vc->status,
                    'is_active' => $vc->isActive(),
                    'is_expired' => $vc->isExpired(),
                    'is_revoked' => $vc->isRevoked(),
                    'issued_at' => $vc->issued_at,
                    'expires_at' => $vc->expires_at,
                    'revoked_at' => $vc->revoked_at,
                    'revocation_reason' => $vc->revocation_reason,
                    'verification_count' => $vc->verification_count,
                    'last_verified_at' => $vc->last_verified_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get VC status failed', [
                'error' => $e->getMessage(),
                'vc_id' => $vcId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get VC status'
            ], 500);
        }
    }

    /**
     * Perform comprehensive verification of a VC
     *
     * @param VerifiableCredential $vc
     * @return array
     */
    private function performComprehensiveVerification(VerifiableCredential $vc): array
    {
        $verificationResults = [
            'vc_id' => $vc->vc_id,
            'vc_type' => $vc->vc_type,
            'issuer_did' => $vc->issuer_did,
            'subject_did' => $vc->recipient_did,
            'subject_name' => $vc->subject_name,
            'issued_at' => $vc->issued_at,
            'expires_at' => $vc->expires_at,
            'status' => $vc->status,
            'verification_results' => []
        ];

        // 1. Database Status Check
        $verificationResults['verification_results']['database_status'] = [
            'is_active' => $vc->isActive(),
            'is_expired' => $vc->isExpired(),
            'is_revoked' => $vc->isRevoked(),
            'status' => 'verified'
        ];

        // 2. IPFS Verification
        if ($vc->ipfs_hash) {
            try {
                $ipfsData = $this->ipfsService->retrieveVC($vc->ipfs_hash);
                $verificationResults['verification_results']['ipfs_verification'] = [
                    'hash' => $vc->ipfs_hash,
                    'accessible' => $ipfsData !== null,
                    'gateway_url' => $vc->getIPFSUrl(),
                    'status' => $ipfsData ? 'verified' : 'failed'
                ];
            } catch (\Exception $e) {
                $verificationResults['verification_results']['ipfs_verification'] = [
                    'hash' => $vc->ipfs_hash,
                    'accessible' => false,
                    'error' => $e->getMessage(),
                    'status' => 'error'
                ];
            }
        }

        // 3. Blockchain Verification
        if ($vc->credential_hash) {
            try {
                $blockchainResult = $this->blockchainService->verifyCredentialHash($vc->credential_hash);
                $verificationResults['verification_results']['blockchain_verification'] = [
                    'credential_hash' => $vc->credential_hash,
                    'exists_on_chain' => $blockchainResult['exists'] ?? false,
                    'is_revoked' => $blockchainResult['is_revoked'] ?? false,
                    'blockchain_tx_hash' => $vc->blockchain_tx_hash,
                    'explorer_url' => $vc->getBlockchainExplorerUrl(),
                    'status' => $blockchainResult ? 'verified' : 'failed'
                ];
            } catch (\Exception $e) {
                $verificationResults['verification_results']['blockchain_verification'] = [
                    'credential_hash' => $vc->credential_hash,
                    'error' => $e->getMessage(),
                    'status' => 'error'
                ];
            }
        }

        // 4. Digital Signature Verification
        if ($vc->digital_signature && $vc->issuer) {
            try {
                $vcData = $vc->toW3CFormat();
                unset($vcData['proof']); // Remove proof for verification
                $vcString = json_encode($vcData, JSON_UNESCAPED_SLASHES);
                
                $isValidSignature = Organization::verifySignature(
                    $vcString,
                    $vc->digital_signature,
                    $vc->issuer->public_key
                );
                
                $verificationResults['verification_results']['signature_verification'] = [
                    'algorithm' => $vc->signature_algorithm,
                    'is_valid' => $isValidSignature,
                    'issuer_public_key_available' => !empty($vc->issuer->public_key),
                    'status' => $isValidSignature ? 'verified' : 'failed'
                ];
            } catch (\Exception $e) {
                $verificationResults['verification_results']['signature_verification'] = [
                    'error' => $e->getMessage(),
                    'status' => 'error'
                ];
            }
        }

        // 5. Overall Verification Status
        $allVerifications = $verificationResults['verification_results'];
        $overallStatus = 'verified';
        
        foreach ($allVerifications as $verification) {
            if (isset($verification['status']) && in_array($verification['status'], ['failed', 'error'])) {
                $overallStatus = 'partial';
                break;
            }
        }
        
        $verificationResults['overall_status'] = $overallStatus;
        $verificationResults['verified_at'] = now()->toISOString();
        
        // Update verification count
        $vc->incrementVerificationCount();
        
        return $verificationResults;
    }

    /**
     * Verify VC integrity from IPFS data
     *
     * @param array $vcData
     * @param VerifiableCredential $vc
     * @return array
     */
    private function verifyVCIntegrity(array $vcData, VerifiableCredential $vc): array
    {
        $verificationResults = [
            'vc_id' => $vcData['id'] ?? null,
            'ipfs_hash' => $vc->ipfs_hash,
            'verification_results' => []
        ];

        // 1. Structure Validation
        $requiredFields = ['@context', 'id', 'type', 'issuer', 'credentialSubject', 'issuanceDate'];
        $missingFields = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($vcData[$field])) {
                $missingFields[] = $field;
            }
        }
        
        $verificationResults['verification_results']['structure_validation'] = [
            'is_valid' => empty($missingFields),
            'missing_fields' => $missingFields,
            'status' => empty($missingFields) ? 'verified' : 'failed'
        ];

        // 2. Data Integrity Check
        if (isset($vcData['credentialSubject']['data'])) {
            $ipfsDataHash = hash('sha256', json_encode($vcData['credentialSubject']['data'], JSON_UNESCAPED_SLASHES));
            $dbDataHash = hash('sha256', json_encode($vc->credential_data, JSON_UNESCAPED_SLASHES));
            
            $verificationResults['verification_results']['data_integrity'] = [
                'ipfs_hash' => $ipfsDataHash,
                'database_hash' => $dbDataHash,
                'matches' => $ipfsDataHash === $dbDataHash,
                'status' => $ipfsDataHash === $dbDataHash ? 'verified' : 'failed'
            ];
        }

        // 3. Digital Signature Verification (if present)
        if (isset($vcData['proof']) && $vc->issuer) {
            try {
                $vcForVerification = $vcData;
                unset($vcForVerification['proof']);
                $vcString = json_encode($vcForVerification, JSON_UNESCAPED_SLASHES);
                
                $isValidSignature = Organization::verifySignature(
                    $vcString,
                    $vcData['proof']['jws'],
                    $vc->issuer->public_key
                );
                
                $verificationResults['verification_results']['signature_verification'] = [
                    'is_valid' => $isValidSignature,
                    'proof_method' => $vcData['proof']['verificationMethod'] ?? null,
                    'status' => $isValidSignature ? 'verified' : 'failed'
                ];
            } catch (\Exception $e) {
                $verificationResults['verification_results']['signature_verification'] = [
                    'error' => $e->getMessage(),
                    'status' => 'error'
                ];
            }
        }

        // Overall status
        $allVerifications = $verificationResults['verification_results'];
        $overallStatus = 'verified';
        
        foreach ($allVerifications as $verification) {
            if (isset($verification['status']) && in_array($verification['status'], ['failed', 'error'])) {
                $overallStatus = 'partial';
                break;
            }
        }
        
        $verificationResults['overall_status'] = $overallStatus;
        $verificationResults['verified_at'] = now()->toISOString();
        
        return $verificationResults;
    }
} 