<?php

require_once 'vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

try {
    echo "Testing QR Code Generation...\n";
    
    $qrCode = new QrCode('did:example:123456789');
    $writer = new PngWriter();
    $result = $writer->write($qrCode);
    
    echo "QR Code generated successfully!\n";
    echo "MIME Type: " . $result->getMimeType() . "\n";
    echo "String length: " . strlen($result->getString()) . "\n";
    
} catch (Exception $e) {
    echo "Error generating QR code: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} 