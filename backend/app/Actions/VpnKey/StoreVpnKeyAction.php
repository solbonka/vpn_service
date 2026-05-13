<?php

namespace App\Actions\VpnKey;

use App\DTO\Actions\VpnKey\StoreVpnKeyActionDto;
use App\Models\VpnKey;

class StoreVpnKeyAction
{
    public function execute(StoreVpnKeyActionDto $data): VpnKey
    {
        return VpnKey::query()->create($data->all());
    }
}
