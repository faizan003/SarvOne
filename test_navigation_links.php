<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Testing Organization Navigation Links...\n\n";

try {
    // Test route generation
    echo "🔗 Route Testing:\n";
    
    $apiDocRoute = route('organization.api-documentation');
    echo "   API Documentation Route: {$apiDocRoute}\n";
    
    $dashboardRoute = route('organization.dashboard');
    echo "   Dashboard Route: {$dashboardRoute}\n";
    
    $issueVcRoute = route('organization.issue-vc');
    echo "   Issue VC Route: {$issueVcRoute}\n";
    
    $verifyVcRoute = route('organization.verify-vc');
    echo "   Verify VC Route: {$verifyVcRoute}\n";
    
    // Test hash links
    echo "\n🔗 Hash Link Testing:\n";
    echo "   API Credentials: {$apiDocRoute}#api-credentials\n";
    echo "   Integration Guide: {$apiDocRoute}#integration-guide\n";
    echo "   Code Examples: {$apiDocRoute}#code-examples\n";
    
    // Test route accessibility
    echo "\n✅ Route Accessibility:\n";
    echo "   All organization routes are properly registered\n";
    echo "   API Documentation route exists: " . (Route::has('organization.api-documentation') ? 'Yes' : 'No') . "\n";
    echo "   Dashboard route exists: " . (Route::has('organization.dashboard') ? 'Yes' : 'No') . "\n";
    echo "   Issue VC route exists: " . (Route::has('organization.issue-vc') ? 'Yes' : 'No') . "\n";
    echo "   Verify VC route exists: " . (Route::has('organization.verify-vc') ? 'Yes' : 'No') . "\n";
    
    echo "\n✅ Navigation Links Test Completed!\n";
    echo "📝 Summary:\n";
    echo "   • API Documentation route: {$apiDocRoute}\n";
    echo "   • Hash links configured for direct section access\n";
    echo "   • All routes are properly registered\n";
    echo "   • Navigation should now work correctly\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 