<?php

declare(strict_types=1);

namespace Tests\Feature\GameType;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\GameType;
use Styde\Enlighten\Tests\EnlightenSetup;

class SearchTest extends TestCase
{
    use EnlightenSetup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnlighten();
    }

    /**
     * @test
     * @enlighten
     */
    public function failSearchItemWhenQueryIsEmpty(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $trySearchGameTypes = $this->getJson(route('gameTypes.getByKeyword', [
            'query' => '',
        ]))->assertStatus(422);

        $this->assertFalse($trySearchGameTypes['ok']);
        $this->assertFalse($trySearchGameTypes['isValid']);
        $this->assertEquals(['code' => 1], $trySearchGameTypes['messages']);
    }


    /**
     * @test
     * @enlighten
     */
    public function failSearchItemWhenQueryIsNotFound(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $trySearchGameTypes = $this->getJson(route('gameTypes.getByKeyword', [
            'query' => \Illuminate\Support\Str::random(256),
        ]))->assertNotFound();

        $this->assertFalse($trySearchGameTypes['ok']);
        $this->assertFalse($trySearchGameTypes['isValid']);
        $this->assertNotFoundMessages($trySearchGameTypes['messages']);
    }

    /**
     * @test
     * @enlighten
     */
    public function successSearchGameTypes(): void
    {
        $this->setName($this->getCurrentCaseKoreanName());
        $activeUser = Sanctum::actingAs(factory(User::class)->create());
        $items = collect([
            'test_1', 'test_2', 'test_3',
            'test_4', 'test_5', 'test_6',
            'test_7', 'test_8', 'test_9',
            'test_10', 'test_11', 'test_12',
            'test_13', 'test_14', 'test_15',
            'test_16', 'test_17', 'test_18'
        ]);

        $gameTypes = $items->map(function (string $keyword): GameType {
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
            $trySearchGameTypes = $this->getJson(route('gameTypes.getByKeyword', [
                'query' => 'test_',
                'page' => $page
            ]))->assertOk();

            $this->assertTrue($trySearchGameTypes['ok']);
            $this->assertTrue($trySearchGameTypes['isValid']);
            $this->assertNotNull($trySearchGameTypes['messages']['meta']['length']);

            $viewLen = $trySearchGameTypes['messages']['meta']['length'];

            $searchItems = collect($trySearchGameTypes['messages']['types'])->map(fn (array $type): string => $type['name']);

            $chunkedItem->get($trySearchGameTypes['messages']['meta']['curr'] - 1)->each(function (string $keyword) use ($searchItems, $page): void {
                $this->assertTrue($searchItems->contains($keyword));
            });

            $page += 1;
        } while ($trySearchGameTypes['messages']['meta']['hasMorePage']);
    }
}
