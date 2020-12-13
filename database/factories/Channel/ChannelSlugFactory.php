<?php

namespace Database\Factories\Channel;

use App\Models\Channel\Slug as ChannelSlug;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SlugFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ChannelSlug::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'slug' => Str::bracketGGslug(ChannelSlug::MIN_SLUG_LENGTH, ChannelSlug::MAX_SLUG_LENGTH),
        ];
    }
}
