<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Laboratory;
use App\Models\Course;
use App\Models\Semester;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $laboratories = Laboratory::all();
        $selectedLabId = $request->input('lab_id', $laboratories->first()->id ?? null);
        $selectedLab = $laboratories->where('id', $selectedLabId)->first();
        $isLabMaintenance = $selectedLab && $selectedLab->status == 0;

        $semesters = Semester::all();
        $selectedSemesterId = $request->input('semester_id');
        $selectedSemester = null;
        $semesterStartDate = null;
        $semesterEndDate = null;

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

        $monthName = $currentDate->format('F Y');
        $params = ($selectedSemesterId ? '&semester_id=' . $selectedSemesterId : '') . ($selectedLabId ? '&lab_id=' . $selectedLabId : '');
        
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
        
        $prevWeek = $prevWeekDate->format('Y-m-d') . $params;
        $nextWeek = $nextWeekDate->format('Y-m-d') . $params;
        
        $prevMonth = $currentDate->copy()->subMonth()->format('Y-m-d') . $params;
        $nextMonth = $currentDate->copy()->addMonth()->format('Y-m-d') . $params;

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
            'isLabMaintenance'
        ));
    }

    public function create()
    {
        $laboratories = Laboratory::all();
        $courses = Course::all();
        $semesters = Semester::all();
        return view('admin.schedules.create', compact('laboratories', 'courses', 'semesters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'lab_id' => 'required|exists:laboratories,id',
            'course_id' => 'required|exists:courses,id',
            'day_of_week' => 'required|string',
            'date' => 'required|date',
            'start_time' => ['required', 'regex:/^(0[8-9]|1[0-9]|20):00$/'],
            'end_time' => ['required', 'regex:/^(0[8-9]|1[0-9]|20):00$/', 'after:start_time'],
        ]);

        Schedule::create($request->all());

        return redirect('/admin/schedules')->with('status', 'Schedule created successfully.');
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
        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'lab_id' => 'required|exists:laboratories,id',
            'course_id' => 'required|exists:courses,id',
            'day_of_week' => 'required|string',
            'date' => 'required|date',
            'start_time' => ['required', 'regex:/^(0[8-9]|1[0-9]|20):00$/'],
            'end_time' => ['required', 'regex:/^(0[8-9]|1[0-9]|20):00$/', 'after:start_time'],
        ]);

        // We only take the first 5 chars if it has seconds.
        $data = $request->all();
        if (strlen($data['start_time']) == 5) { $data['start_time'] .= ':00'; }
        if (strlen($data['end_time']) == 5) { $data['end_time'] .= ':00'; }

        $schedule->update($data);

        return redirect('/admin/schedules')->with('status', 'Schedule updated successfully.');
    }

    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return redirect('/admin/schedules')->with('status', 'Schedule deleted successfully.');
    }
}
