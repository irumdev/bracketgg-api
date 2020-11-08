<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    public function refresh(): void
    {
        $this->artisan('migrate:fresh');
    }

    public function getCurrentCaseKoreanName(): string
    {
        $calledClassName = substr(strrchr(get_called_class(), "\\"), 1);
        $calledMethodName = $this->getName();
        $korCaseName = __('testCase.' . $calledClassName . '.' . $calledMethodName);
        return count(explode('testCase', $korCaseName)) >= 2 ? $calledMethodName : $korCaseName;
    }
}
