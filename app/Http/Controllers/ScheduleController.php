<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Laboratory;
use App\Models\Course;
use App\Models\Semester;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $laboratories = Laboratory::all();
        $selectedLabId = $request->input('lab_id', $laboratories->first()->id ?? null);
        $selectedLab = $laboratories->where('id', $selectedLabId)->first();
        $isLabMaintenance = $selectedLab && $selectedLab->status == 0;

        $semesters = \App\Models\Semester::all();
        $selectedSemesterId = $request->input('semester_id');
        $selectedSemester = $selectedSemesterId ? \App\Models\Semester::find($selectedSemesterId) : null;
        $semesterStartDate = $selectedSemester ? \Carbon\Carbon::parse($selectedSemester->start_date) : null;
        $semesterEndDate = $selectedSemester ? \Carbon\Carbon::parse($selectedSemester->end_date) : null;

        // If arriving from a "just created a schedule" action, override defaults
        // with that specific schedule's metadata so filters pre-select correctly.
        $justCreatedId = session('just_created_schedule_id');
        if ($justCreatedId) {
            $justCreatedSchedule = \App\Models\Schedule::find($justCreatedId);
            if ($justCreatedSchedule) {
                $selectedLabId = $justCreatedSchedule->lab_id;
                if ($justCreatedSchedule->semester_id) {
                    $selectedSemesterId = $justCreatedSchedule->semester_id;
                }
                if ($justCreatedSchedule->date) {
                    $request->merge(['date' => $justCreatedSchedule->date]);
                }
            }
        }

        $lecturers = \App\Models\User::where('user_role', 5)->get();

        $viewTarget = $request->input('view_target'); 
        $selectedLecturerId = null;

        if ($viewTarget) {
            if (str_starts_with($viewTarget, 'lab_')) {
                $selectedLabId = str_replace('lab_', '', $viewTarget);
            } elseif (str_starts_with($viewTarget, 'lec_')) {
                $selectedLecturerId = str_replace('lec_', '', $viewTarget);
            }
        } elseif (!$selectedLabId) {
            // No view_target and no lab_id from query params → default to first lab
            $firstLab = $laboratories->first();
            $selectedLabId = $firstLab ? $firstLab->id : null;

            if ($selectedLabId) {
                $request->merge(['view_target' => 'lab_' . $selectedLabId]);
            }
        } else {
            // lab_id was provided via query param (e.g. from store() redirect),
            // but no view_target → construct view_target so the dropdown shows selected
            $request->merge(['view_target' => 'lab_' . $selectedLabId]);
        }

        $selectedLab = $selectedLabId ? \App\Models\Laboratory::find($selectedLabId) : null;
        $selectedLecturer = $selectedLecturerId ? \App\Models\User::find($selectedLecturerId) : null;

        // Only load enroll schedules here, because booking/maintenance slots are rendered separately

        $scheduleQuery = Schedule::with(['course', 'semester', 'laboratory', 'booking']);   
       
        if ($selectedSemesterId) {
            $selectedSemester = $semesters->where('id', $selectedSemesterId)->first();
            if ($selectedSemester) {
                $semesterStartDate = \Carbon\Carbon::parse($selectedSemester->start_date);
                $semesterEndDate = \Carbon\Carbon::parse($selectedSemester->end_date);
            }
        }

        if ($selectedSemesterId) {
            $scheduleQuery->where(function ($query) use ($selectedSemesterId) {
                $query->where('semester_id', $selectedSemesterId)
                    ->orWhereIn('schedule_type', ['booking', 'maintenance']);
            });
        }

        if ($selectedLabId) {
            $scheduleQuery->where('lab_id', $selectedLabId);
        } elseif ($selectedLecturerId) {
         
            $scheduleQuery->whereHas('course', function ($q) use ($selectedLecturerId) {
                $q->where('user_id', $selectedLecturerId);
            });
        }

        $schedules = $scheduleQuery->get();
        // Setup current week logic
        $currentDate = $request->input('date') ? \Carbon\Carbon::parse($request->input('date')) : \Carbon\Carbon::now();
        
        try {
            $dateInput = $request->input('date');
            if ($dateInput) {
                $cleanDateStr = substr($dateInput, 0, 10); 
                $currentDate = \Carbon\Carbon::parse($cleanDateStr);
            } else {
                $currentDate = \Carbon\Carbon::now();
            }
        } catch (\Exception $e) {
            $currentDate = \Carbon\Carbon::now(); 
        }

        // If semester is selected, constrain currentDate within semester bounds
        if ($selectedSemester && $currentDate < $semesterStartDate) {
            $currentDate = $semesterStartDate->copy();
        } elseif ($selectedSemester && $currentDate > $semesterEndDate) {
            $currentDate = $semesterEndDate->copy();
        }
        
        $startOfWeek = $currentDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        
        $weekDates = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $weekDates[$days[$i]] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('M d'),
                'is_today' => $date->isToday()
            ];
        }

        // Fetch Bookings for this week
       $bookings = \App\Models\Booking::where('lab_id', $selectedLabId)
            ->whereBetween('date', [$startOfWeek->format('Y-m-d'), $startOfWeek->copy()->endOfWeek()->format('Y-m-d')])
            ->whereIn('status', [1, 2]) 
            ->get();

        // Build Timetable Matrix
        $timetable = [];
        for ($h = 8; $h <= 18; $h++) {
            $timeSlot = sprintf('%02d:00', $h);
            $timetable[$timeSlot] = [];
            foreach ($days as $day) {
                $timetable[$timeSlot][$day] = [
                    'type' => 'none',
                    'rowspan' => 1,
                    'schedule_id' => null, 
                    'data' => null
                ];
            }
        }

        foreach ($schedules as $sched) {
                try {
                    // For recurring schedules: render on the day_of_week for every week
                    if ($sched->is_recurring) {
                        $day = $sched->day_of_week;
                        $scheduleDate = $startOfWeek->copy()->addDays(array_search($day, $days));
                    } else {
                        // One-time event: only render if its date belongs to the currently viewed week
                        $scheduleDate = \Carbon\Carbon::parse($sched->date);
                        if ($scheduleDate->lt($startOfWeek) || $scheduleDate->gt($startOfWeek->copy()->endOfWeek())) {
                            continue; // skip events outside this week
                        }
                        $day = $scheduleDate->format('l');
                    }

                    // Enforce semester bounds when relevant
                    $scheduleSemester = $sched->semester;
                    $semesterStart = $scheduleSemester ? \Carbon\Carbon::parse($scheduleSemester->start_date) : null;
                    $semesterEnd = $scheduleSemester ? \Carbon\Carbon::parse($scheduleSemester->end_date) : null;
                    if ($semesterStart && $semesterEnd) {
                        if ($scheduleDate->lt($semesterStart) || $scheduleDate->gt($semesterEnd)) {
                            continue; // outside semester
                        }
                    }

                    $start = substr($sched->start_time, 0, 5);
                    $end = substr($sched->end_time, 0, 5);
                    $startCarbon = \Carbon\Carbon::createFromFormat('H:i', $start);
                    $endCarbon = \Carbon\Carbon::createFromFormat('H:i', $end);
                    $blocks = max(1, $startCarbon->diffInMinutes($endCarbon) / 60);

                    if (isset($timetable[$start][$day])) {

                        $realType = $sched->schedule_type ?: 'enroll';
                        

                        if ($sched->booking_id && $sched->booking) {
                            $realType = $sched->booking->type ?? $realType;
                        }

                        $timetable[$start][$day]['type'] = $realType;
                        $timetable[$start][$day]['rowspan'] = $blocks;
                        $timetable[$start][$day]['schedule_id'] = $sched->id;
                        

                        if ($realType !== 'enroll' && is_null($sched->booking)) {
                            $timetable[$start][$day]['data'] = (object) [
                                'purpose'    => 'Lab Maintenance (Orphaned)',
                                'booker_name'=> 'System',
                                'start_time' => $sched->start_time,
                                'end_time'   => $sched->end_time,
                            ];
                        } else {
                            $timetable[$start][$day]['data'] = ($realType === 'enroll') ? $sched : $sched->booking;
                        }

                        for ($i = 1; $i < $blocks; $i++) {
                            $nextTime = $startCarbon->copy()->addMinutes(60 * $i)->format('H:i');
                            if (isset($timetable[$nextTime][$day])) {
                                $timetable[$nextTime][$day]['type'] = 'skip';
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // ignore malformed schedules but continue processing others
                    continue;
                }
            }

            foreach ($bookings as $booking) {
            try {
                $bookingCarbon = \Carbon\Carbon::parse($booking->date);
                $day = $bookingCarbon->format('l');
                $start = substr($booking->start_time, 0, 5);
                $end = substr($booking->end_time, 0, 5);
                
                $startCarbon = \Carbon\Carbon::createFromFormat('H:i', $start);
                $endCarbon = \Carbon\Carbon::createFromFormat('H:i', $end);
                $blocks = max(1, $startCarbon->diffInMinutes($endCarbon) / 60);
                
                if (isset($timetable[$start][$day]) && $timetable[$start][$day]['type'] === 'none') {
                    $realBookingType = $booking->type ?? 'booking';
                    
                    $timetable[$start][$day]['type'] = $realBookingType;
                    $timetable[$start][$day]['rowspan'] = $blocks;
                    

                    $linkedScheduleId = \App\Models\Schedule::where('booking_id', $booking->id)->value('id');
                    $timetable[$start][$day]['schedule_id'] = $linkedScheduleId;
                    
                    $timetable[$start][$day]['data'] = $booking;
                    
                    for ($i = 1; $i < $blocks; $i++) {
                        $nextTime = $startCarbon->copy()->addMinutes(60 * $i)->format('H:i');
                        if (isset($timetable[$nextTime][$day])) {
                            $timetable[$nextTime][$day]['type'] = 'skip';
                        }
                    }
                }
            } catch (\Exception $e) { }
            }

        // ===== Lab Full Closure Overlay (applied AFTER all schedules/bookings) =====
        // Only paint 'maintenance' on cells that remain 'none', so real schedules
        // and bookings still show through. Data is stored as an object-compatible
        // stdClass so the Blade sidebar won't crash on ->purpose access.
        if ($isLabMaintenance) {
            foreach ($timetable as $time => &$daysRow) {
                foreach ($days as $day) {
                    if ($daysRow[$day]['type'] === 'none') {
                        $daysRow[$day]['type'] = 'maintenance';
                        $daysRow[$day]['data'] = (object) [
                            'purpose'    => 'Lab Closed for Maintenance',
                            'start_time' => '08:00:00',
                            'end_time'   => '18:00:00',
                        ];
                        // schedule_id intentionally stays null → Blade Edit button hidden (safe)
                    }
                }
            }
            unset($daysRow);
        }
        // ===== End Lab Full Closure Overlay =====

        // Setup monthly calendar logic
        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();
        $calendarDays = [];
        
        $startPad = $startOfMonth->dayOfWeekIso - 1;
        for ($i = $startPad; $i > 0; $i--) {
            $calendarDays[] = [
                'day' => $startOfMonth->copy()->subDays($i)->day,
                'is_current_month' => false,
                'is_today' => false,
                'is_selected_week' => false,
                'date' => $startOfMonth->copy()->subDays($i)->format('Y-m-d')
            ];
        }
        
        for ($i = 1; $i <= $endOfMonth->day; $i++) {
            $date = $startOfMonth->copy()->addDays($i - 1);
            $calendarDays[] = [
                'day' => $i,
                'is_current_month' => true,
                'is_today' => $date->isToday(),
                'is_selected_week' => $date->between($startOfWeek, $startOfWeek->copy()->endOfWeek()),
                'date' => $date->format('Y-m-d')
            ];
        }
        
        $endPad = 42 - count($calendarDays);
        for ($i = 1; $i <= $endPad; $i++) {
            $calendarDays[] = [
                'day' => $endOfMonth->copy()->addDays($i)->day,
                'is_current_month' => false,
                'is_today' => false,
                'is_selected_week' => false,
                'date' => $endOfMonth->copy()->addDays($i)->format('Y-m-d')
            ];
        }

        
        // Calculate prev/next week navigation with semester boundary checks
        $prevWeekDate = $currentDate->copy()->subWeek();
        $nextWeekDate = $currentDate->copy()->addWeek();
        
        // Check if prev week goes outside semester bounds
        $canGoPrevWeek = true;
        $prevWeekAtBoundary = false;
        if ($selectedSemester) {
            if ($prevWeekDate->endOfWeek() < $semesterStartDate) {
                $canGoPrevWeek = false;
            } elseif ($prevWeekDate->startOfWeek(\Carbon\Carbon::MONDAY) < $semesterStartDate) {
                $prevWeekAtBoundary = true;
                $prevWeekDate = $semesterStartDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
            }
        }
        
        // Check if next week goes outside semester bounds
        $canGoNextWeek = true;
        $nextWeekAtBoundary = false;
        if ($selectedSemester) {
            if ($nextWeekDate->startOfWeek(\Carbon\Carbon::MONDAY) > $semesterEndDate) {
                $canGoNextWeek = false;
            } elseif ($nextWeekDate->endOfWeek() > $semesterEndDate) {
                $nextWeekAtBoundary = true;
                $nextWeekDate = $semesterEndDate->copy()->endOfWeek();
            }
        }
        
         $monthName = $currentDate->format('F Y');
        $urlQueryArray = [];
        if ($request->filled('semester_id')) {
            $urlQueryArray['semester_id'] = $request->input('semester_id');
        }
        if ($request->filled('view_target')) {
            $urlQueryArray['view_target'] = $request->input('view_target');
        }

        $queryString = http_build_query($urlQueryArray);
        
        $params = $queryString ? '&' . $queryString : '';
        $qs = $params; 

        $prevWeek  = $currentDate->copy()->subWeek()->format('Y-m-d');
        $nextWeek  = $currentDate->copy()->addWeek()->format('Y-m-d');
        $prevMonth = $currentDate->copy()->subMonth()->format('Y-m-d');
        $nextMonth = $currentDate->copy()->addMonth()->format('Y-m-d');

        $selectedLab = $selectedLabId ? \App\Models\Laboratory::find($selectedLabId) : null;
            
        return view('admin.schedules.index', compact(
            'schedules', 
            'weekDates', 
            'calendarDays', 
            'monthName', 
            'currentDate',
            'prevMonth',
            'nextMonth',
            'prevWeek',
            'nextWeek',
            'canGoPrevWeek',
            'canGoNextWeek',
            'semesters',
            'selectedSemesterId',
            'selectedSemester',
            'semesterStartDate',
            'semesterEndDate',
            'laboratories',
            'selectedLabId',
            'selectedLab',
            'timetable',
            'isLabMaintenance',
            'lecturers',
            'selectedLecturerId',
            'selectedLecturer'
            
        ));
    }

    public function create()
    {

        $laboratories = Laboratory::orderBy('lab_name')->get();
        $courses = Course::orderBy('course_name')->get();
        $semesters = Semester::orderBy('start_date', 'desc')->get();

        $technicians = User::whereIn('user_role', [3, 4])->get();

        return view('admin.schedules.create', compact('laboratories', 'courses', 'semesters', 'technicians'));
    }

    /**
     * Unified scheduler conflict-detection function — single source of truth.
     *
     * Checks for lab-room overlap (same semester, same day, overlapping time range)
     * against the schedules table.
     *
     * Interval overlap formula (strict < / > allows exactly-touching slots):
     *   existing.start_time < newEndTime AND existing.end_time > newStartTime
     *
     * @param int         $labId           Laboratory ID
     * @param int         $semesterId      Semester ID
     * @param string      $dayOfWeek       Day name, e.g. "Monday"
     * @param string      $newStartTime    Start time in H:i:s format
     * @param string      $newEndTime      End time in H:i:s format
     * @param int|null    $excludeScheduleId  Optional schedule ID to exclude (for updates)
     * @param array       $inMemorySlots   Optional in-memory accumulator of slots already
     *                                     accepted in the current batch, each as
     *                                     ['lab_id','day_of_week','start_time','end_time']
     * @return bool                        True if a conflict exists
     */
    public static function hasScheduleConflict(
        int $labId,
        int $semesterId,
        string $dayOfWeek,
        string $newStartTime,
        string $newEndTime,
        ?int $excludeScheduleId = null,
        array $inMemorySlots = []
    ): bool
    {
        // ── A. Check in-memory batch accumulator first ──
        foreach ($inMemorySlots as $slot) {
            if (
                (int) $slot['lab_id'] === $labId
                && $slot['day_of_week'] === $dayOfWeek
                && $slot['start_time'] < $newEndTime
                && $slot['end_time'] > $newStartTime
            ) {
                return true;
            }
        }

        // ── B. Query the database ──
        $query = \App\Models\Schedule::where('lab_id', $labId)
            ->where('semester_id', $semesterId)
            ->where('day_of_week', $dayOfWeek);

        if ($excludeScheduleId) {
            $query->where('id', '!=', $excludeScheduleId);
        }

        $query->where('start_time', '<', $newEndTime)
              ->where('end_time', '>', $newStartTime);

        return $query->exists();
    }

    /**
     * Unified lecturer-collision detection.
     *
     * Checks whether a lecturer is already scheduled on a given day + semester
     * during an overlapping time window.
     *
     * @param int       $lecturerUserId   The lecturer's user ID (courses.user_id)
     * @param int       $semesterId       Semester ID
     * @param string    $dayOfWeek        Day name, e.g. "Monday"
     * @param string    $newStartTime     Start time in H:i:s format
     * @param string    $newEndTime       End time in H:i:s format
     * @param int|null  $excludeScheduleId
     * @param array     $inMemorySlots    In-memory batch accumulator (each must also
     *                                    have 'lecturer_user_id' key)
     * @return bool
     */
    public static function hasLecturerConflict(
        int $lecturerUserId,
        int $semesterId,
        string $dayOfWeek,
        string $newStartTime,
        string $newEndTime,
        ?int $excludeScheduleId = null,
        array $inMemorySlots = []
    ): bool
    {
        // ── A. In-memory batch check ──
        foreach ($inMemorySlots as $slot) {
            if (
                ((int)($slot['lecturer_user_id'] ?? 0)) === $lecturerUserId
                && $slot['day_of_week'] === $dayOfWeek
                && $slot['start_time'] < $newEndTime
                && $slot['end_time'] > $newStartTime
            ) {
                return true;
            }
        }

        // ── B. Database query ──
        $query = \App\Models\Schedule::where('schedules.semester_id', $semesterId)
            ->where('schedules.day_of_week', $dayOfWeek)
            ->where('schedules.start_time', '<', $newEndTime)
            ->where('schedules.end_time', '>', $newStartTime)
            ->join('courses', 'schedules.course_id', '=', 'courses.id')
            ->where('courses.user_id', $lecturerUserId);

        if ($excludeScheduleId) {
            $query->where('schedules.id', '!=', $excludeScheduleId);
        }

        return $query->exists();
    }

    public function getOccupiedSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'lab_id' => 'required|exists:laboratories,id',
            'semester_id' => 'required|exists:semesters,id',
            'exclude_schedule_id' => 'nullable|exists:schedules,id',
        ]);

        $date = $request->input('date');
        $dayOfWeek = \Carbon\Carbon::parse($date)->format('l');
        $labId = $request->input('lab_id');
        $semesterId = $request->input('semester_id');
        $excludeScheduleId = $request->input('exclude_schedule_id');

        // 1. Query schedules (both recurring matching day-of-week AND date-specific on same date)
        $schedules = Schedule::where('lab_id', $labId)
            ->where('semester_id', $semesterId)
            ->when($excludeScheduleId, function ($query) use ($excludeScheduleId) {
                $query->where('id', '!=', $excludeScheduleId);
            })
            ->where(function ($query) use ($dayOfWeek, $date) {
                $query->where(function ($subQuery) use ($dayOfWeek) {
                    $subQuery->where('is_recurring', true)
                             ->where('day_of_week', $dayOfWeek);
                })
                ->orWhere(function ($subQuery) use ($date) {
                    $subQuery->where('is_recurring', false)
                             ->where('date', $date);
                });
            })
            ->get(['id', 'start_time', 'end_time', 'is_recurring']);

        // 2. Also query bookings table for the same date (bookings don't have semester_id, so match by lab + date)
        $bookings = Booking::where('lab_id', $labId)
            ->where('date', $date)
            ->when($excludeScheduleId, function ($q) use ($excludeScheduleId) {
                $linkedBookingId = Schedule::where('id', $excludeScheduleId)->value('booking_id');
                if ($linkedBookingId) {
                    $q->where('id', '!=', $linkedBookingId);
                }
            })
            ->get(['id', 'start_time', 'end_time']);

        // 3. Merge and normalize to H:i format
        $occupied = collect();

        foreach ($schedules as $s) {
            $occupied->push([
                'id' => $s->id,
                'is_recurring' => (bool) $s->is_recurring,
                'start_time' => substr($s->start_time, 0, 5),
                'end_time' => substr($s->end_time, 0, 5),
            ]);
        }

        foreach ($bookings as $b) {
            $occupied->push([
                'id' => $b->id,
                'is_recurring' => false,
                'start_time' => substr($b->start_time, 0, 5),
                'end_time' => substr($b->end_time, 0, 5),
            ]);
        }

        return response()->json($occupied->values());
    }

    public function store(Request $request)
{
    $type = $request->input('schedule_type');
    $isAllDayMaintenance = ($type === 'maintenance' && ($request->input('all_day') == '1' || $request->input('all_day') == true));

    // Force strict 8:00-18:00 timestamp parameters if it is an All-Day Maintenance
    if ($isAllDayMaintenance) {
        $request->merge([
            'start_time' => '08:00',
            'end_time' => '18:00'
        ]);
    }

    $rules = [
        'schedule_type' => ['required', Rule::in(['enroll','booking','maintenance'])],
        'lab_id' => ['required', 'exists:laboratories,id'],
        'date' => in_array($type, ['booking', 'maintenance']) 
            ? ['required', 'date', 'after_or_equal:today'] 
            : ['required', 'date'],
        'start_time' => ['required'],
        'end_time' => ['required'],
        'is_recurring' => ['sometimes', 'in:0,1'],
    ];

    if ($type === 'enroll') {
        $rules['semester_id'] = ['required', 'exists:semesters,id'];
        $rules['course_id'] = ['required', 'exists:courses,id'];
    } elseif ($type === 'booking') {
        $rules['purpose'] = ['required', 'string', 'max:255'];
        $rules['booked_by'] = ['nullable', 'string', 'max:255'];
    } elseif ($type === 'maintenance') {
        $rules['purpose'] = ['required', 'string', 'max:255'];
        $rules['user_id'] = ['nullable', 'exists:users,id'];
        $rules['all_day'] = ['sometimes'];
    }

    $validator = Validator::make($request->all(), $rules);

    $validator->after(function ($validator) use ($request, $type, $isAllDayMaintenance) {
        if ($validator->errors()->isNotEmpty()) return;

        // CRITICAL PROTECTION: Completely bypass overlap check if it's an All-Day Maintenance override
        if ($isAllDayMaintenance) {
            return;
        }

        if ($type === 'enroll') {
            $semesterId = $request->input('semester_id');
            $semester = \App\Models\Semester::find($semesterId);
            if ($semester) {
                $inputDate = $request->input('date');
                if ($inputDate < $semester->start_date || $inputDate > $semester->end_date) {
                    $validator->errors()->add('date', "The selected date ({$inputDate}) falls outside the range of this semester ({$semester->start_date} to {$semester->end_date}).");
                    return;
                }
            }
        }

        $labId = $request->input('lab_id');
        $date = $request->input('date');
        $start = $request->input('start_time');
        $end = $request->input('end_time');

        try {
            $newStart = \Carbon\Carbon::createFromFormat('H:i', substr($start, 0, 5))->format('H:i:s');
            $newEnd = \Carbon\Carbon::createFromFormat('H:i', substr($end, 0, 5))->format('H:i:s');
            $dayOfWeek = \Carbon\Carbon::parse($date)->format('l');

            // 1. Check against existing Schedules
            $schedules = Schedule::where('lab_id', $labId)
                ->where(function ($q) use ($dayOfWeek, $date) {
                    $q->where(function ($sub) use ($dayOfWeek) {
                        $sub->where('is_recurring', true)->where('day_of_week', $dayOfWeek);
                    })->orWhere(function ($sub) use ($date) {
                        $sub->where('is_recurring', false)->where('date', $date);
                    });
                })->get(['start_time', 'end_time']);

            foreach ($schedules as $s) {
                if ($newStart < $s->end_time && $newEnd > $s->start_time) {
                    $validator->errors()->add('start_time', "Conflict with existing regular scheduling (conflict period: {$s->start_time} - {$s->end_time}).");
                    return;
                }
            }

            // 2. Check against existing Bookings / Maintenances
            $bookings = Booking::where('lab_id', $labId)->where('date', $date)->get(['start_time', 'end_time']);
            foreach ($bookings as $b) {
                if ($newStart < $b->end_time && $newEnd > $b->start_time) {
                    $validator->errors()->add('start_time', "Conflict with existing booking or maintenance (conflict period: {$b->start_time} - {$b->end_time}).");
                    return;
                }
            }
        } catch (\Exception $ex) {
            $validator->errors()->add('start_time', 'Time validation parsing exception format error.');
        }
    });

    if ($validator->fails()) {
        if ($request->expectsJson()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return redirect()->back()->withErrors($validator)->withInput();
    }

    DB::beginTransaction();
    try {
        $isRecurring = $request->boolean('is_recurring', false);
        if ($type === 'enroll') {
            $isRecurring = true;
        }
        
        $dayOfWeek = \Carbon\Carbon::parse($request->input('date'))->format('l');
        $date = $request->input('date');
        
        // Ensure standard string timestamp format matching DB precision (H:i:s)
        $startTime = \Carbon\Carbon::createFromFormat('H:i', substr($request->input('start_time'), 0, 5))->format('H:i:s');
        $endTime = \Carbon\Carbon::createFromFormat('H:i', substr($request->input('end_time'), 0, 5))->format('H:i:s');

        // ===== ALL-DAY MAINTENANCE OVERRIDE: Delete conflicting records before inserting =====
        if ($isAllDayMaintenance) {
            $labId = $request->input('lab_id');

            // 1. Find all schedules for this lab on this date
            $conflictScheduleIds = Schedule::where('lab_id', $labId)
                ->where(function ($q) use ($dayOfWeek, $date) {
                    $q->where(function ($sub) use ($dayOfWeek) {
                        $sub->where('is_recurring', true)->where('day_of_week', $dayOfWeek);
                    })->orWhere(function ($sub) use ($date) {
                        $sub->where('is_recurring', false)->where('date', $date);
                    });
                })
                ->pluck('id');

            // 2. Find booking IDs linked to those schedules
            $linkedBookingIds = Schedule::whereIn('id', $conflictScheduleIds)
                ->whereNotNull('booking_id')
                ->pluck('booking_id');

            // 3. Also find standalone bookings for this lab on this date
            $standaloneBookingIds = Booking::where('lab_id', $labId)
                ->where('date', $date)
                ->whereNotIn('id', $linkedBookingIds)
                ->pluck('id');

            // 4. Merge all booking IDs to delete
            $allBookingIdsToDelete = $linkedBookingIds->merge($standaloneBookingIds)->unique();

            // 5. Delete child schedules first
            if ($conflictScheduleIds->isNotEmpty()) {
                Schedule::whereIn('id', $conflictScheduleIds)->delete();
            }

            // 6. Delete parent bookings & clean up request maps
            if ($allBookingIdsToDelete->isNotEmpty()) {
                \App\Models\BookingRequest::whereIn('booking_id', $allBookingIdsToDelete)->delete();
                Booking::whereIn('id', $allBookingIdsToDelete)->delete();
            }
        }
        // ===== END ALL-DAY MAINTENANCE OVERRIDE =====

        $targetSemesterId = $request->input('semester_id');
        if (!$targetSemesterId && $date) {
            $matchedSemester = \App\Models\Semester::where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->first();
            if ($matchedSemester) {
                $targetSemesterId = $matchedSemester->id;
            }
        }

        if ($type === 'enroll') {
            $schedule = Schedule::create([
                'schedule_type' => 'enroll',
                'semester_id' => $targetSemesterId,
                'lab_id' => $request->input('lab_id'),
                'course_id' => $request->input('course_id'),
                'booking_id' => null,
                'date' => $date,
                'day_of_week' => $dayOfWeek,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'is_recurring' => $isRecurring,
            ]);
        } elseif ($type === 'booking') {
            $booking = Booking::create([
                'lab_id' => $request->input('lab_id'),
                'type' => 'booking',
                'user_id' => Auth::id() ?? null,
                'booker_name' => $request->input('booked_by'),
                'purpose' => $request->input('purpose'),
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => 2,
            ]);

            $schedule = Schedule::create([
                'schedule_type' => 'booking',
                'semester_id' => $targetSemesterId ?: null,
                'lab_id' => $request->input('lab_id'),
                'course_id' => null,
                'booking_id' => $booking->id,
                'date' => $date,
                'day_of_week' => $dayOfWeek,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'is_recurring' => $isRecurring,
            ]);
        } else { // maintenance
            $booking = Booking::create([
                'lab_id' => $request->input('lab_id'),
                'type' => 'maintenance',
                'user_id' => $request->input('user_id') ?: null,
                'booker_name' => null,
                'purpose' => $request->input('purpose'),
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => 2,
            ]);

            $schedule = Schedule::create([
                'schedule_type' => 'maintenance',
                'semester_id' => $targetSemesterId ?: null,
                'lab_id' => $request->input('lab_id'),
                'course_id' => null,
                'booking_id' => $booking->id,
                'date' => $date,
                'day_of_week' => $dayOfWeek,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'is_recurring' => $isRecurring,
            ]);
        }

        DB::commit();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Schedule saved successfully.']);
        }
        session()->flash('just_created_schedule_id', $schedule->id);
        return redirect()->route('schedules.index')
            ->with('success', 'Schedule saved successfully.');
        
    } catch (\Exception $ex) {
        DB::rollBack();
        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $ex->getMessage()], 500);
        }
        return redirect()->back()->withErrors(['error' => 'Database Save Failed: ' . $ex->getMessage()])->withInput();
    }
}

public function getAvailableTimeSlots(Request $request)
{
    $date = $request->query('date');
    $labId = $request->query('laboratory_id');
    $excludeId = $request->query('exclude_schedule_id');

    $allSlots = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];

    if (!$date || !$labId) {
        return response()->json($allSlots);
    }

    $dayOfWeek = \Carbon\Carbon::parse($date)->format('l');

    // Fetch occupied slots from schedules — both recurring (day-of-week) and date-specific
    $occupiedSchedules = Schedule::where('lab_id', $labId)
        ->where(function ($q) use ($dayOfWeek, $date) {
            $q->where(function ($sub) use ($dayOfWeek) {
                $sub->where('is_recurring', true)->where('day_of_week', $dayOfWeek);
            })->orWhere(function ($sub) use ($date) {
                $sub->where('is_recurring', false)->where('date', $date);
            });
        })
        ->when($excludeId, function($query) use ($excludeId) {
            $query->where('id', '!=', $excludeId);
        })
        ->get(['start_time', 'end_time']);

    // Fetch occupied slots from bookings / maintenance — only date-specific
    $occupiedBookings = Booking::where('lab_id', $labId)
        ->where('date', $date)
        ->when($excludeId, function($q) use ($excludeId) {
            $linkedBookingId = Schedule::where('id', $excludeId)->value('booking_id');
            if ($linkedBookingId) {
                $q->where('id', '!=', $linkedBookingId);
            }
        })
        ->get(['start_time', 'end_time']);

    $allOccupied = $occupiedSchedules->merge($occupiedBookings);

    $availableSlots = array_filter($allSlots, function($time) use ($allOccupied) {
        foreach ($allOccupied as $slot) {
            // Standardize format to 5 characters (H:i) to properly compare with array slots
            $s = substr($slot->start_time, 0, 5);
            $e = substr($slot->end_time, 0, 5);
            if ($time >= $s && $time < $e) {
                return false;
            }
        }
        return true;
    });

    return response()->json(array_values($availableSlots));
}

    public function checkOccupiedSlots(Request $request)
{
    $date = $request->query('date');
    $labId = $request->query('laboratory_id');
    $excludeScheduleId = $request->query('exclude_schedule_id');

    if (!$date || !$labId) return response()->json([]);

    $dayOfWeek = \Carbon\Carbon::parse($date)->format('l');

    // 1. Query schedules (both recurring and date-specific) — exclude current schedule when editing
    $schedules = Schedule::where('lab_id', $labId)
        ->when($excludeScheduleId, function ($q) use ($excludeScheduleId) {
            // Exclude the schedule being edited AND its linked booking (if any)
            $linkedBookingId = Schedule::where('id', $excludeScheduleId)->value('booking_id');
            $q->where('id', '!=', $excludeScheduleId);
            if ($linkedBookingId) {
                $q->where('booking_id', '!=', $linkedBookingId);
            }
        })
        ->where(function ($q) use ($dayOfWeek, $date) {
            $q->where(function ($sub) use ($dayOfWeek) {
                $sub->where('is_recurring', true)->where('day_of_week', $dayOfWeek);
            })->orWhere(function ($sub) use ($date) {
                $sub->where('is_recurring', false)->where('date', $date);
            });
        })
        ->get(['id', 'start_time', 'end_time', 'booking_id']);

    // 2. Query bookings for the specific date — exclude booking linked to the schedule being edited
    $bookings = Booking::where('lab_id', $labId)
        ->where('date', $date)
        ->when($excludeScheduleId, function ($q) use ($excludeScheduleId) {
            $linkedBookingId = Schedule::where('id', $excludeScheduleId)->value('booking_id');
            if ($linkedBookingId) {
                $q->where('id', '!=', $linkedBookingId);
            }
        })
        ->get(['id', 'start_time', 'end_time']);

    // 3. Merge and normalize time format to H:i (strip seconds)
    $occupied = collect();

    foreach ($schedules as $s) {
        $occupied->push([
            'start_time' => substr($s->start_time, 0, 5),
            'end_time' => substr($s->end_time, 0, 5),
        ]);
    }

    foreach ($bookings as $b) {
        $occupied->push([
            'start_time' => substr($b->start_time, 0, 5),
            'end_time' => substr($b->end_time, 0, 5),
        ]);
    }

    return response()->json($occupied->values());
}

    public function edit(Schedule $schedule)
    {
        $laboratories = Laboratory::all();
        $courses = Course::all();
        $semesters = Semester::all();
        $technicians = User::whereIn('user_role', [3,4])->get();
        return view('admin.schedules.edit', compact('schedule', 'laboratories', 'courses', 'semesters', 'technicians'));
    }

   public function update(Request $request, Schedule $schedule)
{
    // Determine the schedule type (use existing if not provided)
    $type = $schedule->schedule_type ?? $request->input('schedule_type');

    // Base validation rules
    $rules = [
        'lab_id' => 'required|exists:laboratories,id',
        'date' => 'required|date',
        'start_time' => ['required', 'date_format:H:i'],
        'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        'is_recurring' => 'sometimes|in:0,1',
    ];

    // Conditional rules based on schedule type
    if ($type === 'enroll') {
        $rules['semester_id'] = 'required|exists:semesters,id';
        $rules['course_id'] = 'required|exists:courses,id';
    } elseif ($type === 'booking') {
        $rules['purpose'] = 'required|string|max:255';
        $rules['booker_name'] = 'nullable|string|max:255';
    } elseif ($type === 'maintenance') {
        $rules['purpose'] = 'required|string|max:255';
        $rules['technician_id'] = 'nullable|exists:users,id';
        $rules['all_day'] = 'sometimes|in:1';
    }

    $validator = Validator::make($request->all(), $rules);

    // Business rules and conflict checks
    $validator->after(function ($validator) use ($request, $schedule, $type) {
        if ($validator->errors()->isNotEmpty()) return;

        $date = $request->input('date');

        // Enroll type: date must fall within the semester duration
        if ($type === 'enroll' && $request->filled('semester_id')) {
            $semester = Semester::find($request->input('semester_id'));
            if ($semester) {
                if ($date < $semester->start_date || $date > $semester->end_date) {
                    $validator->errors()->add('date', "Selected date must fall within the semester ({$semester->start_date} - {$semester->end_date}).");
                }
            }
        }

        // Validate business operating hours (08:00 - 18:00)
        $start = $request->input('start_time');
        $end = $request->input('end_time');
        if (isset($start) && isset($end)) {
            try {
                $s = \Carbon\Carbon::createFromFormat('H:i', $start);
                $e = \Carbon\Carbon::createFromFormat('H:i', $end);
                if ($s->hour < 8 || $e->hour > 18 || $s->gte($e)) {
                    $validator->errors()->add('start_time', 'Time must be between 08:00 and 18:00 and end after start.');
                }
            } catch (\Exception $ex) {
                $validator->errors()->add('start_time', 'Invalid time format.');
            }
        }

        // Conflict checking variables
        $labId = $request->input('lab_id');
        

        $newStart = substr($request->input('start_time'), 0, 5);
        $newEnd = substr($request->input('end_time'), 0, 5);
        $dayOfWeek = \Carbon\Carbon::parse($date)->format('l');

        // 1) Conflict Check: Schedules Table (Excluding current schedule record)
        $schedules = Schedule::where('lab_id', $labId)
            ->where(function ($q) use ($dayOfWeek, $date) {
                $q->where(function ($sub) use ($dayOfWeek) {
                    $sub->where('is_recurring', true)->where('day_of_week', $dayOfWeek);
                })->orWhere(function ($sub) use ($date) {
                    $sub->where('is_recurring', false)->where('date', $date);
                });
            })
            ->where('id', '!=', $schedule->id) // Safely exclude self
            ->get(['start_time', 'end_time']);

        foreach ($schedules as $s) {

            $dbStart = substr($s->start_time, 0, 5);
            $dbEnd = substr($s->end_time, 0, 5);

            if ($dbStart < $newEnd && $dbEnd > $newStart) {
                $validator->errors()->add('start_time', 'Conflicts with an existing schedule.');
                break;
            }
        }

        // 2) Conflict Check: Bookings Table (Excluding the linked booking record)
        if ($validator->errors()->isEmpty()) {
            $bookings = Booking::where('lab_id', $labId)
                ->where('date', $date)
                ->get(['start_time', 'end_time', 'id']);

            foreach ($bookings as $b) {
                // If this schedule record is linked to this specific booking row, skip check
                if ($schedule->booking_id && $b->id == $schedule->booking_id) {
                    continue;
                }

                $dbBookingStart = substr($b->start_time, 0, 5);
                $dbBookingEnd = substr($b->end_time, 0, 5);

                if ($dbBookingStart < $newEnd && $dbBookingEnd > $newStart) {
                    $validator->errors()->add('start_time', 'Conflicts with an existing booking/maintenance.');
                    break;
                }
            }
        }
    });

    // Handle Validation Failure
    if ($validator->fails()) {
        if ($request->expectsJson()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return redirect()->back()->withErrors($validator)->withInput();
    }

    // Begin building update data array
    $data = [];
    $data['lab_id'] = $request->input('lab_id');
    $data['date'] = $request->input('date');
    $data['day_of_week'] = \Carbon\Carbon::parse($request->input('date'))->format('l');
    $data['is_recurring'] = $request->boolean('is_recurring', $schedule->is_recurring);
    $data['start_time'] = $request->input('start_time');
    $data['end_time'] = $request->input('end_time');

    // Execute save strategy based on schedule workflow type
    if ($type === 'enroll') {
        $data['semester_id'] = $request->input('semester_id');
        $data['course_id'] = $request->input('course_id');
        $data['booking_id'] = null;
        $data['schedule_type'] = 'enroll';

        // Only calculate end time via course hours for Enroll schedules
        $course = Course::find($data['course_id']);
        if ($course) {
            $hours = max(1, (int)$course->hours);
            $parsedStart = (strlen($data['start_time']) === 5) ? 
                \Carbon\Carbon::createFromFormat('H:i', $data['start_time']) : 
                \Carbon\Carbon::createFromFormat('H:i:s', $data['start_time']);

            $data['end_time'] = $parsedStart->copy()->addHours($hours)->format('H:i:s');
        }

    } else {
        // booking or maintenance logic
        if ($schedule->booking_id) {
            $bookingData = [
                'lab_id' => $request->input('lab_id'),
                'purpose' => $request->input('purpose'),
                'date' => $request->input('date'),
                'start_time' => $request->input('start_time'),
                'end_time' => $request->input('end_time'),
            ];

            if ($type === 'booking') {
                $bookingData['booker_name'] = $request->input('booker_name');
                $bookingData['type'] = 'booking';
            } else {
                $bookingData['user_id'] = $request->input('technician_id') ?: null;
                $bookingData['type'] = 'maintenance';
            }
            Booking::where('id', $schedule->booking_id)->update($bookingData);
        }

        $data['semester_id'] = $type === 'booking' ? $request->input('semester_id') ?: null : null;
        $data['course_id'] = null;
        $data['booking_id'] = $schedule->booking_id;
        $data['schedule_type'] = $type;
    }

    // Final Single Update Execution
    $schedule->update($data);

    // Build matching query parameters for smooth view refreshing
    $redirectParams = [];
    if ($request->filled('return_date')) { $redirectParams['date'] = $request->input('return_date'); }
    if ($request->filled('return_lab_id')) { $redirectParams['lab_id'] = $request->input('return_lab_id'); }
    if ($request->filled('return_semester_id')) { $redirectParams['semester_id'] = $request->input('return_semester_id'); }
    
    $queryString = count($redirectParams) ? '?' . http_build_query($redirectParams) : '';
    $finalRedirectUrl = url('/schedules' . $queryString);

    // Return responsive outputs
    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'Schedule updated successfully.',
            'redirect_url' => $finalRedirectUrl,
            'schedule' => $schedule
        ]);
    }

    return redirect($finalRedirectUrl)->with('success', 'Schedule updated successfully.');
}
   
   public function destroy(Request $request, Schedule $schedule)
    {

        if (!empty($schedule->booking_id)) {
            $linkedBooking = \App\Models\Booking::find($schedule->booking_id);
            if ($linkedBooking) {
                $linkedBooking->delete();
            }

        }

        $date  = $request->input('redirect_date', $schedule->date);
        $labId = $request->input('redirect_lab_id', $schedule->lab_id);

        $schedule->delete();

        $queryParams = [];
        if (!empty($date)) {
            $queryParams['date'] = $date;
        }

        if (!empty($labId)) {
            $queryParams['view_target'] = 'lab_' . $labId;
        } elseif ($request->has('view_target')) {
            $queryParams['view_target'] = $request->input('view_target');
        }

        if (!empty($queryParams)) {
            return redirect()->to('/schedules?' . http_build_query($queryParams))
                             ->with('success', 'Maintenance completely removed.');
        }

        return redirect()->to('/schedules')->with('success', 'Maintenance completely removed.');
    }

    /**
     * AJAX Slot Checker API — Checks whether a lab room is available
     * for a given date and time window.
     *
     * GET /api/slot-checker?lab_id=X&date=Y&start_time=Z&end_time=W
     *
     * Returns JSON:
     *   { "available": true }   — slot is free
     *   { "available": false }  — slot is occupied (conflict exists)
     *
     * Conflict formula:
     *   existing.start_time < input.end_time
     *   AND existing.end_time > input.start_time
     */
    public function slotChecker(Request $request)
    {
        $request->validate([
            'lab_id'     => 'required|integer|exists:laboratories,id',
            'date'       => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
        ]);

        $labId       = $request->input('lab_id');
        $date        = $request->input('date');
        $startTime   = $request->input('start_time') . ':00';
        $endTime     = $request->input('end_time') . ':00';
        $dayOfWeek   = \Carbon\Carbon::parse($date)->format('l');

        // 1. Check Schedules table (recurring + date-specific)
        $scheduleConflict = Schedule::where('lab_id', $labId)
            ->where(function ($q) use ($dayOfWeek, $date) {
                $q->where(function ($sub) use ($dayOfWeek) {
                    $sub->where('is_recurring', true)
                         ->where('day_of_week', $dayOfWeek);
                })->orWhere(function ($sub) use ($date) {
                    $sub->where('is_recurring', false)
                         ->where('date', $date);
                });
            })
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();

        if ($scheduleConflict) {
            return response()->json(['available' => false]);
        }

        // 2. Check Bookings table (date-specific)
        $bookingConflict = Booking::where('lab_id', $labId)
            ->where('date', $date)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();

        if ($bookingConflict) {
            return response()->json(['available' => false]);
        }

        return response()->json(['available' => true]);
    }
}
