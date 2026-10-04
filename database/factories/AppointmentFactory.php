<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'service_id' => Service::factory(),
            'starts_at' => fake()->dateTimeBetween('+1 day', '+30 days'),
            'status' => fake()->randomElement(Appointment::STATUSES),
            'notes' => null,
        ];
    }
}
