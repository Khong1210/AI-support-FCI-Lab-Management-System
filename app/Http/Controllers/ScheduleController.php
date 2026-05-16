<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Laboratory;
use App\Models\Course;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $schedules = Schedule::with(['laboratory', 'course'])->get();
        
        // Setup current week logic
        $currentDate = $request->input('date') ? \Carbon\Carbon::parse($request->input('date')) : \Carbon\Carbon::now();
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

        // Setup monthly calendar logic
        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();
        $calendarDays = [];
        
        // Pad beginning of month
        $startPad = $startOfMonth->dayOfWeekIso - 1; // 1 for Monday, 7 for Sunday -> 0-6 padding
        for ($i = $startPad; $i > 0; $i--) {
            $calendarDays[] = [
                'day' => $startOfMonth->copy()->subDays($i)->day,
                'is_current_month' => false,
                'is_today' => false,
                'is_selected_week' => false,
                'date' => $startOfMonth->copy()->subDays($i)->format('Y-m-d')
            ];
        }
        
        // Current month days
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
        
        // Pad end of month
        $endPad = 42 - count($calendarDays); // 6 rows * 7 days = 42
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
        $prevMonth = $currentDate->copy()->subMonth()->format('Y-m-d');
        $nextMonth = $currentDate->copy()->addMonth()->format('Y-m-d');
        $prevWeek = $currentDate->copy()->subWeek()->format('Y-m-d');
        $nextWeek = $currentDate->copy()->addWeek()->format('Y-m-d');

        return view('admin.schedules.index', compact(
            'schedules', 
            'weekDates', 
            'calendarDays', 
            'monthName', 
            'currentDate',
            'prevMonth',
            'nextMonth',
            'prevWeek',
            'nextWeek'
        ));
    }

    public function create()
    {
        $laboratories = Laboratory::all();
        $courses = Course::all();
        return view('admin.schedules.create', compact('laboratories', 'courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'lab_id' => 'required|exists:laboratories,id',
            'course_id' => 'required|exists:courses,id',
            'day_of_week' => 'required|string',
            'date' => 'required|date',
            'start_time' => ['required', 'regex:/^(0[8-9]|1[0-9]):(00|30)$|^20:00$/'],
            'end_time' => ['required', 'regex:/^(0[8-9]|1[0-9]):(00|30)$|^20:00$/', 'after:start_time'],
        ]);

        Schedule::create($request->all());

        return redirect('/admin/schedules')->with('status', 'Schedule created successfully.');
    }

    public function edit(Schedule $schedule)
    {
        $laboratories = Laboratory::all();
        $courses = Course::all();
        return view('admin.schedules.edit', compact('schedule', 'laboratories', 'courses'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        $request->validate([
            'lab_id' => 'required|exists:laboratories,id',
            'course_id' => 'required|exists:courses,id',
            'day_of_week' => 'required|string',
            'date' => 'required|date',
            'start_time' => ['required', 'regex:/^(0[8-9]|1[0-9]):(00|30)$|^20:00$/'],
            'end_time' => ['required', 'regex:/^(0[8-9]|1[0-9]):(00|30)$|^20:00$/', 'after:start_time'],
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
