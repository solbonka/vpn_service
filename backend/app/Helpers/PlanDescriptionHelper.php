<?php

namespace App\Helpers;

use App\Models\Plan;

class PlanDescriptionHelper
{
    public static function getDescription(Plan $plan): string
    {
        $servers = $plan->servers;

        $serverList = $servers->isNotEmpty()
            ? $servers->map(fn($server) => "           • $server->name")->implode("\n")
            : "           • Нет доступных регионов";

        $descriptions = [
            'Базовый' => [
                'icon' => '📦',
                'text' => "",
            ]
        ];

        $icon = $descriptions[$plan->name]['icon'] ?? '💼';
        $text = $descriptions[$plan->name]['text'] ?? '';

        return "$icon *$plan->name*\n" .
            ($text ? "       $text\n" : '') .
            "           _Доступные регионы:_\n$serverList";
    }
}
