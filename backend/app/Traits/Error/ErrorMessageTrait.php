<?php

namespace App\Traits\Error;

trait ErrorMessageTrait
{
    protected function getErrorMessage(): string
    {
        return 'Произошла ошибка. Попробуйте еще раз.';
    }
}
