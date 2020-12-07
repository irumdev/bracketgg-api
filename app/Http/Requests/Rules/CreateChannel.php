<?php

declare(strict_types=1);

namespace App\Http\Requests\Rules;

/**
 * 채널,팀 생성을 위한 룰 공통 클래스 입니다
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class CreateChannel
{
    /**
     * @var int 이름 최소 자리수
     */
    public const NAME_MIN_LENGTH = 1;

    /**
     * @var int 이름 최대 자리수
     */
    public const NAME_MAX_LENGTH = 20;

    /**
     * @var int 이름이 비어있음
     */
    public const NAME_IS_EMPTY = 1;

    /**
     * @var int 이름이 스트링이 아님
     */
    public const NAME_IS_NOT_STRING = 2;

    /**
     * @var int 이름이 최소자리수 미달
     */
    public const NAME_LENGTH_SHORT = 3;

    /**
     * @var int 이름 최대자리수 초과
     */
    public const NAME_LENGTH_LONG = 4;

    /**
     * @var int 이름이 고유하지 않음
     */
    public const NAME_IS_NOT_UNIQUE = 5;

    /**
     * 채널 이름 생성 규칙을 만들어줍니다.
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return array 채널생성 규칙
     */
    public static function rules(): array
    {
        return  [
            'name' => 'required|string|min:' . self::NAME_MIN_LENGTH . '|max:' . self::NAME_MAX_LENGTH . '|unique:App\Models\Channel\Channel,name',
        ];
    }

    /**
     * 채널 생성룰 위반시 에러메세지 정보들 입니다.
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return array 룰 위반시 메세지들
     */
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
