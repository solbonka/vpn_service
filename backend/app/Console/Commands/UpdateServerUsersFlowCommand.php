<?php

namespace App\Console\Commands;

use App\Enums\Subscription\SubscriptionStatusEnum;
use App\Models\Server;
use App\Services\VpnKey\VpnKeyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateServerUsersFlowCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:update-flow {server_id : ID сервера для обновления}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновляет flow для всех пользователей выбранного сервера';

    /**
     * Execute the console command.
     */
    public function handle(VpnKeyService $vpnKeyService): int
    {
        $serverId = $this->argument('server_id');

        ini_set('memory_limit', '2048M');

        $server = Server::find($serverId);

        if (!$server) {
            $this->error("Сервер с ID {$serverId} не найден!");
            return 1;
        }


        $this->info("Обновление flow для сервера: {$server->name}");
        $this->info("Flow значение: {$server->flow}");

        if (!$this->confirm('Продолжить обновление всех пользователей этого сервера?')) {
            $this->info('Операция отменена.');
            return 0;
        }

        try {
            $usersResponse = $vpnKeyService->getUsers($server);

            if ($usersResponse->getStatusCode() !== 200) {
                $this->error('Не удалось получить список пользователей с сервера');
                return 1;
            }

            $usersData = json_decode($usersResponse->getBody()->getContents(), true);
            $users = $usersData['users'] ?? [];

            if (empty($users)) {
                $this->info('Пользователи на сервере не найдены.');
                return 0;
            }

            $this->info("Найдено пользователей: " . count($users));


            $updatedCount = 0;
            $errorCount = 0;

            $progressBar = $this->output->createProgressBar(count($users));
            $progressBar->start();

            foreach ($users as $user) {
                try {
                    $username = $user['username'];
                    $uuid = $user['proxies']['vless']['id'] ?? null;
                    $expire = $user['expire'] ?? 0;

                    $marzbanStatus = $user['status'] ?? 'active';

                    if (!$uuid) {
                        $this->warn("Пользователь {$username} не имеет UUID, пропускаем");
                        $progressBar->advance();
                        continue;
                    }

                    $vpnKeyService->updateUserFromMarzban(
                        $server,
                        $uuid,
                        $username,
                        $expire,
                        $marzbanStatus,
                        true
                    );

                    $updatedCount++;

                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error("Ошибка обновления пользователя {$username}: " . $e->getMessage());
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine();

            $this->info("Обновление завершено!");
            $this->info("Успешно обновлено: {$updatedCount}");
            $this->info("Ошибок: {$errorCount}");

            return 0;

        } catch (\Exception $e) {
            $this->error("Ошибка при получении пользователей: " . $e->getMessage());
            return 1;
        }
    }
}
