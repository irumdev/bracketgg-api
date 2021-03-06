<?php

declare(strict_types=1);

namespace App\Factories;

/**
 * 방송국 정보를 저장하는 타입 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class BroadcastFactory
{
    public const YOUTUBE = 1;
    public const TWICH = 2;
    public const AFERICA = 3;
    public const INATAGRAM = 4;
    public const FACEBOOK = 5;
    public const NAVER = 6;
    public const KAKAO = 7;

    public const PLATFORMS = [
        self::YOUTUBE => '유튜브',
        self::TWICH => '트위치',
        self::AFERICA => '아프리카 TV',
        self::INATAGRAM => '인스타그램',
        self::FACEBOOK => '페이스북',
        self::NAVER => '네이버',
        self::KAKAO => '카카오 TV',
    ];
}
