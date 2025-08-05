<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\VerifiableCredential;

echo "🔍 Hash Matching Test\n";
echo "===================\n\n";

try {
    // Get the VC we know exists
    $vc = VerifiableCredential::where('ipfs_cid', 'QmQp3ay3WrKiJXZTaxr7udBZG9vVBMvH9SDR3v28MoBx9N')->first();
    
    if (!$vc) {
        echo "❌ VC not found\n";
        exit(1);
    }
    
    echo "📋 VC Details:\n";
    echo "   IPFS CID: " . $vc->ipfs_cid . "\n";
    echo "   Stored Hash: " . $vc->vc_hash . "\n";
    
    // Calculate hash from IPFS CID
    $calculatedHash = hash('sha256', $vc->ipfs_cid);
    echo "   Calculated Hash: " . $calculatedHash . "\n";
    
    // Blockchain returned hash (with 0x prefix)
    $blockchainHash = '0xef9bd426e55338429ddfdbc3c6121b290818b168c2dd3d6a05103a756ff90e24';
    $blockchainHashWithoutPrefix = substr($blockchainHash, 2);
    
    echo "\n🔐 Hash Comparison:\n";
    echo "   Blockchain Hash: " . $blockchainHash . "\n";
    echo "   Blockchain Hash (no prefix): " . $blockchainHashWithoutPrefix . "\n";
    echo "   Stored Hash Match: " . ($vc->vc_hash === $blockchainHash ? 'YES' : 'NO') . "\n";
    echo "   Stored Hash Match (no prefix): " . ($vc->vc_hash === $blockchainHashWithoutPrefix ? 'YES' : 'NO') . "\n";
    echo "   Calculated Hash Match: " . ($calculatedHash === $blockchainHash ? 'YES' : 'NO') . "\n";
    echo "   Calculated Hash Match (no prefix): " . ($calculatedHash === $blockchainHashWithoutPrefix ? 'YES' : 'NO') . "\n";
    
    // Test the matching logic
    echo "\n🧪 Testing Matching Logic:\n";
    
    // Remove 0x prefix if present
    $hashWithoutPrefix = $blockchainHash;
    if (strpos($blockchainHash, '0x') === 0) {
        $hashWithoutPrefix = substr($blockchainHash, 2);
    }
    
    echo "   Hash without prefix: " . $hashWithoutPrefix . "\n";
    
    // Try to find by exact match
    $exactMatch = VerifiableCredential::where('vc_hash', $blockchainHash)->first();
    echo "   Exact match (with 0x): " . ($exactMatch ? 'YES' : 'NO') . "\n";
    
    $exactMatchNoPrefix = VerifiableCredential::where('vc_hash', $hashWithoutPrefix)->first();
    echo "   Exact match (no 0x): " . ($exactMatchNoPrefix ? 'YES' : 'NO') . "\n";
    
    // Try to find by calculated hash
    $calculatedMatch = VerifiableCredential::where('vc_hash', $calculatedHash)->first();
    echo "   Calculated hash match: " . ($calculatedMatch ? 'YES' : 'NO') . "\n";
    
    // Test the new logic
    $found = false;
    if ($calculatedHash === $blockchainHash || $calculatedHash === $hashWithoutPrefix) {
        $found = true;
    }
    echo "   New logic match: " . ($found ? 'YES' : 'NO') . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?> 