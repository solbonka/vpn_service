<?php

namespace App\Telegraph\Handlers\Connect\Steps\OperatingSystem;

use App\Models\ClientOperatingSystem;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class OperatingSystemButtons
{
    public static function buttons(Keyboard $keyboard, ClientOperatingSystem $os): Keyboard
    {
        return $keyboard->buttons([
            Button::make($os->name)->action('setupAppAction')->param('os_id', $os->id)
        ]);
    }
}
