<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Dholakpur School Organization ===\n\n";

// Search for Dholakpur School
$dholakpurOrg = \App\Models\Organization::where('legal_name', 'LIKE', '%Dholakpur%')
    ->orWhere('legal_name', 'LIKE', '%dholakpur%')
    ->orWhere('registration_number', 'LIKE', '%DHOLAKPUR%')
    ->first();

if ($dholakpurOrg) {
    echo "✅ Found Dholakpur School:\n";
    echo "   ID: {$dholakpurOrg->id}\n";
    echo "   Name: {$dholakpurOrg->legal_name}\n";
    echo "   Type: {$dholakpurOrg->organization_type}\n";
    echo "   Status: {$dholakpurOrg->verification_status}\n";
    echo "   Registration: {$dholakpurOrg->registration_number}\n";
    echo "   Email: {$dholakpurOrg->official_email}\n";
    echo "   Phone: {$dholakpurOrg->official_phone}\n";
    echo "   Created: {$dholakpurOrg->created_at}\n";
} else {
    echo "❌ Dholakpur School not found in database\n";
}

echo "\n=== All Organizations with Status ===\n";
$allOrgs = \App\Models\Organization::all();
foreach ($allOrgs as $org) {
    echo "   - {$org->legal_name} (Status: {$org->verification_status}, Type: {$org->organization_type})\n";
}

echo "\n=== Counts by Status ===\n";
$pendingCount = \App\Models\Organization::where('verification_status', 'pending')->count();
$approvedCount = \App\Models\Organization::where('verification_status', 'approved')->count();
$rejectedCount = \App\Models\Organization::where('verification_status', 'rejected')->count();

echo "   Pending: {$pendingCount}\n";
echo "   Approved: {$approvedCount}\n";
echo "   Rejected: {$rejectedCount}\n"; 