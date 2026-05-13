<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Miniapp\MiniappSettingTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\MiniappSettings;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MiniappSettingsController extends Controller
{
    /**
     * Получить настройки мини-приложения
     */
    public function index(): JsonResponse
    {
        $logoSetting = MiniappSettings::getByType(MiniappSettingTypeEnum::LOGO);
        $lotterySetting = MiniappSettings::getByType(MiniappSettingTypeEnum::LOTTERY_PRIZE);
        
        return response()->json([
            'success' => true,
            'data' => [
                'logo' => $logoSetting?->value,
                'lottery_prize_image' => $lotterySetting?->value,
                'logo_updated_at' => $logoSetting?->updated_at,
                'lottery_updated_at' => $lotterySetting?->updated_at,
            ]
        ]);
    }

    /**
     * Обновить логотип
     */
    public function updateLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => 'required|string'
        ]);

        $setting = MiniappSettings::setLogo($request->logo);

        return response()->json([
            'success' => true,
            'message' => 'Логотип успешно обновлен',
            'data' => [
                'id' => $setting->id,
                'logo' => $setting->value,
                'updated_at' => $setting->updated_at,
            ]
        ]);
    }

    /**
     * Удалить логотип
     */
    public function deleteLogo(): JsonResponse
    {
        MiniappSettings::setLogo(null);

        return response()->json([
            'success' => true,
            'message' => 'Логотип успешно удален'
        ]);
    }

    /**
     * Обновить фото приза лотереи
     */
    public function updateLotteryImage(Request $request): JsonResponse
    {
        $request->validate([
            'lottery_prize_image' => 'required|string'
        ]);

        $setting = MiniappSettings::setLotteryImage($request->lottery_prize_image);

        return response()->json([
            'success' => true,
            'message' => 'Фото приза лотереи успешно обновлено',
            'data' => [
                'id' => $setting->id,
                'lottery_prize_image' => $setting->value,
                'updated_at' => $setting->updated_at,
            ]
        ]);
    }

    /**
     * Удалить фото приза лотереи
     */
    public function deleteLotteryImage(): JsonResponse
    {
        MiniappSettings::setLotteryImage(null);

        return response()->json([
            'success' => true,
            'message' => 'Фото приза лотереи успешно удалено'
        ]);
    }
}
