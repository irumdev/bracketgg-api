<?php

namespace Database\Factories\Team;

use App\Models\Team\Slug;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SlugFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Slug::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            //
            'slug' => Str::random(random_int(3, 20))
        ];
    }
}
