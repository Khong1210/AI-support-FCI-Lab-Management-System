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
            ->where(function($q) { $q->where('bookings.status', 2)->orWhere('bookings.type', 'maintenance'); })
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
            $parts = explode(':', $row->start_time);
            $s = max($dayOpen, (int)$parts[0] * 60 + (int)$parts[1]);
            $parts = explode(':', $row->end_time);
            $e = min($dayClose, (int)$parts[0] * 60 + (int)$parts[1]);
            for ($m = $s; $m < $e; $m++) if (isset($occupied[$lab][$day][$m])) $occupied[$lab][$day][$m] = true;
        }
        foreach ($bookingConflicts as $row) {
            $lab = $row->lab_name;
            $day = $dayNames[Carbon::parse($row->date)->dayOfWeek] ?? null;
            if (!$day || !in_array($day, $weekdays)) continue;
            $parts = explode(':', $row->start_time);
            $s = max($dayOpen, (int)$parts[0] * 60 + (int)$parts[1]);
            $parts = explode(':', $row->end_time);
            $e = min($dayClose, (int)$parts[0] * 60 + (int)$parts[1]);
            for ($m = $s; $m < $e; $m++) if (isset($occupied[$lab][$day][$m])) $occupied[$lab][$day][$m] = true;
        }

        // ── 3. Scan each (lab, day) for continuous free windows ≥ minDuration ──
        $findWindows = function($lab, $day, $minMinutes) use (&$occupied, $dayOpen, $dayClose) {
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
        try {
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
                            $windowStart = $w['start_min'];
                            $windowEnd   = $w['end_min'];

                            for ($currentStart = $windowStart; $currentStart + $minMinutes <= $windowEnd; $currentStart += 60) {
                                $currentEnd = $currentStart + $minMinutes;

                                $slotHour = (int)floor($currentStart / 60);
                                if ($timePref === 'morning' && $slotHour >= 12) continue;
                                if ($timePref === 'afternoon' && $slotHour < 12) continue;

                                $startH = intdiv($currentStart, 60);
                                $startM = $currentStart % 60;
                                $endH   = intdiv($currentEnd, 60);
                                $endM   = $currentEnd % 60;

                                $label = \Carbon\Carbon::createFromTime($startH, $startM)->format('h:i A')
                                    . ' - '
                                    . \Carbon\Carbon::createFromTime($endH, $endM)->format('h:i A');

                                $options[] = [
                                    'lab'   => $lab,
                                    'day'   => $day,
                                    'start' => $currentStart,
                                    'end'   => $currentEnd,
                                    'label' => $label,
                                ];
                            }
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

                // ═══════════════════════════════════════════════════════════
                // 【RANDOMIZED DAY-DIVERSIFIED OPTION SELECTION】
                // Instead of fixed sort Monday→Friday (which biases slots into
                // Monday–Wednesday), we group by day, shuffle within each day,
                // and re-merge with Friday first. Every "Run Batch AI Optimization"
                // yields a different top-3 menu for each course, naturally
                // spreading assignments across Thursday and Friday.
                // ═══════════════════════════════════════════════════════════
                if (!empty($options)) {
                    $groupedByDay = [];
                    foreach ($options as $opt) {
                        $groupedByDay[$opt['day']][] = $opt;
                    }
                    foreach ($groupedByDay as $day => $dayOptions) {
                        shuffle($dayOptions);
                        $groupedByDay[$day] = $dayOptions;
                    }
                    $finalOptions = [];
                    $days = ['Friday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
                    foreach ($days as $d) {
                        if (isset($groupedByDay[$d])) {
                            foreach ($groupedByDay[$d] as $opt) {
                                $finalOptions[] = $opt;
                            }
                        }
                    }
                    $options = $finalOptions;
                }

                // Limit to TOP 3 options to keep the menu crisp
                $topOptions = array_slice($options, 0, 3);

                // ═══════════════════════════════════════════════════════════
                // 【SNAPSHOT ISOLATION FIX】Reserve only the single best option
                // (topOptions[0]) of THIS course within the live &$occupied
                // grid so subsequent courses see a dynamic menu where the
                // most-likely-to-be-picked slot is already marked busy.
                // This prevents the AI from receiving the same (lab, day, time)
                // combination for multiple courses while keeping options 2 & 3
                // open as safety valves.
                // ═══════════════════════════════════════════════════════════
                if (!empty($topOptions)) {
                    $opt = $topOptions[0];
                    $reserveLab   = $opt['lab'];
                    $reserveDay   = $opt['day'];
                    $reserveStart = $opt['start'];
                    $reserveEnd   = $opt['end'];
                    if (isset($occupied[$reserveLab][$reserveDay])) {
                        for ($m = $reserveStart; $m < $reserveEnd; $m++) {
                            $occupied[$reserveLab][$reserveDay][$m] = true;
                        }
                    }
                }

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
        } catch (\Exception $e) {
            Log::error('AI Scheduler menu-building failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'error' => 'Menu building failed: ' . $e->getMessage()
            ], 500);
        }

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
     *
     * Uses the shared ScheduleController::hasScheduleConflict() and
     * ScheduleController::hasLecturerConflict() for a single source of truth.
     */
    public function saveOptimizedSchedule(Request $request)
    {
        // ═══════════════════════════════════════════════════════════════
        // 【ENROLL DEBUG LOGGING】Dump everything received from frontend
        // to verify whether batch_slots arrives correctly.
        // ═══════════════════════════════════════════════════════════════
        Log::info('ENROLL_DEBUG: === New Enroll Request ===');
        Log::info('ENROLL_DEBUG: Raw batch_slots from request:', [$request->input('batch_slots')]);
        Log::info('ENROLL_DEBUG: Current course being enrolled:', [
            'course_id'   => $request->input('course_id'),
            'lab_id'      => $request->input('lab_id'),
            'day_of_week' => $request->input('day_of_week'),
            'start_time'  => $request->input('start_time'),
            'end_time'    => $request->input('end_time'),
        ]);

        $semesterId = $request->input('semester_id');
        $dayOfWeek  = $request->input('day_of_week');  
        $startTime  = $request->input('start_time');   
        $endTime    = $request->input('end_time');     
        $labId      = $request->input('lab_id');
        $courseId   = $request->input('course_id');

        // ═══════════════════════════════════════════════════════════════
        // 【IN-MEMORY BATCH COLLISION SHIELD】Parse the full sibling matrix
        // sent by the frontend so hasScheduleConflict can check against
        // other unsaved rows from the same AI batch — preventing the
        // "self-collision" scenario where row 1 saves, row 2 fails because
        // it shares the same slot with row 1 but the user only finds out
        // at save time.
        // ═══════════════════════════════════════════════════════════════
        $batchSlotsRaw = $request->input('batch_slots', '[]');
        $batchSlots = is_string($batchSlotsRaw) ? json_decode($batchSlotsRaw, true) : $batchSlotsRaw;
        $batchSlots = is_array($batchSlots) ? $batchSlots : [];

        // Build in-memory accumulator from sibling rows (exclude current course)
        $inMemorySlots = [];
        $dayMapping = [
            'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3,
            'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7,
        ];
        $allLabs = \App\Models\Laboratory::pluck('id', 'lab_name')->toArray();
        $allCourses = \App\Models\Course::pluck('user_id', 'id')->toArray();

        foreach ($batchSlots as $sib) {
            $sibCourseId = (int)($sib['course_id'] ?? 0);
            if ($sibCourseId === (int)$courseId) continue; // skip self

            $sibLabName  = trim($sib['lab_name'] ?? '');
            $sibDay      = $sib['day_of_week'] ?? '';
            $sibTimeWin  = $sib['time_window'] ?? '';

            if (!$sibLabName || !$sibDay || !$sibTimeWin) continue;

            $sibLabId = $allLabs[$sibLabName]
                ?? $allLabs[strtolower($sibLabName)]
                ?? \App\Models\Laboratory::whereRaw('LOWER(lab_name) = ?', [strtolower($sibLabName)])->value('id')
                ?? null;
            if (!$sibLabId) continue;

            $twParts = preg_split('/\s*-\s*/', $sibTimeWin);
            if (count($twParts) !== 2) continue;
            try {
                $sibStart = \Carbon\Carbon::createFromFormat('h:i A', trim($twParts[0]))->format('H:i:s');
                $sibEnd   = \Carbon\Carbon::createFromFormat('h:i A', trim($twParts[1]))->format('H:i:s');
            } catch (\Exception $e) { continue; }

            $sibLecturerId = $allCourses[$sibCourseId] ?? null;

            $inMemorySlots[] = [
                'lab_id'           => $sibLabId,
                'lecturer_user_id' => $sibLecturerId,
                'day_of_week'      => $sibDay,
                'start_time'       => $sibStart,
                'end_time'         => $sibEnd,
            ];
        }

        // 1. Fetch semester
        $semester = Semester::find($semesterId);
        if (!$semester) {
            return response()->json(['success' => false, 'message' => 'Semester not found']);
        }

        $semesterStartDate = Carbon::parse($semester->start_date);

        $targetDayIso = $dayMapping[$dayOfWeek] ?? null;
        if (!$targetDayIso) {
            return response()->json(['success' => false, 'message' => 'Invalid day_of_week: ' . $dayOfWeek]);
        }

        $startDayIso = $semesterStartDate->dayOfWeekIso; 
        $diff = $targetDayIso - $startDayIso;
        if ($diff < 0) {
            $diff += 7; 
        }
        $targetDate = $semesterStartDate->copy()->addDays($diff);
        $calculatedDateStr = $targetDate->format('Y-m-d');

        // Normalize time format to H:i:s for consistent comparison
        $startTime = \Carbon\Carbon::createFromFormat('H:i', substr($startTime, 0, 5))->format('H:i:s');
        $endTime   = \Carbon\Carbon::createFromFormat('H:i', substr($endTime, 0, 5))->format('H:i:s');

        // ═══ Pass 1: Lab collision re-check (shared logic, now with batch_siblings) ═══
        if (\App\Http\Controllers\ScheduleController::hasScheduleConflict(
            $labId, $semesterId, $dayOfWeek, $startTime, $endTime,
            null, $inMemorySlots
        )) {
            return response()->json([
                'success' => false,
                'message' => 'Scheduling Conflict Detected! The selected laboratory is already occupied during this timeslot for the current semester.'
            ], 422);
        }

        // ═══ Pass 2: Lecturer collision re-check (shared logic, now with batch_siblings) ═══
        $course = Course::find($courseId);
        if ($course && $course->user_id) {
            if (\App\Http\Controllers\ScheduleController::hasLecturerConflict(
                $course->user_id, $semesterId, $dayOfWeek, $startTime, $endTime,
                null, $inMemorySlots
            )) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lecturer Overlap Conflict! The assigned lecturer has already been scheduled for another class during this timeslot.'
                ], 422);
            }
        }

        // ═══ Defensive re-validation inside a transaction ═══
        DB::beginTransaction();
        try {
            // Final re-check before insert (catches race conditions within the lock window)
            if (\App\Http\Controllers\ScheduleController::hasScheduleConflict(
                $labId, $semesterId, $dayOfWeek, $startTime, $endTime,
                null, $inMemorySlots
            )) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Scheduling Conflict Detected! (safety re-check before commit)'
                ], 422);
            }

            if ($course && $course->user_id) {
                if (\App\Http\Controllers\ScheduleController::hasLecturerConflict(
                    $course->user_id, $semesterId, $dayOfWeek, $startTime, $endTime,
                    null, $inMemorySlots
                )) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Lecturer Overlap Conflict! (safety re-check before commit)'
                    ], 422);
                }
            }

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

            DB::commit();

            session()->flash('just_created_schedule_id', $schedule->id);

            return response()->json([
                'success' => true,
                'message' => $targetDate->format('Y-m-d (l)'),
                'schedule_id' => $schedule->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('saveOptimizedSchedule transaction failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Run the AI Scheduler pipeline end-to-end.
     *
     * POST /ai-scheduler/run
     *
     * Orchestrates the full workflow:
     *   1. Validates the incoming request (semester_id, prompt).
     *   2. Invokes generateAiSchedule() to obtain AI-computed timetable slots.
     *   3. Parses the structured slot list from the AI JSON response.
     *   4. Loops through each slot and persists it into the schedules table.
     *   5. Redirects with a flash message indicating success.
     *
     * If the AI call fails or returns an error, the method redirects back
     * with the appropriate error message embedded in the session.
     */
    public function runAiScheduler(Request $request)
    {
        // 1. Validate the structural inputs required by the AI pipeline
        $request->validate([
            'semester_id' => 'required|integer|exists:semesters,id',
            'prompt'      => 'required|string',
        ]);

        $semesterId = $request->input('semester_id');

        // 2. Invoke the AI generation endpoint to obtain raw timetable data.
        //    generateAiSchedule() returns a JsonResponse — extract its payload.
        $aiResponse = $this->generateAiSchedule($request);
        $payload    = $aiResponse->getData(true);

        // 3. Defensive guard: if the AI engine returned an error, surface it
        if (isset($payload['error']) || empty($payload['text'] ?? null)) {
            $errorMsg = $payload['error'] ?? 'The AI engine returned an empty response.';
            return redirect()->back()
                ->with('error', 'AI Scheduler Error: ' . $errorMsg)
                ->withInput();
        }

        $rawText = $payload['text'];

        // 4. Parse the JSON array from the AI response text.
        //    The system prompt instructs the model to emit a RAW JSON array
        //    without markdown fences, but we defensively strip backticks.
        $cleanJson = trim($rawText);
        $cleanJson = preg_replace('/^```(?:json)?\s*/i', '', $cleanJson);
        $cleanJson = preg_replace('/\s*```$/', '', $cleanJson);

        $slots = json_decode($cleanJson, true);

        if (!is_array($slots)) {
            Log::error('AI Scheduler runAiScheduler: Failed to decode AI JSON payload.', [
                'raw' => $rawText,
            ]);
            return redirect()->back()
                ->with('error', 'AI Scheduler Error: The AI returned an unreadable schedule format.')
                ->withInput();
        }

        // 5. Loop through each slot and persist into the database WITH VALIDATION.
        //    Each slot is expected to contain:
        //      - course_id   (int)
        //      - lab_name    (string — resolved to lab_id, case-insensitive)
        //      - day_of_week (string, e.g. "Monday")
        //      - time_window (string, e.g. "08:00 AM - 10:00 AM")
        //
        //    ═══════════════════════════════════════════════════════════════
        //    COLLISION SHIELD: Two-stage re-validation per slot:
        //      Stage A:  In-memory accumulator — checks against all slots
        //                already accepted earlier in THIS batch.
        //      Stage B:  Database query — checks against all committed rows.
        //    Both stages check BOTH lab-room overlap AND lecturer overlap.
        //    ═══════════════════════════════════════════════════════════════
        $acceptedSlots = []; // in-memory accumulator
        $savedCount = 0;
        $skippedCount = 0;

        // Pre-load all courses we'll need for lecturer lookups
        $courseIdsInBatch = array_unique(array_map(fn($s) => (int)($s['course_id'] ?? 0), $slots));
        $courseLecturerMap = \App\Models\Course::whereIn('id', $courseIdsInBatch)
            ->pluck('user_id', 'id')
            ->toArray();

        // Wrap the entire batch in a single DB transaction for atomicity + race-condition defense
        DB::beginTransaction();
        try {
            // Pre-load semester outside the loop
            $semester = Semester::find($semesterId);
            $dayMapping = [
                'Monday'    => 1, 'Tuesday'   => 2, 'Wednesday' => 3,
                'Thursday'  => 4, 'Friday'    => 5, 'Saturday'  => 6, 'Sunday'    => 7,
            ];
            $semesterStart = \Carbon\Carbon::parse($semester->start_date);
            $startDayIso = $semesterStart->dayOfWeekIso;

            foreach ($slots as $slot) {
                $courseId  = (int) ($slot['course_id'] ?? 0);
                $labName   = trim($slot['lab_name'] ?? '');
                $dayOfWeek = $slot['day_of_week'] ?? '';
                $timeWindow = $slot['time_window'] ?? '';

                if (!$courseId || !$labName || !$dayOfWeek || !$timeWindow) {
                    Log::warning('AI Scheduler runAiScheduler: Skipping malformed slot.', ['slot' => $slot]);
                    $skippedCount++;
                    continue;
                }

                // Resolve lab_name → lab_id (case-insensitive, with fallback)
                $lab = Laboratory::whereRaw('LOWER(lab_name) = ?', [strtolower($labName)])->first();
                if (!$lab) {
                    // Try exact match as fallback
                    $lab = Laboratory::where('lab_name', $labName)->first();
                }
                if (!$lab) {
                    Log::warning('AI Scheduler runAiScheduler: Lab name not found.', [
                        'lab_name' => $labName
                    ]);
                    $skippedCount++;
                    continue;
                }

                // Parse time_window "08:00 AM - 10:00 AM" → start_time / end_time
                $parts = preg_split('/\s*-\s*/', $timeWindow);
                if (count($parts) !== 2) {
                    Log::warning('AI Scheduler runAiScheduler: Bad time_window format.', ['time_window' => $timeWindow]);
                    $skippedCount++;
                    continue;
                }

                $startTime = \Carbon\Carbon::createFromFormat('h:i A', trim($parts[0]))->format('H:i:s');
                $endTime   = \Carbon\Carbon::createFromFormat('h:i A', trim($parts[1]))->format('H:i:s');

                // Compute the anchor date (first occurrence of day_of_week in the semester)
                $targetDayIso = $dayMapping[$dayOfWeek] ?? 1;
                $diff = $targetDayIso - $startDayIso;
                if ($diff < 0) {
                    $diff += 7;
                }
                $calculatedDate = $semesterStart->copy()->addDays($diff)->format('Y-m-d');

                // ── STAGE A+B: Lab collision check ──
                if (\App\Http\Controllers\ScheduleController::hasScheduleConflict(
                    $lab->id, $semesterId, $dayOfWeek, $startTime, $endTime,
                    null, $acceptedSlots
                )) {
                    Log::warning('AI Scheduler runAiScheduler: Lab collision — slot REJECTED.', [
                        'course_id' => $courseId,
                        'lab_name'  => $labName,
                        'day'       => $dayOfWeek,
                        'time'      => $timeWindow,
                    ]);
                    $skippedCount++;
                    continue;
                }

                // ── STAGE A+B: Lecturer collision check ──
                $lecturerUserId = $courseLecturerMap[$courseId] ?? null;
                if ($lecturerUserId) {
                    if (\App\Http\Controllers\ScheduleController::hasLecturerConflict(
                        $lecturerUserId, $semesterId, $dayOfWeek, $startTime, $endTime,
                        null, $acceptedSlots
                    )) {
                        Log::warning('AI Scheduler runAiScheduler: Lecturer collision — slot REJECTED.', [
                            'course_id'       => $courseId,
                            'lecturer_user_id'=> $lecturerUserId,
                            'day'            => $dayOfWeek,
                            'time'           => $timeWindow,
                        ]);
                        $skippedCount++;
                        continue;
                    }
                }

                // ── Persist ──
                $newSchedule = Schedule::create([
                    'schedule_type' => 'enroll',
                    'semester_id'   => $semesterId,
                    'lab_id'        => $lab->id,
                    'course_id'     => $courseId,
                    'booking_id'    => null,
                    'date'          => $calculatedDate,
                    'day_of_week'   => $dayOfWeek,
                    'start_time'    => $startTime,
                    'end_time'      => $endTime,
                    'is_recurring'  => 1,
                ]);

                // ── Log this slot into the in-memory accumulator ──
                $acceptedSlots[] = [
                    'lab_id'           => $lab->id,
                    'lecturer_user_id' => $lecturerUserId,
                    'day_of_week'      => $dayOfWeek,
                    'start_time'       => $startTime,
                    'end_time'         => $endTime,
                ];

                $savedCount++;
            }

            // ═══════════════════════════════════════════════════════════════
            // DEFENSE IN DEPTH: Final pre-commit re-validation of ALL saved
            // rows against each other — guarantees no double-booking can
            // persist even if a future code change breaks the loop checks.
            // ═══════════════════════════════════════════════════════════════
            if (count($acceptedSlots) > 1) {
                for ($i = 0; $i < count($acceptedSlots); $i++) {
                    for ($j = $i + 1; $j < count($acceptedSlots); $j++) {
                        $a = $acceptedSlots[$i];
                        $b = $acceptedSlots[$j];

                        // Lab overlap check
                        if (
                            $a['lab_id'] === $b['lab_id']
                            && $a['day_of_week'] === $b['day_of_week']
                            && $a['start_time'] < $b['end_time']
                            && $a['end_time'] > $b['start_time']
                        ) {
                            DB::rollBack();
                            Log::error('AI Scheduler: DEFENSE-IN-DEPTH lab overlap caught pre-commit!', [
                                'slot_a' => $a, 'slot_b' => $b,
                            ]);
                            return redirect()->back()
                                ->with('error', 'AI Scheduler Error: Internal collision detected between generated slots. Batch aborted — no schedules were saved.')
                                ->withInput();
                        }

                        // Lecturer overlap check
                        if (
                            !empty($a['lecturer_user_id']) && !empty($b['lecturer_user_id'])
                            && $a['lecturer_user_id'] === $b['lecturer_user_id']
                            && $a['day_of_week'] === $b['day_of_week']
                            && $a['start_time'] < $b['end_time']
                            && $a['end_time'] > $b['start_time']
                        ) {
                            DB::rollBack();
                            Log::error('AI Scheduler: DEFENSE-IN-DEPTH lecturer overlap caught pre-commit!', [
                                'slot_a' => $a, 'slot_b' => $b,
                            ]);
                            return redirect()->back()
                                ->with('error', 'AI Scheduler Error: Internal lecturer collision detected between generated slots. Batch aborted — no schedules were saved.')
                                ->withInput();
                        }
                    }
                }
            }

            DB::commit();

            $message = "AI Schedule generated and synchronized successfully. Saved: {$savedCount} slot(s).";
            if ($skippedCount > 0) {
                $message .= " Skipped: {$skippedCount} slot(s) due to conflicts or errors.";
            }

            session()->flash('just_created_schedule_id', $newSchedule->id);
            return redirect()->to('/ai-scheduler')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AI Scheduler runAiScheduler: Transaction failed.', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()
                ->with('error', 'AI Scheduler Error: ' . $e->getMessage())
                ->withInput();
        }
    }
}
