<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Checking organization verification status values...\n\n";

try {
    // Get existing government organization
    $govOrg = \App\Models\Organization::where('organization_type', 'government')->first();
    
    if ($govOrg) {
        echo "📋 Government Organization Details:\n";
        echo "   ID: {$govOrg->id}\n";
        echo "   Name: {$govOrg->legal_name}\n";
        echo "   Current Status: '{$govOrg->verification_status}'\n";
        echo "   Wallet: {$govOrg->wallet_address}\n\n";
        
        // Check what status values are valid
        echo "🔍 Checking database schema for verification_status...\n";
        
        $result = \DB::select("SHOW COLUMNS FROM organizations LIKE 'verification_status'");
        if (!empty($result)) {
            $column = $result[0];
            echo "   Column Type: {$column->Type}\n";
            echo "   Null: {$column->Null}\n";
            echo "   Default: {$column->Default}\n";
            
            // Parse ENUM values if it's an ENUM
            if (strpos($column->Type, 'enum') === 0) {
                preg_match('/enum\((.*)\)/', $column->Type, $matches);
                if (isset($matches[1])) {
                    $values = str_getcsv($matches[1], ',', "'");
                    echo "   Valid Values:\n";
                    foreach ($values as $value) {
                        echo "     - {$value}\n";
                    }
                }
            }
        }
        
        // Try to update with correct status
        echo "\n🔄 Updating organization with correct status...\n";
        
        $updateData = [
            'write_scopes' => [
                'aadhaar_card',
                'pan_card',
                'voter_id',
                'driving_license',
                'passport',
                'birth_certificate',
                'marriage_certificate',
                'domicile_certificate',
                'caste_certificate',
                'disability_certificate',
                'income_certificate',
                'family_income_verification',
                'domicile_residence_verification',
                'caste_category_verification',
                'disability_assessment',
                'ration_card',
                'ayushman_card',
                'pension_card',
                'scholarship_approval',
                'economic_weaker_section',
                'property_land_records'
            ],
            'read_scopes' => [
                'verify_aadhaar_card',
                'verify_pan_card',
                'verify_voter_id',
                'verify_driving_license',
                'verify_passport',
                'verify_birth_certificate',
                'verify_marriage_certificate',
                'verify_domicile_certificate',
                'verify_caste_certificate',
                'verify_disability_certificate',
                'verify_income_certificate',
                'verify_family_income_verification',
                'verify_domicile_residence_verification',
                'verify_caste_category_verification',
                'verify_disability_assessment',
                'verify_ration_card',
                'verify_ayushman_card',
                'verify_pension_card',
                'verify_scholarship_approval',
                'verify_economic_weaker_section',
                'verify_property_land_records'
            ]
        ];
        
        // Try different status values
        $possibleStatuses = ['approved', 'verified', 'active', 'pending'];
        
        foreach ($possibleStatuses as $status) {
            try {
                $updateData['verification_status'] = $status;
                $govOrg->update($updateData);
                echo "✅ Successfully updated with status: '{$status}'\n";
                break;
            } catch (Exception $e) {
                echo "❌ Failed with status '{$status}': " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n📝 Updated Write Scopes:\n";
        foreach ($govOrg->write_scopes as $scope) {
            echo "   ✅ {$scope}\n";
        }
        
        echo "\n📖 Updated Read Scopes:\n";
        foreach ($govOrg->read_scopes as $scope) {
            echo "   ✅ {$scope}\n";
        }
        
    } else {
        echo "❌ No government organization found!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} 