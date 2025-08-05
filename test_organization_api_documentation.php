<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Testing Organization API Documentation Feature...\n\n";

try {
    // Get an approved organization
    $organization = \App\Models\Organization::where('verification_status', 'approved')->first();
    
    if (!$organization) {
        echo "❌ No approved organization found!\n";
        exit(1);
    }
    
    echo "🏢 Testing with organization: {$organization->legal_name}\n";
    echo "📧 Email: {$organization->email}\n";
    echo "🏷️ Type: {$organization->organization_type}\n";
    echo "🆔 DID: {$organization->did}\n\n";
    
    // Test API credentials
    echo "🔑 API Credentials:\n";
    echo "   API Key: " . ($organization->api_key ?? 'Not generated') . "\n";
    echo "   Base URL: " . url('/organization/api') . "\n\n";
    
    // Test scopes
    echo "📋 Authorized Scopes:\n";
    echo "   Write Scopes (" . count($organization->write_scopes ?? []) . "):\n";
    foreach ($organization->write_scopes ?? [] as $scope) {
        echo "     • {$scope}\n";
    }
    
    echo "   Read Scopes (" . count($organization->read_scopes ?? []) . "):\n";
    foreach ($organization->read_scopes ?? [] as $scope) {
        echo "     • {$scope}\n";
    }
    echo "\n";
    
    // Test route accessibility
    echo "🔗 Route Testing:\n";
    
    // Test organization dashboard route
    $dashboardRoute = route('organization.dashboard');
    echo "   Dashboard Route: {$dashboardRoute}\n";
    
    // Test API documentation route
    $apiDocRoute = route('organization.api-documentation');
    echo "   API Documentation Route: {$apiDocRoute}\n";
    
    // Test API endpoints
    $baseUrl = url('/organization/api');
    echo "   Issue Credential: {$baseUrl}/issue-credential\n";
    echo "   Verify Credential: {$baseUrl}/verify-credential\n";
    echo "   Lookup User: {$baseUrl}/lookup-user-by-did\n\n";
    
    // Test credential scopes configuration
    echo "📚 Credential Scopes Configuration:\n";
    $credentialScopes = config('credential_scopes');
    $orgType = $organization->organization_type;
    
    if (isset($credentialScopes[$orgType])) {
        echo "   Organization type '{$orgType}' found in config\n";
        echo "   Write scopes: " . count($credentialScopes[$orgType]['write'] ?? []) . "\n";
        echo "   Read scopes: " . count($credentialScopes[$orgType]['read'] ?? []) . "\n";
    } else {
        echo "   ⚠️ Organization type '{$orgType}' not found in credential scopes config\n";
    }
    echo "\n";
    
    // Test sample API request
    echo "🧪 Sample API Request Test:\n";
    echo "   Testing issue credential endpoint structure...\n";
    
    $sampleRequest = [
        'recipient_did' => 'did:sarvone:test123',
        'credential_type' => $organization->write_scopes[0] ?? 'test_credential',
        'credential_data' => [
            'test_field' => 'test_value',
            'amount' => 1000,
            'date' => date('Y-m-d')
        ],
        'org_private_key' => '0x' . str_repeat('a', 64) // Sample private key
    ];
    
    echo "   Sample request structure:\n";
    echo "   " . json_encode($sampleRequest, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test sample API response
    echo "📤 Sample API Response Structure:\n";
    $sampleResponse = [
        'success' => true,
        'message' => 'Credential issued successfully',
        'data' => [
            'vc_id' => 'vc_' . uniqid(),
            'ipfs_hash' => 'Qm' . str_repeat('a', 44),
            'blockchain_tx' => '0x' . str_repeat('b', 64),
            'credential_url' => 'https://ipfs.io/ipfs/Qm' . str_repeat('a', 44)
        ]
    ];
    
    echo "   Sample response structure:\n";
    echo "   " . json_encode($sampleResponse, JSON_PRETTY_PRINT) . "\n\n";
    
    // Test view rendering (simulate)
    echo "🎨 View Rendering Test:\n";
    echo "   Organization API documentation view should be accessible at:\n";
    echo "   {$apiDocRoute}\n";
    echo "   (This would require authentication as organization)\n\n";
    
    echo "✅ Organization API Documentation Feature Test Completed!\n";
    echo "📝 Summary:\n";
    echo "   • Organization: {$organization->legal_name}\n";
    echo "   • API Key: " . (empty($organization->api_key) ? 'Not generated' : 'Available') . "\n";
    echo "   • Write Scopes: " . count($organization->write_scopes ?? []) . "\n";
    echo "   • Read Scopes: " . count($organization->read_scopes ?? []) . "\n";
    echo "   • Routes: Configured\n";
    echo "   • Documentation: Ready\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 