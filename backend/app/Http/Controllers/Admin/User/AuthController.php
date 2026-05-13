<?php

namespace App\Http\Controllers\Admin\User;

use App\Actions\Auth\LoginAction;
use App\Actions\Auth\RefreshTokenAction;
use App\DTO\Actions\Auth\LoginDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\TokenResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AuthController extends Controller
{
    /**
     * @throws Throwable
     */
    public function store(
        LoginRequest $request,
        LoginAction  $action
    ): JsonResponse
    {
        try {
            $data = $action->execute(
                new LoginDto(
                    $request->email,
                    $request->password,
                )
            );

            if (!$data) {
                return response()->json([
                    'error' => 'Ошибка авторизации',
                    'message' => 'Не верный логин или пароль'
                ], 401);
            }
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Ошибка авторизации',
                'message' => 'Не верный логин или пароль'
            ], 401);
        }

        $cookie = cookie('refresh_token', $data['refresh_token'], 60 * 24 * 30, '/api', null, true);

        return response()
            ->json(new TokenResource($data))
            ->withCookie($cookie);
    }

    public function destroy(Request $request): JsonResponse
    {
        $token = $request->user()->token();

        $token->revoke();

        if ($token->refreshToken) {
            $token->refreshToken->revoke();
        }

        $forgetCookie = cookie('refresh_token', null, -1, '/api', null, true);

        return response()
            ->json(['message' => 'Logged out successfully'])
            ->withCookie($forgetCookie);
    }

    /**
     * @throws Throwable
     */
    public function refreshToken(RefreshTokenAction $action): JsonResponse
    {
        $refreshToken = request()->cookie('refresh_token');

        if (!$refreshToken) {
            return response()->json(['error' => 'Refresh token not found'], 401);
        }

        try {
            $data = $action->execute($refreshToken);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to refresh token: ' . $e->getMessage(),
            ], 401);
        }

        $cookie = cookie('refresh_token', $data['refresh_token'], 60 * 24 * 30, '/api', null, true);

        return response()
            ->json(new TokenResource($data))
            ->withCookie($cookie);
    }
}
