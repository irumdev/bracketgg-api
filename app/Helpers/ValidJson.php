<?php

namespace App\Helpers;

use Illuminate\Contracts\Validation\Validator;

class ValidJson
{
    public static function isJson(string $jsonString): bool
    {
        $jsonObject = json_decode($jsonString);

        return json_last_error() === JSON_ERROR_NONE;
    }
}
