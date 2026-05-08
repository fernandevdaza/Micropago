<?php

namespace Tests\Feature;

use App\Models\Tariff;
use App\Models\TransportLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TariffTest extends TestCase
{
    use RefreshDatabase;

    public function test_passenger_can_list_tariffs(): void
    {
        Tariff::factory()->count(2)->create();
        $passenger = User::factory()->passenger()->create();

        $this->actingAs($passenger)->getJson('/api/tariffs')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_driver_can_list_tariffs(): void
    {
        Tariff::factory()->count(2)->create();
        $line   = TransportLine::factory()->create();
        $driver = User::factory()->driver($line->id)->create();

        $this->actingAs($driver)->getJson('/api/tariffs')->assertOk();
    }

    public function test_super_admin_can_create_tariff(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->postJson('/api/tariffs', [
            'name'  => 'General',
            'price' => 2.80,
        ])->assertCreated()
            ->assertJsonPath('data.nombre_tarifa', 'General')
            ->assertJsonPath('data.precio_bs', 2.80);
    }

    public function test_create_tariff_fails_with_duplicate_name(): void
    {
        Tariff::factory()->create(['name' => 'General']);
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->postJson('/api/tariffs', [
            'name'  => 'General',
            'price' => 3.00,
        ])->assertUnprocessable();
    }

    public function test_admin_cannot_create_tariff(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->postJson('/api/tariffs', [
            'name'  => 'Especial',
            'price' => 5.00,
        ])->assertForbidden();
    }

    public function test_super_admin_can_update_tariff(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $tariff     = Tariff::factory()->create(['price' => 2.00]);

        $this->actingAs($superAdmin)->patchJson("/api/tariffs/{$tariff->id}", [
            'price' => 3.50,
        ])->assertOk()->assertJsonPath('data.precio_bs', 3.50);
    }

    public function test_admin_cannot_delete_tariff(): void
    {
        $admin  = User::factory()->admin()->create();
        $tariff = Tariff::factory()->create();

        $this->actingAs($admin)->deleteJson("/api/tariffs/{$tariff->id}")
            ->assertForbidden();
    }

    public function test_super_admin_can_delete_tariff(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $tariff     = Tariff::factory()->create();

        $this->actingAs($superAdmin)->deleteJson("/api/tariffs/{$tariff->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('tariffs', ['id' => $tariff->id]);
    }
}
