<?php

namespace App\Services;

use App\Models\User;
use App\Models\VerifiableCredential;
use App\Services\CredentialService;
use App\Services\IPFSService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AadhaarSimulationService
{
    protected $credentialService;
    protected $ipfsService;

    public function __construct(CredentialService $credentialService, IPFSService $ipfsService)
    {
        $this->credentialService = $credentialService;
        $this->ipfsService = $ipfsService;
    }

    /**
     * Simulate Aadhaar verification and issue VC
     */
    public function simulateAadhaarVerification(User $user, string $aadhaarNumber, string $name)
    {
        try {
            Log::info('Starting Aadhaar simulation for user', [
                'user_id' => $user->id,
                'aadhaar_number' => $aadhaarNumber,
                'name' => $name
            ]);

            // Generate simulated Aadhaar data
            $aadhaarData = $this->generateAadhaarData($aadhaarNumber, $name);

            // Issue Aadhaar VC using government credentials
            $result = $this->issueAadhaarVC($user, $aadhaarData);

            if ($result['success']) {
                Log::info('Aadhaar VC issued successfully', [
                    'user_id' => $user->id,
                    'credential_id' => $result['credential_id'],
                    'transaction_hash' => $result['transaction_hash']
                ]);

                return [
                    'success' => true,
                    'message' => 'Aadhaar verification successful! Your Aadhaar credential has been issued.',
                    'aadhaar_data' => $aadhaarData,
                    'credential_id' => $result['credential_id'],
                    'transaction_hash' => $result['transaction_hash']
                ];
            } else {
                Log::error('Failed to issue Aadhaar VC', [
                    'user_id' => $user->id,
                    'error' => $result['error']
                ]);

                return [
                    'success' => false,
                    'message' => 'Aadhaar verification failed: ' . $result['error']
                ];
            }

        } catch (\Exception $e) {
            Log::error('Aadhaar simulation error', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Aadhaar verification error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate simulated Aadhaar data
     */
    private function generateAadhaarData(string $aadhaarNumber, string $name): array
    {
        // Generate random DOB between 18-25 years ago
        $minAge = 18;
        $maxAge = 25;
        $randomAge = rand($minAge, $maxAge);
        $dob = now()->subYears($randomAge)->subDays(rand(1, 365));

        // Generate random address components
        $states = ['Maharashtra', 'Karnataka', 'Tamil Nadu', 'Kerala', 'Andhra Pradesh'];
        $districts = ['Mumbai', 'Bangalore', 'Chennai', 'Thiruvananthapuram', 'Hyderabad'];
        $cities = ['Mumbai', 'Bangalore', 'Chennai', 'Kochi', 'Hyderabad'];

        $randomState = $states[array_rand($states)];
        $randomDistrict = $districts[array_rand($districts)];
        $randomCity = $cities[array_rand($cities)];

        // Generate random address
        $houseNumbers = ['123', '456', '789', '321', '654'];
        $streetNames = ['Main Street', 'Park Avenue', 'Lake Road', 'Hill View', 'Garden Lane'];
        $areas = ['Downtown', 'Suburb', 'Business District', 'Residential Area', 'Tech Park'];

        $houseNumber = $houseNumbers[array_rand($houseNumbers)];
        $streetName = $streetNames[array_rand($streetNames)];
        $area = $areas[array_rand($areas)];

        return [
            'aadhaar_number' => $aadhaarNumber,
            'name' => $name,
            'date_of_birth' => $dob->format('Y-m-d'),
            'gender' => rand(0, 1) ? 'Male' : 'Female',
            'address' => [
                'house_number' => $houseNumber,
                'street' => $streetName,
                'area' => $area,
                'city' => $randomCity,
                'district' => $randomDistrict,
                'state' => $randomState,
                'pincode' => str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT)
            ],
            'photo_url' => 'https://via.placeholder.com/150x200/0066cc/ffffff?text=' . urlencode(substr($name, 0, 1)),
            'issued_date' => now()->format('Y-m-d'),
            'valid_until' => now()->addYears(10)->format('Y-m-d'),
            'issuing_authority' => 'Unique Identification Authority of India (UIDAI)',
            'simulation' => true
        ];
    }

    /**
     * Issue Aadhaar VC using government credentials
     */
    private function issueAadhaarVC(User $user, array $aadhaarData): array
    {
        try {
            // Get UIDAI organization (for Aadhaar issuance)
            $uidaiOrg = \App\Models\Organization::where('organization_type', 'uidai')->first();
            
            if (!$uidaiOrg) {
                // Fallback to government organization if UIDAI not found
                $uidaiOrg = \App\Models\Organization::where('organization_type', 'government')->first();
                
                if (!$uidaiOrg) {
                    throw new \Exception('UIDAI organization not found for Aadhaar issuance');
                }
            }

            // Prepare credential data
            $credentialData = [
                'id' => $user->did,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'aadhaar_card' => $aadhaarData
            ];

            // Issue credential using UIDAI credentials
            $result = $this->credentialService->issueCredential(
                $uidaiOrg, // issuerOrganization
                $user, // recipientUser
                'aadhaar_card', // credentialType
                $credentialData, // credentialData
                env('GOVERNMENT_PRIVATE_KEY') // orgPrivateKey
            );

            return $result;

        } catch (\Exception $e) {
            Log::error('Aadhaar VC issuance failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate Aadhaar number format
     */
    public function validateAadhaarNumber(string $aadhaarNumber): bool
    {
        // Aadhaar number should be 12 digits
        if (!preg_match('/^\d{12}$/', $aadhaarNumber)) {
            return false;
        }

        // Check for valid checksum (simplified validation)
        $digits = str_split($aadhaarNumber);
        $sum = 0;
        
        for ($i = 0; $i < 11; $i++) {
            $sum += $digits[$i] * (12 - $i);
        }
        
        $checksum = $sum % 11;
        $expectedChecksum = $checksum == 0 ? 1 : (11 - $checksum);
        
        return $digits[11] == $expectedChecksum;
    }

    /**
     * Check if user already has Aadhaar VC
     */
    public function userHasAadhaarVC(User $user): bool
    {
        return $user->verifiableCredentials()
            ->where('vc_type', 'aadhaar_card')
            ->exists();
    }
} 