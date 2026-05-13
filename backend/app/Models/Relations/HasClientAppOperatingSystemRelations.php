<?php

namespace App\Models\Relations;

use App\Models\ClientApp;
use App\Models\ClientOperatingSystem;
use App\Models\ClientAppDownloadUrl;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasClientAppOperatingSystemRelations
{
    public function clientApp(): BelongsTo
    {
        return $this->belongsTo(ClientApp::class);
    }

    public function clientOperatingSystem(): BelongsTo
    {
        return $this->belongsTo(ClientOperatingSystem::class);
    }

    public function downloadUrls(): HasMany
    {
        return $this->hasMany(ClientAppDownloadUrl::class);
    }

    public function activeDownloadUrls(): HasMany
    {
        return $this->downloadUrls()->where('is_active', true);
    }
}
