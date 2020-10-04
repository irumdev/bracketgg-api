<?php

namespace App\Helpers;

use Illuminate\Contracts\Validation\Validator;
use App\Helpers\ValidJson;

class ValidMessage
{
    public static function first(Validator $validator): array
    {
        $error = collect(
            $validator->errors()
        );
        $firstError = $error->first();

        do {
            $error = array_shift($firstError);
            if (ValidJson::isJson($error)) {
                $error = json_decode($error, true);
                break;
            }
        } while (count($firstError) !== 0);

        return $error;
    }
}
