<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'password']);
        $cliente = User::factory()->create(['name' => 'Cliente', 'email' => 'cliente@example.com', 'password' => 'password']);

        $servicios = collect([
            ['Corte de pelo', 30, 15], ['Tinte', 90, 45], ['Barba', 20, 10], ['Peinado', 45, 25],
        ])->map(fn ($s) => Service::create(['name' => $s[0], 'duration_minutes' => $s[1], 'price' => $s[2]]));

        Appointment::factory()->count(25)->recycle($servicios)->create();
        Appointment::factory()->count(3)->for($cliente)->recycle($servicios)->create();
    }
}
