<?php

namespace App\DTO\Actions\Auth;

class LoginDto
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}

    public function all(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
