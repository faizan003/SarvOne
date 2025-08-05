<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Testing Aadhaar Credential Data Display...\n\n";

try {
    // Create a test user and issue Aadhaar VC
    $testUser = new \App\Models\User();
    $testUser->name = 'Test User ' . time();
    $testUser->phone = '+91' . rand(6000000000, 9999999999);
    $testUser->verification_status = 'verified';
    $testUser->aadhaar_number = '123456789012';
    $testUser->did = 'did:sarvone:' . bin2hex(random_bytes(16));
    $testUser->save();
    
    echo "✅ Test user created: {$testUser->name}\n";
    
    // Issue Aadhaar VC
    $aadhaarService = app(\App\Services\AadhaarSimulationService::class);
    $aadhaarResult = $aadhaarService->simulateAadhaarVerification(
        $testUser, 
        $testUser->aadhaar_number, 
        $testUser->name
    );
    
    if ($aadhaarResult['success']) {
        echo "✅ Aadhaar VC issued successfully!\n";
        
        // Get the saved VC
        $savedVC = \App\Models\VerifiableCredential::where('vc_id', $aadhaarResult['credential_id'])->first();
        
        if ($savedVC) {
            echo "\n📋 Credential Data Analysis:\n";
            echo "   VC ID: {$savedVC->vc_id}\n";
            echo "   Type: {$savedVC->vc_type}\n";
            echo "   Status: {$savedVC->status}\n";
            
            // Check credential data
            $credentialData = is_string($savedVC->credential_data) ? json_decode($savedVC->credential_data, true) : $savedVC->credential_data;
            
            echo "\n📊 Raw Credential Data:\n";
            echo "   Data Type: " . gettype($savedVC->credential_data) . "\n";
            echo "   Data Keys: " . implode(', ', array_keys($credentialData)) . "\n";
            
            // Check for Aadhaar card data
            if (isset($credentialData['aadhaar_card'])) {
                echo "\n🎯 Aadhaar Card Data Found:\n";
                $aadhaarData = $credentialData['aadhaar_card'];
                foreach ($aadhaarData as $key => $value) {
                    if (is_array($value)) {
                        echo "   {$key}: " . json_encode($value) . "\n";
                    } else {
                        echo "   {$key}: {$value}\n";
                    }
                }
                
                // Check for unwanted fields
                $unwantedFields = ['photo_url', 'simulation'];
                $hasUnwantedFields = false;
                foreach ($unwantedFields as $field) {
                    if (isset($aadhaarData[$field])) {
                        echo "   ⚠️  Unwanted field found: {$field} = {$aadhaarData[$field]}\n";
                        $hasUnwantedFields = true;
                    }
                }
                
                if (!$hasUnwantedFields) {
                    echo "   ✅ No unwanted fields found\n";
                }
                
            } else {
                echo "\n❌ Aadhaar card data not found in expected format\n";
                echo "   Available keys: " . implode(', ', array_keys($credentialData)) . "\n";
            }
            
            // Test the JavaScript formatting functions (simulate)
            echo "\n🎨 Simulated Display Format:\n";
            echo "   This would show in the dashboard as:\n";
            
            $importantFields = ['aadhaar_number', 'name', 'date_of_birth', 'gender', 'address'];
            foreach ($importantFields as $field) {
                if (isset($aadhaarData[$field])) {
                    $value = $aadhaarData[$field];
                    if ($field === 'aadhaar_number') {
                        $displayValue = '**** **** ' . substr($value, -4);
                    } elseif ($field === 'address' && is_array($value)) {
                        $displayValue = implode(', ', array_filter($value));
                    } else {
                        $displayValue = $value;
                    }
                    
                    $fieldName = ucwords(str_replace('_', ' ', $field));
                    echo "   {$fieldName}: {$displayValue}\n";
                }
            }
            
        } else {
            echo "❌ VC not found in database\n";
        }
        
    } else {
        echo "❌ Aadhaar VC issuance failed: {$aadhaarResult['message']}\n";
    }
    
    // Clean up
    echo "\n🧹 Cleaning up test data...\n";
    if (isset($savedVC)) {
        $savedVC->delete();
        echo "   ✅ Test VC deleted\n";
    }
    $testUser->delete();
    echo "   ✅ Test user deleted\n";
    
    echo "\n🎉 Credential display test completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 