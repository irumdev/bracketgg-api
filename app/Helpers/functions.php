<?php

declare(strict_types=1);

if (! function_exists('executeIf')) {
    function executeIf(bool $canExecute, callable $executeTarget)
    {
        if ($canExecute) {
            return $executeTarget($canExecute);
        }
    }
}

if (! function_exists('executeUnless')) {
    function executeUnless(bool $canExecute, callable $executeTarget)
    {
        if (! $canExecute) {
            return $executeTarget($canExecute);
        }
    }
}
