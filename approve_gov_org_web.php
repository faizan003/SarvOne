<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🌐 Approving Government Organization via Web Interface...\n\n";

try {
    // Get government organization
    $govOrg = \App\Models\Organization::where('organization_type', 'government')->first();
    
    if (!$govOrg) {
        echo "❌ Government organization not found!\n";
        exit(1);
    }
    
    echo "📋 Government Organization Details:\n";
    echo "   ID: {$govOrg->id}\n";
    echo "   Name: {$govOrg->legal_name}\n";
    echo "   Wallet: {$govOrg->wallet_address}\n";
    echo "   Status: {$govOrg->verification_status}\n";
    echo "   Blockchain Approved: " . ($govOrg->blockchain_approved ? 'Yes' : 'No') . "\n\n";
    
    // Check if organization is already approved
    if ($govOrg->blockchain_approved) {
        echo "✅ Organization is already approved on blockchain!\n";
    } else {
        echo "⚠️ Organization needs blockchain approval.\n";
        echo "💡 Please go to the admin panel and approve this organization manually.\n";
        echo "   Admin URL: http://127.0.0.1:8000/admin/approval-dashboard\n";
        echo "   Organization ID: {$govOrg->id}\n";
        echo "   Organization Name: {$govOrg->legal_name}\n\n";
    }
    
    // Show current scopes
    echo "📝 Current Write Scopes:\n";
    foreach ($govOrg->write_scopes as $scope) {
        echo "   ✅ {$scope}\n";
    }
    
    echo "\n📖 Current Read Scopes:\n";
    foreach ($govOrg->read_scopes as $scope) {
        echo "   ✅ {$scope}\n";
    }
    
    // Check if all required scopes are present
    $requiredWriteScopes = ['aadhaar_card', 'pan_card', 'voter_id', 'driving_license'];
    $missingWriteScopes = array_diff($requiredWriteScopes, $govOrg->write_scopes);
    
    if (!empty($missingWriteScopes)) {
        echo "\n⚠️ Missing Required Write Scopes:\n";
        foreach ($missingWriteScopes as $scope) {
            echo "   ❌ {$scope}\n";
        }
        echo "\n🔄 Updating organization with missing scopes...\n";
        
        $updatedWriteScopes = array_merge($govOrg->write_scopes, $missingWriteScopes);
        $govOrg->update(['write_scopes' => $updatedWriteScopes]);
        
        echo "✅ Organization updated with missing scopes!\n";
    } else {
        echo "\n✅ All required write scopes are present!\n";
    }
    
    // Test Aadhaar simulation without blockchain approval
    echo "\n🧪 Testing Aadhaar simulation (local test)...\n";
    
    $testUser = \App\Models\User::whereNotNull('did')->first();
    
    if ($testUser) {
        echo "👤 Testing with user: {$testUser->name}\n";
        
        // Test Aadhaar data generation only
        $aadhaarService = app(\App\Services\AadhaarSimulationService::class);
        
        // Use reflection to access private method for testing
        $reflection = new ReflectionClass($aadhaarService);
        $generateMethod = $reflection->getMethod('generateAadhaarData');
        $generateMethod->setAccessible(true);
        
        $aadhaarData = $generateMethod->invoke($aadhaarService, '123456789012', $testUser->name);
        
        echo "✅ Aadhaar data generated successfully!\n";
        echo "   Aadhaar Number: {$aadhaarData['aadhaar_number']}\n";
        echo "   Name: {$aadhaarData['name']}\n";
        echo "   Date of Birth: {$aadhaarData['date_of_birth']}\n";
        echo "   Gender: {$aadhaarData['gender']}\n";
        echo "   Address: {$aadhaarData['address']['house_number']} {$aadhaarData['address']['street']}, {$aadhaarData['address']['area']}, {$aadhaarData['address']['city']}, {$aadhaarData['address']['state']} - {$aadhaarData['address']['pincode']}\n";
        echo "   Issuing Authority: {$aadhaarData['issuing_authority']}\n";
        echo "   Simulation: " . ($aadhaarData['simulation'] ? 'Yes' : 'No') . "\n";
    }
    
    echo "\n📋 Next Steps:\n";
    echo "1. Go to admin panel: http://127.0.0.1:8000/admin/approval-dashboard\n";
    echo "2. Find organization: {$govOrg->legal_name} (ID: {$govOrg->id})\n";
    echo "3. Click 'Approve' to approve on blockchain\n";
    echo "4. After approval, test Aadhaar VC issuance\n";
    
    echo "\n🎉 Setup completed! Organization is ready for blockchain approval.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 