<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\WebLoginRequest;
use App\Http\Requests\Web\WebRegisterRequest;
use App\Models\CustomTelegraphChat;
use App\Models\Subscription;
use App\Models\WebUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WebAuthController extends Controller
{
    public function register(WebRegisterRequest $request): JsonResponse
    {
        $email = $request->validated('email');
        $webUser = WebUser::create([
            'name' => $request->validated('name') ?: Str::before($email, '@'),
            'email' => $email,
            'password' => $request->validated('password'),
        ]);

        $token = $webUser->createToken('web-client')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->formatWebUser($webUser),
        ], 201);
    }

    public function login(WebLoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $webUser = WebUser::where('email', $credentials['email'])->first();

        if (! $webUser || ! Hash::check($credentials['password'], $webUser->password)) {
            return response()->json([
                'message' => 'Неверный email или пароль',
            ], 401);
        }

        $token = $webUser->createToken('web-client')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->formatWebUser($webUser),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var WebUser $user */
        $user = $request->user();

        return response()->json([
            'user' => $this->formatWebUser($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var WebUser $user */
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'OK']);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatWebUser(WebUser $webUser): array
    {
        return [
            'id' => $webUser->id,
            'email' => $webUser->email,
            'name' => $webUser->name,
        ];
    }

    public function telegramLogin(Request $request): JsonResponse
    {
        $telegramUser = $request->input('telegram_user', []);

        if (!is_array($telegramUser) || empty($telegramUser)) {
            return response()->json(['error' => 'Telegram user data is required'], 422);
        }

        if (!$this->validateTelegramWidgetSignature($telegramUser)) {
            return response()->json(['error' => 'Invalid Telegram signature'], 401);
        }

        $chatId = (string) ($telegramUser['id'] ?? '');
        if ($chatId === '') {
            return response()->json(['error' => 'Telegram user id is required'], 422);
        }

        $chat = CustomTelegraphChat::where('chat_id', $chatId)->first();
        if (!$chat) {
            return response()->json([
                'error' => 'Chat not found',
                'message' => 'Open the bot at least once before website login',
            ], 404);
        }

        $subscription = Subscription::where('telegraph_chat_id', $chat->id)->first();
        $userData = $this->extractTelegramUser($telegramUser);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $chatId,
                'first_name' => $userData['first_name'] ?? ($chat->first_name ?? 'User'),
                'last_name' => $userData['last_name'] ?? ($chat->last_name ?? ''),
                'username' => $userData['username'] ?? ($chat->name ?? ''),
            ],
            'subscription' => $subscription ? [
                'id' => $subscription->id,
                'status' => $subscription->status->value,
                'end_date' => $subscription->end_datetime?->format('Y-m-d H:i:s'),
                'plan' => $subscription->plan->name ?? null,
                'duration' => $subscription->duration->days ?? null,
                'token' => $subscription->token,
            ] : null,
        ]);
    }

    private function validateTelegramWidgetSignature(array $telegramUser): bool
    {
        $hash = $telegramUser['hash'] ?? null;
        $authDate = $telegramUser['auth_date'] ?? null;

        if (!$hash || !$authDate) {
            return false;
        }

        $dataToCheck = $telegramUser;
        unset($dataToCheck['hash']);

        ksort($dataToCheck);

        $pairs = [];
        foreach ($dataToCheck as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $pairs[] = $key . '=' . $value;
        }
        $dataCheckString = implode("\n", $pairs);

        $botToken = config('telegram.bot_token');
        $secretKey = hash('sha256', $botToken, true);
        $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        $isValid = hash_equals($calculatedHash, $hash);
        if (!$isValid) {
            Log::warning('Telegram widget hash validation failed', [
                'calculated' => $calculatedHash,
                'received' => $hash,
            ]);
        }

        return $isValid;
    }

    private function extractTelegramUser(array $telegramUser): array
    {
        return [
            'id' => $telegramUser['id'] ?? null,
            'first_name' => $telegramUser['first_name'] ?? null,
            'last_name' => $telegramUser['last_name'] ?? null,
            'username' => $telegramUser['username'] ?? null,
        ];
    }
}
