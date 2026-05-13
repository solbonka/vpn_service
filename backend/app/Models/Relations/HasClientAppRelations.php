<?php

namespace App\Models\Relations;

use App\Models\ClientOperatingSystem;
use App\Models\ClientAppOperatingSystem;
use App\Models\ClientAppDownloadUrl;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasClientAppRelations
{
    public function operatingSystems(): BelongsToMany
    {
        return $this->belongsToMany(
            ClientOperatingSystem::class, 'client_app_operating_system'
        )->withPivot('is_active', 'download_url')
            ->withTimestamps();
    }

    public function activeOperatingSystems(): BelongsToMany
    {
        return $this->operatingSystems()->wherePivot('is_active', true);
    }

    public function clientAppOperatingSystems(): HasMany
    {
        return $this->hasMany(ClientAppOperatingSystem::class);
    }

    public function activeClientAppOperatingSystems(): HasMany
    {
        return $this->clientAppOperatingSystems()->where('is_active', true);
    }

    public function getDownloadUrlsForOs(int $osId): ?Collection
    {
        $clientAppOperatingSystem = $this->clientAppOperatingSystems()
            ->where('client_operating_system_id', $osId)
            ->first();

        if (!$clientAppOperatingSystem) {
            return null;
        }

        return $clientAppOperatingSystem->activeDownloadUrls;
    }
}
