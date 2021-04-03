<?php

declare(strict_types=1);

namespace App\Wrappers;

class UpdateImageTypeWrapper
{
    public const LOGO_IMAGE = 'logo';
    public const BASE = 'base';
    public const BANNER_IMAGE = 'banner';

    public function __construct(public string $target, public string $type)
    {
        $this->target = strtolower($target);
        $this->type = strtolower($type);
    }
}
