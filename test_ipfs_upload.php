<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

try {
    echo "Testing IPFS Upload via Pinata...\n";
    
    $pinataJwt = env('PINATA_JWT_KEY');
    $endpoint = 'https://api.pinata.cloud/pinning/pinFileToIPFS';
    
    // Test JSON content
    $testData = [
        'test' => 'data',
        'timestamp' => now()->toISOString(),
        'message' => 'Hello from SarvOne!'
    ];
    
    $jsonContent = json_encode($testData, JSON_PRETTY_PRINT);
    $fileName = 'test_' . uniqid() . '.json';
    
    echo "Uploading test file: $fileName\n";
    echo "Content: " . $jsonContent . "\n";
    
    // Create a temporary file for upload
    $tempFile = tmpfile();
    $tempPath = stream_get_meta_data($tempFile)['uri'];
    fwrite($tempFile, $jsonContent);
    
    // Upload to Pinata
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $pinataJwt,
    ])->attach(
        'file', 
        file_get_contents($tempPath), 
        $fileName
    )->post($endpoint);

    fclose($tempFile);
    
    echo "Response Status: " . $response->status() . "\n";
    echo "Response Body: " . $response->body() . "\n";
    
    if ($response->successful()) {
        $data = $response->json();
        echo "✅ Upload successful!\n";
        echo "CID: " . ($data['IpfsHash'] ?? 'unknown') . "\n";
        echo "Name: " . ($data['Name'] ?? 'unknown') . "\n";
        echo "Size: " . ($data['PinSize'] ?? 'unknown') . "\n";
        
        // Test retrieval
        $gatewayUrl = 'https://ipfs.io/ipfs/' . $data['IpfsHash'];
        echo "Gateway URL: $gatewayUrl\n";
        
        $retrieveResponse = Http::get($gatewayUrl);
        if ($retrieveResponse->successful()) {
            echo "✅ Retrieval successful!\n";
            echo "Retrieved content: " . $retrieveResponse->body() . "\n";
        } else {
            echo "❌ Retrieval failed: " . $retrieveResponse->status() . "\n";
        }
    } else {
        echo "❌ Upload failed!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 