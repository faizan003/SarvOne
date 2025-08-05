<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Fixing Government Organization Read Scopes...\n";

try {
    // Find the government organization
    $org = DB::table('organizations')->where('organization_type', 'government')->first();
    
    if (!$org) {
        echo "No government organization found!\n";
        exit(1);
    }
    
    echo "Found organization: {$org->legal_name} (ID: {$org->id})\n";
    
    // Get the correct read scopes from config
    $config = config('credential_scopes');
    $governmentReadScopes = $config['government']['read'];
    $correctReadScopes = array_keys($governmentReadScopes);
    
    echo "Correct read scopes (" . count($correctReadScopes) . "):\n";
    foreach ($correctReadScopes as $i => $scope) {
        echo "  {$i}: {$scope}\n";
    }
    
    // Update the organization with correct read scopes
    DB::table('organizations')->where('id', $org->id)->update([
        'read_scopes' => json_encode($correctReadScopes),
        'updated_at' => now()
    ]);
    
    echo "\n✓ Organization read scopes updated successfully!\n";
    echo "✓ Read scopes count: " . count($correctReadScopes) . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 