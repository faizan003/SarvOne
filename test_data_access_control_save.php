<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Data Access Control Save Functionality ===\n\n";

// Get a user
$user = \App\Models\User::where('verification_status', 'verified')
    ->whereNotNull('verified_at')
    ->first();

if (!$user) {
    echo "❌ No verified user found!\n";
    exit(1);
}

echo "👤 User: {$user->name} (DID: {$user->did})\n\n";

// Get organization types
$organizationTypes = \App\Models\UserDataAccessPreference::getOrganizationTypes();
$availableDataTypes = \App\Models\UserDataAccessPreference::getAvailableDataTypes();

echo "📋 Organization Types:\n";
foreach ($organizationTypes as $type => $config) {
    echo "   • {$config['name']} ({$type})\n";
    echo "     Mandatory: " . implode(', ', $config['mandatory']) . "\n";
    echo "     Optional: " . implode(', ', $config['optional']) . "\n\n";
}

// Get current preferences
echo "🔍 Current User Preferences:\n";
$userPreferences = [];
foreach ($organizationTypes as $type => $config) {
    $preference = $user->getDataAccessPreference($type);
    $userPreferences[$type] = $preference;
    
    if ($preference) {
        echo "   ✅ {$config['name']}: Active = " . ($preference->is_active ? 'Yes' : 'No') . "\n";
        echo "      Allowed Types: " . implode(', ', $preference->allowed_data_types) . "\n";
    } else {
        echo "   ❌ {$config['name']}: No preference set (will use defaults)\n";
    }
}

// Test creating/updating preferences
echo "\n🧪 Testing Preference Creation/Update...\n";

// Simulate form data
$testPreferences = [
    'uidai' => [
        'organization_type' => 'uidai',
        'allowed_data_types' => ['aadhaar_card'],
        'is_active' => true
    ],
    'government' => [
        'organization_type' => 'government',
        'allowed_data_types' => ['aadhaar_card', 'pan_card', 'income_certificate'],
        'is_active' => true
    ],
    'bank' => [
        'organization_type' => 'bank',
        'allowed_data_types' => ['aadhaar_card', 'pan_card'],
        'is_active' => false
    ]
];

foreach ($testPreferences as $orgType => $pref) {
    echo "   Testing {$orgType}...\n";
    
    // Get the organization type configuration
    $orgConfig = $organizationTypes[$orgType] ?? null;
    
    if ($orgConfig) {
        $mandatoryTypes = $orgConfig['mandatory'];
        $allowedDataTypes = $pref['allowed_data_types'] ?? [];
        $isActive = $pref['is_active'] ?? true;
        
        // Ensure mandatory types are always included
        $finalAllowedTypes = array_unique(array_merge($allowedDataTypes, $mandatoryTypes));
        
        $result = $user->dataAccessPreferences()->updateOrCreate(
            ['organization_type' => $orgType],
            [
                'allowed_data_types' => $finalAllowedTypes,
                'mandatory_data_types' => $mandatoryTypes,
                'is_active' => $isActive
            ]
        );
        
        echo "     ✅ Saved: Active = " . ($result->is_active ? 'Yes' : 'No') . "\n";
        echo "        Allowed Types: " . implode(', ', $result->allowed_data_types) . "\n";
    } else {
        echo "     ❌ Organization type not found\n";
    }
}

// Verify the changes
echo "\n🔍 Verifying Changes...\n";
foreach ($organizationTypes as $type => $config) {
    $preference = $user->getDataAccessPreference($type);
    
    if ($preference) {
        echo "   ✅ {$config['name']}: Active = " . ($preference->is_active ? 'Yes' : 'No') . "\n";
        echo "      Allowed Types: " . implode(', ', $preference->allowed_data_types) . "\n";
    } else {
        echo "   ❌ {$config['name']}: No preference found\n";
    }
}

echo "\n=== Test Complete ===\n"; 