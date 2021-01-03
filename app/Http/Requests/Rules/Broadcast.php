<?php

declare(strict_types=1);

namespace App\Http\Requests\Rules;

use App\Wrappers\UpdateBroadcastTypeWrapper;
use Illuminate\Support\Str;

/**
 * 채널, 팀 방송국 주소를 위한 룰 클래스 입니다.
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class Broadcast
{
    private static UpdateBroadcastTypeWrapper $attribute;
    /**
     * @var int 이미 존재하는 방송국 주소
     */
    public const BROADCAST_URL_IS_NOT_UNIQUE = 30;

    /**
     * @var int 방송국 주소가 배열이 아님
     */
    public const BROADCAST_IS_NOT_ARRAY = 31;

    /**
     * @var int 방송국 플랫폼을 지정하지 않음
     */
    public const BROADCAST_ADDRESS_HAS_NOT_PLATFORM = 32;

    /**
     * @var int 방송국 주소를 첨부하지 않음
     */
    public const BROADCAST_ADDRESS_HAS_NOT_URL = 33;

    /**
     * @var int 방송국 플랫폼이 올바르지 않음
     */
    public const BROADCAST_PLATFORM_IS_INVALID = 34;

    /**
     * @var int 방송국 아이디가 숫자가 아님
     */
    public const BROADCAST_ID_IS_NOT_NUMERIC = 35;

    /**
     * @var int 방송국 주소가 문자열 아님
     */
    public const BROADCAST_URL_IS_NOT_STRING = 36;

    /**
     * @var int 방송국 주소가 문자열 아님
     */
    public const BROADCAST_ID_IS_NOT_BELONGS_TO_MY_TEAM = 37;

    public static function broadcastRules(UpdateBroadcastTypeWrapper $attribute): array
    {
        self::$attribute = $attribute;
        return [
            'broadcasts' => 'nullable|array',
            'broadcasts.*.url' => [
                'bail',
                'string',
                'required_with:broadcasts.*.platform',
                'unique:team_broadcasts,broadcast_address',
                sprintf("unique:%s,broadcast_address", $attribute->table)
                // 'unique:team_broadcasts,broadcast_address'
            ],
            'broadcasts.*.platform' => [
                'bail',
                'numeric',
                'required_with:broadcasts.*.url',
                'in:' . collect($attribute->platforms)->keys()->implode(',')
            ],
            'broadcasts.*.id' => [
                'bail',
                'nullable',
                'numeric',
                $attribute->filterType,
            ],
        ];
    }

    public static function messages(): array
    {
        return [
            'broadcasts.array' => self::BROADCAST_IS_NOT_ARRAY,

            'broadcasts.*.url.required_with' => self::BROADCAST_ADDRESS_HAS_NOT_PLATFORM,
            'broadcasts.*.url.unique' => self::BROADCAST_URL_IS_NOT_UNIQUE,
            'broadcasts.*.url.string' => self::BROADCAST_URL_IS_NOT_STRING,

            'broadcasts.*.platform.required_with' => self::BROADCAST_ADDRESS_HAS_NOT_URL,
            'broadcasts.*.platform.in' => self::BROADCAST_PLATFORM_IS_INVALID,
            'broadcasts.*.platform.numeric' => self::BROADCAST_PLATFORM_IS_INVALID,

            'broadcasts.*.id.numeric' => self::BROADCAST_ID_IS_NOT_NUMERIC,
            'broadcasts.*.id.' . Str::snake(self::$attribute->filterType) => self::BROADCAST_ID_IS_NOT_BELONGS_TO_MY_TEAM,

        ];
    }
}
