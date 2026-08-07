<?php

namespace Database\Factories;

use App\Models\Schedule;
use App\Models\Laboratory;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        $start = $this->faker->numberBetween(8, 15);
        return [
            'schedule_type' => 'enroll',
            'lab_id'        => Laboratory::factory(),
            'course_id'     => Course::factory(),
            'semester_id'   => null,
            'booking_id'    => null,
            'day_of_week'   => 'Monday',
            'date'          => $this->faker->dateTimeBetween('+1 week', '+2 weeks')->format('Y-m-d'),
            'start_time'    => sprintf('%02d:00:00', $start),
            'end_time'      => sprintf('%02d:00:00', $start + 2),
            'is_recurring'  => true,
        ];
    }
}