<?php

declare(strict_types=1);

namespace App\Contracts\Image;

interface UpdateContract
{
    public function updateImage(): bool;
    public function createImage(): bool;
}
