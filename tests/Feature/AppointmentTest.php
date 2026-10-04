<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_sees_only_own_appointments(): void
    {
        $user = User::factory()->create();
        Appointment::factory()->for($user)->create(['notes' => 'mia']);
        $ajena = Appointment::factory()->create();

        $this->actingAs($user)->get('/citas')->assertOk()->assertViewHas('appointments', fn ($p) => $p->count() === 1);
        $this->assertNotSame($user->id, $ajena->user_id);
    }

    public function test_user_can_book_an_appointment(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $this->actingAs($user)->post('/citas', [
            'service_id' => $service->id,
            'starts_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
        ])->assertRedirect('/citas');

        $this->assertDatabaseHas('appointments', ['user_id' => $user->id, 'service_id' => $service->id, 'status' => 'pending']);
    }

    public function test_cannot_book_in_the_past(): void
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $this->actingAs($user)->post('/citas', [
            'service_id' => $service->id,
            'starts_at' => now()->subDay()->format('Y-m-d H:i:s'),
        ])->assertSessionHasErrors('starts_at');
    }

    public function test_cannot_double_book_same_slot(): void
    {
        $service = Service::factory()->create();
        $slot = now()->addDays(3)->setSecond(0)->format('Y-m-d H:i:s');
        Appointment::factory()->create(['service_id' => $service->id, 'starts_at' => $slot, 'status' => 'confirmed']);

        $this->actingAs(User::factory()->create())->post('/citas', [
            'service_id' => $service->id, 'starts_at' => $slot,
        ])->assertSessionHasErrors('starts_at');
    }

    public function test_cancelled_slot_can_be_booked_again(): void
    {
        $service = Service::factory()->create();
        $slot = now()->addDays(3)->setSecond(0)->format('Y-m-d H:i:s');
        Appointment::factory()->create(['service_id' => $service->id, 'starts_at' => $slot, 'status' => 'cancelled']);

        $this->actingAs(User::factory()->create())->post('/citas', [
            'service_id' => $service->id, 'starts_at' => $slot,
        ])->assertSessionHasNoErrors();
    }

    public function test_user_can_cancel_own_but_not_others(): void
    {
        $user = User::factory()->create();
        $mine = Appointment::factory()->for($user)->create(['status' => 'pending']);
        $other = Appointment::factory()->create(['status' => 'pending']);

        $this->actingAs($user)->patch("/citas/{$mine->id}/cancelar")->assertRedirect();
        $this->assertSame('cancelled', $mine->fresh()->status);

        $this->actingAs($user)->patch("/citas/{$other->id}/cancelar")->assertForbidden();
        $this->assertSame('pending', $other->fresh()->status);
    }
}
