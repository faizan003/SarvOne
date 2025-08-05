<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🌐 Direct Blockchain Service Test\n";
echo "================================\n\n";

try {
    // Test with the known user DID hash
    $userDidHash = '2b6799e97b3f0cf8e962e07dd98ae0d01fd02a364656b03f1fc0907370d2b35a';
    
    echo "Testing with user DID hash: $userDidHash\n\n";
    
    $blockchainServiceUrl = env('BLOCKCHAIN_SERVICE_URL', 'http://localhost:8003');
    echo "Service URL: $blockchainServiceUrl\n";
    
    $response = \Illuminate\Support\Facades\Http::timeout(30)
        ->get($blockchainServiceUrl . '/get_user_vcs/' . $userDidHash);
    
    echo "Response Status: " . $response->status() . "\n";
    
    if ($response->successful()) {
        $data = $response->json();
        echo "Response Success: " . ($data['success'] ? 'YES' : 'NO') . "\n";
        
        if ($data['success']) {
            echo "VCs Found: " . count($data['vcs']) . "\n";
            
            if (count($data['vcs']) > 0) {
                echo "\nVC Details:\n";
                foreach ($data['vcs'] as $index => $vc) {
                    echo "VC " . ($index + 1) . ":\n";
                    echo "  Hash: " . $vc['hash'] . "\n";
                    echo "  Type: " . $vc['vc_type'] . "\n";
                    echo "  Issued At: " . $vc['issued_at'] . "\n";
                    echo "  Is Active: " . ($vc['is_active'] ? 'YES' : 'NO') . "\n";
                    echo "  Revoked: " . ($vc['revoked'] ? 'YES' : 'NO') . "\n\n";
                }
            }
        } else {
            echo "Error: " . ($data['error'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "Error: " . $response->body() . "\n";
    }
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

?> 