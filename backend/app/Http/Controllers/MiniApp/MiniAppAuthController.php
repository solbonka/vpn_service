<?php

namespace App\Http\Controllers\MiniApp;

use App\Http\Controllers\Controller;
use App\Models\CustomTelegraphChat;
use App\Models\Subscription;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MiniAppAuthController extends Controller
{
    public function authenticate(Request $request)
    {
        $initData = $request->header('X-Telegram-Init-Data');
        Log::info('Init data received:', ['initData' => $initData]);

        if (!$initData) {
            Log::warning('Missing Telegram init data');
            return response()->json(['error' => 'Missing Telegram init data'], 401);
        }

        if (!$this->validateTelegramSignature($initData)) {
            Log::warning('Invalid Telegram signature');
            return response()->json(['error' => 'Invalid Telegram signature'], 401);
        }

        $userId = $this->extractUserId($initData);
        Log::info('Extracted user ID:', ['userId' => $userId]);

        if (!$userId) {
            Log::warning('User ID not found');
            return response()->json(['error' => 'User ID not found'], 401);
        }

        $chat = CustomTelegraphChat::where('chat_id', $userId)->first();

        if (!$chat) {
            Log::warning('Chat not found for user ID:', ['userId' => $userId]);
            return response()->json(['error' => 'Chat not found'], 404);
        }

        $subscription = Subscription::where('telegraph_chat_id', $chat->id)->first();
        Log::info('Found subscription:', ['subscription' => $subscription?->toArray()]);

        $appLink = null;
        if ($subscription && $subscription->status->value === 'ACTIVE') {
            $timestamp = time();

            $relativePath = route('subscription.keys', ['subscription' => $subscription->token], false);
            $subscriptionUrl = config('telegram.domain') . $relativePath . "?t={$timestamp}";

            $appLink = $subscriptionUrl .
                "#" . config('app.name') . "[" . $subscription->telegraph_chat_id . "]";
        }

        $isChannelSubscribed = $this->checkChannelSubscription($userId);

        $responseData = [
            'success' => true,
            'user' => [
                'id' => $userId,
                'first_name' => $this->extractUserData($initData)['first_name'] ?? 'User',
                'last_name' => $this->extractUserData($initData)['last_name'] ?? '',
                'username' => $this->extractUserData($initData)['username'] ?? '',
                'is_channel_subscribed' => $isChannelSubscribed
            ],
            'subscription' => $subscription ? [
                'id' => $subscription->id,
                'status' => $subscription->status->value,
                'end_date' => $subscription->end_datetime?->format('Y-m-d H:i:s'),
                'plan' => $subscription->plan->name ?? null,
                'duration' => $subscription->duration->days ?? null,
                'token' => $subscription->token
            ] : null,
            'vpn_key_url' => $appLink,
            'support_channel' => config('telegram.support_chanel_name', '@your_support_channel'),
            'lottery_enabled' => filter_var(env('LOTTERY_ENABLE', false), FILTER_VALIDATE_BOOLEAN),
            'channel_name' => config('telegram.chanel_name'),
            'channel_link' => config('telegram.chanel_link'),
            'check_channel_subscription' => config('telegram.check_subscription_to_chanel', true)
        ];

        Log::info('Response data:', $responseData);

        return response()->json($responseData);
    }

    private function validateTelegramSignature($initData): bool
    {
        if (empty($initData)) {
            return false;
        }

        $botToken = config('telegram.bot_token');
        $decodedData = urldecode($initData);

        parse_str($decodedData, $data);

        if (!$data['hash'] || !$data['auth_date']) {
            Log::warning('empty hash or auth_date');
            return false;
        }

        $hash = $data['hash'];
        unset($data['hash']);

        ksort($data);

        $dataCheckString = '';
        foreach ($data as $key => $value) {
            $dataCheckString .= $key . '=' . $value . "\n";
        }
        $dataCheckString = rtrim($dataCheckString, "\n");

        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        $result = hash_equals($calculatedHash, $hash);

        if (!$result) {
            Log::warning('Hash validation failed', [
                'calculated' => $calculatedHash,
                'received' => $hash,
                'data' => $data,
            ]);
        }

        return $result;
    }

    private function extractUserId($initData)
    {
        $decodedData = urldecode($initData);
        parse_str($decodedData, $data);
        return $data['user'] ? json_decode($data['user'], true)['id'] ?? null : null;
    }

    private function extractUserData($initData)
    {
        $decodedData = urldecode($initData);
        parse_str($decodedData, $data);
        return $data['user'] ? json_decode($data['user'], true) : [];
    }

    private function checkChannelSubscription($userId): bool
    {
        $isCheckEnabled = config('telegram.check_subscription_to_chanel');

        if (!$isCheckEnabled) {
            return true;
        }

        try {
            $botToken = config('telegram.bot_token');
            $channelName = config('telegram.chanel_name');

            $response = Http::get('https://api.telegram.org/bot' . $botToken . '/getChatMember', [
                'chat_id' => $channelName,
                'user_id' => $userId
            ]);

            if (!$response->ok()) {
                Log::warning('Failed to check channel subscription', [
                    'user_id' => $userId,
                    'response' => $response->json()
                ]);
                return false;
            }

            $status = $response->json('result.status');
            $isSubscribed = !in_array($status, ['left', 'kicked']);

            Log::info('Channel subscription check', [
                'user_id' => $userId,
                'status' => $status,
                'is_subscribed' => $isSubscribed
            ]);

            return $isSubscribed;
        } catch (\Exception $e) {
            Log::error('Channel subscription check error', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function getSubscription(Request $request)
    {
        $subscription = $request->attributes->get('subscription');
        
        if (!$subscription) {
            return response()->json([
                'success' => false,
                'subscription' => null,
                'user' => null
            ]);
        }

        $chat = $subscription->telegraphChat;
        $isChannelSubscribed = $this->checkChannelSubscription($chat->chat_id);

        $subscriptionData = [
            'id' => $subscription->id,
            'status' => $subscription->status->value,
            'end_date' => $subscription->end_datetime?->format('Y-m-d H:i:s'),
            'plan' => $subscription->plan->name ?? null,
            'duration' => $subscription->duration->days ?? null,
            'token' => $subscription->token,
            'is_channel_subscribed' => $isChannelSubscribed
        ];

        $userData = [
            'id' => $chat->chat_id,
            'first_name' => $chat->first_name ?? 'User',
            'last_name' => $chat->last_name ?? '',
            'username' => $chat->name ?? '',
            'is_channel_subscribed' => $isChannelSubscribed
        ];

        return response()->json([
            'success' => true,
            'subscription' => $subscriptionData,
            'user' => $userData
        ]);
    }
}
