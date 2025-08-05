<?php

namespace App\Services;

class CredentialScopeService
{
    /**
     * Convert frontend scope selections to smart contract scope array
     * 
     * @param array $writeScopes - Selected write permissions from form
     * @param array $readScopes - Selected read permissions from form  
     * @return array - Formatted scopes for smart contract
     */
    public static function mapScopesForContract(array $writeScopes = [], array $readScopes = []): array
    {
        $contractScopes = [];
        $mapping = config('credential_scopes.contract_mapping');

        // Process write scopes (what org can ISSUE)
        foreach ($writeScopes as $scope) {
            if (isset($mapping[$scope])) {
                if (is_array($mapping[$scope])) {
                    $contractScopes = array_merge($contractScopes, $mapping[$scope]);
                } else {
                    $contractScopes[] = $mapping[$scope];
                }
            } else {
                // If not in mapping, use the scope name directly (for government write scopes)
                $contractScopes[] = $scope;
            }
        }

        // Process read scopes (what org can VERIFY) 
        foreach ($readScopes as $scope) {
            // Skip if this scope is already in write scopes (to avoid duplication)
            if (in_array($scope, $writeScopes)) {
                continue;
            }
            
            if (isset($mapping[$scope])) {
                if (is_array($mapping[$scope])) {
                    // For read permissions, use the scope names directly (they already have verify_ prefix)
                    foreach ($mapping[$scope] as $specificScope) {
                        $contractScopes[] = $specificScope;
                    }
                } else {
                    $contractScopes[] = $mapping[$scope];
                }
            } else {
                // If not in mapping, use the scope name directly (for read scopes that already have verify_ prefix)
                $contractScopes[] = $scope;
            }
        }

        // Remove duplicates and ensure proper array indexing
        return array_values(array_unique($contractScopes));
    }

    /**
     * Example of how scopes are processed for a bank
     */
    public static function getBankExample(): array
    {
        $writeScopes = ['loan_approval', 'account_opening'];
        $readScopes = ['kyc_identity', 'address_proof'];

        return [
            'input' => [
                'write' => $writeScopes,
                'read' => $readScopes
            ],
            'output' => self::mapScopesForContract($writeScopes, $readScopes)
        ];
    }

    /**
     * Your specific example: Bank selects KYC Identity + Academic Credentials
     */
    public static function getBankKycAcademicExample(): array
    {
        $writeScopes = []; // No write permissions in this example
        $readScopes = ['kyc_identity', 'academic_credentials'];

        $contractScopes = self::mapScopesForContract($writeScopes, $readScopes);

        return [
            'frontend_selection' => [
                'organization_type' => 'bank',
                'read_scopes' => $readScopes,
                'write_scopes' => $writeScopes
            ],
            'smart_contract_scopes' => $contractScopes,
            'explanation' => [
                'kyc_identity_expands_to' => [
                    'verify_aadhaar_card',
                    'verify_pan_card', 
                    'verify_passport',
                    'verify_voter_id',
                    'verify_driving_license'
                ],
                'academic_credentials_expands_to' => [
                    'verify_degree_verification',
                    'verify_marksheet_verification',
                    'verify_diploma_verification'
                ]
            ]
        ];
    }

    /**
     * Validate if organization type can have these scopes
     */
    public static function validateScopesForOrgType(string $orgType, array $writeScopes, array $readScopes): array
    {
        // Define simplified scope mappings for each organization type
        $allowedScopes = [
            'uidai' => [
                'write' => ['aadhaar_card'],
                'read' => ['aadhaar_card']
            ],
            'government' => [
                'write' => [
                    'aadhaar_card', 'pan_card', 'voter_id', 'caste_certificate', 'ration_card',
                    'income_certificate', 'domicile_certificate', 'birth_certificate',
                    'death_certificate', 'marriage_certificate'
                ],
                'read' => [
                    'aadhaar_card', 'pan_card', 'voter_id', 'caste_certificate', 'ration_card',
                    'income_certificate', 'domicile_certificate', 'birth_certificate',
                    'death_certificate', 'marriage_certificate'
                ]
            ],
            'land_property' => [
                'write' => [
                    'land_property', 'property_tax_receipt', 'encumbrance_certificate'
                ],
                'read' => [
                    'aadhaar_card', 'pan_card', 'land_property', 'income_certificate'
                ]
            ],
            'bank' => [
                'write' => [
                    'bank_account', 'loan', 'land_loan', 'credit_score', 
                    'income_certificate', 'employment_certificate'
                ],
                'read' => [
                    'aadhaar_card', 'pan_card', 'income_certificate', 'land_property', 
                    'employment_certificate'
                ]
            ],
            'school_university' => [
                'write' => [
                    'student_status', 'marksheet', 'study_certificate', 
                    'degree_certificate', 'transfer_certificate'
                ],
                'read' => [
                    'aadhaar_card', 'income_certificate', 'marksheet', 'caste_certificate'
                ]
            ]
        ];

        $errors = [];

        if (!isset($allowedScopes[$orgType])) {
            return ['Organization type not supported'];
        }

        $orgAllowedScopes = $allowedScopes[$orgType];

        // Check write scopes
        $allowedWriteScopes = $orgAllowedScopes['write'] ?? [];
        foreach ($writeScopes as $scope) {
            if (!in_array($scope, $allowedWriteScopes)) {
                $errors[] = "Write scope '{$scope}' not allowed for {$orgType}";
            }
        }

        // Check read scopes
        $allowedReadScopes = $orgAllowedScopes['read'] ?? [];
        foreach ($readScopes as $scope) {
            if (!in_array($scope, $allowedReadScopes)) {
                $errors[] = "Read scope '{$scope}' not allowed for {$orgType}";
            }
        }

        return $errors;
    }
} 