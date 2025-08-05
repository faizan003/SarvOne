<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Organization Counts ===\n\n";

// Get actual counts from database
$totalCount = \App\Models\Organization::count();
$pendingCount = \App\Models\Organization::where('verification_status', 'pending')->count();
$approvedCount = \App\Models\Organization::where('verification_status', 'approved')->count();
$rejectedCount = \App\Models\Organization::where('verification_status', 'rejected')->count();

echo "📊 Database Counts:\n";
echo "   Total: {$totalCount}\n";
echo "   Pending: {$pendingCount}\n";
echo "   Approved: {$approvedCount}\n";
echo "   Rejected: {$rejectedCount}\n\n";

// Show pending organizations
echo "📋 Pending Organizations:\n";
$pendingOrgs = \App\Models\Organization::where('verification_status', 'pending')->get();
foreach ($pendingOrgs as $org) {
    echo "   - {$org->legal_name} (ID: {$org->id}, Type: {$org->organization_type})\n";
}

echo "\n📋 Approved Organizations:\n";
$approvedOrgs = \App\Models\Organization::where('verification_status', 'approved')->get();
foreach ($approvedOrgs as $org) {
    echo "   - {$org->legal_name} (ID: {$org->id}, Type: {$org->organization_type})\n";
}

echo "\n📋 Rejected Organizations:\n";
$rejectedOrgs = \App\Models\Organization::where('verification_status', 'rejected')->get();
foreach ($rejectedOrgs as $org) {
    echo "   - {$org->legal_name} (ID: {$org->id}, Type: {$org->organization_type})\n";
}

echo "\n=== Check Complete ===\n"; 