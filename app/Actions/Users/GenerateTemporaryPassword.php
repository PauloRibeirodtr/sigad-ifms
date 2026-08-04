<?php

namespace App\Actions\Users;

use Illuminate\Support\Str;

class GenerateTemporaryPassword
{
    public function execute(): string
    {
        do {
            $password = Str::password(length: 14, letters: true, numbers: true, symbols: true);
        } while (! preg_match('/[a-z]/', $password)
            || ! preg_match('/[A-Z]/', $password)
            || ! preg_match('/\d/', $password)
            || ! preg_match('/[^a-zA-Z0-9]/', $password));

        return $password;
    }
}
