<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\VerifiableCredential;
use App\Models\AccessLog;
use Endroid\QrCode\Writer\PngWriter;

class DashboardController extends Controller
{
    /**
     * Show the user dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return view('dashboard');
        }
        
        if (!$user->isVerified()) {
            return view('dashboard', compact('user'));
        }

        // Get VCs for the user
        $vcs = VerifiableCredential::where('subject_did', $user->did)
            ->with('issuer')
            ->orderBy('issued_at', 'desc')
            ->get();

        // Group VCs by category
        $vcsByCategory = $this->groupVCsByCategory($vcs);

        return view('dashboard', compact('user', 'vcs', 'vcsByCategory'));
    }

    /**
     * Get VCs for mobile API
     */
    public function getVCs(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'User not verified'
            ], 403);
        }

        // Get VCs from database for this specific user
        $dbVCs = VerifiableCredential::where('subject_did', $user->did)
            ->where('status', 'active')
            ->orderBy('issued_at', 'desc')
            ->get();

        // Convert to frontend format
        $vcs = [];
        foreach ($dbVCs as $dbVC) {
            $vcData = $this->formatVCForFrontend($dbVC);
            if ($vcData) {
                $vcs[] = $vcData;
            }
        }

        $vcsByCategory = $this->groupVCsByCategory($vcs);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'vcs' => $vcs,
                'vcs_by_category' => $vcsByCategory,
                'total_vcs' => count($vcs),
                'last_sync' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Verify a specific VC against blockchain
     */
    public function verifyVCOnBlockchain(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'User not verified'
            ], 403);
        }

        $vcId = $request->input('vc_id');
        
        if (!$vcId) {
            return response()->json([
                'success' => false,
                'message' => 'VC ID is required'
            ], 400);
        }

        try {
            // Get VC from database
            $vc = VerifiableCredential::where('vc_id', $vcId)
                ->where('subject_did', $user->did)
                ->first();

            if (!$vc) {
                return response()->json([
                    'success' => false,
                    'message' => 'VC not found'
                ], 404);
            }

            // Verify against blockchain
            $verificationResult = $this->verifySingleVCOnBlockchain($vc);

            // Update verification status in database
            $vc->update([
                'blockchain_verified' => $verificationResult['verified'],
                'last_blockchain_sync' => now()
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'vc_id' => $vcId,
                    'verified' => $verificationResult['verified'],
                    'verification_details' => $verificationResult['details'],
                    'last_verified' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error verifying VC on blockchain', [
                'error' => $e->getMessage(),
                'vc_id' => $vcId,
                'user_did' => $user->did
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to verify VC: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get VCs from blockchain and IPFS
     */
    public function getVCsFromBlockchain(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'User not verified'
            ], 403);
        }

        try {
            \Illuminate\Support\Facades\Log::info('Getting VCs for user', [
                'user_did' => $user->did,
                'user_did_hash' => hash('sha256', $user->did)
            ]);

            // First, get VCs from database (fast, always available)
            $dbVCs = VerifiableCredential::where('status', 'active')
                ->orderBy('issued_at', 'desc')
                ->get();

            \Illuminate\Support\Facades\Log::info('Retrieved VCs from database', [
                'total_db_vcs' => $dbVCs->count()
            ]);

            // Convert database VCs to frontend format
            $vcs = [];
            foreach ($dbVCs as $dbVC) {
                $vcData = $this->formatVCForFrontend($dbVC);
                if ($vcData) {
                    $vcs[] = $vcData;
                }
            }

            // Now sync with blockchain (background process)
            $syncResult = $this->syncWithBlockchain($user, $vcs);

            \Illuminate\Support\Facades\Log::info('Returning VCs to frontend', [
                'total_vcs' => count($vcs),
                'sync_result' => $syncResult,
                'user_did' => $user->did
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'vcs' => $vcs,
                    'total_vcs' => count($vcs),
                    'user_did_hash' => hash('sha256', $user->did),
                    'sync_result' => $syncResult
                ]
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error retrieving VCs', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_did' => $user->did
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve VCs from blockchain: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process a single VC from blockchain data
     */
    private function processBlockchainVC($blockchainVC)
    {
        try {
            // Extract IPFS CID from the hash
            $ipfsCid = $this->extractIPFSCID($blockchainVC['hash']);
            
            if (!$ipfsCid) {
                \Illuminate\Support\Facades\Log::warning('Could not extract IPFS CID from hash', [
                    'hash' => $blockchainVC['hash']
                ]);
                return null;
            }

            // Retrieve VC data from IPFS
            $vcJson = $this->retrieveFromIPFS($ipfsCid);
            
            if (!$vcJson) {
                \Illuminate\Support\Facades\Log::warning('Could not retrieve VC from IPFS', [
                    'ipfs_cid' => $ipfsCid
                ]);
                return null;
            }

            // Try to get transaction hash from database
            $transactionHash = null;
            $blockchainUrl = null;
            
            // Look up the VC in our database to get the transaction hash
            $storedVC = VerifiableCredential::where('vc_hash', $blockchainVC['hash'])
                ->orWhere('vc_hash', substr($blockchainVC['hash'], 2)) // Try without 0x prefix
                ->first();
            
            if ($storedVC && $storedVC->transaction_hash) {
                $transactionHash = $storedVC->transaction_hash;
                $blockchainUrl = "https://amoy.polygonscan.com/tx/{$transactionHash}";
            }
            
            // Parse and structure the VC data
            $vcData = [
                'id' => $blockchainVC['hash'],
                'vc_id' => $vcJson['id'] ?? 'unknown',
                'vc_type' => $this->extractVCType($vcJson),
                'issuer_did' => $vcJson['issuer']['id'] ?? 'unknown',
                'issuer_name' => $vcJson['issuer']['name'] ?? 'Unknown Issuer',
                'issuer_type' => $vcJson['issuer']['type'] ?? 'unknown',
                'recipient_did' => $vcJson['credentialSubject']['id'] ?? 'unknown',
                'recipient_name' => $vcJson['credentialSubject']['name'] ?? 'Unknown',
                'issued_at' => $vcJson['issuanceDate'] ?? now()->toISOString(),
                'expires_at' => $vcJson['expirationDate'] ?? null,
                'status' => $blockchainVC['is_active'] && !$blockchainVC['revoked'] ? 'active' : 'revoked',
                'ipfs_cid' => $ipfsCid,
                'ipfs_url' => "https://ipfs.io/ipfs/{$ipfsCid}",
                'blockchain_hash' => $blockchainVC['hash'],
                'blockchain_tx_hash' => $transactionHash,
                'blockchain_url' => $blockchainUrl,
                'credential_data' => $this->extractCredentialData($vcJson),
                'raw_vc_json' => $vcJson
            ];

            return $vcData;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to process blockchain VC', [
                'error' => $e->getMessage(),
                'blockchain_vc' => $blockchainVC
            ]);
            return null;
        }
    }

    /**
     * Format VC from database for frontend display
     */
    private function formatVCForFrontend($dbVC)
    {
        try {
            // Safely decode metadata
            $metadata = [];
            if ($dbVC->metadata) {
                if (is_string($dbVC->metadata)) {
                    $metadata = json_decode($dbVC->metadata, true) ?: [];
                } elseif (is_array($dbVC->metadata)) {
                    $metadata = $dbVC->metadata;
                }
            }
            
            // Get credential data from metadata or fallback to database field
            $credentialData = [];
            if (isset($metadata['credential_data'])) {
                $credentialData = is_array($metadata['credential_data']) ? $metadata['credential_data'] : [];
            } elseif ($dbVC->credential_data) {
                if (is_string($dbVC->credential_data)) {
                    $credentialData = json_decode($dbVC->credential_data, true) ?: [];
                } elseif (is_array($dbVC->credential_data)) {
                    $credentialData = $dbVC->credential_data;
                }
            }
            
            // Get issuer information
            $issuerName = $dbVC->issuer_name;
            if (!$issuerName || $issuerName === 'Unknown Issuer') {
                // Try to get from organization table
                $organization = \App\Models\Organization::where('did', $dbVC->issuer_did)->first();
                $issuerName = $organization ? $organization->legal_name : 'Unknown Issuer';
            }
            
            $vcData = [
                'id' => $dbVC->vc_id ?? $dbVC->id,
                'vc_id' => $dbVC->vc_id ?? $dbVC->id,
                'vc_type' => $dbVC->vc_type,
                'issuer_did' => $dbVC->issuer_did,
                'issuer_name' => $issuerName,
                'issuer_type' => 'organization',
                'recipient_did' => $dbVC->subject_did,
                'recipient_name' => $dbVC->subject_name ?? 'Unknown',
                'issued_at' => $dbVC->issued_at ? $dbVC->issued_at->toISOString() : now()->toISOString(),
                'expires_at' => $dbVC->expires_at ? $dbVC->expires_at->toISOString() : null,
                'status' => $dbVC->status === 'active' ? 'active' : 'revoked',
                'ipfs_cid' => $dbVC->ipfs_hash,
                'ipfs_url' => $dbVC->ipfs_hash ? "https://ipfs.io/ipfs/{$dbVC->ipfs_hash}" : null,
                'blockchain_hash' => $dbVC->blockchain_hash,
                'blockchain_tx_hash' => $dbVC->blockchain_tx_hash,
                'blockchain_url' => $dbVC->blockchain_tx_hash ? "https://amoy.polygonscan.com/tx/{$dbVC->blockchain_tx_hash}" : null,
                'credential_data' => $credentialData,
                'blockchain_verified' => $dbVC->blockchain_verified ?? false,
                'last_blockchain_sync' => $dbVC->last_blockchain_sync ? (is_string($dbVC->last_blockchain_sync) ? $dbVC->last_blockchain_sync : $dbVC->last_blockchain_sync->toISOString()) : null,
                'raw_vc_json' => $metadata['vc_json'] ?? []
            ];

            return $vcData;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to format VC for frontend', [
                'error' => $e->getMessage(),
                'vc_id' => $dbVC->vc_id ?? $dbVC->id ?? 'unknown'
            ]);
            return null;
        }
    }

    /**
     * Verify a single VC against blockchain
     */
    private function verifySingleVCOnBlockchain($vc)
    {
        try {
            $blockchainServiceUrl = env('BLOCKCHAIN_SERVICE_URL', 'http://localhost:8003');
            
            // Call blockchain service to get VC details
            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->get($blockchainServiceUrl . '/get_vc_details/' . $vc->blockchain_hash);
            
            if (!$response->successful()) {
                return [
                    'verified' => false,
                    'details' => 'Blockchain service unavailable'
                ];
            }

            $blockchainData = $response->json();
            
            if (!$blockchainData['success']) {
                return [
                    'verified' => false,
                    'details' => 'VC not found on blockchain'
                ];
            }

            $blockchainVC = $blockchainData['vc'];
            
            // Compare database data with blockchain data
            $verificationDetails = [];
            $isVerified = true;

            // Check if hash matches
            if ($blockchainVC['hash'] !== $vc->blockchain_hash) {
                $isVerified = false;
                $verificationDetails[] = 'Hash mismatch';
            }

            // Check if issuer DID matches
            if ($blockchainVC['issuer_did'] !== $vc->issuer_did) {
                $isVerified = false;
                $verificationDetails[] = 'Issuer DID mismatch';
            }

            // Check if recipient DID matches
            if ($blockchainVC['recipient_did'] !== $vc->subject_did) {
                $isVerified = false;
                $verificationDetails[] = 'Recipient DID mismatch';
            }

            // Check if credential type matches
            if ($blockchainVC['credential_type'] !== $vc->vc_type) {
                $isVerified = false;
                $verificationDetails[] = 'Credential type mismatch';
            }

            if ($isVerified) {
                $verificationDetails[] = 'All data matches blockchain';
            }

            return [
                'verified' => $isVerified,
                'details' => implode(', ', $verificationDetails),
                'blockchain_data' => $blockchainVC
            ];

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error verifying VC on blockchain', [
                'error' => $e->getMessage(),
                'vc_id' => $vc->vc_id
            ]);

            return [
                'verified' => false,
                'details' => 'Verification failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sync VCs with blockchain (background process)
     */
    private function syncWithBlockchain($user, &$vcs)
    {
        try {
            $userDidHash = hash('sha256', $user->did);
            $blockchainServiceUrl = env('BLOCKCHAIN_SERVICE_URL', 'http://localhost:8003');
            
            // Call blockchain service
            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->get($blockchainServiceUrl . '/get_user_vcs/' . $userDidHash);
            
            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Blockchain sync failed',
                    'last_sync' => now()->toISOString()
                ];
            }

            $blockchainData = $response->json();
            
            if (!$blockchainData['success'] || !isset($blockchainData['vcs'])) {
                return [
                    'success' => true,
                    'message' => 'No VCs found on blockchain',
                    'last_sync' => now()->toISOString()
                ];
            }

            // Update verification status for each VC
            $updatedCount = 0;
            foreach ($vcs as &$vc) {
                $foundOnBlockchain = false;
                foreach ($blockchainData['vcs'] as $blockchainVC) {
                    if ($blockchainVC['hash'] === $vc['blockchain_hash']) {
                        $foundOnBlockchain = true;
                        $vc['blockchain_verified'] = true;
                        $vc['last_blockchain_sync'] = now()->toISOString();
                        
                        // Update database
                        VerifiableCredential::where('id', $vc['id'])
                            ->update([
                                'blockchain_verified' => true,
                                'last_blockchain_sync' => now()
                            ]);
                        
                        $updatedCount++;
                        break;
                    }
                }
                
                if (!$foundOnBlockchain) {
                    $vc['blockchain_verified'] = false;
                }
            }

            return [
                'success' => true,
                'message' => "Synced {$updatedCount} VCs with blockchain",
                'last_sync' => now()->toISOString(),
                'blockchain_vcs_count' => count($blockchainData['vcs'])
            ];

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Blockchain sync error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Blockchain sync error: ' . $e->getMessage(),
                'last_sync' => now()->toISOString()
            ];
        }
    }

    /**
     * Extract IPFS CID from blockchain hash
     */
    private function extractIPFSCID($hash)
    {
        // The hash stored on blockchain is SHA256 of IPFS CID
        // We need to reverse this process to get the original CID
        
        \Illuminate\Support\Facades\Log::info('Extracting IPFS CID for hash', [
            'blockchain_hash' => $hash,
            'method_called' => 'extractIPFSCID'
        ]);
        
        // Remove 0x prefix if present for comparison
        $hashWithoutPrefix = $hash;
        if (strpos($hash, '0x') === 0) {
            $hashWithoutPrefix = substr($hash, 2);
        }
        
        \Illuminate\Support\Facades\Log::info('Hash processing', [
            'original_hash' => $hash,
            'hash_without_prefix' => $hashWithoutPrefix,
            'has_0x_prefix' => strpos($hash, '0x') === 0
        ]);
        
        \Illuminate\Support\Facades\Log::info('Hash comparison values', [
            'original_hash' => $hash,
            'hash_without_prefix' => $hashWithoutPrefix
        ]);
        
        // Try to get from database first (if we have stored the mapping)
        // Try both with and without 0x prefix
        $storedVC = VerifiableCredential::where('vc_hash', $hash)->first();
        if (!$storedVC) {
            $storedVC = VerifiableCredential::where('vc_hash', $hashWithoutPrefix)->first();
        }
        
        if ($storedVC && $storedVC->ipfs_cid) {
            \Illuminate\Support\Facades\Log::info('Found IPFS CID in database by exact match', [
                'blockchain_hash' => $hash,
                'ipfs_cid' => $storedVC->ipfs_cid
            ]);
            return $storedVC->ipfs_cid;
        }

        // If not found in database, try to find by calculating hash from IPFS CID
        // This is a fallback for when we have the IPFS CID but need to match it
        $allVCs = VerifiableCredential::whereNotNull('ipfs_cid')->get();
        foreach ($allVCs as $vc) {
            if ($vc->ipfs_cid) {
                // Calculate SHA-256 hash of the IPFS CID (without 0x prefix)
                $calculatedHash = hash('sha256', $vc->ipfs_cid);
                
                // Compare with both formats
                if ($calculatedHash === $hash || $calculatedHash === $hashWithoutPrefix) {
                    \Illuminate\Support\Facades\Log::info('Found IPFS CID by hash calculation', [
                        'blockchain_hash' => $hash,
                        'calculated_hash' => $calculatedHash,
                        'ipfs_cid' => $vc->ipfs_cid,
                        'method' => 'sha256'
                    ]);
                    return $vc->ipfs_cid;
                }
            }
        }

        // If still not found, try to find any VC for the current user
        $user = Auth::user();
        if ($user) {
            $userVCs = VerifiableCredential::where('recipient_user_id', $user->id)
                ->whereNotNull('ipfs_cid')
                ->get();
            
            foreach ($userVCs as $vc) {
                if ($vc->ipfs_cid) {
                    $calculatedHash = hash('sha256', $vc->ipfs_cid);
                    if ($calculatedHash === $hash || $calculatedHash === $hashWithoutPrefix) {
                        \Illuminate\Support\Facades\Log::info('Found IPFS CID for current user', [
                            'blockchain_hash' => $hash,
                            'calculated_hash' => $calculatedHash,
                            'ipfs_cid' => $vc->ipfs_cid,
                            'user_id' => $user->id
                        ]);
                        return $vc->ipfs_cid;
                    }
                }
            }
        }

        \Illuminate\Support\Facades\Log::warning('Could not find IPFS CID for blockchain hash', [
            'blockchain_hash' => $hash,
            'hash_without_prefix' => $hashWithoutPrefix,
            'total_vcs_checked' => $allVCs->count()
        ]);

        // For now, return null - in production you'd need a proper mapping
        return null;
    }

    /**
     * Retrieve VC data from IPFS
     */
    private function retrieveFromIPFS($cid)
    {
        try {
            $gatewayUrl = "https://ipfs.io/ipfs/{$cid}";
            
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get($gatewayUrl);
            
            if ($response->successful()) {
                $content = $response->body();
                $jsonData = json_decode($content, true);
                
                if ($jsonData !== null) {
                    return $jsonData;
                }
            }
            
            return null;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to retrieve from IPFS', [
                'cid' => $cid,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Extract VC type from VC JSON
     */
    private function extractVCType($vcJson)
    {
        if (isset($vcJson['type']) && is_array($vcJson['type'])) {
            foreach ($vcJson['type'] as $type) {
                if ($type !== 'VerifiableCredential') {
                    return strtolower(str_replace('Credential', '', $type));
                }
            }
        }
        return 'unknown';
    }

    /**
     * Extract credential data from VC JSON
     */
    private function extractCredentialData($vcJson)
    {
        $data = [];
        
        if (isset($vcJson['credentialSubject'])) {
            $subject = $vcJson['credentialSubject'];
            
            // Extract basic subject info
            if (isset($subject['name'])) $data['name'] = $subject['name'];
            if (isset($subject['email'])) $data['email'] = $subject['email'];
            if (isset($subject['phone'])) $data['phone'] = $subject['phone'];
            
            // Extract credential-specific data
            foreach ($subject as $key => $value) {
                if (!in_array($key, ['id', 'name', 'email', 'phone']) && !is_array($value)) {
                    $data[$key] = $value;
                } elseif (is_array($value)) {
                    $data[$key] = $value;
                }
            }
        }
        
        return $data;
    }

    /**
     * Group VCs by category based on issuer type and VC type
     */
    private function groupVCsByCategory($vcs)
    {
        $categories = [
            'bank' => [],
            'education' => [],
            'employment' => [],
            'healthcare' => [],
            'government' => [],
            'other' => []
        ];

        foreach ($vcs as $vc) {
            $category = $this->determineVCCategory($vc);
            $categories[$category][] = $vc;
        }

        return array_filter($categories, function($categoryVCs) {
            return !empty($categoryVCs);
        });
    }

    /**
     * Determine VC category based on issuer type and VC type
     */
    private function determineVCCategory($vc)
    {
        // Handle both object and array formats
        $vcType = '';
        $issuerType = '';
        
        if (is_array($vc)) {
            $vcType = strtolower($vc['vc_type'] ?? '');
            $issuerType = strtolower($vc['issuer_type'] ?? '');
        } else {
            $vcType = strtolower($vc->vc_type ?? '');
            $issuerType = strtolower($vc->issuer_type ?? '');
        }
        
        // Check issuer type first
        if ($issuerType) {
            switch ($issuerType) {
                case 'bank':
                    return 'bank';
                case 'college':
                    return 'education';
                case 'employer':
                    return 'employment';
                case 'hospital':
                    return 'healthcare';
                case 'government':
                    return 'government';
            }
        }

        // Check VC type
        if (strpos($vcType, 'bank') !== false || strpos($vcType, 'loan') !== false || strpos($vcType, 'credit') !== false || strpos($vcType, 'account') !== false) {
            return 'bank';
        } elseif (strpos($vcType, 'education') !== false || strpos($vcType, 'degree') !== false || strpos($vcType, 'certificate') !== false || strpos($vcType, 'marksheet') !== false) {
            return 'education';
        } elseif (strpos($vcType, 'employment') !== false || strpos($vcType, 'job') !== false || strpos($vcType, 'work') !== false || strpos($vcType, 'salary') !== false) {
            return 'employment';
        } elseif (strpos($vcType, 'health') !== false || strpos($vcType, 'medical') !== false) {
            return 'healthcare';
        } elseif (strpos($vcType, 'government') !== false || strpos($vcType, 'license') !== false) {
            return 'government';
        }

        return 'other';
    }

    /**
     * Get category display information
     */
    private function getCategoryInfo($category)
    {
        $categoryInfo = [
            'bank' => [
                'name' => 'Banking',
                'icon' => 'fa-university',
                'color' => 'blue',
                'description' => 'Bank statements, loans, credit reports'
            ],
            'education' => [
                'name' => 'Education',
                'icon' => 'fa-graduation-cap',
                'color' => 'green',
                'description' => 'Degrees, certificates, transcripts'
            ],
            'employment' => [
                'name' => 'Employment',
                'icon' => 'fa-briefcase',
                'color' => 'purple',
                'description' => 'Job offers, employment history'
            ],
            'healthcare' => [
                'name' => 'Healthcare',
                'icon' => 'fa-heartbeat',
                'color' => 'red',
                'description' => 'Medical records, prescriptions'
            ],
            'government' => [
                'name' => 'Government',
                'icon' => 'fa-landmark',
                'color' => 'yellow',
                'description' => 'Licenses, permits, certifications'
            ],
            'other' => [
                'name' => 'Other',
                'icon' => 'fa-file-alt',
                'color' => 'gray',
                'description' => 'Miscellaneous credentials'
            ]
        ];

        return $categoryInfo[$category] ?? $categoryInfo['other'];
    }

    /**
     * Show access history for the user
     */
    public function accessHistory()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Get credential access logs for this user using Eloquent model
        $accessLogs = \App\Models\AccessLog::where('user_did', $user->did)
            ->with(['organization', 'accessFlags'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('access-history', compact('user', 'accessLogs'));
    }

    /**
     * Get user's access logs via API
     */
    public function getUserAccessLogs(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $logs = \DB::table('credential_access_logs')
                ->where('user_did', $user->did)
                ->orderBy('created_at', 'desc')
                ->limit(50) // Limit to recent 50 logs
                ->get();

            // Convert string dates to Carbon objects
            $logs->transform(function ($log) {
                $log->created_at = \Carbon\Carbon::parse($log->created_at);
                $log->updated_at = \Carbon\Carbon::parse($log->updated_at);
                return $log;
            });

            return response()->json([
                'success' => true,
                'logs' => $logs,
                'total_count' => $logs->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to fetch user access logs', [
                'user_id' => $user->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch access logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate QR code for the user's DID
     */
    public function didQrCode()
    {
        $user = auth()->user();
        if (!$user || !$user->did) {
            abort(404);
        }

        // Check if client wants JSON or image
        $acceptHeader = request()->header('Accept');
        $wantsJson = request()->has('format') && request()->get('format') === 'json';
        
        if ($wantsJson || strpos($acceptHeader, 'application/json') !== false) {
            return response()->json([
                'did' => $user->did,
                'success' => true
            ]);
        }

        // Generate QR code image using Endroid QR Code library
        try {
            $size = request()->get('size') === 'small' ? 100 : 300;
            $margin = request()->get('size') === 'small' ? 2 : 10;
            
            $qrCode = new \Endroid\QrCode\QrCode($user->did);
            $writer = new \Endroid\QrCode\Writer\PngWriter();
            $result = $writer->write($qrCode);

            return response($result->getString())
                ->header('Content-Type', $result->getMimeType())
                ->header('Cache-Control', 'public, max-age=3600'); // Cache for 1 hour
        } catch (\Exception $e) {
            \Log::error('QR Code generation failed: ' . $e->getMessage());
            
            // Fallback: Generate QR code using Google Charts API
            try {
                $did = urlencode($user->did);
                $qrUrl = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl={$did}&choe=UTF-8";
                
                $qrImage = file_get_contents($qrUrl);
                if ($qrImage !== false) {
                    return response($qrImage)
                        ->header('Content-Type', 'image/png')
                        ->header('Cache-Control', 'public, max-age=3600'); // Cache for 1 hour
                }
            } catch (\Exception $fallbackError) {
                \Log::error('Fallback QR generation also failed: ' . $fallbackError->getMessage());
            }
            
            // Final fallback: return error
            return response('QR Code generation failed', 500);
        }
    }

    /**
     * Show data access control page
     */
    public function dataAccessControl()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Get organization types and their default settings
        $organizationTypes = \App\Models\UserDataAccessPreference::getOrganizationTypes();
        $availableDataTypes = \App\Models\UserDataAccessPreference::getAvailableDataTypes();

        // Get user's current preferences
        $userPreferences = [];
        foreach ($organizationTypes as $type => $config) {
            $preference = $user->getDataAccessPreference($type);
            $userPreferences[$type] = $preference;
        }

        return view('data-access-control', compact('user', 'organizationTypes', 'availableDataTypes', 'userPreferences'));
    }

    /**
     * Update data access control preferences
     */
    public function updateDataAccessControl(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'preferences' => 'required|array',
            'preferences.*.organization_type' => 'required|string',
            'preferences.*.allowed_data_types' => 'required|array',
            'preferences.*.is_active' => 'boolean'
        ]);

        foreach ($request->preferences as $pref) {
            $organizationType = $pref['organization_type'];
            $allowedDataTypes = $pref['allowed_data_types'] ?? [];
            $isActive = $pref['is_active'] ?? true;

            // Get the organization type configuration
            $orgConfig = \App\Models\UserDataAccessPreference::getOrganizationTypes()[$organizationType] ?? null;
            
            if ($orgConfig) {
                $mandatoryTypes = $orgConfig['mandatory'];
                
                // Ensure mandatory types are always included
                $finalAllowedTypes = array_unique(array_merge($allowedDataTypes, $mandatoryTypes));

                $user->dataAccessPreferences()->updateOrCreate(
                    ['organization_type' => $organizationType],
                    [
                        'allowed_data_types' => $finalAllowedTypes,
                        'mandatory_data_types' => $mandatoryTypes,
                        'is_active' => $isActive
                    ]
                );
            }
        }

        return redirect()->route('data-access-control')->with('success', 'Data access preferences updated successfully!');
    }

    /**
     * Show the opportunity hub page
     */
    public function opportunityHub()
    {
        $user = Auth::user();
        
        // Sample opportunities data - you can later fetch from database
        $opportunities = [
            [
                'id' => 1,
                'title' => 'Personal Loan',
                'description' => 'Get instant personal loans up to ₹5 lakhs with minimal documentation',
                'interest_rate' => '12% p.a.',
                'amount_range' => '₹50,000 - ₹5,00,000',
                'processing_time' => '24 hours',
                'provider' => 'SarvOne Finance',
                'category' => 'Financial Services',
                'icon' => 'fas fa-money-bill-wave',
                'color' => 'blue'
            ],
            [
                'id' => 2,
                'title' => 'Health Insurance',
                'description' => 'Comprehensive health coverage for you and your family',
                'coverage' => 'Up to ₹10 lakhs',
                'premium' => 'Starting ₹2,500/year',
                'benefits' => 'Cashless treatment',
                'provider' => 'SarvOne Health',
                'category' => 'Insurance',
                'icon' => 'fas fa-heartbeat',
                'color' => 'green'
            ],
            [
                'id' => 3,
                'title' => 'Credit Card',
                'description' => 'Lifetime free credit card with exclusive rewards',
                'credit_limit' => 'Up to ₹2 lakhs',
                'annual_fee' => 'Lifetime Free',
                'rewards' => '5% cashback',
                'provider' => 'SarvOne Bank',
                'category' => 'Banking',
                'icon' => 'fas fa-credit-card',
                'color' => 'purple'
            ],
            [
                'id' => 4,
                'title' => 'Mutual Funds',
                'description' => 'Start investing with SIP as low as ₹500',
                'minimum_sip' => '₹500/month',
                'expected_returns' => '12-15% p.a.',
                'investment_type' => 'Equity & Debt',
                'provider' => 'SarvOne Investments',
                'category' => 'Investments',
                'icon' => 'fas fa-chart-line',
                'color' => 'orange'
            ]
        ];

        return view('opportunity-hub', compact('user', 'opportunities'));
    }

    /**
     * Get government schemes for opportunity hub
     */
    public function getGovernmentSchemes()
    {
        try {
            $user = Auth::user();
            
            // Debug authentication status
            \Log::info('Government Schemes API called', [
                'authenticated' => Auth::check(),
                'user_id' => $user ? $user->id : null,
                'user_did' => $user ? $user->did : null
            ]);
            
            // Get user's VCs if authenticated
            $userVCs = [];
            if ($user && $user->did) {
                $userVCs = \App\Models\VerifiableCredential::where('subject_did', $user->did)
                    ->pluck('vc_type')
                    ->toArray();
            }
            
            // Get all active schemes
            $schemes = \App\Models\GovernmentScheme::where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Add eligibility details for each scheme
            $schemesWithEligibility = $schemes->map(function ($scheme) use ($user, $userVCs) {
                $eligibilityDetails = $user ? $scheme->getEligibilityDetails($user) : [
                    'eligible' => false,
                    'checks' => [],
                    'missing_criteria' => ['User not authenticated']
                ];
                
                return [
                    'id' => $scheme->id,
                    'name' => $scheme->scheme_name,
                    'description' => $scheme->description,
                    'category' => $scheme->category,
                    'benefit_type' => $scheme->benefit_type,
                    'benefit_amount' => $scheme->benefit_amount,
                    'application_deadline' => $scheme->application_deadline,
                    'status' => $scheme->status,
                    'eligibility_details' => $eligibilityDetails,
                    'user_vcs' => $userVCs
                ];
            });

            return response()->json([
                'success' => true,
                'schemes' => $schemesWithEligibility,
                'user_vcs' => $userVCs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch schemes: ' . $e->getMessage()
            ], 500);
        }
    }
} 