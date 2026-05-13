<?php

namespace App\Helpers\Params;

class ParamsHelper
{
    public static function encode(array $params): string
    {
        return implode('|', [
            $params['duration_id'],
            $params['plan_id'],
            $params['is_extension']
        ]);
    }

    public static function decode(string $data): array
    {
        $parts = explode('|', $data);

        return [
            'duration_id' => $parts[0],
            'plan_id'     => $parts[1],
            'is_extension'  => $parts[2]
        ];
    }
}
