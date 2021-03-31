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

if (! function_exists('uglifyHtml')) {
    function uglifyHtml(string $prettifyHtml): string
    {
        $searchRegexps = [
            '/(\n|^)(\x20+|\t)/',
            '/(\n|^)\/\/(.*?)(\n|$)/',
            '/\n/',
            '/\<\!--.*?-->/',
            '/(\x20+|\t)/',
            '/\>\s+\</',
            '/(\"|\')\s+\>/',
            '/=\s+(\"|\')/'
        ];

        $willReplaceItems = [
            "\n",
            "\n",
            " ",
            "",
            " ",
            "><",
            "$1>",
            "=$1"

        ];

        return preg_replace($searchRegexps, $willReplaceItems, trim($prettifyHtml));
    }
}
