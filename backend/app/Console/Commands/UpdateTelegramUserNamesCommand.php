<?php

namespace App\Console\Commands;

use App\Models\CustomTelegraphChat;
use App\Services\Telegram\TelegramClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateTelegramUserNamesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:update-user-names 
                            {--limit=50 : Number of chats to process per batch}
                            {--force : Force update even if recently updated}
                            {--test : Test connection without updating data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update user names for all Telegram chats using Telegram Bot API';

    private TelegramClient $telegramClient;

    public function __construct(TelegramClient $telegramClient)
    {
        parent::__construct();
        $this->telegramClient = $telegramClient;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting Telegram user names update...');

        // Test connection first
        if (!$this->telegramClient->testConnection()) {
            $this->error('Failed to connect to Telegram API. Check bot token configuration.');
            return Command::FAILURE;
        }

        $this->info('Telegram API connection successful');

        if ($this->option('test')) {
            $this->info('Test mode - no data will be updated');
            return Command::SUCCESS;
        }

        $limit = (int) $this->option('limit');
        $force = $this->option('force');

        // Get chats to update
        $query = CustomTelegraphChat::query();
        
        if (!$force) {
            // Only update chats that don't have user info yet
            $query->where(function ($q) {
                $q->whereNull('first_name')
                  ->orWhereNull('last_name');
            });
        }

        $totalChats = $query->count();
        
        if ($totalChats === 0) {
            $this->info('No chats need updating');
            return Command::SUCCESS;
        }

        $this->info("Found {$totalChats} chats to update");

        $progressBar = $this->output->createProgressBar($totalChats);
        $progressBar->start();

        $updated = 0;
        $errors = 0;
        $batchSize = min($limit, 50);

        $query->chunk($batchSize, function ($chats) use ($progressBar, &$updated, &$errors) {
            foreach ($chats as $chat) {
                try {
                    $this->updateChatInfo($chat);
                    $updated++;
                } catch (\Exception $e) {
                    $errors++;
                    Log::error('Failed to update chat info', [
                        'chat_id' => $chat->chat_id,
                        'error' => $e->getMessage()
                    ]);
                }
                
                $progressBar->advance();
                
                // Small delay to avoid hitting rate limits
                usleep(100000); // 100ms
            }
        });

        $progressBar->finish();
        $this->newLine();

        $this->info("Update completed:");
        $this->info("- Updated: {$updated}");
        $this->info("- Errors: {$errors}");
        $this->info("- Total processed: " . ($updated + $errors));

        return Command::SUCCESS;
    }

    private function updateChatInfo(CustomTelegraphChat $chat): void
    {
        $userInfo = $this->telegramClient->getUserInfo((int) $chat->chat_id);
        
        if (!$userInfo) {
            throw new \Exception('Failed to get user info from Telegram API');
        }

        $chat->update([
            'first_name' => $userInfo['first_name'],
            'last_name' => $userInfo['last_name'],
            'username' => $userInfo['username'],
        ]);
    }
}
