<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\Laboratory;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    // ----------------------------------------------------------------
    //  Test 1 — "laboratory_id" parameter name for occupancy check
    // ----------------------------------------------------------------
    public function test_check_occupied_slots_uses_laboratory_id_parameter(): void
    {
        $user = User::factory()->create(['user_role' => 1]);
        $lab  = Laboratory::factory()->create();

        // Create a booking so a slot IS occupied
        Booking::factory()->create([
            'lab_id'     => $lab->id,
            'date'       => '2026-06-22',
            'start_time' => '10:00:00',
            'end_time'   => '12:00:00',
            'status'     => 2,
            'type'       => 'booking',
        ]);

        $response = $this->actingAs($user)->getJson(
            "/schedules/check-occupied-slots?date=2026-06-22&laboratory_id={$lab->id}"
        );

        $response->assertOk();
        $slots = $response->json();

        // The 10:00 and 11:00 slots should be marked occupied
        $occupied = collect($slots)->filter(function ($s) {
            return $s['start_time'] === '10:00';
        });

        $this->assertCount(1, $occupied, 'Expected 10:00 to be occupied when using "laboratory_id" parameter.');
    }

    // ----------------------------------------------------------------
    //  Test 2 — Destroy cascades: Schedule + Booking both deleted
    // ----------------------------------------------------------------
    public function test_destroy_deletes_both_schedule_and_linked_booking(): void
    {
        $user = User::factory()->create(['user_role' => 1]);
        $lab  = Laboratory::factory()->create();

        $booking = Booking::factory()->create([
            'lab_id'     => $lab->id,
            'date'       => '2026-06-22',
            'start_time' => '09:00:00',
            'end_time'   => '11:00:00',
            'status'     => 2,
            'type'       => 'maintenance',
        ]);

        $schedule = Schedule::factory()->create([
            'lab_id'        => $lab->id,
            'booking_id'    => $booking->id,
            'schedule_type' => 'maintenance',
            'date'          => '2026-06-22',
            'day_of_week'   => 'Monday',
            'start_time'    => '09:00:00',
            'end_time'      => '11:00:00',
            'is_recurring'  => false,
        ]);

        $response = $this->actingAs($user)->deleteJson(
            "/schedules/{$schedule->id}",
            ['redirect_date' => '2026-06-22', 'redirect_lab_id' => $lab->id]
        );

        $response->assertRedirect();

        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
        $this->assertDatabaseMissing('bookings',  ['id' => $booking->id]);
    }

    // ----------------------------------------------------------------
    //  Test 3 — Destroy does NOT crash when booking is already deleted
    //           (orphan / ghost data resilience)
    // ----------------------------------------------------------------
    public function test_destroy_handles_orphan_booking_gracefully(): void
    {
        $user = User::factory()->create(['user_role' => 1]);
        $lab  = Laboratory::factory()->create();

        // Create a schedule whose booking_id points to a non-existent Booking
        $schedule = Schedule::factory()->create([
            'lab_id'        => $lab->id,
            'booking_id'    => 99999,   // ghost — no Matching Booking row
            'schedule_type' => 'maintenance',
            'date'          => '2026-06-22',
            'day_of_week'   => 'Monday',
            'start_time'    => '09:00:00',
            'end_time'      => '11:00:00',
            'is_recurring'  => false,
        ]);

        $response = $this->actingAs($user)->deleteJson(
            "/schedules/{$schedule->id}"
        );

        // Must NOT throw 500 — even with a ghost booking_id
        $response->assertRedirect();

        // Schedule itself must still be deleted
        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
    }

    // ----------------------------------------------------------------
    //  Test 4 — After destroy, redirect URL includes date + view_target
    // ----------------------------------------------------------------
    public function test_destroy_redirect_contains_date_and_view_target(): void
    {
        $user = User::factory()->create(['user_role' => 1]);
        $lab  = Laboratory::factory()->create();

        $schedule = Schedule::factory()->create([
            'lab_id'        => $lab->id,
            'booking_id'    => null,
            'schedule_type' => 'enroll',
            'date'          => '2026-07-15',
            'day_of_week'   => 'Wednesday',
            'start_time'    => '08:00:00',
            'end_time'      => '10:00:00',
            'is_recurring'  => true,
        ]);

        $response = $this->actingAs($user)->deleteJson(
            "/schedules/{$schedule->id}",
            ['redirect_date' => '2026-07-15', 'redirect_lab_id' => $lab->id]
        );

        $response->assertRedirect();
        $target = $response->headers->get('Location');

        $this->assertStringContainsString('/schedules', $target);
        $this->assertStringContainsString('date=2026-07-15', $target);
        $this->assertStringContainsString('view_target=lab_' . $lab->id, $target);
    }

    // ----------------------------------------------------------------
    //  Test 5 — Index renders maintenance with fallback object when
    //           $sched->booking is null (orphan defence in render)
    // ----------------------------------------------------------------
    public function test_index_renders_orphan_maintenance_schedule_without_crash(): void
    {
        $user = User::factory()->create(['user_role' => 1]);
        $lab  = Laboratory::factory()->create(['status' => 1]);

        // Schedule with a ghost booking_id
        Schedule::factory()->create([
            'lab_id'        => $lab->id,
            'booking_id'    => 88888,   // ghost — no Booking row in DB
            'schedule_type' => 'maintenance',
            'date'          => '2026-07-20',
            'day_of_week'   => 'Monday',
            'start_time'    => '10:00:00',
            'end_time'      => '12:00:00',
            'is_recurring'  => false,
        ]);

        $response = $this->actingAs($user)->get('/schedules?lab_id=' . $lab->id . '&date=2026-07-20');

        // Page must render successfully — no 500 error
        $response->assertOk();

        // Blade must show the Orphaned fallback text (the object-injected purpose)
        $response->assertSee('Orphaned');
    }
}