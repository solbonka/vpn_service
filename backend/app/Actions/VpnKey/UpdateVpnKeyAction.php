<?php

namespace App\Actions\VpnKey;

use App\DTO\Actions\VpnKey\UpdateVpnKeyActionDto;
use App\Models\VpnKey;

class UpdateVpnKeyAction
{
    public function execute(UpdateVpnKeyActionDto $data, VpnKey $vpnKey): VpnKey
    {
        $vpnKey->update($data->all());

        return $vpnKey;
    }
}
