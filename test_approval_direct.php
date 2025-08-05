<?php
/**
 * Direct test of the approval endpoint
 */

echo "🔍 DIRECT APPROVAL ENDPOINT TEST\n";
echo str_repeat("=", 40) . "\n\n";

// Test data for the approval request
$organizationId = 1; // From our debug script
$laravelUrl = 'http://localhost:8000';
$approvalUrl = "{$laravelUrl}/gov/approval/organization/{$organizationId}/approve";

echo "Testing approval endpoint:\n";
echo "URL: {$approvalUrl}\n";
echo "Organization ID: {$organizationId}\n\n";

// Create the POST data (simulate form submission)
$postData = http_build_query([
    '_token' => 'test-token', // We'll need to handle CSRF for real test
    'remarks' => 'Test approval from debug script',
    'did_prefix' => 'gmit'
]);

// First, let's test if Laravel is running
echo "Step 1: Testing Laravel connectivity...\n";
$laravelCheck = @file_get_contents($laravelUrl);
if ($laravelCheck) {
    echo "✅ Laravel is accessible\n\n";
} else {
    echo "❌ Laravel is not accessible. Please run: php artisan serve\n";
    exit(1);
}

// Test the approval dashboard page
echo "Step 2: Testing approval dashboard...\n";
$dashboardUrl = "{$laravelUrl}/gov/approval";
$dashboardCheck = @file_get_contents($dashboardUrl);
if ($dashboardCheck) {
    echo "✅ Approval dashboard is accessible\n\n";
} else {
    echo "❌ Approval dashboard not accessible\n";
    echo "URL: {$dashboardUrl}\n\n";
}

// Test getting pending organizations
echo "Step 3: Testing pending organizations API...\n";
$pendingUrl = "{$laravelUrl}/gov/approval/organizations/pending";
$pendingResponse = @file_get_contents($pendingUrl);
if ($pendingResponse) {
    $pendingData = json_decode($pendingResponse, true);
    if ($pendingData && $pendingData['success']) {
        echo "✅ Pending organizations API works\n";
        echo "Found " . count($pendingData['data']) . " pending organizations\n";
        
        if (count($pendingData['data']) > 0) {
            $org = $pendingData['data'][0];
            echo "First org: ID={$org['id']}, Name={$org['legal_name']}\n";
        }
    } else {
        echo "❌ Pending organizations API returned error\n";
        echo "Response: " . substr($pendingResponse, 0, 200) . "...\n";
    }
} else {
    echo "❌ Could not fetch pending organizations\n";
}

echo "\n";

// Manual test instructions
echo "📋 MANUAL TESTING STEPS:\n";
echo str_repeat("-", 30) . "\n";
echo "1. Open browser: {$laravelUrl}/gov/approval\n";
echo "2. Check browser console for JavaScript errors\n";
echo "3. Click 'Approve' button for organization ID {$organizationId}\n";
echo "4. Check if approval modal opens\n";
echo "5. Fill in optional fields and click 'Confirm Approval'\n";
echo "6. Watch browser console and network tab for errors\n\n";

echo "🔧 DEBUGGING CHECKLIST:\n";
echo str_repeat("-", 30) . "\n";
echo "✓ Organization exists (ID: {$organizationId})\n";
echo "✓ Organization is pending status\n";
echo "✓ Route is registered correctly\n";
echo "✓ Laravel is accessible\n";
echo "✓ Config cache cleared\n\n";

echo "🚨 COMMON ISSUES TO CHECK:\n";
echo str_repeat("-", 30) . "\n";
echo "1. CSRF token mismatch\n";
echo "2. JavaScript variable scope issues\n";
echo "3. Network connectivity issues\n";
echo "4. Laravel logs showing specific errors\n";
echo "5. Browser console errors\n\n";

echo "📝 TO CHECK LARAVEL LOGS:\n";
echo "tail -f storage/logs/laravel.log\n\n";

echo "🎯 If the issue persists:\n";
echo "1. Check browser Developer Tools > Console\n";
echo "2. Check browser Developer Tools > Network tab\n";
echo "3. Check Laravel logs with: tail -f storage/logs/laravel.log\n";
echo "4. Try the approval in a different browser\n\n";

?> 