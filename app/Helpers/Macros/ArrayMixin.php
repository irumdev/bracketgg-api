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

    public function replaceItemByKey(): callable
    {
        return function (array $items, $key, $item) {
            $items[$key] = $item;
            return $items;
        };
    }
}
