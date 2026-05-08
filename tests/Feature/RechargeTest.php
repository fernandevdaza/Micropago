<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\TransportLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RechargeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_recharge_passenger(): void
    {
        $admin     = User::factory()->admin()->create();
        $passenger = User::factory()->passenger()->withBalance(5.0)->create();

        $this->actingAs($admin)->postJson('/api/admin/recharge', [
            'user_id' => $passenger->id,
            'amount'  => 50,
        ])->assertOk()->assertJsonPath('message', 'Recarga exitosa');

        $this->assertEquals(55.0, $passenger->fresh()->balance);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $passenger->id,
            'type'    => 'recharge',
            'amount'  => 50,
            'status'  => 'completed',
        ]);
    }

    public function test_super_admin_can_recharge_passenger(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $passenger  = User::factory()->passenger()->create();

        $this->actingAs($superAdmin)->postJson('/api/admin/recharge', [
            'user_id' => $passenger->id,
            'amount'  => 30,
        ])->assertOk();
    }

    public function test_line_admin_cannot_recharge(): void
    {
        $line      = TransportLine::factory()->create();
        $lineAdmin = User::factory()->lineAdmin($line->id)->create();
        $passenger = User::factory()->passenger()->create();

        $this->actingAs($lineAdmin)->postJson('/api/admin/recharge', [
            'user_id' => $passenger->id,
            'amount'  => 10,
        ])->assertForbidden();
    }

    public function test_driver_cannot_recharge(): void
    {
        $line      = TransportLine::factory()->create();
        $driver    = User::factory()->driver($line->id)->create();
        $passenger = User::factory()->passenger()->create();

        $this->actingAs($driver)->postJson('/api/admin/recharge', [
            'user_id' => $passenger->id,
            'amount'  => 10,
        ])->assertForbidden();
    }

    public function test_cannot_recharge_a_driver(): void
    {
        $admin  = User::factory()->admin()->create();
        $line   = TransportLine::factory()->create();
        $driver = User::factory()->driver($line->id)->create();

        $this->actingAs($admin)->postJson('/api/admin/recharge', [
            'user_id' => $driver->id,
            'amount'  => 10,
        ])->assertForbidden();
    }

    public function test_recharge_fails_with_zero_amount(): void
    {
        $admin     = User::factory()->admin()->create();
        $passenger = User::factory()->passenger()->create();

        $this->actingAs($admin)->postJson('/api/admin/recharge', [
            'user_id' => $passenger->id,
            'amount'  => 0,
        ])->assertUnprocessable();
    }

    public function test_recharge_fails_with_negative_amount(): void
    {
        $admin     = User::factory()->admin()->create();
        $passenger = User::factory()->passenger()->create();

        $this->actingAs($admin)->postJson('/api/admin/recharge', [
            'user_id' => $passenger->id,
            'amount'  => -5,
        ])->assertUnprocessable();
    }
}
