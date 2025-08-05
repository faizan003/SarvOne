<?php
// Test server-side QR code generation
require_once 'vendor/autoload.php';

// Test DID
$testDid = "did:sarvone:80687094068c27d3";

echo "Testing server-side QR code generation for DID: $testDid\n";

try {
    // Test Endroid QR Code library
    $qrCode = new \Endroid\QrCode\QrCode($testDid);
    $writer = new \Endroid\QrCode\Writer\PngWriter();
    $result = $writer->write($qrCode);
    
    echo "✅ QR Code generated successfully!\n";
    echo "MIME Type: " . $result->getMimeType() . "\n";
    echo "Data size: " . strlen($result->getString()) . " bytes\n";
    
    // Save test image
    $testImagePath = 'public/test_qr.png';
    file_put_contents($testImagePath, $result->getString());
    echo "Test image saved to: $testImagePath\n";
    
} catch (Exception $e) {
    echo "❌ QR Code generation failed: " . $e->getMessage() . "\n";
}

echo "\nTest completed!\n";
?> 