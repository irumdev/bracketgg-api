<?php

declare(strict_types=1);

namespace App\Http\Requests\Rules;

/**
 * 슬러그 패턴을 래핑해놓은 클래스 입니다.
 *
 * @author dhtmdgkr123 <osh12201@gmail.com>
 * @version 1.0.0
 */
class Slug
{
    public const PATTERN = '/^[a-z]{1}[a-zA-Z0-9\-]{2,16}$/';
}
