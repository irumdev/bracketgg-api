<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    public function refresh(): void
    {
        $this->artisan('migrate:fresh');
    }

    public function getCurrentCaseKoreanName(): string
    {
        $calledClassName = array_merge(array_filter(explode("Tests\\Feature\\", get_called_class())));

        $calledMethodName = $this->getName();
        $korCaseName = __(join('.', [
            'testCase',
            str_replace('\\', '.', $calledClassName[0]),
            $calledMethodName,
        ]));

        return count(explode('testCase', $korCaseName)) >= 2 ? $calledMethodName : $korCaseName;
    }

    public function assertNotFoundMessages(array $messages): void
    {
        $this->assertEquals(['code' => Response::HTTP_NOT_FOUND], $messages);
    }

    public function assertUnauthorizedMessages(array $messages): void
    {
        $this->assertEquals(['code' => Response::HTTP_UNAUTHORIZED], $messages);
    }
}
