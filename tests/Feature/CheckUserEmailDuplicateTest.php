<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;
use App\Models\User;


class CheckUserEmailDuplicateTest extends TestCase
{
    /** @test */
    public function 이메일이_중복되지않아_false_를_받아라()
    {
        $tryCheckEmailDuplicate = $this->getJson(route('checkEmailDuplicate', [
            'email' => Str::random(10) . '@' . Str::random(10),
        ]))->assertOk();

        $this->assertTrue($tryCheckEmailDuplicate['ok']);
        $this->assertTrue($tryCheckEmailDuplicate['isValid']);
        $this->assertFalse($tryCheckEmailDuplicate['messages']['isDuplicate']);
    }

    /** @test */
    public function 이메일이_중복되_true_를_받아라()
    {
        $user = factory(User::class)->create();
        $tryCheckEmailDuplicate = $this->getJson(route('checkEmailDuplicate', [
            'email' => $user->email,
        ]))->assertStatus(422);

        $this->assertFalse($tryCheckEmailDuplicate['ok']);
        $this->assertFalse($tryCheckEmailDuplicate['isValid']);
        $this->assertTrue($tryCheckEmailDuplicate['messages']['isDuplicate']);
    }
}
