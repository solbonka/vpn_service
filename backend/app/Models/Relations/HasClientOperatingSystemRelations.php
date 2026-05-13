<?php

namespace App\Models\Relations;

use App\Models\ClientApp;
use App\Models\ClientAppOperatingSystem;
use App\Models\CustomTelegraphChat;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasClientOperatingSystemRelations
{
    public function clientApps(): BelongsToMany
    {
        return $this->belongsToMany(
            ClientApp::class, 'client_app_operating_system'
        )->withPivot('is_active', 'download_url')
            ->withTimestamps();
    }

    public function activeClientApps(): BelongsToMany
    {
        return $this->clientApps()->wherePivot('is_active', true);
    }

    public function clientAppOperatingSystems(): HasMany
    {
        return $this->hasMany(ClientAppOperatingSystem::class);
    }

    public function activeClientAppOperatingSystems(): HasMany
    {
        return $this->clientAppOperatingSystems()->where('is_active', true);
    }

    public function telegraphChats(): HasMany
    {
        return $this->hasMany(CustomTelegraphChat::class);
    }
}
