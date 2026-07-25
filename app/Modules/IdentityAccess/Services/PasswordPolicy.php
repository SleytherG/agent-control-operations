<?php

namespace App\Modules\IdentityAccess\Services;

use Illuminate\Validation\Rules\Password;

class PasswordPolicy
{
    private const LETTERS = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    private const NUMBERS = '23456789';
    private const SYMBOLS = '!@#$%&*+-_=';

    public function permanentRule(): Password
    {
        return Password::min(8);
    }

    public function generateTemporary(): string
    {
        $length = max(8, (int) config('session-security.password_reset.temporary_length', 20));
        $all = self::LETTERS.self::NUMBERS.self::SYMBOLS;
        $characters = [
            self::LETTERS[random_int(0, strlen(self::LETTERS) - 1)],
            self::NUMBERS[random_int(0, strlen(self::NUMBERS) - 1)],
            self::SYMBOLS[random_int(0, strlen(self::SYMBOLS) - 1)],
        ];

        while (count($characters) < $length) {
            $characters[] = $all[random_int(0, strlen($all) - 1)];
        }

        for ($index = count($characters) - 1; $index > 0; $index--) {
            $swap = random_int(0, $index);
            [$characters[$index], $characters[$swap]] = [$characters[$swap], $characters[$index]];
        }

        return implode('', $characters);
    }
}
