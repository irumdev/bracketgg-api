<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;
use App\Models\User;

class EmailDuplicateTest extends TestCase
{
    /** @test */
    public function getFalseWhenEmailIsNotDuplicate(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $tryCheckEmailDuplicate = $this->getJson(route('checkEmailDuplicate', [
            'email' => Str::random(10) . '@' . Str::random(10),
        ]))->assertOk();

        $this->assertTrue($tryCheckEmailDuplicate['ok']);
        $this->assertTrue($tryCheckEmailDuplicate['isValid']);
        $this->assertFalse($tryCheckEmailDuplicate['messages']['isDuplicate']);
    }

    /** @test */
    public function getTrueWhenEmailIsDuplicate(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $user = factory(User::class)->create();
        $tryCheckEmailDuplicate = $this->getJson(route('checkEmailDuplicate', [
            'email' => $user->email,
        ]))->assertStatus(422);

        $this->assertFalse($tryCheckEmailDuplicate['ok']);
        $this->assertFalse($tryCheckEmailDuplicate['isValid']);
        $this->assertTrue($tryCheckEmailDuplicate['messages']['isDuplicate']);
    }
}
