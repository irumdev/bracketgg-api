<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Contracts\Validation\Validator;

/**
 * 밸러데이션에 통과하지 못한 값에 대한 에러코드를 가져오는
 * 클래스 입니다.
 *
 * @author  dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class ValidMessage
{
    /**
     * 여러가지 에러 메세지중 첫번째 에러 메시지를
     * 추출하는 메소드 입니다.
     *
     * @param   lluminate\Contracts\Validation\Validator 벨러데이터 인스턴스
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return array 에러정보를 담은 array
     */
    public static function first(Validator $validator): int
    {
        $error = collect(
            $validator->errors()
        );
        $firstError = $error->first();

        return (int)$firstError[0];
    }
}
