<?php

declare(strict_types=1);

namespace App\Helpers\Macros;

use Illuminate\Support\Str;
use App\Http\Requests\Rules\Slug;

/**
 * Str 헬퍼에 메소드를 추가하기 위한 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class StringMixin
{
    /**
     * 채널, 팀에 맞는 랜덤 슬러그를 생성하기 위한 메소드 입니다.
     *
     * @param int $min 최소자리수
     * @param int $max 최대자리수
     * @author dhtmdgkr123 <osh12201@gmail.com>
     * @version 1.0.0
     * @return callable 콜백 펑션
     */
    public function bracketGGslug(): callable
    {
        return function (int $min, int $max): string {
            do {
                $randSlug = Str::random(random_int($min, $max));
            } while (preg_match(Slug::PATTERN, $randSlug) === 0);
            return $randSlug;
        };
    }
}
