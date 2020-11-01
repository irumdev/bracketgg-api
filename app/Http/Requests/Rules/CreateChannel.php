<?php

namespace App\Http\Requests\Rules;

class CreateChannel
{
    public const NAME_MIN_LENGTH = 1;
    public const NAME_MAX_LENGTH = 20;

    public const NAME_IS_EMPTY = 1;
    public const NAME_IS_NOT_STRING = 2;
    public const NAME_LENGTH_SHORT = 3;
    public const NAME_LENGTH_LONG = 4;
    public const NAME_IS_NOT_UNIQUE = 5;

    public static function rules(): array
    {
        return  [
            'name' => 'required|string|min:' . self::NAME_MIN_LENGTH . '|max:' . self::NAME_MAX_LENGTH . '|unique:App\Models\Channel,name',
        ];
    }

    public static function messages(): array
    {
        return [
            'name.required' => json_encode(['code' => self::NAME_IS_EMPTY]),
            'name.string' => json_encode(['code' => self::NAME_IS_NOT_STRING]),
            'name.min' => json_encode(['code' => self::NAME_LENGTH_SHORT]),
            'name.max' => json_encode(['code' => self::NAME_LENGTH_LONG]),
            'name.unique' => json_encode(['code' => self::NAME_IS_NOT_UNIQUE]),
        ];
    }
}
