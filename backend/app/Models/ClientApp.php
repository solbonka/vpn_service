<?php

namespace App\Models;

use App\Models\Relations\HasClientAppRelations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * @mixin IdeHelperClientApp
 */
class ClientApp extends Model
{
    use HasClientAppRelations;

    protected $fillable = [
        'name',
        'description',
        'routing'
    ];

    public function getRouteKeyName(): string
    {
        return 'name';
    }

    /**
     *
     * @param string $userAgent
     * @return ClientApp|null
     */
    public static function detectByUserAgent(string $userAgent): ?self
    {
        $userAgent = strtolower($userAgent);

        if (empty($userAgent)) {
            return null;
        }

        if (str_contains($userAgent, 'v2raytun')) {
            return self::query()->where('name', 'v2RayTun')->first();
        }

        if (str_contains($userAgent, 'happ')) {
            return self::query()->where('name', 'Happ')->first();
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getRouting(): ?string
    {
        return $this->routing;
    }
}
