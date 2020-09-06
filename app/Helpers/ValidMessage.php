<?php

namespace App\Helpers;

use Illuminate\Contracts\Validation\Validator;

class ValidMessage
{
    public static function first(Validator $validator): array
    {
        $error = collect(
            $validator->errors()
        );
        $firstErrorKey = $error->keys()->first();
        $firstError = is_array($error->get($firstErrorKey)[0]) ? $error->get($firstErrorKey)[0] : json_decode(
            $error->get($firstErrorKey)[0],
            true
        );
        return [
            'code' => $firstError['code'],
        ];
    }
}
