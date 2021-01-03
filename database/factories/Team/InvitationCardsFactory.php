<?php

namespace Database\Factories\Team;

use App\Models\Team\InvitationCard;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvitationCardFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InvitationCard::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [];
    }
}
