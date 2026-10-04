<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_normal_user_cannot_access_admin_area(): void
    {
        $user = User::factory()->create();

        foreach (['/admin', '/admin/servicios', '/admin/citas'] as $url) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }
    }

    public function test_guest_is_redirected_from_admin(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_admin_dashboard_computes_stats(): void
    {
        $admin = User::factory()->admin()->create();
        $corte = Service::factory()->create(['name' => 'Corte', 'price' => 20]);
        Appointment::factory()->count(2)->create(['service_id' => $corte->id, 'status' => 'confirmed']);
        Appointment::factory()->create(['service_id' => $corte->id, 'status' => 'pending']);
        Appointment::factory()->create(['service_id' => $corte->id, 'status' => 'cancelled']);

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertViewHas('total', 4)
            ->assertViewHas('revenue', fn ($r) => (float) $r === 40.0)
            ->assertViewHas('byService', fn ($s) => $s['Corte'] === 3);
    }

    public function test_admin_can_create_and_delete_service(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/servicios', ['name' => 'Barba', 'duration_minutes' => 20, 'price' => 10])
            ->assertSessionHasNoErrors();
        $service = Service::firstWhere('name', 'Barba');
        $this->assertNotNull($service);

        $this->actingAs($admin)->delete("/admin/servicios/{$service->id}");
        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    public function test_cannot_delete_service_with_appointments(): void
    {
        $admin = User::factory()->admin()->create();
        $a = Appointment::factory()->create();

        $this->actingAs($admin)->delete("/admin/servicios/{$a->service_id}")->assertSessionHasErrors('service');
        $this->assertDatabaseHas('services', ['id' => $a->service_id]);
    }

    public function test_admin_can_change_status_and_validation_applies(): void
    {
        $admin = User::factory()->admin()->create();
        $a = Appointment::factory()->create(['status' => 'pending']);

        $this->actingAs($admin)->patch("/admin/citas/{$a->id}", ['status' => 'confirmed']);
        $this->assertSame('confirmed', $a->fresh()->status);

        $this->actingAs($admin)->patch("/admin/citas/{$a->id}", ['status' => 'raro'])->assertSessionHasErrors('status');
    }

    public function test_normal_user_cannot_change_status(): void
    {
        $a = Appointment::factory()->create(['status' => 'pending']);

        $this->actingAs(User::factory()->create())->patch("/admin/citas/{$a->id}", ['status' => 'confirmed'])->assertForbidden();
        $this->assertSame('pending', $a->fresh()->status);
    }
}
