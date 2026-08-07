<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Laboratory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $start = $this->faker->numberBetween(8, 16);
        return [
            'user_id'    => User::factory(),
            'lab_id'     => Laboratory::factory(),
            'type'       => 'booking',
            'booker_name'=> $this->faker->name,
            'purpose'    => $this->faker->sentence(3),
            'date'       => $this->faker->dateTimeBetween('+1 week', '+2 weeks')->format('Y-m-d'),
            'start_time' => sprintf('%02d:00:00', $start),
            'end_time'   => sprintf('%02d:00:00', $start + 2),
            'status'     => 2,
        ];
    }
}