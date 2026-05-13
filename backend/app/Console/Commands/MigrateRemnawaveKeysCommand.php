<?php

namespace App\Console\Commands;

use App\Models\RemnawaveVpnKey;
use App\Models\Subscription;
use App\Models\VpnKey;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateRemnawaveKeysCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'migrate:remnawave-keys
                            {--dry-run : Показать что будет сделано без выполнения}
                            {--force : Принудительно перезаписать существующие ключи}';

    /**
     * The console command description.
     */
    protected $description = 'Мигрировать данные из vpn_keys в remnawave_vpn_keys';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('🚀 Начинаем миграцию ключей для Remnawave...');

        $subscriptions = Subscription::all();

        $this->info("📊 Найдено подписок: {$subscriptions->count()}");

        $migrated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($subscriptions as $subscription) {
            try {
                $existingKey = $subscription->remnawaveVpnKey;

                if ($existingKey && !$force) {
                    $this->warn("⚠️  Подписка {$subscription->id} уже имеет Remnawave ключ (UUID: {$existingKey->uuid})");
                    $skipped++;
                    continue;
                }

                $vpnKey = $subscription->vpnKeys()->first();

                if (!$vpnKey) {
                    $this->warn("⚠️  У подписки {$subscription->id} нет VPN ключей");
                    $skipped++;
                    continue;
                }

                if ($dryRun) {
                    $this->line("🔍 [DRY RUN] Подписка {$subscription->id} ({$subscription->status->value}): {$vpnKey->username} -> {$vpnKey->uuid}");
                    $migrated++;
                    continue;
                }

                $remnawaveKey = $subscription->remnawaveVpnKey()->updateOrCreate(
                    ['subscription_id' => $subscription->id],
                    [
                        'uuid' => $vpnKey->uuid,
                        'username' => $vpnKey->username,
                        'is_active' => $vpnKey->is_active,
                    ]
                );

                $this->info("✅ Подписка {$subscription->id} ({$subscription->status->value}): {$vpnKey->username} -> {$vpnKey->uuid}");
                $migrated++;

            } catch (\Exception $e) {
                $this->error("❌ Ошибка для подписки {$subscription->id}: {$e->getMessage()}");
                $errors++;
            }
        }

        // Итоговая статистика
        $this->newLine();
        $this->info('📈 РЕЗУЛЬТАТЫ МИГРАЦИИ:');
        $this->line("✅ Успешно: {$migrated}");
        $this->line("⚠️  Пропущено: {$skipped}");
        $this->line("❌ Ошибок: {$errors}");

        if ($dryRun) {
            $this->warn('🔍 Это был DRY RUN - никаких изменений не было сделано');
            $this->info('💡 Запустите без --dry-run для выполнения миграции');
        }

        return Command::SUCCESS;
    }
}
