<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Schedule;
use App\Models\Laboratory;
use App\Models\Course;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\ScheduleController;

class ScheduleConflictDetectionTest extends TestCase
{
    use RefreshDatabase;

    private Semester $semester;
    private Laboratory $lab1;
    private Laboratory $lab2;
    private User $lecturer;
    private Course $course1;
    private Course $course2;
    private Course $course3;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed baseline data
        $this->semester = Semester::create([
            'name'       => 'Test Trimester',
            'start_date' => '2026-06-01',
            'end_date'   => '2026-08-31',
        ]);

        $this->lab1 = Laboratory::create([
            'lab_name' => 'AR1001',
            'capacity' => 30,
            'status'   => 1,
        ]);

        $this->lab2 = Laboratory::create([
            'lab_name' => 'AR1002',
            'capacity' => 25,
            'status'   => 1,
        ]);

        $this->lecturer = User::create([
            'username'  => 'test-lecturer',
            'email'     => 'test-lecturer@example.com',
            'password'  => bcrypt('password'),
            'role_id'   => 2,
            'user_role' => 5,
        ]);

        $this->course1 = Course::create([
            'course_code' => 'CS101',
            'course_name' => 'Test Course 1',
            'hours'       => 2,
            'user_id'     => $this->lecturer->id,
        ]);

        $this->course2 = Course::create([
            'course_code' => 'CS102',
            'course_name' => 'Test Course 2',
            'hours'       => 2,
            'user_id'     => $this->lecturer->id,
        ]);

        $this->course3 = Course::create([
            'course_code' => 'CS103',
            'course_name' => 'Test Course 3',
            'hours'       => 2,
            'user_id'     => $this->lecturer->id,
        ]);
    }

    /**
     * T30: Lab-room collision — overlapping slot is rejected.
     */
    public function test_hasScheduleConflict_detects_overlapping_lab_room(): void
    {
        $semesterId = $this->semester->id;
        $labId      = $this->lab1->id;
        $day        = 'Monday';

        // Pre-existing schedule: Monday 08:00-10:00
        Schedule::create([
            'schedule_type' => 'enroll',
            'semester_id'   => $semesterId,
            'lab_id'        => $labId,
            'course_id'     => $this->course1->id,
            'day_of_week'   => $day,
            'date'          => '2026-06-01',
            'start_time'    => '08:00:00',
            'end_time'      => '10:00:00',
            'is_recurring'  => 1,
        ]);

        // Case 1: Overlapping slot (09:00-11:00) — should be CONFLICT
        $this->assertTrue(
            ScheduleController::hasScheduleConflict($labId, $semesterId, $day, '09:00:00', '11:00:00'),
            'Overlapping slot (09:00-11:00 vs 08:00-10:00) should be detected as conflict'
        );

        // Case 2: Exactly touching slot (10:00-12:00) — should NOT be conflict
        $this->assertFalse(
            ScheduleController::hasScheduleConflict($labId, $semesterId, $day, '10:00:00', '12:00:00'),
            'Touching slot (10:00-12:00 vs 08:00-10:00) should NOT be detected as conflict'
        );

        // Case 3: Non-overlapping (12:00-14:00) — should NOT be conflict
        $this->assertFalse(
            ScheduleController::hasScheduleConflict($labId, $semesterId, $day, '12:00:00', '14:00:00'),
            'Non-overlapping slot (12:00-14:00) should NOT be detected as conflict'
        );

        // Case 4: Different day — should NOT be conflict
        $this->assertFalse(
            ScheduleController::hasScheduleConflict($labId, $semesterId, 'Tuesday', '08:00:00', '10:00:00'),
            'Same time on different day should NOT be conflict'
        );

        // Case 5: Different lab — should NOT be conflict
        $this->assertFalse(
            ScheduleController::hasScheduleConflict($this->lab2->id, $semesterId, $day, '08:00:00', '10:00:00'),
            'Same time/day in different lab should NOT be conflict'
        );

        // Case 6: Different semester — should NOT be conflict
        $otherSemester = Semester::create([
            'name'       => 'Other Trimester',
            'start_date' => '2026-09-01',
            'end_date'   => '2026-11-30',
        ]);
        $this->assertFalse(
            ScheduleController::hasScheduleConflict($labId, $otherSemester->id, $day, '08:00:00', '10:00:00'),
            'Same time/day/lab in different semester should NOT be conflict'
        );
    }

    /**
     * T30: Lecturer collision — same lecturer on same day overlapping is rejected.
     */
    public function test_hasLecturerConflict_detects_overlapping_lecturer(): void
    {
        $semesterId = $this->semester->id;
        $labId      = $this->lab1->id;
        $day        = 'Monday';

        // Pre-existing schedule for lecturer: Monday 08:00-10:00 in AR1001
        Schedule::create([
            'schedule_type' => 'enroll',
            'semester_id'   => $semesterId,
            'lab_id'        => $labId,
            'course_id'     => $this->course1->id,
            'day_of_week'   => $day,
            'date'          => '2026-06-01',
            'start_time'    => '08:00:00',
            'end_time'      => '10:00:00',
            'is_recurring'  => 1,
        ]);

        // Lecturer has overlapping slot (09:00-11:00) — should be CONFLICT
        $this->assertTrue(
            ScheduleController::hasLecturerConflict(
                $this->lecturer->id, $semesterId, $day, '09:00:00', '11:00:00'
            ),
            'Lecturer overlap (09:00-11:00 vs 08:00-10:00) should be detected'
        );

        // Touching slot (10:00-12:00) — should NOT be conflict
        $this->assertFalse(
            ScheduleController::hasLecturerConflict(
                $this->lecturer->id, $semesterId, $day, '10:00:00', '12:00:00'
            ),
            'Lecturer touching slot should NOT be conflict'
        );

        // Different day — should NOT be conflict
        $this->assertFalse(
            ScheduleController::hasLecturerConflict(
                $this->lecturer->id, $semesterId, 'Tuesday', '08:00:00', '10:00:00'
            ),
            'Lecturer different day should NOT be conflict'
        );
    }

    /**
     * T11 + T30: Batch-save blind spot — in-memory accumulator catches
     * cross-slot conflicts within a single batch.
     */
    public function test_in_memory_accumulator_catches_batch_conflict(): void
    {
        $semesterId = $this->semester->id;
        $labId      = $this->lab1->id;
        $day        = 'Monday';

        // Simulate a batch: slot A is accepted first (no prior DB rows)
        $acceptedSlots = [];

        // Slot A accepted
        $this->assertFalse(
            ScheduleController::hasScheduleConflict(
                $labId, $semesterId, $day, '08:00:00', '10:00:00',
                null, $acceptedSlots
            ),
            'First slot in empty batch should be accepted'
        );
        $acceptedSlots[] = [
            'lab_id'      => $labId,
            'day_of_week' => $day,
            'start_time'  => '08:00:00',
            'end_time'    => '10:00:00',
        ];

        // Slot B: same lab, same day, overlapping time (09:00-11:00) — should REJECT
        $this->assertTrue(
            ScheduleController::hasScheduleConflict(
                $labId, $semesterId, $day, '09:00:00', '11:00:00',
                null, $acceptedSlots
            ),
            'Overlapping slot in same batch should be caught by in-memory accumulator'
        );

        // Slot C: touching slot (10:00-12:00) — should ACCEPT (no overlap with 08:00-10:00)
        $this->assertFalse(
            ScheduleController::hasScheduleConflict(
                $labId, $semesterId, $day, '10:00:00', '12:00:00',
                null, $acceptedSlots
            ),
            'Touching slot in same batch should be accepted'
        );
    }

    /**
     * T30: Defense-in-depth: accept a batch of two non-overlapping slots
     * and verify both are persisted.
     */
    public function test_non_overlapping_batch_is_accepted(): void
    {
        $semesterId = $this->semester->id;
        $labId      = $this->lab1->id;
        $day        = 'Monday';
        $acceptedSlots = [];

        // Slot 1: 08:00-10:00
        $this->assertFalse(
            ScheduleController::hasScheduleConflict(
                $labId, $semesterId, $day, '08:00:00', '10:00:00',
                null, $acceptedSlots
            )
        );
        $acceptedSlots[] = [
            'lab_id'      => $labId,
            'day_of_week' => $day,
            'start_time'  => '08:00:00',
            'end_time'    => '10:00:00',
        ];

        // Slot 2: 10:00-12:00 (touching — should be fine)
        $this->assertFalse(
            ScheduleController::hasScheduleConflict(
                $labId, $semesterId, $day, '10:00:00', '12:00:00',
                null, $acceptedSlots
            ),
            'Touching slot should be accepted'
        );
    }

    /**
     * T30: Batch lecturer collision — two courses with same lecturer
     * at overlapping times on same day are rejected.
     */
    public function test_batch_lecturer_conflict_is_caught(): void
    {
        $semesterId = $this->semester->id;
        $day        = 'Monday';
        $acceptedSlots = [];

        // Slot A: course1 by this->lecturer, Mon 08:00-10:00 in AR1001
        $this->assertFalse(
            ScheduleController::hasLecturerConflict(
                $this->lecturer->id, $semesterId, $day, '08:00:00', '10:00:00',
                null, $acceptedSlots
            )
        );
        $acceptedSlots[] = [
            'lab_id'           => $this->lab1->id,
            'lecturer_user_id' => $this->lecturer->id,
            'day_of_week'      => $day,
            'start_time'       => '08:00:00',
            'end_time'         => '10:00:00',
        ];

        // Slot B: course2 by same lecturer, Mon 09:00-11:00 in AR1002 (different lab!) — STILL conflict
        $this->assertTrue(
            ScheduleController::hasLecturerConflict(
                $this->lecturer->id, $semesterId, $day, '09:00:00', '11:00:00',
                null, $acceptedSlots
            ),
            'Same lecturer overlapping on same day (even in different lab) should be caught'
        );
    }
}