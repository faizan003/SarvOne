<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\VerifiableCredential;

echo "🔍 VC Hash Mismatch Debug\n";
echo "========================\n\n";

try {
    // Get the user we know has VCs
    $user = User::where('did', 'did:sarvone:d0460b16ef8cfe9f')->first();
    if (!$user) {
        echo "❌ User not found\n";
        exit(1);
    }
    
    echo "👤 User: " . $user->name . " (" . $user->did . ")\n\n";
    
    // Get all VCs for this user
    $vcs = VerifiableCredential::where('recipient_user_id', $user->id)->get();
    
    if ($vcs->count() == 0) {
        echo "❌ No VCs found for user\n";
        exit(1);
    }
    
    echo "📋 Database VCs:\n";
    foreach ($vcs as $vc) {
        echo "   VC ID: " . $vc->id . "\n";
        echo "   Type: " . $vc->credential_type . "\n";
        echo "   IPFS CID: " . ($vc->ipfs_cid ?? 'NOT SET') . "\n";
        echo "   VC Hash: " . ($vc->vc_hash ?? 'NOT SET') . "\n";
        
        if ($vc->ipfs_cid) {
            $calculatedHash = hash('sha256', $vc->ipfs_cid);
            echo "   Calculated Hash: " . $calculatedHash . "\n";
            echo "   Hash Match: " . ($calculatedHash === $vc->vc_hash ? 'YES' : 'NO') . "\n";
        }
        echo "\n";
    }
    
    // Test blockchain API call
    $userDidHash = hash('sha256', $user->did);
    echo "🌐 Testing Blockchain API:\n";
    echo "   User DID Hash: " . $userDidHash . "\n";
    
    $blockchainServiceUrl = env('BLOCKCHAIN_SERVICE_URL', 'http://localhost:8003');
    $response = \Illuminate\Support\Facades\Http::timeout(30)
        ->get($blockchainServiceUrl . '/get_user_vcs/' . $userDidHash);
    
    if ($response->successful()) {
        $blockchainData = $response->json();
        
        if ($blockchainData['success'] && count($blockchainData['vcs']) > 0) {
            echo "   ✅ Blockchain returned " . count($blockchainData['vcs']) . " VCs\n";
            
            foreach ($blockchainData['vcs'] as $index => $blockchainVC) {
                echo "   VC " . ($index + 1) . ":\n";
                echo "     Hash: " . $blockchainVC['hash'] . "\n";
                echo "     Type: " . $blockchainVC['vc_type'] . "\n";
                
                // Try to find matching VC in database
                $matchingVC = VerifiableCredential::where('vc_hash', $blockchainVC['hash'])->first();
                if ($matchingVC) {
                    echo "     ✅ Database Match: YES\n";
                    echo "     IPFS CID: " . $matchingVC->ipfs_cid . "\n";
                } else {
                    echo "     ❌ Database Match: NO\n";
                    
                    // Try to find by calculating hash
                    $found = false;
                    foreach ($vcs as $vc) {
                        if ($vc->ipfs_cid) {
                            $calculatedHash = hash('sha256', $vc->ipfs_cid);
                            if ($calculatedHash === $blockchainVC['hash']) {
                                echo "     ✅ Found by hash calculation: YES\n";
                                echo "     IPFS CID: " . $vc->ipfs_cid . "\n";
                                $found = true;
                                break;
                            }
                        }
                    }
                    
                    if (!$found) {
                        echo "     ❌ Found by hash calculation: NO\n";
                    }
                }
                echo "\n";
            }
        } else {
            echo "   ❌ No VCs returned from blockchain\n";
        }
    } else {
        echo "   ❌ Blockchain API call failed: " . $response->body() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?> 