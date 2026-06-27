<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Models\Schedule;
use App\Models\Laboratory;
use App\Models\Course;
use App\Models\Semester;
use App\Models\Booking;
use App\Models\User;

class AiSchedulerController extends Controller
{
    /**
     * Display the AI scheduler interface with schedule, software, and equipment data.
     */
    public function index(): View
    {
        $today = Carbon::today()->toDateString();

        // Dynamically find the current semester based on today's date
        // This is used ONLY to pre-fetch schedules for the initial view.
        // The dropdown itself MUST NOT be pre-selected — the user controls selection.
        $currentSemester = DB::table('semesters')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        $activeSemesterId = $currentSemester ? $currentSemester->id : (DB::table('semesters')->orderBy('id', 'desc')->value('id') ?? 1);

        // Core data: fetch schedules strictly filtered by active semester
        $schedules = DB::table('schedules')
            ->leftJoin('laboratories', 'schedules.lab_id', '=', 'laboratories.id')
            ->leftJoin('courses', 'schedules.course_id', '=', 'courses.id')
            ->select(
                'schedules.*',
                'laboratories.lab_name as laboratory_name',
                'courses.course_name as course_title'
            )
            ->where('schedules.semester_id', $activeSemesterId)
            ->whereNotNull('schedules.day_of_week')
            ->whereNotNull('schedules.start_time')
            ->whereNotNull('schedules.end_time')
            ->get();

        $softwares = DB::table('software')
            ->leftJoin('laboratories', 'software.lab_id', '=', 'laboratories.id')
            ->select('software.*', 'laboratories.lab_name as lab_room')
            ->get();
        $equipments = DB::table('equipment')
            ->leftJoin('laboratories', 'equipment.lab_id', '=', 'laboratories.id')
            ->select('equipment.*', 'laboratories.lab_name as lab_room')
            ->get();
        $laboratories = DB::table('laboratories')->get();
        // Join with users to expose lecturer ID and name for AI lecturer collision detection
        $lecturers = DB::table('users')->where('user_role', 5)->get();
        $courses = DB::table('courses')
            ->leftJoin('users', 'courses.user_id', '=', 'users.id')
            ->select('courses.*', 'users.username as lecturer_name', 'users.id as lecturer_id')
            ->get();
        
        $semesters = DB::table('semesters')->get();

        // Pass currentSemesterId as null — user must explicitly choose a semester.
        // This prevents the Blade from pre-rendering a selected option and deadlocking
        // the workflow state machine on page load / refresh.
        return view('ai-scheduler', compact(
            'schedules',
            'softwares',
            'equipments',
            'laboratories',
            'courses',
            'lecturers',
            'semesters'
        ))->with('currentSemesterId', null);
    }

    /**
     * Handle AI scheduler logic via DeepSeek native API.
     * Fetches ALL existing schedules (cross-semester) as absolute physical blocks.
     */
    public function generateAiSchedule(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
            'semester_id' => 'required|integer|exists:semesters,id',
        ]);

        $semesterId = $request->input('semester_id');

        // ─── Fetch semester metadata for date context in the prompt
        $semester = DB::table('semesters')->where('id', $semesterId)->first();
        $semesterStartDate = $semester ? $semester->start_date : 'N/A';
        $semesterEndDate   = $semester ? $semester->end_date   : 'N/A';

        // ═══════════════════════════════════════════════════════════════
        // SAFE-MENU ENGINE (PHP computes every available window)
        // Strategy: Instead of a FORBIDDEN blacklist that AI must
        // interpret, we precompute ALL safe continuous windows for
        // each lab/day/course and serve them as an Options Menu.
        // AI's sole job: pick 1 option per course from the menu.
        // ═══════════════════════════════════════════════════════════════

        // ── 1. Fetch all conflict sources (Schedules + Bookings) ──
        $scheduleConflicts = DB::table('schedules')
            ->leftJoin('laboratories', 'schedules.lab_id', '=', 'laboratories.id')
            ->select('schedules.day_of_week', 'schedules.start_time', 'schedules.end_time', 'laboratories.lab_name')
            ->where('schedules.semester_id', $semesterId)
            ->whereNotNull('schedules.day_of_week')
            ->whereNotNull('schedules.start_time')
            ->whereNotNull('schedules.end_time')
            ->get();

        $dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        $bookingConflicts = DB::table('bookings')
            ->leftJoin('laboratories', 'bookings.lab_id', '=', 'laboratories.id')
            ->select('bookings.date', 'bookings.start_time', 'bookings.end_time', 'laboratories.lab_name')
            ->whereBetween('bookings.date', [$semesterStartDate, $semesterEndDate])
            ->where(function($q) { $q->where('bookings.status', 'approved')->orWhere('bookings.type', 'maintenance'); })
            ->get();

        // ── 2. Build minute‑resolution occupied timeline per (lab, day) ──
        $dayOpen  = 8 * 60;   // 08:00
        $dayClose = 18 * 60;  // 18:00
        $occupied = []; // $occupied[lab][day][minute] = true

        $allLabs = DB::table('laboratories')->pluck('lab_name');
        $weekdays = ['Monday','Tuesday','Wednesday','Thursday','Friday'];
        foreach ($allLabs as $lab) {
            foreach ($weekdays as $day) {
                for ($m = $dayOpen; $m < $dayClose; $m++) {
                    $occupied[$lab][$day][$m] = false;
                }
            }
        }

        foreach ($scheduleConflicts as $row) {
            $lab = $row->lab_name;
            $day = $row->day_of_week;
            $s = max($dayOpen, (int)floor(strtotime($row->start_time) / 60));
            $e = min($dayClose, (int)ceil(strtotime($row->end_time) / 60));
            for ($m = $s; $m < $e; $m++) if (isset($occupied[$lab][$day][$m])) $occupied[$lab][$day][$m] = true;
        }
        foreach ($bookingConflicts as $row) {
            $lab = $row->lab_name;
            $day = $dayNames[Carbon::parse($row->date)->dayOfWeek] ?? null;
            if (!$day || !in_array($day, $weekdays)) continue;
            $s = max($dayOpen, (int)floor(strtotime($row->start_time) / 60));
            $e = min($dayClose, (int)ceil(strtotime($row->end_time) / 60));
            for ($m = $s; $m < $e; $m++) if (isset($occupied[$lab][$day][$m])) $occupied[$lab][$day][$m] = true;
        }

        // ── 3. Scan each (lab, day) for continuous free windows ≥ minDuration ──
        $findWindows = function($lab, $day, $minMinutes) use ($occupied, $dayOpen, $dayClose) {
            $windows = [];
            $start = null;
            for ($m = $dayOpen; $m <= $dayClose; $m++) {
                $busy = ($m >= $dayClose) || ($occupied[$lab][$day][$m] ?? false);
                if (!$busy && $start === null) {
                    $start = $m;
                } elseif ($busy && $start !== null) {
                    if ($m - $start >= $minMinutes) {
                        $windows[] = ['start_min' => $start, 'end_min' => $m];
                    }
                    $start = null;
                }
            }
            return $windows;
        };

        $fmt12 = fn($mm) => date('h:i A', mktime(0, $mm));

        // ── 4. Look up all courses + their hours ──
        $courseCatalog = DB::table('courses')
            ->leftJoin('users', 'courses.user_id', '=', 'users.id')
            ->select('courses.id', 'courses.course_name', 'courses.hours', 'courses.user_id as lecturer_id', 'users.username as lecturer_name')
            ->get()->keyBy('id');

        $allLaboratories = DB::table('laboratories')->get();
        $allLecturers = DB::table('users')->where('user_role', 5)->get();

        // ── 5. Parse queue from user prompt to know which courses are pending ──
        $userPrompt = $request->input('prompt');
        // The prompt contains lines like: "- Course Name: "XYZ" (Database ID: 3)"
        preg_match_all('/Database ID:\s*(\d+)/', $userPrompt, $idMatches);
        // Also extract constraint info: "- Target Asset Condition Rule: [Type: xxx, Target Value: "yyy"]"
        preg_match_all('/Type:\s*(\w+),\s*Target Value:\s*"([^"]*)"/', $userPrompt, $constraintMatches, PREG_SET_ORDER);
        // Extract time preference: "- Target Time Slot Interval Strategy: (full_day|morning|afternoon)"
        preg_match_all('/Time Slot Interval Strategy:\s*(\w+)/', $userPrompt, $prefMatches);

        $softwareLabMapping = DB::table('software')
            ->leftJoin('laboratories', 'software.lab_id', '=', 'laboratories.id')
            ->select('software.software_name', 'laboratories.lab_name')->get();
        $equipmentLabMapping = DB::table('equipment')
            ->leftJoin('laboratories', 'equipment.lab_id', '=', 'laboratories.id')
            ->select('equipment.equipment_name', 'laboratories.lab_name')->get();

        // ── Filter labs by asset: if course requires asset X, only labs that have X ──
        $assetEligibleLabs = function($courseId, $constraintType, $constraintValue) use ($allLaboratories, $softwareLabMapping, $equipmentLabMapping) {
            $eligible = [];
            foreach ($allLaboratories as $l) {
                if ($constraintType === 'software') {
                    foreach ($softwareLabMapping as $sw) {
                        if ($sw->software_name === $constraintValue && $sw->lab_name === $l->lab_name) { $eligible[] = $l->lab_name; break; }
                    }
                } elseif ($constraintType === 'hardware') {
                    foreach ($equipmentLabMapping as $eq) {
                        if ($eq->equipment_name === $constraintValue && $eq->lab_name === $l->lab_name) { $eligible[] = $l->lab_name; break; }
                    }
                } elseif ($constraintType === 'laboratory') {
                    if ($l->lab_name === $constraintValue) $eligible[] = $l->lab_name;
                } else {
                    $eligible[] = $l->lab_name;
                }
            }
            return $eligible ?: $allLaboratories->pluck('lab_name')->toArray();
        };

        // ── 6. Build AVAILABLE COMPLIANT SLOTS MENU per pending course ──
        $menuSections = [];
        $queueCount = count($idMatches[1]);

        for ($i = 0; $i < $queueCount; $i++) {
            $courseId   = (int) $idMatches[1][$i];
            $course     = $courseCatalog[$courseId] ?? null;
            $courseName = $course->course_name ?? "Course #$courseId";
            $hours      = $course->hours ?? 2;
            $minMinutes = (int)($hours * 60);
            $lecturer   = $course->lecturer_name ?? 'N/A';
            $lecturerId = $course->lecturer_id ?? '';
            $constraintType  = $constraintMatches[$i][1] ?? 'any';
            $constraintValue = $constraintMatches[$i][2] ?? '';
            $timePref  = $prefMatches[1][$i] ?? 'full_day';

            $eligibleLabs = $assetEligibleLabs($courseId, $constraintType, $constraintValue);

            $options = [];
            foreach ($weekdays as $day) {
                foreach ($eligibleLabs as $lab) {
                    if (!isset($occupied[$lab][$day])) continue;
                    $windows = $findWindows($lab, $day, $minMinutes);
                    foreach ($windows as $w) {
                        $label = \Carbon\Carbon::createFromTime(0, $w['start_min'])->format('h:i A')
                               . ' - '
                               . \Carbon\Carbon::createFromTime(0, $w['end_min'])->format('h:i A');
                        // Shift filtering
                        $slotHour = (int)floor($w['start_min'] / 60);
                        if ($timePref === 'morning' && $slotHour >= 12) continue;
                        if ($timePref === 'afternoon' && $slotHour < 12) continue;
                        $options[] = [
                            'lab' => $lab,
                            'day' => $day,
                            'start' => $w['start_min'],
                            'end'   => $w['end_min'],
                            'label' => $label,
                        ];
                    }
                }
            }

            // Deduplicate
            $seen = [];
            $options = array_values(array_filter($options, function($o) use (&$seen) {
                $k = $o['lab'].'|'.$o['day'].'|'.$o['start'];
                if (isset($seen[$k])) return false;
                $seen[$k] = true;
                return true;
            }));

            // Sort: Monday→Friday, then morning→afternoon
            usort($options, fn($a,$b) =>
                array_search($a['day'],$weekdays) - array_search($b['day'],$weekdays)
                ?: $a['start'] - $b['start']
            );

            // Limit to TOP 3 options to keep the menu crisp
            $topOptions = array_slice($options, 0, 3);

            if (empty($topOptions)) {
                $menuSections[] = "⚠️  COURSE: {$courseName} (ID: {$courseId})\n"
                    . "   Lecturer: {$lecturer} (ID: {$lecturerId}) | Required: {$hours}h | Pref: {$timePref}\n"
                    . "   ⛔ NO VIABLE WINDOWS FOUND. All slots conflicted or no eligible lab.\n\n";
            } else {
                $section = "📋 COURSE: {$courseName} (ID: {$courseId}) — Requires {$hours} hour(s)\n"
                         . "   Lecturer: {$lecturer} (ID: {$lecturerId}) | Constraint: {$constraintType}={$constraintValue} | Pref: {$timePref}\n";
                foreach ($topOptions as $j => $opt) {
                    $num = $j + 1;
                    $section .= "   Option {$num}: {$opt['day']} {$opt['label']} — Lab: {$opt['lab']}\n";
                }
                $section .= "\n";
                $menuSections[] = $section;
            }
        }

        $menuText = count($menuSections) > 0
            ? "=== AVAILABLE COMPLIANT SLOTS MENU (PHP precomputed — NO clock math needed) ===\n\n" . implode("\n", $menuSections)
            : "=== MENU: [EMPTY — No courses to schedule.] ===\n";

        // ── 7. Build the System Prompt — Menu-Picking Mode ──
        $systemPrompt = "CRITICAL SYSTEM DIRECTIVE — FCI Lab Menu-Picking Scheduler\n"
            . "Semester: {$semesterStartDate} → {$semesterEndDate}  |  All courses are weekly recurring (is_recurring = 1)\n\n"

            . "YOU ARE A MENU PICKER, NOT A PLANNER.\n"
            . "Every valid (Lab, Day, Time) combination has been precomputed by PHP.\n"
            . "You are NO LONGER allowed to invent ANY time format or pick ANY slot outside the menu below.\n\n"

            . $menuText . "\n"
            . "═══════════════════════════════════════\n"
            . "═══ YOUR SOLE TASK: PICK 1 OPTION PER COURSE ═══\n"
            . "═══════════════════════════════════════\n\n"

            . "RULES (read carefully — NO exceptions):\n"
            . "1. LECTURER RULE: If two courses have the same lecturer ID, they CANNOT share the same day AND overlapping times. If both offer 'Monday 08:00 AM', pick different days for them.\n"
            . "2. QUEUE RULE: Within this batch, no two courses can claim the same (Lab, Day, Start). As you pick Course #1, Course #2 must use a DIFFERENT option.\n"
            . "3. ASSET RULE: The menu already filters eligible labs. Trust it.\n"
            . "4. PREFERENCE: If a course says 'morning', favour Options before 12:00 PM. 'afternoon' → after 12:00 PM. The menu already filters for this.\n"
            . "5. TIME FLOW: Output must be '08:00 AM - 10:00 AM' format — start BEFORE end.\n"
            . "6. EXACT CARDINALITY: Output exactly 1 element per course. No trial logs, no alternatives.\n\n"

            . "═════ OUTPUT FORMAT ═════\n"
            . "RAW JSON array ONLY — no markdown, no backticks, no prose.\n"
            . "Exact schema per element:\n"
            . "{\n"
            . "  \"course_id\": \"1\",\n"
            . "  \"course_name\": \"CS3243 - Cybersecurity Fundamentals\",\n"
            . "  \"lab_name\": \"AR1001\",\n"
            . "  \"day_of_week\": \"Monday\",\n"
            . "  \"time_window\": \"10:00 AM - 12:00 PM\",\n"
            . "  \"conflict_log\": \"Picked Option A. Lecturer + room collision-free.\"\n"
            . "}\n"
            . "AAAAAAAAAA[RAW JSON ARRAY ONLY]AAAAAAAAAA\n";

        // ─── Build the user prompt with the frontend's scheduling requirements
        $userPrompt = $request->input('prompt');

        // ─── DEEPSEEK NATIVE API CALL ───
        // Use config() instead of direct env() to survive config caching.
        $apiKey   = config('services.deepseek.key');
        $baseUrl  = config('services.deepseek.base_url');

        // Diagnostic: log the first 8 chars of the key to verify it loaded.
        Log::info('DeepSeek Request | Key prefix: ' . ($apiKey ? substr($apiKey, 0, 8) . '...' : '[NULL — EMPTY]') . ' | Base URL: ' . $baseUrl);

        if (!$apiKey) {
            Log::error('DeepSeek API key is null. Check DEEPSEEK_API_KEY in .env.');
            return response()->json([
                'error' => 'DeepSeek API key not configured. Please set DEEPSEEK_API_KEY in your .env file.'
            ], 500);
        }

        try {
            $response = Http::withoutVerifying()
                ->withOptions([
                    'verify' => false,
                    'timeout' => 120,
                ])
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $apiKey,
                ])
                ->post(rtrim($baseUrl, '/') . '/chat/completions', [
                    'model' => 'deepseek-chat',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'max_tokens' => 4096,
                    'temperature' => 0.1,
                ]);

            if (!$response->successful()) {
                Log::error('DeepSeek API Non-200 Error: ' . $response->body());
                return response()->json(['error' => 'DeepSeek API Error: ' . $response->body()], $response->status());
            }

            $aiResponseBody = $response->json();
            $pureText = $aiResponseBody['choices'][0]['message']['content'] ?? '';

            if (empty($pureText)) {
                return response()->json(['error' => 'DeepSeek engine returned an empty response block.'], 500);
            }

            return response()->json([
                'success' => true,
                'text' => $pureText,
            ]);

        } catch (\Exception $e) {
            Log::error('DeepSeek API Exception Caught: ' . $e->getMessage());
            return response()->json(['error' => 'DeepSeek API Exception: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Save the AI optimized schedule and bind it dynamically to a real calendar date
     * using precise Carbon calculation. Includes full-semester recurring collision shield.
     */
    public function saveOptimizedSchedule(Request $request)
    {
        $semesterId = $request->input('semester_id');
        $dayOfWeek  = $request->input('day_of_week');  // e.g., 'Monday'
        $startTime  = $request->input('start_time');   // e.g., '08:00:00'
        $endTime    = $request->input('end_time');     // e.g., '10:00:00'
        $labId      = $request->input('lab_id');
        $courseId   = $request->input('course_id');

        // 1. Fetch semester
        $semester = Semester::find($semesterId);
        if (!$semester) {
            return response()->json(['success' => false, 'message' => 'Semester not found']);
        }

        $semesterStartDate = Carbon::parse($semester->start_date);

        // 2. Precise Carbon date calculation:
        //    Map day name (e.g., 'Monday') to ISO weekday number (1=Mon, 7=Sun)
        $dayMapping = [
            'Monday'    => 1,
            'Tuesday'   => 2,
            'Wednesday' => 3,
            'Thursday'  => 4,
            'Friday'    => 5,
            'Saturday'  => 6,
            'Sunday'    => 7,
        ];

        $targetDayIso = $dayMapping[$dayOfWeek] ?? null;
        if (!$targetDayIso) {
            return response()->json(['success' => false, 'message' => 'Invalid day_of_week: ' . $dayOfWeek]);
        }

        // Calculate the closest calendar date matching the target weekday on or after semester start
        $startDayIso = $semesterStartDate->dayOfWeekIso; // 1=Mon ... 7=Sun
        $diff = $targetDayIso - $startDayIso;
        if ($diff < 0) {
            $diff += 7; // Go to next week
        }
        $targetDate = $semesterStartDate->copy()->addDays($diff);
        $calculatedDateStr = $targetDate->format('Y-m-d');

        // 3. ⚡ FULL-SEMESTER RECURRING COLLISION SHIELD
        //    Since all enrolled courses are weekly recurring (is_recurring = 1),
        //    collision is: same semester_id + same day_of_week + same lab_id + time overlap.
        //    Formula: $startTime < existing_end_time && $endTime > existing_start_time
        $semesterCollision = Schedule::where('semester_id', $semesterId)
            ->where('day_of_week', $dayOfWeek)
            ->where('lab_id', $labId)
            ->where(function($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
            })
            ->exists();

        if ($semesterCollision) {
            return response()->json([
                'success' => false,
                'message' => 'Scheduling Conflict Detected! The selected laboratory is already occupied during this timeslot for the current semester.'
            ], 422);
        }

        // 4. ⚡ LECTURER COLLISION SHIELD (BACKEND HARD STOP)
        //    Resolve the lecturer (user_id) of the current course,
        //    then check all existing schedules in the same semester for
        //    same lecturer + same day_of_week + overlapping time windows.
        $course = Course::find($courseId);
        if ($course && $course->user_id) {
            $lecturerCollision = Schedule::where('schedules.semester_id', $semesterId)
                ->where('schedules.day_of_week', $dayOfWeek)
                ->where(function($query) use ($startTime, $endTime) {
                    $query->where('schedules.start_time', '<', $endTime)
                          ->where('schedules.end_time', '>', $startTime);
                })
                ->join('courses', 'schedules.course_id', '=', 'courses.id')
                ->where('courses.user_id', $course->user_id)
                ->exists();

            if ($lecturerCollision) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lecturer Overlap Conflict! The assigned lecturer has already been scheduled for another class during this timeslot.'
                ], 422);
            }
        }

        // 5. Persist the Schedule with hardcoded recurring fields
        $schedule = new Schedule();
        $schedule->semester_id  = $semesterId;
        $schedule->course_id    = $courseId;
        $schedule->lab_id       = $labId;
        $schedule->start_time   = $startTime;
        $schedule->end_time     = $endTime;
        $schedule->date         = $calculatedDateStr;
        $schedule->day_of_week  = $dayOfWeek;
        $schedule->is_recurring = 1;
        $schedule->schedule_type = 'enroll';
        $schedule->booking_id   = null;
        $schedule->save();

        return response()->json([
            'success' => true,
            'message' => $targetDate->format('Y-m-d (l)'),
            'schedule_id' => $schedule->id,
        ]);
    }
}