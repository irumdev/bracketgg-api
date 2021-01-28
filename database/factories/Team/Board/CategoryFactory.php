<?php

namespace Database\Factories\Team\Board;

use App\Models\Team\Board\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'show_order' => 1,
            'article_count' => 0,
            'is_public' => random_int(0, 1) === 0,
        ];
    }
}
