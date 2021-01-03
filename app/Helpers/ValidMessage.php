<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Contracts\Validation\Validator;
use App\Helpers\ValidJson;
use Exception;

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
     * 정보가 없는 경우
     * @var int NOT_EXISTS
     */
    private const NOT_EXISTS = 0;

    /**
     * 여러가지 에러 메세지중 첫번째 에러 메시지를
     * 추출하는 메소드 입니다.
     *
     * @param   lluminate\Contracts\Validation\Validator 벨러데이터 인스턴스
     * @author  dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return array 에러정보를 담은 array
     */
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
        } while (count($firstError) !== self::NOT_EXISTS);

        if (is_array($error) === false) {
            $errorDataJson = collect(
                $validator->errors()
            )->toJson();
            throw new \TypeError(sprintf('Invalid Type / %s / %s', $error, $errorDataJson));
        }
        return $error;
    }
}
