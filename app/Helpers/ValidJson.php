<?php

namespace App\Helpers;

use Illuminate\Contracts\Validation\Validator;

/**
 * Json이 유효한지 안한지 여부를 체크해주는 클래스 입니다.
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class ValidJson
{
    /**
     * 유효한 제이슨인지 판단하는 메소드 입니다.
     *
     * @param string $jsonString 제이슨 스트링입
     * @return boolean 유효한 제이슨 여부
     */
    public static function isJson(string $jsonString): bool
    {
        $jsonObject = json_decode($jsonString);

        return json_last_error() === JSON_ERROR_NONE;
    }
}
