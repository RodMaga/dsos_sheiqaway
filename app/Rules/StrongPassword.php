<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class StrongPassword implements Rule
{
    public function passes($attribute, $value)
    {
        return strlen($value) >= 8 &&
               preg_match('/[a-z]/', $value) &&
               preg_match('/[A-Z]/', $value) &&
               preg_match('/[0-9]/', $value) &&
               preg_match('/[^a-zA-Z0-9]/', $value);
    }

    public function message()
    {
        return 'A palavra-passe deve ter pelo menos 8 caracteres, incluindo maiúsculas, minúsculas, números e símbolos.';
    }
}