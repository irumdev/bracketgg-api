<?php

declare(strict_types=1);

namespace App\Wrappers;

class UpdateBroadcastTypeWrapper
{
    public array $platforms;
    public string $filterType;
    public string $table;

    public function __construct(array $platforms, string $filterType, string $table)
    {
        $this->platforms = $platforms;
        $this->filterType = $filterType;
        $this->table = $table;
    }
}
