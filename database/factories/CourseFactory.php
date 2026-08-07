<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'course_name' => $this->faker->unique()->words(2, true),
            'user_id'     => User::factory(),
            'hours'       => $this->faker->numberBetween(1, 4),
        ];
    }
}