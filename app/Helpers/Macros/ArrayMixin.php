<?php

declare(strict_types=1);

namespace App\Helpers\Macros;

class ArrayMixin
{
    public function changeKey(): callable
    {
        return function (array $target, string $oldKey, string $newKey) {
            $tmp = $target[$oldKey];
            $target[$newKey] = $tmp;
            unset($target[$oldKey]);
            return $target;
        };
    }
}
