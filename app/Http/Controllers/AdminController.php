<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use App\Services\CredentialScopeService;
use App\Services\CredentialService;
use App\Services\TwilioSMSService;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    protected $blockchainService;

    public function __construct()
    {
        // No dependency injection needed - using HTTP client directly
    }
    /**
     * Show government approval dashboard
     */
    public function approvalDashboard()
    {
        // Get organization statistics
        $stats = [
            'total' => Organization::count(),
            'pending' => Organization::where('verification_status', 'pending')->count(),
            'approved' => Organization::where('verification_status', 'approved')->count(),
            'rejected' => Organization::where('verification_status', 'rejected')->count(),
        ];

        // Get recent organizations
        $recentOrganizations = Organization::latest()
            ->take(5)
            ->get();

        return view('admin.approval-dashboard', compact('stats', 'recentOrganizations'));
    }

    /**
     * Get organizations by status for AJAX requests
     */
    public function getOrganizationsByStatus(Request $request, $status = 'pending')
    {
        $validStatuses = ['pending', 'approved', 'rejected'];
        
        if (!in_array($status, $validStatuses)) {
            $status = 'pending';
        }

        // For approved organizations, fetch data from blockchain
        if ($status === 'approved') {
            return $this->getApprovedOrganizationsFromBlockchain($request);
        }

        // For pending and rejected, use database as usual
        $organizations = Organization::where('verification_status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $organizations->items(),
            'pagination' => [
                'current_page' => $organizations->currentPage(),
                'last_page' => $organizations->lastPage(),
                'total' => $organizations->total(),
            ]
        ]);
    }

    /**
     * Get approved organizations from blockchain instead of database
     */
    private function getApprovedOrganizationsFromBlockchain(Request $request)
    {
        try {
            // Get all organizations that have a DID (meaning they were processed for approval)
            $organizationsWithDIDs = Organization::whereNotNull('did')
                ->where('did', '!=', '')
                ->orderBy('verified_at', 'desc')
                ->paginate(10);

            $blockchainVerifiedOrgs = [];
            
            foreach ($organizationsWithDIDs->items() as $org) {
                try {
                    // Call the FastAPI blockchain service to get real-time data
                    $response = Http::timeout(30)
                        ->get(config('services.blockchain_service.url') . '/get_org/' . urlencode($org->did));

                    if ($response->successful()) {
                        $blockchainData = $response->json();
                        
                        // Only include if actually approved on blockchain
                        if ($blockchainData['success'] && $blockchainData['approved']) {
                            // Merge database org data with blockchain data
                            $orgData = $org->toArray();
                            $orgData['blockchain_verified'] = true;
                            $orgData['blockchain_address'] = $blockchainData['mainAddress'];
                            $orgData['blockchain_scopes'] = $blockchainData['scopes'];
                            $orgData['blockchain_check_timestamp'] = $blockchainData['timestamp'];
                            
                            $blockchainVerifiedOrgs[] = $orgData;
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to verify organization on blockchain', [
                        'org_id' => $org->id,
                        'did' => $org->did,
                        'error' => $e->getMessage()
                    ]);
                    
                    // If blockchain call fails, include the org but mark as unverified
                    $orgData = $org->toArray();
                    $orgData['blockchain_verified'] = false;
                    $orgData['blockchain_error'] = 'Failed to verify on blockchain';
                    
                    $blockchainVerifiedOrgs[] = $orgData;
                }
            }

            return response()->json([
                'success' => true,
                'data' => $blockchainVerifiedOrgs,
                'message' => 'Organizations verified from blockchain',
                'source' => 'blockchain',
                'pagination' => [
                    'current_page' => $organizationsWithDIDs->currentPage(),
                    'last_page' => $organizationsWithDIDs->lastPage(),
                    'total' => count($blockchainVerifiedOrgs),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get approved organizations from blockchain', [
                'error' => $e->getMessage()
            ]);

            // Fallback to database on error
            $organizations = Organization::where('verification_status', 'approved')
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $organizations->items(),
                'message' => 'Fallback to database due to blockchain error',
                'source' => 'database_fallback',
                'pagination' => [
                    'current_page' => $organizations->currentPage(),
                    'last_page' => $organizations->lastPage(),
                    'total' => $organizations->total(),
                ]
            ]);
        }
    }

    /**
     * Show organization details
     */
    public function showOrganization(Organization $organization)
    {
        // Parse credential scopes if they exist
        $writeScopes = [];
        $readScopes = [];
        
        if ($organization->write_scopes) {
            $writeScopes = is_string($organization->write_scopes) 
                ? json_decode($organization->write_scopes, true) 
                : $organization->write_scopes;
            // Ensure it's an array, not an object with numeric keys
            $writeScopes = is_array($writeScopes) ? array_values($writeScopes) : [];
        }
        
        if ($organization->read_scopes) {
            $readScopes = is_string($organization->read_scopes) 
                ? json_decode($organization->read_scopes, true) 
                : $organization->read_scopes;
            // Ensure it's an array, not an object with numeric keys
            $readScopes = is_array($readScopes) ? array_values($readScopes) : [];
        }

        // Get scope names for display
        $credentialConfig = config('credential_scopes');
        $orgTypeScopes = $credentialConfig[$organization->organization_type] ?? [];
        
        $writeScopeNames = [];
        $readScopeNames = [];
        
        if (isset($orgTypeScopes['write'])) {
            foreach ($writeScopes as $scope) {
                $writeScopeNames[] = $orgTypeScopes['write'][$scope] ?? $scope;
            }
        }
        
        if (isset($orgTypeScopes['read'])) {
            foreach ($readScopes as $scope) {
                $readScopeNames[] = $orgTypeScopes['read'][$scope] ?? $scope;
            }
        }

        return response()->json([
            'success' => true,
            'organization' => $organization,
            'write_scopes' => $writeScopeNames,
            'read_scopes' => $readScopeNames,
            'smart_contract_scopes' => CredentialScopeService::mapScopesForContract($writeScopes, $readScopes)
        ]);
    }

    /**
     * Approve organization
     */
    public function approveOrganization(Request $request, $organizationId)
    {
        // Find the organization manually to provide better error handling
        $organization = Organization::find($organizationId);
        
        if (!$organization) {
            Log::error('Organization not found for approval', [
                'organization_id' => $organizationId,
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Organization not found.'
            ], 404);
        }

        // Log the incoming request for debugging
        Log::info('Approval request received', [
            'organization_id' => $organization->id,
            'organization_name' => $organization->legal_name,
            'request_data' => $request->all()
        ]);

        $request->validate([
            'remarks' => 'nullable|string|max:1000',
            'did_prefix' => 'nullable|string|max:50',
        ]);

        try {
            // Generate W3C compliant SarvOne DID
            $sarvoneDID = $this->generateW3CDID($organization, $request->did_prefix);

            // Prepare credential scopes for smart contract
            $writeScopes = is_array($organization->write_scopes) ? $organization->write_scopes : (json_decode($organization->write_scopes, true) ?? []);
            $readScopes = is_array($organization->read_scopes) ? $organization->read_scopes : (json_decode($organization->read_scopes, true) ?? []);
            
            // Ensure scopes are arrays, not objects with numeric keys
            $writeScopes = is_array($writeScopes) ? array_values($writeScopes) : [];
            $readScopes = is_array($readScopes) ? array_values($readScopes) : [];
            
            $contractScopes = CredentialScopeService::mapScopesForContract($writeScopes, $readScopes);
            
            // Ensure contract scopes is a proper array
            $contractScopes = array_values($contractScopes);
            
            // Calculate dynamic gas limit based on number of scopes
            $finalGasLimit = $this->calculateGasLimit($contractScopes, 'approve_org');
            
            // Log the data being sent to blockchain for debugging
            Log::info('Sending data to blockchain service', [
                'orgDID' => $sarvoneDID,
                'orgAddress' => $organization->wallet_address,
                'scopes' => $contractScopes,
                'scopes_count' => count($contractScopes),
                'scopes_json' => json_encode($contractScopes),
                'scopes_type' => gettype($contractScopes),
                'scopes_is_array' => is_array($contractScopes),
                'gas_limit' => $finalGasLimit
            ]);

            // Call FastAPI blockchain service with dynamic gas limit
            $response = Http::timeout(config('services.blockchain_service.timeout', 30))
                ->post(config('services.blockchain_service.url', 'http://localhost:8003') . '/approve_org', [
                    'orgDID' => $sarvoneDID,
                    'orgAddress' => $organization->wallet_address,
                    'scopes' => $contractScopes,
                    'gas_limit' => $finalGasLimit
                ]);

            if (!$response->successful()) {
                throw new \Exception('Blockchain service error: ' . $response->body());
            }

            $blockchainResult = $response->json();
            
            if (!$blockchainResult['success']) {
                throw new \Exception('Blockchain approval failed: ' . ($blockchainResult['error'] ?? 'Unknown error'));
            }

            // Generate API key for the organization
            $apiKey = $organization->generateApiKey();

            // Update organization in database
            $organization->update([
                'verification_status' => 'approved',
                'verified_at' => now(),
                'verification_notes' => $request->remarks,
                'blockchain_tx_hash' => $blockchainResult['tx_hash'],
                'did' => $sarvoneDID,
                'trust_score' => 100
            ]);

            // Send SMS notification to organization
            $this->sendApprovalSMS($organization, $contractScopes);

            Log::info('Organization approved successfully via FastAPI', [
                'organization_id' => $organization->id,
                'did' => $sarvoneDID,
                'wallet_address' => $organization->wallet_address,
                'contract_scopes' => $contractScopes,
                'tx_hash' => $blockchainResult['tx_hash'],
                'admin_remarks' => $request->remarks,
                'explorer_url' => $blockchainResult['explorer_url'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Organization approved successfully on blockchain!',
                'did' => $sarvoneDID,
                'tx_hash' => $blockchainResult['tx_hash'],
                'explorer_url' => $blockchainResult['explorer_url'] ?? null,
                'block_number' => $blockchainResult['block_number'] ?? null,
                'gas_used' => $blockchainResult['gas_used'] ?? null
            ]);

        } catch (\Exception $e) {
            Log::error('FastAPI blockchain service call failed', [
                'error' => $e->getMessage(),
                'organization_id' => $organization->id ?? null,
                'did' => $sarvoneDID ?? null,
                'wallet_address' => $organization->wallet_address ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Blockchain approval failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject organization
     */
    public function rejectOrganization(Request $request, Organization $organization)
    {
        $request->validate([
            'remarks' => 'required|string|max:1000',
        ]);

        try {
            $organization->update([
                'verification_status' => 'rejected',
                'verification_notes' => $request->remarks,
            ]);

            Log::info('Organization rejected', [
                'organization_id' => $organization->id,
                'admin_remarks' => $request->remarks
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Organization rejected successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Organization rejection failed', [
                'organization_id' => $organization->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Rejection failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Get organization type statistics
     */
    public function getOrganizationTypeStats()
    {
        $typeStats = Organization::selectRaw('organization_type, verification_status, COUNT(*) as count')
            ->groupBy('organization_type', 'verification_status')
            ->get()
            ->groupBy('organization_type');

        return response()->json([
            'success' => true,
            'data' => $typeStats
        ]);
    }

    /**
     * Generate W3C compliant DID for SarvOne
     * Format: did:sarvone:{method-specific-identifier}
     * Method-specific identifier: {org-type}:{3-word-identifier}:{timestamp}:{checksum}
     */
    private function generateW3CDID(Organization $organization, $userPrefix = null)
    {
        // W3C DID Method: sarvone
        $method = 'sarvone';
        
        // Get organization type abbreviation
        $orgTypeMap = [
            'bank' => 'bnk',
            'company' => 'cmp',
            'school' => 'scl',
            'college' => 'col',
            'hospital' => 'hsp',
            'government' => 'gov',
            'ngo' => 'ngo',
            'fintech' => 'fin',
            'scholarship_board' => 'sbd',
            'welfare_board' => 'wbd',
            'scheme_partner' => 'spn',
            'hr_agency' => 'hra',
            'training_provider' => 'trp',
            'other' => 'oth'
        ];
        
        $orgType = $orgTypeMap[$organization->organization_type] ?? 'org';
        
        // Generate 3-word identifier (either from user input or company name)
        if ($userPrefix) {
            // Clean user input: remove spaces, special chars, lowercase, max 15 chars
            $identifier = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $userPrefix));
            $identifier = substr($identifier, 0, 15);
        } else {
            // Generate from company name: take first 3 words, first 3 chars each
            $words = explode(' ', strtolower($organization->legal_name));
            $identifier = '';
            for ($i = 0; $i < min(3, count($words)); $i++) {
                $identifier .= substr(preg_replace('/[^a-zA-Z0-9]/', '', $words[$i]), 0, 3);
            }
            // Pad if less than 9 characters
            $identifier = str_pad($identifier, 9, '0');
        }
        
        // Add timestamp (Unix timestamp in base36 for compactness)
        $timestamp = base_convert(time(), 10, 36);
        
        // Generate unique sequence number for this organization type
        $sequence = Organization::where('organization_type', $organization->organization_type)
                              ->where('verification_status', 'approved')
                              ->count() + 1;
        $sequenceStr = str_pad($sequence, 3, '0', STR_PAD_LEFT);
        
        // Create method-specific identifier
        $methodSpecificId = "{$orgType}:{$identifier}:{$timestamp}:{$sequenceStr}";
        
        // Generate checksum (last 4 chars of SHA256 hash)
        $checksum = substr(hash('sha256', $methodSpecificId . $organization->id), -4);
        
        // Final DID: did:sarvone:orgtype:identifier:timestamp:sequence:checksum
        $did = "did:{$method}:{$orgType}:{$identifier}:{$timestamp}:{$sequenceStr}:{$checksum}";
        
        // Log DID generation for audit
        Log::info('W3C DID generated', [
            'organization_id' => $organization->id,
            'organization_name' => $organization->legal_name,
            'organization_type' => $organization->organization_type,
            'user_prefix' => $userPrefix,
            'generated_identifier' => $identifier,
            'did' => $did
        ]);
        
        return $did;
    }

    /**
     * Calculate dynamic gas limit based on transaction complexity
     */
    private function calculateGasLimit(array $scopes, string $operation = 'approve_org'): int
    {
        $baseGasLimits = [
            'approve_org' => 300000, // Increased base gas
            'issue_credential' => 200000,
            'verify_credential' => 150000
        ];
        
        $baseGasLimit = $baseGasLimits[$operation] ?? 300000;
        $gasPerScope = 15000; // Increased gas per scope (was 5000)
        $dynamicGasLimit = $baseGasLimit + (count($scopes) * $gasPerScope);
        
        // Cap the gas limit to prevent excessive costs
        $maxGasLimit = 2000000; // Increased max gas limit
        $finalGasLimit = min($dynamicGasLimit, $maxGasLimit);
        
        Log::info('Gas limit calculation', [
            'operation' => $operation,
            'scopes_count' => count($scopes),
            'base_gas' => $baseGasLimit,
            'gas_per_scope' => $gasPerScope,
            'calculated_gas' => $dynamicGasLimit,
            'final_gas_limit' => $finalGasLimit
        ]);
        
        return $finalGasLimit;
    }

    /**
     * Send approval SMS to organization
     */
    private function sendApprovalSMS($organization, $scopes): void
    {
        try {
            if ($organization->official_phone) {
                $smsService = app(TwilioSMSService::class);
                $smsResult = $smsService->sendOrgApprovalNotification(
                    $organization->official_phone,
                    $organization->legal_name,
                    $scopes
                );
                
                if ($smsResult['success']) {
                    Log::info('Organization approval SMS sent successfully', [
                        'organization_phone' => $organization->official_phone,
                        'organization_name' => $organization->legal_name,
                        'message_sid' => $smsResult['message_sid'] ?? 'unknown'
                    ]);
                } else {
                    Log::warning('Organization approval SMS failed', [
                        'organization_phone' => $organization->official_phone,
                        'organization_name' => $organization->legal_name,
                        'error' => $smsResult['error'] ?? 'unknown error'
                    ]);
                }
            } else {
                Log::info('Organization approval SMS skipped - no phone number', [
                    'organization_id' => $organization->id,
                    'organization_name' => $organization->legal_name
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Organization approval SMS error', [
                'error' => $e->getMessage(),
                'organization_id' => $organization->id
            ]);
            // Don't throw - SMS failure shouldn't break approval
        }
    }

    /**
     * Show government document simulation page
     */
    public function showSimulateDocuments()
    {
        return view('admin.simulate-documents');
    }

    /**
     * Lookup user by DID for simulation
     */
    public function lookupUserForSimulation(Request $request)
    {
        $request->validate([
            'user_did' => 'required|string'
        ]);

        try {
            // Find user by DID
            $user = \App\Models\User::where('did', $request->user_did)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found with this DID'
                ]);
            }

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'did' => $user->did,
                    'phone' => $user->phone
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Error looking up user for simulation: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error looking up user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Issue simulated government documents
     */
    public function issueSimulatedDocuments(Request $request)
    {
        $request->validate([
            'user_did' => 'required|string',
            'user_name' => 'required|string',
            'documents' => 'required|array'
        ]);

        try {
            $userDid = $request->user_did;
            $userName = $request->user_name;
            $selectedDocuments = $request->documents;

            // Find the user
            $user = User::where('did', $userDid)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Find the government organization in database (should be registered through normal process)
            $govOrganization = Organization::where('organization_type', 'government')
                ->where('verification_status', 'approved')
                ->first();
            
            if (!$govOrganization) {
                return response()->json([
                    'success' => false,
                    'message' => 'No approved government organization found. Please register a government organization first.'
                ], 404);
            }

            // Use the organization's wallet address and private key
            $govPrivateKey = env('GOV_TEST_PRIVATE_KEY', '90adca3d565759964e304c0b08ef721092e7390886a7986bec51c23d83d57007');
            
            // Log the organization details for debugging
            Log::info("Using government organization for simulation", [
                'org_id' => $govOrganization->id,
                'org_name' => $govOrganization->legal_name,
                'org_did' => $govOrganization->did,
                'wallet_address' => $govOrganization->wallet_address,
                'verification_status' => $govOrganization->verification_status
            ]);

            // Pre-defined government documents with realistic data
            $allDocuments = [
                'pan_card' => [
                    'credential_type' => 'pan_card',
                    'data' => [
                        'name' => $userName,
                        'pan_number' => 'ABCDE' . strtoupper(substr(md5($userDid), 0, 5)),
                        'date_of_birth' => '1990-01-15',
                        'father_name' => 'Father of ' . $userName,
                        'issued_date' => now()->format('Y-m-d'),
                        'issuing_authority' => 'Income Tax Department, Government of India'
                    ]
                ],
                'aadhaar_card' => [
                    'credential_type' => 'aadhaar_card',
                    'data' => [
                        'name' => $userName,
                        'aadhaar_number' => '1234' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT) . '5678',
                        'date_of_birth' => '1990-01-15',
                        'gender' => 'Male',
                        'address' => '123 Main Street, City, State - 123456',
                        'issued_date' => now()->format('Y-m-d'),
                        'issuing_authority' => 'Unique Identification Authority of India'
                    ]
                ],
                'income_certificate' => [
                    'credential_type' => 'income_certificate',
                    'data' => [
                        'name' => $userName,
                        'annual_income' => rand(300000, 800000),
                        'income_source' => 'Salary',
                        'certificate_number' => 'INC' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                        'issued_date' => now()->format('Y-m-d'),
                        'valid_until' => now()->addYear()->format('Y-m-d'),
                        'issuing_authority' => 'Tehsildar Office, District Administration'
                    ]
                ],
                'voter_id' => [
                    'credential_type' => 'voter_id',
                    'data' => [
                        'name' => $userName,
                        'voter_id_number' => 'ABC' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                        'constituency' => 'Sample Constituency',
                        'part_number' => rand(1, 999),
                        'serial_number' => rand(1, 9999),
                        'issued_date' => now()->format('Y-m-d'),
                        'issuing_authority' => 'Election Commission of India'
                    ]
                ],
                'driving_license' => [
                    'credential_type' => 'driving_license',
                    'data' => [
                        'name' => $userName,
                        'license_number' => 'DL' . strtoupper(substr(md5($userDid), 0, 2)) . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                        'date_of_birth' => '1990-01-15',
                        'valid_from' => now()->format('Y-m-d'),
                        'valid_until' => now()->addYears(20)->format('Y-m-d'),
                        'vehicle_classes' => ['LMV', 'MCWG'],
                        'issued_date' => now()->format('Y-m-d'),
                        'issuing_authority' => 'RTO'
                    ]
                ]
            ];

            $issuedCredentials = [];
            $credentialService = new CredentialService();

            // Only process selected documents
            foreach ($selectedDocuments as $docType) {
                if (!isset($allDocuments[$docType])) {
                    $issuedCredentials[] = [
                        'type' => $docType,
                        'success' => false,
                        'error' => 'Document type not supported'
                    ];
                    continue;
                }

                $document = $allDocuments[$docType];
                
                try {
                    Log::info("Issuing government document", [
                        'document_type' => $docType,
                        'user_did' => $userDid,
                        'user_name' => $userName
                    ]);

                    // Use the same CredentialService as regular VC issuance
                    $result = $credentialService->issueCredential(
                        $govOrganization,
                        $user,
                        $document['credential_type'],
                        $document['data'],
                        $govPrivateKey
                    );

                    if ($result['success']) {
                        Log::info("Government document issued successfully", [
                            'document_type' => $docType,
                            'credential_id' => $result['credential_id'],
                            'tx_hash' => $result['transaction_hash'],
                            'ipfs_cid' => $result['ipfs_cid']
                        ]);
                        
                        $issuedCredentials[] = [
                            'type' => $document['credential_type'],
                            'success' => true,
                            'credential_id' => $result['credential_id'],
                            'tx_hash' => $result['transaction_hash'],
                            'ipfs_cid' => $result['ipfs_cid'],
                            'ipfs_cid_hash' => $result['ipfs_cid_hash']
                        ];
                    } else {
                        Log::error("Government document issuance failed", [
                            'document_type' => $docType,
                            'error' => $result['message'] ?? 'Unknown error'
                        ]);
                        
                        $issuedCredentials[] = [
                            'type' => $document['credential_type'],
                            'success' => false,
                            'error' => $result['message'] ?? 'Unknown error'
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error("Exception issuing government document", [
                        'document_type' => $docType,
                        'error' => $e->getMessage()
                    ]);
                    
                    $issuedCredentials[] = [
                        'type' => $document['credential_type'],
                        'success' => false,
                        'error' => 'Exception: ' . $e->getMessage()
                    ];
                }
            }

            Log::info("Government document simulation completed", [
                'user_did' => $userDid,
                'user_name' => $userName,
                'total_documents' => count($selectedDocuments),
                'successful' => count(array_filter($issuedCredentials, fn($c) => $c['success'])),
                'failed' => count(array_filter($issuedCredentials, fn($c) => !$c['success']))
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Government documents issued successfully',
                'credentials' => $issuedCredentials
            ]);

        } catch (\Exception $e) {
            Log::error("Error in issueSimulatedDocuments: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error issuing documents: ' . $e->getMessage()
            ], 500);
        }
    }
} 