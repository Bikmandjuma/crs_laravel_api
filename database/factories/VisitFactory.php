<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VisitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'count' => $this->faker->numberBetween(0, 100),
            'date' => Carbon::today(),
        ];
    }
}