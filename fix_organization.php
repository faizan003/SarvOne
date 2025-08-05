<?php
// Fix organization status and provide correct API key
require_once 'vendor/autoload.php';

use App\Models\Organization;

echo "=== Fix Organization Status ===\n\n";

try {
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    echo "✅ Laravel bootstrapped successfully\n\n";
    
    // Get all organizations
    $organizations = Organization::all();
    
    echo "Found " . $organizations->count() . " organizations:\n";
    echo str_repeat("-", 60) . "\n";
    
    foreach ($organizations as $org) {
        echo "ID: " . $org->id . "\n";
        echo "Name: " . $org->legal_name . "\n";
        echo "Status: '" . $org->status . "'\n";
        echo "API Key: " . ($org->api_key ?: 'Not set') . "\n";
        
        // Fix status if empty
        if (empty($org->status)) {
            echo "⚠️  Status is empty, setting to 'approved'...\n";
            $org->status = 'approved';
            $org->save();
            echo "✅ Status updated to 'approved'\n";
        }
        
        // Generate API key if not set
        if (empty($org->api_key)) {
            echo "⚠️  API key not set, generating new one...\n";
            $org->api_key = 'org_' . bin2hex(random_bytes(16));
            $org->save();
            echo "✅ New API key generated: " . $org->api_key . "\n";
        }
        
        echo str_repeat("-", 60) . "\n";
    }
    
    // Show the organization you should use
    $approvedOrg = Organization::where('status', 'approved')->first();
    if ($approvedOrg) {
        echo "\n🎯 Use this organization for API testing:\n";
        echo "ID: " . $approvedOrg->id . "\n";
        echo "Name: " . $approvedOrg->legal_name . "\n";
        echo "API Key: " . $approvedOrg->api_key . "\n";
        echo "Status: " . $approvedOrg->status . "\n";
        
        echo "\n📝 Postman Configuration:\n";
        echo "URL: http://127.0.0.1:8000/organization/api/verify-credential\n";
        echo "Method: POST\n";
        echo "Headers:\n";
        echo "  Content-Type: application/json\n";
        echo "  Accept: application/json\n";
        echo "  Authorization: Bearer " . $approvedOrg->api_key . "\n";
        echo "\nBody (JSON):\n";
        echo "{\n";
        echo "  \"user_did\": \"did:sarvone:80687094068c27d3\",\n";
        echo "  \"credential_type\": \"income_proof\",\n";
        echo "  \"purpose\": \"loan_application\"\n";
        echo "}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nFix completed!\n";
?> 