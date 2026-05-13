<?php

namespace App\Console\Commands;

use App\Models\BonusAccount;
use App\Models\ReferralCode;
use App\Models\Subscription;
use Illuminate\Console\Command;

class GenerateReferralDataCommand extends Command
{
    protected $signature = 'referral:generate 
                            {--subscription-id= : Generate for specific subscription ID}
                            {--all : Generate for all subscriptions}';

    protected $description = 'Generate referral codes and bonus accounts for subscriptions';

    public function handle(): int
    {
        $subscriptionId = $this->option('subscription-id');
        $all = $this->option('all');

        if ($subscriptionId) {
            return $this->generateForSubscription($subscriptionId);
        }

        if ($all) {
            return $this->generateForAllSubscriptions();
        }

        $this->error('Please specify --subscription-id=ID or --all');
        return 1;
    }

    private function generateForSubscription(int $subscriptionId): int
    {
        $subscription = Subscription::find($subscriptionId);

        if (!$subscription) {
            $this->error("Subscription with ID {$subscriptionId} not found");
            return 1;
        }

        $this->info("Generating referral data for subscription ID: {$subscriptionId}");

        // Создаем реферальный код
        $referralCode = ReferralCode::getOrCreateForSubscription($subscription);
        $this->line("✓ Referral code: {$referralCode->code}");

        // Создаем бонусный счет
        $bonusAccount = BonusAccount::getOrCreateForSubscription($subscription);
        $this->line("✓ Bonus account created");

        // Показываем информацию о бонусе
        if ($referralCode->bonusType) {
            $this->line("✓ Bonus type: {$referralCode->getBonusTypeLabel()} ({$referralCode->getBonusAmount()})");
        }

        $this->info("Referral data generated successfully!");
        return 0;
    }

    private function generateForAllSubscriptions(): int
    {
        $subscriptions = Subscription::all();
        $total = $subscriptions->count();

        if ($total === 0) {
            $this->warn('No subscriptions found');
            return 0;
        }

        $this->info("Generating referral data for {$total} subscriptions...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $created = 0;
        $skipped = 0;

        foreach ($subscriptions as $subscription) {
            // Проверяем, есть ли уже реферальный код
            $existingCode = ReferralCode::where('subscription_id', $subscription->id)->first();
            $existingAccount = BonusAccount::where('subscription_id', $subscription->id)->first();

            if ($existingCode && $existingAccount) {
                $skipped++;
            } else {
                // Создаем реферальный код
                ReferralCode::getOrCreateForSubscription($subscription);
                
                // Создаем бонусный счет
                BonusAccount::getOrCreateForSubscription($subscription);
                
                $created++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Generation completed!");
        $this->line("✓ Created: {$created}");
        $this->line("✓ Skipped (already exists): {$skipped}");
        $this->line("✓ Total processed: {$total}");

        return 0;
    }
}
