<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\DailyReportItem;
use App\Models\Tank;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TankMonitoringAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_leader_can_view_latest_approved_tank_monitoring(): void
    {
        $groupLeader = User::factory()->create(['role' => 'group_leader']);
        $fuelman = User::factory()->create(['role' => 'fuelman']);
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $tank = Tank::create([
            'code' => 'SPM-01',
            'main_hole' => 'MH-01',
            'capacity' => 10000,
            'is_active' => true,
        ]);
        $report = DailyReport::create([
            'date' => '2026-07-20',
            'status' => 'approved',
            'fuelman_id' => $fuelman->id,
            'spv_id' => $supervisor->id,
        ]);
        DailyReportItem::create([
            'daily_report_id' => $report->id,
            'tank_id' => $tank->id,
            'sounding_pagi' => 120.5,
            'liter_pagi' => 5000,
            'sounding_sore' => 110.0,
            'liter_sore' => 4500,
            'fm_pakai' => 500,
        ]);

        $this->actingAs($groupLeader)
            ->get(route('tanks.monitoring'))
            ->assertOk()
            ->assertSee('Monitoring Tangki BBM')
            ->assertSee('SPM-01')
            ->assertSee('5.000 L')
            ->assertSee('500 L');
    }

    public function test_supervisor_can_view_tank_monitoring(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);

        $this->actingAs($supervisor)
            ->get(route('tanks.monitoring'))
            ->assertOk()
            ->assertSee('Data Monitoring Belum Tersedia');
    }

    public function test_fuelman_cannot_view_tank_monitoring(): void
    {
        $fuelman = User::factory()->create(['role' => 'fuelman']);

        $this->actingAs($fuelman)
            ->get(route('tanks.monitoring'))
            ->assertForbidden();
    }
}
