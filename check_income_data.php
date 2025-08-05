<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Checking income certificate data...\n\n";

try {
    // Get income certificate VCs
    $incomeVCs = \App\Models\VerifiableCredential::where('vc_type', 'income_certificate')->get();
    
    echo "📊 Found {$incomeVCs->count()} income certificate VCs:\n\n";
    
    foreach ($incomeVCs as $vc) {
        echo "VC ID: {$vc->id}\n";
        echo "Subject DID: {$vc->subject_did}\n";
        echo "VC Type: {$vc->vc_type}\n";
        echo "Credential Data:\n";
        
        if ($vc->credential_data) {
            $data = is_string($vc->credential_data) ? json_decode($vc->credential_data, true) : $vc->credential_data;
            print_r($data);
        } else {
            echo "No credential data\n";
        }
        
        echo "\n" . str_repeat("-", 50) . "\n\n";
    }
    
    // Check user profiles
    echo "👥 User Profile Data:\n";
    $users = \App\Models\User::whereNotNull('did')->get();
    
    foreach ($users as $user) {
        echo "User ID: {$user->id}\n";
        echo "Name: {$user->name}\n";
        echo "Phone: {$user->phone}\n";
        echo "DID: {$user->did}\n";
        echo "Family Income: " . ($user->family_income ?? 'Not set') . "\n";
        echo "Age: " . ($user->age ?? 'Not set') . "\n";
        echo "Caste: " . ($user->caste ?? 'Not set') . "\n";
        echo "Education: " . ($user->education_level ?? 'Not set') . "\n";
        echo "Employment: " . ($user->employment_status ?? 'Not set') . "\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} 