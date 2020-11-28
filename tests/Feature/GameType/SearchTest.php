<?php

declare(strict_types=1);

namespace Tests\Feature\GameType;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\GameType;

class SearchTest extends TestCase
{
    /** @test */
    public function failSearchItemWhenQueryIsEmpty(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $trySearchGameTypes = $this->getJson(route('getGameTypeByKeyword', [
            'query' => '',
        ]))->assertStatus(422);

        $this->assertFalse($trySearchGameTypes['ok']);
        $this->assertFalse($trySearchGameTypes['isValid']);
        $this->assertEquals(['code' => 1], $trySearchGameTypes['messages']);
    }


    /** @test */
    public function failSearchItemWhenQueryIsNotFound(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $trySearchGameTypes = $this->getJson(route('getGameTypeByKeyword', [
            'query' => \Illuminate\Support\Str::random(256),
        ]))->assertNotFound();

        $this->assertFalse($trySearchGameTypes['ok']);
        $this->assertFalse($trySearchGameTypes['isValid']);
        $this->assertEquals(['code' => 404], $trySearchGameTypes['messages']);
    }

    /** @test */
    public function successSearchGameTypes(): void
    {
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $items = collect([
            'test_1', 'test_2', 'test_3',
            'test_4', 'test_5', 'test_6',
            'test_7', 'test_8', 'test_9',
            'test_10', 'test_11', 'test_12',
            'test_13', 'test_14', 'test_15',
            'test_16', 'test_17', 'test_18'
        ]);

        $gameTypes = $items->map(function ($keyword) {
            if (GameType::where('name', $keyword)->exists() === false) {
                return GameType::factory()->create([
                    'name' => $keyword
                ]);
            }
            return GameType::where('name', $keyword)->first();
        });

        $page = 1;

        $viewLen = 15;
        $chunkedItem = $items->chunk($viewLen);

        do {
            $trySearchGameTypes = $this->getJson(route('getGameTypeByKeyword', [
                'query' => 'test_',
                'page' => $page
            ]))->assertOk();

            $this->assertTrue($trySearchGameTypes['ok']);
            $this->assertTrue($trySearchGameTypes['isValid']);
            $this->assertNotNull($trySearchGameTypes['messages']['meta']['length']);

            $viewLen = $trySearchGameTypes['messages']['meta']['length'];

            $searchItems = collect($trySearchGameTypes['messages']['types'])->map(fn ($type) => $type['name']);

            $chunkedItem->get($trySearchGameTypes['messages']['meta']['curr'] - 1)->each(function ($keyword) use ($searchItems, $page) {
                $this->assertTrue($searchItems->contains($keyword));
            });

            $page += 1;
        } while ($trySearchGameTypes['messages']['meta']['hasMorePage']);
    }
}
