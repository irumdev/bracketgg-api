<?php

declare(strict_types=1);

namespace Database\Factories\Channel\Board;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Channel\Board\Reply;

class ReplyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Reply::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'content' => $this->faker->sentence(),
            'delete_reason' => $this->faker->sentence(),
        ];
    }
}
