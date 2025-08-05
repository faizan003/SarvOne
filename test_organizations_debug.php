<?php
/**
 * Debug script to check organizations in database
 */

// Change to Laravel directory context
if (!file_exists('artisan')) {
    echo "❌ Please run this script from the Laravel root directory\n";
    exit(1);
}

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 ORGANIZATIONS DEBUG\n";
echo str_repeat("=", 40) . "\n\n";

// Check total organizations
$totalOrgs = \App\Models\Organization::count();
echo "Total Organizations: " . $totalOrgs . "\n\n";

if ($totalOrgs === 0) {
    echo "❌ No organizations found in database!\n";
    echo "💡 Create some test organizations first.\n";
    exit(1);
}

// Get organizations by status
$pending = \App\Models\Organization::where('verification_status', 'pending')->get();
$approved = \App\Models\Organization::where('verification_status', 'approved')->get();
$rejected = \App\Models\Organization::where('verification_status', 'rejected')->get();

echo "📊 By Status:\n";
echo "  - Pending: " . $pending->count() . "\n";
echo "  - Approved: " . $approved->count() . "\n";
echo "  - Rejected: " . $rejected->count() . "\n\n";

// Show pending organizations details
if ($pending->count() > 0) {
    echo "🕒 PENDING ORGANIZATIONS:\n";
    echo str_repeat("-", 30) . "\n";
    foreach ($pending as $org) {
        echo "ID: " . $org->id . "\n";
        echo "Name: " . $org->legal_name . "\n";
        echo "Email: " . $org->official_email . "\n";
        echo "Status: " . $org->verification_status . "\n";
        echo "DID: " . ($org->did ?: 'Not generated') . "\n";
        echo "Created: " . $org->created_at . "\n";
        echo str_repeat("-", 20) . "\n";
    }
} else {
    echo "⚠️ No pending organizations found\n";
}

// Test route resolution
echo "\n🔗 ROUTE TESTING:\n";
echo str_repeat("-", 20) . "\n";

if ($pending->count() > 0) {
    $testOrgId = $pending->first()->id;
    $approvalUrl = url("gov/approval/organization/{$testOrgId}/approve");
    echo "Test Approval URL: " . $approvalUrl . "\n";
    
    // Test if route exists
    try {
        $route = app('router')->getRoutes()->match(
            \Illuminate\Http\Request::create($approvalUrl, 'POST')
        );
        echo "✅ Route found: " . $route->getName() . "\n";
    } catch (\Exception $e) {
        echo "❌ Route not found: " . $e->getMessage() . "\n";
    }
} else {
    echo "⚠️ Cannot test routes - no pending organizations\n";
}

echo "\n📝 RECOMMENDATIONS:\n";
echo str_repeat("-", 20) . "\n";

if ($totalOrgs === 0) {
    echo "1. Create test organizations first\n";
    echo "2. Visit organization registration page\n";
} elseif ($pending->count() === 0) {
    echo "1. All organizations are already processed\n";
    echo "2. Create new test organization\n";
    echo "3. Or reset existing organization status to 'pending'\n";
} else {
    echo "1. Test with Organization ID: " . $pending->first()->id . "\n";
    echo "2. Check browser console for JavaScript errors\n";
    echo "3. Check Laravel logs for detailed errors\n";
}

echo "\n🚀 Ready for testing!\n";
?> 