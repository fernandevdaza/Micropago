<?php

namespace Tests\Feature;

use App\Models\TransportLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransportLineTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_sees_all_lines(): void
    {
        TransportLine::factory()->count(3)->create();
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->getJson('/api/transport-lines')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_admin_sees_all_lines(): void
    {
        TransportLine::factory()->count(3)->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->getJson('/api/transport-lines')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_line_admin_sees_only_own_line(): void
    {
        $lineA = TransportLine::factory()->create();
        TransportLine::factory()->create(); // lineB — no debe aparecer

        $lineAdmin = User::factory()->lineAdmin($lineA->id)->create();

        $this->actingAs($lineAdmin)->getJson('/api/transport-lines')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $lineA->id);
    }

    public function test_passenger_cannot_list_lines(): void
    {
        $passenger = User::factory()->passenger()->create();

        $this->actingAs($passenger)->getJson('/api/transport-lines')->assertForbidden();
    }

    public function test_admin_can_create_line(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->postJson('/api/transport-lines', [
            'name'        => 'Línea 12',
            'description' => 'Norte - Sur',
        ])->assertCreated()->assertJsonPath('data.name', 'Línea 12');
    }

    public function test_super_admin_can_create_line(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->postJson('/api/transport-lines', [
            'name' => 'Línea 33',
        ])->assertCreated();
    }

    public function test_line_admin_cannot_create_line(): void
    {
        $line      = TransportLine::factory()->create();
        $lineAdmin = User::factory()->lineAdmin($line->id)->create();

        $this->actingAs($lineAdmin)->postJson('/api/transport-lines', [
            'name' => 'Nueva línea no permitida',
        ])->assertForbidden();
    }

    public function test_admin_can_update_line(): void
    {
        $admin = User::factory()->admin()->create();
        $line  = TransportLine::factory()->create();

        $this->actingAs($admin)->patchJson("/api/transport-lines/{$line->id}", [
            'name' => 'Línea Editada',
        ])->assertOk()->assertJsonPath('data.name', 'Línea Editada');
    }

    public function test_line_admin_cannot_delete_line(): void
    {
        $line      = TransportLine::factory()->create();
        $lineAdmin = User::factory()->lineAdmin($line->id)->create();

        $this->actingAs($lineAdmin)->deleteJson("/api/transport-lines/{$line->id}")
            ->assertForbidden();
    }

    public function test_admin_cannot_delete_line(): void
    {
        $admin = User::factory()->admin()->create();
        $line  = TransportLine::factory()->create();

        $this->actingAs($admin)->deleteJson("/api/transport-lines/{$line->id}")
            ->assertForbidden();
    }

    public function test_super_admin_can_delete_line(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $line       = TransportLine::factory()->create();

        $this->actingAs($superAdmin)->deleteJson("/api/transport-lines/{$line->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('transport_lines', ['id' => $line->id]);
    }
}
