<?php

declare(strict_types=1);

namespace App\Wrappers\BoardWritePermission;

use Illuminate\Support\Collection;
use ReflectionClass;

class Channel
{
    /**
     * @var int ONLY_OWNER 채널장만 사용 가능
     */
    public const ONLY_OWNER = 1;

    /**
     * @var int ALL_USER 모든 유저 작성 가능
     */
    public const ALL_USER = 3;

    public static function getAllPermissions(): Collection
    {
        return collect((new ReflectionClass(__CLASS__))->getConstants());
    }
}
