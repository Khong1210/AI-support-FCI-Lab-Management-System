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
        $currentSemester = DB::table('semesters')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        $currentSemesterId = $currentSemester ? $currentSemester->id : (DB::table('semesters')->orderBy('id', 'desc')->value('id') ?? 1);

        // Core data: fetch schedules strictly filtered by semester_id
        $schedules = DB::table('schedules')
            ->leftJoin('laboratories', 'schedules.lab_id', '=', 'laboratories.id')
            ->leftJoin('courses', 'schedules.course_id', '=', 'courses.id')
            ->select(
                'schedules.*',
                'laboratories.lab_name as laboratory_name',
                'courses.course_name as course_title'
            )
            ->where('schedules.semester_id', $currentSemesterId)
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
        $courses = DB::table('courses')->get();
        $lecturers = DB::table('users')->where('user_role', 5)->get();
        $semesters = DB::table('semesters')->get();

        return view('ai-scheduler', compact(
            'schedules',
            'softwares',
            'equipments',
            'laboratories',
            'courses',
            'lecturers',
            'currentSemesterId',
            'semesters'
        ));
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

        // ─── BACKEND DE-COLLISION: Fetch ALL existing schedules (cross-semester)
        //     No semester_id filter — every physical room occupancy is a hard constraint
        $existingSchedules = DB::table('schedules')
            ->leftJoin('laboratories', 'schedules.lab_id', '=', 'laboratories.id')
            ->leftJoin('courses', 'schedules.course_id', '=', 'courses.id')
            ->select(
                'schedules.day_of_week',
                'schedules.start_time',
                'schedules.end_time',
                'laboratories.lab_name',
                'courses.course_name'
            )
            ->whereNotNull('schedules.day_of_week')
            ->whereNotNull('schedules.start_time')
            ->whereNotNull('schedules.end_time')
            ->get();

        $allLaboratories = DB::table('laboratories')->get();
        $allLecturers = DB::table('users')->where('user_role', 5)->get();

        // ─── Build system prompt with absolute collision constraints
        $systemPrompt = "You are a professional lab scheduling AI. You MUST avoid the following time slots which are ALREADY OCCUPIED in the database. Failure to avoid these will result in an invalid schedule: " . json_encode($existingSchedules);

        // ─── Build the user prompt with the frontend's scheduling requirements
        $userPrompt = $request->input('prompt');

        // ─── DEEPSEEK NATIVE API CALL ───
        $apiKey = env('DEEPSEEK_API_KEY');

        if (!$apiKey) {
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
                ->post('https://api.deepseek.com/chat/completions', [
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
     * using precise Carbon calculation. Includes absolute physical collision safeguard.
     */
    public function saveOptimizedSchedule(Request $request)
    {
        $semesterId = $request->input('semester_id');
        $dayOfWeek  = $request->input('day_of_week');  // e.g., 'Monday'
        $startTime  = $request->input('start_time');   // e.g., '08:00:00'
        $endTime    = $request->input('end_time');     // e.g., '10:00:00'
        $labId      = $request->input('lab_id');
        $courseId   = $request->input('course_id');

        // 1. Fetch semester start_date
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

        // 3. ⚡ ABSOLUTE HARD STOP: Physical room collision check (ignores semester_id)
        //     Checks if the exact physical room at that exact calendar date + time window is taken
        $physicalCollision = Schedule::where('date', $calculatedDateStr)
            ->where('lab_id', $labId)
            ->where(function($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
            })
            ->exists();

        if ($physicalCollision) {
            return response()->json([
                'success' => false,
                'message' => 'Collision Blocked! Physical Room ' . $labId . ' is already occupied on ' . $calculatedDateStr . ' during this time slot. Sync rejected.'
            ], 422);
        }

        // 4. Persist the Schedule with hardcoded fields
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