<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Laboratory;
use App\Models\Course;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

        $lecturers = \App\Models\User::where('user_role', 5)->get();

        $viewTarget = $request->input('view_target'); 
        $selectedLabId = null;
        $selectedLecturerId = null;

        if ($viewTarget) {
            if (str_starts_with($viewTarget, 'lab_')) {
                $selectedLabId = str_replace('lab_', '', $viewTarget);
            } elseif (str_starts_with($viewTarget, 'lec_')) {
                $selectedLecturerId = str_replace('lec_', '', $viewTarget);
            }
        } else {
            $firstLab = $laboratories->first();
            $selectedLabId = $firstLab ? $firstLab->id : null;

            if ($selectedLabId) {
                $request->merge(['view_target' => 'lab_' . $selectedLabId]);
            }
        }

        $selectedLecturerId = $request->input('user_id'); 
        $selectedLecturer = $selectedLecturerId ? \App\Models\User::find($selectedLecturerId) : null;

        $scheduleQuery = Schedule::with(['course', 'semester', 'laboratory'])->where('lab_id', $selectedLabId);
        if ($selectedSemesterId) {
            $selectedSemester = $semesters->where('id', $selectedSemesterId)->first();
            if ($selectedSemester) {
                $semesterStartDate = \Carbon\Carbon::parse($selectedSemester->start_date);
                $semesterEndDate = \Carbon\Carbon::parse($selectedSemester->end_date);
            }
            $scheduleQuery->where('semester_id', $selectedSemesterId);
        }
        $schedules = $scheduleQuery->get();
        
        // Setup current week logic
        $currentDate = $request->input('date') ? \Carbon\Carbon::parse($request->input('date')) : \Carbon\Carbon::now();
        
        try {
            $dateInput = $request->input('date');
            if ($dateInput) {
                // 不管後面有沒有被髒資料黏住，一律只切前 10 個字元 (YYYY-MM-DD)
                $dateStr = substr($dateInput, 0, 10); 
                $currentDate = \Carbon\Carbon::parse($dateStr);
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
            ->where('status', 2)
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
                    'data' => null
                ];
            }
        }

        if ($isLabMaintenance) {
            foreach ($timetable as $time => &$daysRow) {
                foreach ($days as $day) {
                    $daysRow[$day]['type'] = 'maintenance';
                    $daysRow[$day]['data'] = 'Lab Closed for Maintenance';
                }
            }
        } else {
            foreach ($schedules as $sched) {
                $day = $sched->day_of_week;
                $start = substr($sched->start_time, 0, 5);
                $end = substr($sched->end_time, 0, 5);
                
                try {
                    // Check if schedule falls within semester date range
                    $scheduleSemester = $sched->semester;
                    $semesterStart = $scheduleSemester ? \Carbon\Carbon::parse($scheduleSemester->start_date) : null;
                    $semesterEnd = $scheduleSemester ? \Carbon\Carbon::parse($scheduleSemester->end_date) : null;
                    
                    // Find the actual date for this day of week in the current week
                    $dayIndex = array_search($day, $days);
                    if ($dayIndex === false) continue;
                    
                    $scheduleDate = $startOfWeek->copy()->addDays($dayIndex);
                    
                    // Check if the schedule date is within the semester date range
                    if ($semesterStart && $semesterEnd) {
                        if ($scheduleDate < $semesterStart || $scheduleDate > $semesterEnd) {
                            continue; // Skip this schedule as it's outside the semester range
                        }
                    }
                    
                    $startCarbon = \Carbon\Carbon::createFromFormat('H:i', $start);
                    $endCarbon = \Carbon\Carbon::createFromFormat('H:i', $end);
                    $blocks = max(1, $startCarbon->diffInMinutes($endCarbon) / 60);
                    
                    if (isset($timetable[$start][$day])) {
                        $timetable[$start][$day]['type'] = 'enroll';
                        $timetable[$start][$day]['rowspan'] = $blocks;
                        $timetable[$start][$day]['data'] = $sched;
                        
                        for ($i = 1; $i < $blocks; $i++) {
                            $nextTime = $startCarbon->copy()->addMinutes(60 * $i)->format('H:i');
                            if (isset($timetable[$nextTime][$day])) {
                                $timetable[$nextTime][$day]['type'] = 'skip';
                            }
                        }
                    }
                } catch (\Exception $e) { }
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
                        $isMaintenance = stripos($booking->purpose, 'maintenance') !== false || stripos($booking->purpose, 'close') !== false;
                        $timetable[$start][$day]['type'] = $isMaintenance ? 'maintenance' : 'booking';
                        $timetable[$start][$day]['rowspan'] = $blocks;
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
        }

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

        $prevWeek = $prevWeekDate->format('Y-m-d') . $params;
        $nextWeek = $nextWeekDate->format('Y-m-d') . $params;
        
        $prevMonth = $currentDate->copy()->subMonth()->format('Y-m-d') . $params;
        $nextMonth = $currentDate->copy()->addMonth()->format('Y-m-d') . $params;

        $selectedLab = $selectedLabId ? \App\Models\Laboratory::find($selectedLabId) : null;
        $selectedLecturer = $selectedLecturerId ? \App\Models\User::find($selectedLecturerId) : null;

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
        $laboratories = Laboratory::all();
        $courses = Course::all();
        $semesters = Semester::all();
        return view('admin.schedules.create', compact('laboratories', 'courses', 'semesters'));
    }

    protected function hasScheduleConflict(int $labId, int $semesterId, string $date, string $newStartTime, string $newEndTime, ?int $excludeScheduleId = null): bool
{
    // 1. 取得目標日期的星期幾 (例如 'Monday')
    $dayOfWeek = \Carbon\Carbon::parse($date)->format('l');

    // 2. 建立防撞查詢：我們只關心「同一間實驗室」且「同一個學期」的情況
    $query = \App\Models\Schedule::where('lab_id', $labId)
        ->where('semester_id', $semesterId);

    // 3. 如果是 Edit（編輯模式），要把自己排除掉，否則自己會跟自己撞期
    if ($excludeScheduleId) {
        $query->where('id', '!=', $excludeScheduleId);
    }

    // 4. 【核心混合模型防撞核心】：
    // 只要滿足以下任一時間重疊條件，就代表衝突了：
    $query->where(function ($q) use ($dayOfWeek, $date) {
        $q->where(function ($sub) use ($dayOfWeek) {
            // 狀況 A：它是一堂每週重複的課，且星期幾跟目標日期相同
            $sub->where('is_recurring', true)
                ->where('day_of_week', $dayOfWeek);
        })->orWhere(function ($sub) use ($date) {
            // 狀況 B：它是一堂單次事件/課，且日期跟目標日期完全一模一樣
            $sub->where('is_recurring', false)
                ->where('date', $date);
        });
    });

    // 5. 拿出所有可能撞期的課
    $schedules = $query->get();

    // 6. 跑時間區間的數學重疊判定： (StartA < EndB) AND (EndA > StartB)
    return $schedules->contains(function ($schedule) use ($newStartTime, $newEndTime) {
        return $schedule->start_time < $newEndTime && $schedule->end_time > $newStartTime;
    });
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

        $schedules = Schedule::where('lab_id', $request->input('lab_id'))
            ->where('semester_id', $request->input('semester_id'))
            ->when($request->filled('exclude_schedule_id'), function ($query) use ($request) {
                $query->where('id', '!=', $request->input('exclude_schedule_id'));
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

        return response()->json($schedules->map(function ($schedule) {
            return [
                'id' => $schedule->id,
                'is_recurring' => (bool) $schedule->is_recurring,
                'start_time' => substr($schedule->start_time, 0, 5),
                'end_time' => substr($schedule->end_time, 0, 5),
            ];
        }));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'semester_id' => 'required|exists:semesters,id',
            'lab_id' => 'required|exists:laboratories,id',
            'course_id' => 'required|exists:courses,id',
            'date' => 'required|date',
            'start_time' => ['required', 'regex:/^(0[8-9]|1[0-8]):00$/'],
            'is_recurring' => 'required|boolean',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($validator->errors()->isEmpty()) {
                $semester = Semester::find($request->input('semester_id'));
                $course = Course::find($request->input('course_id'));
                $hours = max(1, (int) ($course?->hours ?? 1));
                $date = $request->input('date');

                if ($date < $semester->start_date || $date > $semester->end_date) {
                    $validator->errors()->add(
                        'date',
                        "The selected date ({$date}) must fall within the chosen semester period ({$semester->start_date} to {$semester->end_date})."
                    );
                    return;
                }

                $startTimeStr = $request->input('start_time');
                if (strlen($startTimeStr) == 5) { $startTimeStr .= ':00'; }
                $start = \Carbon\Carbon::createFromFormat('H:i:s', $startTimeStr);
                $end = $start->copy()->addHours($hours);

                $latestStart = \Carbon\Carbon::createFromFormat('H:i:s', sprintf('%02d:00:00', max(8, 18 - $hours)));
                if ($start->gt($latestStart)) {
                    $validator->errors()->add(
                        'start_time',
                        "A {$hours}-hour course must start by {$latestStart->format('H:i')} so it ends by 18:00."
                    );
                    return;
                }

                $labId = $request->input('lab_id');
                $newStartTime = $start->format('H:i:s');
                $newEndTime = $end->format('H:i:s');
                $dayOfWeek = \Carbon\Carbon::parse($date)->format('l');

                if ($this->hasScheduleConflict($labId, $semester->id, $date, $newStartTime, $newEndTime)) {
                    $validator->errors()->add(
                        'start_time',
                        "Time slot conflict! This laboratory already has a recurring or one-time schedule during this period ({$start->format('H:i')} - {$end->format('H:i')})."
                    );
                }
            }
        });

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        $data['is_recurring'] = $request->boolean('is_recurring', true);
        $data['day_of_week'] = \Carbon\Carbon::parse($data['date'])->format('l');

        if (isset($data['start_time']) && strlen($data['start_time']) == 5) {
            $data['start_time'] .= ':00';
        }

        $course = Course::find($data['course_id']);
        $hours = max(1, (int) ($course?->hours ?? 1));
        $data['end_time'] = \Carbon\Carbon::createFromFormat('H:i:s', $data['start_time'])
            ->copy()
            ->addHours($hours)
            ->format('H:i:s');

        $schedule = Schedule::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Schedule added successfully',
                'schedule' => $schedule
            ]);
        }

        $redirectParams = [];
        if ($request->filled('return_date')) { $redirectParams['date'] = $request->input('return_date'); }
        if ($request->filled('return_lab_id')) { $redirectParams['lab_id'] = $request->input('return_lab_id'); }
        if ($request->filled('return_semester_id')) { $redirectParams['semester_id'] = $request->input('return_semester_id'); }

        $url = empty($redirectParams) ? '/admin/schedules' : '/admin/schedules?' . http_build_query($redirectParams);

        return redirect()->to(url($url))->with('status', 'Schedule added successfully.');
    }

    public function edit(Schedule $schedule)
    {
        $laboratories = Laboratory::all();
        $courses = Course::all();
        $semesters = Semester::all();
        return view('admin.schedules.edit', compact('schedule', 'laboratories', 'courses', 'semesters'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        $validator = Validator::make($request->all(), [
            'semester_id' => 'required|exists:semesters,id',
            'lab_id' => 'required|exists:laboratories,id',
            'course_id' => 'required|exists:courses,id',
            'date' => 'required|date',
            'start_time' => ['required', 'regex:/^(0[8-9]|1[0-8]):00$/'],
            'is_recurring' => 'required|boolean',
        ]);

        $validator->after(function ($validator) use ($request, $schedule) {
            if ($validator->errors()->isEmpty()) {
                $course = Course::find($request->input('course_id'));
                $hours = max(1, (int) ($course?->hours ?? 1));
                $semester = Semester::find($request->input('semester_id'));
                $date = $request->input('date');

                if ($date < $semester->start_date || $date > $semester->end_date) {
                    $validator->errors()->add(
                        'date',
                        "The selected date ({$date}) must fall within the chosen semester period ({$semester->start_date} to {$semester->end_date})."
                    );
                    return;
                }

                $startTimeStr = $request->input('start_time');
                if (strlen($startTimeStr) == 5) { $startTimeStr .= ':00'; }
                $start = \Carbon\Carbon::createFromFormat('H:i:s', $startTimeStr);
                $end = $start->copy()->addHours($hours);

                $latestStart = \Carbon\Carbon::createFromFormat('H:i:s', sprintf('%02d:00:00', max(8, 18 - $hours)));
                if ($start->gt($latestStart)) {
                    $validator->errors()->add(
                        'start_time',
                        "A {$hours}-hour course must start by {$latestStart->format('H:i')} so it ends by 18:00."
                    );
                    return;
                }

                $newStartTime = $start->format('H:i:s');
                $newEndTime = $end->format('H:i:s');
                $labId = $request->input('lab_id');

                if ($this->hasScheduleConflict($labId, $semester->id, $date, $newStartTime, $newEndTime, $schedule->id)) {
                    $validator->errors()->add(
                        'start_time',
                        "Time slot conflict! This laboratory already has a recurring or one-time schedule during this period ({$start->format('H:i')} - {$end->format('H:i')})."
                    );
                }
            }
        });

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        $data['is_recurring'] = $request->boolean('is_recurring', true);
        $data['day_of_week'] = \Carbon\Carbon::parse($data['date'])->format('l');

        if (isset($data['start_time']) && strlen($data['start_time']) == 5) {
            $data['start_time'] .= ':00';
        }

        $course = Course::find($data['course_id']);
        $hours = max(1, (int) ($course?->hours ?? 1));
        $data['end_time'] = \Carbon\Carbon::createFromFormat('H:i:s', $data['start_time'])
            ->copy()
            ->addHours($hours)
            ->format('H:i:s');

        $schedule->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Schedule updated successfully',
                'schedule' => $schedule
            ]);
        }

        $redirectParams = [];
        if ($request->filled('return_date')) { $redirectParams['date'] = $request->input('return_date'); }
        if ($request->filled('return_lab_id')) { $redirectParams['lab_id'] = $request->input('return_lab_id'); }
        if ($request->filled('return_semester_id')) { $redirectParams['semester_id'] = $request->input('return_semester_id'); }

        return redirect()->to(url('/admin/schedules?' . http_build_query($redirectParams)))
                         ->with('status', 'Schedule updated successfully.');
    }

    public function destroy(Request $request, Schedule $schedule)
    {
        $schedule->delete();

        $queryParams = $request->query();
        return redirect()->to(url('/admin/schedules?' . http_build_query($queryParams)))
                         ->with('status', 'Schedule deleted successfully.');
    }
}
