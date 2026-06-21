<?php

namespace Database\Factories;

use App\Models\Laboratory;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaboratoryFactory extends Factory
{
    protected $model = Laboratory::class;

    public function definition(): array
    {
        return [
            'lab_name' => 'Lab ' . $this->faker->unique()->numerify('###'),
            'status'   => 1,
            'capacity' => $this->faker->numberBetween(20, 60),
        ];
    }
}