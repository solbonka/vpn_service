<?php

namespace App\Models;

use App\Enums\Miniapp\MiniappSettingTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperMiniappSettings
 */
class MiniappSettings extends Model
{
    use HasFactory;

    protected $table = 'miniapp_settings';

    protected $fillable = [
        'type',
        'value',
    ];

    protected $casts = [
        'type' => MiniappSettingTypeEnum::class,
        'value' => 'string',
    ];


    public static function getByType(MiniappSettingTypeEnum $type): ?self
    {
        return self::query()->where('type', $type)->first();
    }


    public static function getOrCreateByType(MiniappSettingTypeEnum $type): self
    {
        return self::firstOrCreate(
            ['type' => $type],
            ['value' => null]
        );
    }

    public static function getLogo(): ?string
    {
        $setting = self::getByType(MiniappSettingTypeEnum::LOGO);
        return $setting?->value;
    }

    public static function getLotteryImage(): ?string
    {
        $setting = self::getByType(MiniappSettingTypeEnum::LOTTERY_PRIZE);
        return $setting?->value;
    }

    public static function setLogo(?string $value): self
    {
        $setting = self::getOrCreateByType(MiniappSettingTypeEnum::LOGO);
        $setting->update(['value' => $value]);
        return $setting;
    }


    public static function setLotteryImage(?string $value): self
    {
        $setting = self::getOrCreateByType(MiniappSettingTypeEnum::LOTTERY_PRIZE);
        $setting->update(['value' => $value]);
        return $setting;
    }

}
