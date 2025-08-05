<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\VerifiableCredential;

echo "🔍 Blockchain VC Retrieval Debug\n";
echo "===============================\n\n";

try {
    $user = Auth::user();
    if (!$user) {
        echo "❌ No authenticated user found\n";
        exit(1);
    }
    
    echo "👤 User Details:\n";
    echo "   Name: " . $user->name . "\n";
    echo "   DID: " . $user->did . "\n";
    echo "   Verified: " . ($user->isVerified() ? 'YES' : 'NO') . "\n\n";
    
    if (!$user->isVerified()) {
        echo "❌ User is not verified\n";
        exit(1);
    }
    
    // Calculate the hash that will be sent to blockchain
    $userDidHash = hash('sha256', $user->did);
    echo "🔐 Hash Calculation:\n";
    echo "   Original DID: " . $user->did . "\n";
    echo "   SHA-256 Hash: " . $userDidHash . "\n\n";
    
    // Check database VCs for this user
    echo "📋 Database VCs for User:\n";
    $dbVCs = VerifiableCredential::where('recipient_user_id', $user->id)->get();
    
    if ($dbVCs->count() == 0) {
        echo "   No VCs found in database\n\n";
    } else {
        foreach ($dbVCs as $vc) {
            echo "   VC ID: " . $vc->id . "\n";
            echo "   Type: " . $vc->credential_type . "\n";
            echo "   IPFS CID: " . ($vc->ipfs_cid ?? 'NOT SET') . "\n";
            echo "   VC Hash: " . ($vc->vc_hash ?? 'NOT SET') . "\n";
            echo "   Created: " . $vc->created_at . "\n";
            
            // Calculate what the hash should be
            if ($vc->ipfs_cid) {
                $calculatedHash = hash('sha256', $vc->ipfs_cid);
                echo "   Calculated Hash (SHA-256): " . $calculatedHash . "\n";
                echo "   Hash Match: " . ($calculatedHash === $vc->vc_hash ? 'YES' : 'NO') . "\n";
            }
            echo "\n";
        }
    }
    
    // Test blockchain API call
    echo "🌐 Testing Blockchain API Call:\n";
    $blockchainServiceUrl = env('BLOCKCHAIN_SERVICE_URL', 'http://localhost:8003');
    
    echo "   Service URL: " . $blockchainServiceUrl . "\n";
    echo "   Endpoint: /get_user_vcs/" . $userDidHash . "\n\n";
    
    $response = \Illuminate\Support\Facades\Http::timeout(30)
        ->get($blockchainServiceUrl . '/get_user_vcs/' . $userDidHash);
    
    echo "   Response Status: " . $response->status() . "\n";
    
    if ($response->successful()) {
        $blockchainData = $response->json();
        echo "   Response Success: " . ($blockchainData['success'] ? 'YES' : 'NO') . "\n";
        
        if ($blockchainData['success']) {
            echo "   VCs Found: " . count($blockchainData['vcs']) . "\n";
            
            if (count($blockchainData['vcs']) > 0) {
                echo "\n📋 Blockchain VCs:\n";
                foreach ($blockchainData['vcs'] as $index => $blockchainVC) {
                    echo "   VC " . ($index + 1) . ":\n";
                    echo "     Hash: " . $blockchainVC['hash'] . "\n";
                    echo "     Type: " . $blockchainVC['vc_type'] . "\n";
                    echo "     Issued At: " . $blockchainVC['issued_at'] . "\n";
                    echo "     Is Active: " . ($blockchainVC['is_active'] ? 'YES' : 'NO') . "\n";
                    echo "     Revoked: " . ($blockchainVC['revoked'] ? 'YES' : 'NO') . "\n";
                    
                    // Try to find matching VC in database
                    $matchingVC = VerifiableCredential::where('vc_hash', $blockchainVC['hash'])->first();
                    if ($matchingVC) {
                        echo "     Database Match: YES (ID: " . $matchingVC->id . ")\n";
                        echo "     IPFS CID: " . $matchingVC->ipfs_cid . "\n";
                    } else {
                        echo "     Database Match: NO\n";
                        
                        // Try to find by calculating hash
                        $allVCs = VerifiableCredential::whereNotNull('ipfs_cid')->get();
                        $found = false;
                        foreach ($allVCs as $vc) {
                            $calculatedHash = hash('sha256', $vc->ipfs_cid);
                            if ($calculatedHash === $blockchainVC['hash']) {
                                echo "     Found by hash calculation: YES (ID: " . $vc->id . ")\n";
                                echo "     IPFS CID: " . $vc->ipfs_cid . "\n";
                                $found = true;
                                break;
                            }
                        }
                        
                        if (!$found) {
                            echo "     Found by hash calculation: NO\n";
                        }
                    }
                    echo "\n";
                }
            }
        } else {
            echo "   Error: " . ($blockchainData['error'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "   Error: " . $response->body() . "\n";
    }
    
    echo "\n🔧 Potential Issues:\n";
    echo "==================\n";
    echo "1. Hash mismatch between database and blockchain\n";
    echo "2. IPFS CID not stored in database\n";
    echo "3. Blockchain service not returning correct data\n";
    echo "4. User DID hash calculation mismatch\n";
    
} catch (Exception $e) {
    echo "❌ Debug failed with exception: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

?> 