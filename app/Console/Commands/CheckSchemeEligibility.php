<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GovernmentScheme;
use App\Services\SchemeNotificationService;

class CheckSchemeEligibility extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schemes:check-eligibility {--scheme-id= : Check specific scheme by ID} {--all : Check all active schemes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check user eligibility for government schemes and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking scheme eligibility and sending notifications...');

        $notificationService = app(SchemeNotificationService::class);

        if ($this->option('scheme-id')) {
            $schemeId = $this->option('scheme-id');
            $scheme = GovernmentScheme::find($schemeId);
            
            if (!$scheme) {
                $this->error("❌ Scheme with ID {$schemeId} not found!");
                return 1;
            }

            $this->info("📋 Checking eligibility for scheme: {$scheme->scheme_name}");
            $result = $notificationService->notifyEligibleUsersForNewScheme($scheme);
            
            $this->displayResults($result, $scheme);
        } elseif ($this->option('all')) {
            $schemes = GovernmentScheme::where('status', 'active')->get();
            
            if ($schemes->isEmpty()) {
                $this->warn('⚠️ No active schemes found!');
                return 0;
            }

            $this->info("📋 Found {$schemes->count()} active schemes");
            
            $totalNotified = 0;
            foreach ($schemes as $scheme) {
                $this->line("🔍 Checking: {$scheme->scheme_name}");
                $result = $notificationService->notifyEligibleUsersForNewScheme($scheme);
                $totalNotified += $result['notified_users'];
                
                $this->displayResults($result, $scheme);
            }
            
            $this->info("✅ Total notifications sent: {$totalNotified}");
        } else {
            // Check recent schemes (last 24 hours)
            $recentSchemes = GovernmentScheme::where('status', 'active')
                ->where('created_at', '>=', now()->subDay())
                ->get();

            if ($recentSchemes->isEmpty()) {
                $this->warn('⚠️ No recent schemes found in the last 24 hours!');
                $this->line('💡 Use --all to check all active schemes or --scheme-id=X for a specific scheme.');
                return 0;
            }

            $this->info("📋 Found {$recentSchemes->count()} recent schemes");
            
            $totalNotified = 0;
            foreach ($recentSchemes as $scheme) {
                $this->line("🔍 Checking: {$scheme->scheme_name}");
                $result = $notificationService->notifyEligibleUsersForNewScheme($scheme);
                $totalNotified += $result['notified_users'];
                
                $this->displayResults($result, $scheme);
            }
            
            $this->info("✅ Total notifications sent: {$totalNotified}");
        }

        return 0;
    }

    /**
     * Display results in a formatted way
     */
    private function displayResults($result, $scheme)
    {
        $this->line("   📊 Total users checked: {$result['total_users']}");
        $this->line("   ✅ Eligible users: {$result['eligible_users']}");
        $this->line("   📱 Notifications sent: {$result['notified_users']}");
        
        if ($result['notified_users'] > 0) {
            $this->info("   🎉 Successfully notified {$result['notified_users']} users for {$scheme->scheme_name}!");
        } else {
            $this->warn("   ⚠️ No eligible users found for {$scheme->scheme_name}");
        }
        
        $this->line('');
    }
}
