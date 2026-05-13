<?php

namespace App\Telegraph\Handlers\Extension\Steps\Tariff;

class TariffMessage
{
    public static function message(array $planDescriptions): string
    {
        return  "
💼 *Выберите тарифный план*

Пожалуйста, выберите подходящий тариф из списка ниже.

" . implode("\n\n", $planDescriptions) . "

🌍 *Регионы* — это географические локации серверов. Больше регионов означает больше возможностей для стабильного подключения.
";
    }
}
