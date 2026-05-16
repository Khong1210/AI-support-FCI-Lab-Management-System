@extends('layouts.admin')

@section('title', 'Schedule Management')
@section('page-title', 'Schedule Management')
@section('breadcrumb', 'Schedules')

@push('styles')
<style>
    .calendar-table th, .calendar-table td {
        border: 1px solid #dee2e6;
        text-align: center;
        vertical-align: middle;
        height: 60px;
    }
    .calendar-table th {
        background-color: #f4f6f9;
        font-weight: bold;
    }
    .time-col {
        width: 100px;
        background-color: #f4f6f9;
        font-weight: bold;
    }
    .schedule-block {
        background-color: #007bff;
        color: white;
        border-radius: 4px;
        padding: 4px;
        font-size: 0.85rem;
        margin: 2px;
        position: relative;
    }
    .schedule-actions {
        display: none;
        position: absolute;
        top: 2px;
        right: 2px;
    }
    .schedule-block:hover .schedule-actions {
        display: block;
    }
    
    /* Mini Calendar */
    .mini-calendar {
        width: 100%;
        text-align: center;
        font-size: 0.9rem;
    }
    .mini-calendar th {
        font-weight: bold;
        color: #495057;
        padding: 5px;
    }
    .mini-calendar td {
        padding: 5px;
        cursor: pointer;
    }
    .mini-calendar .text-muted {
        color: #adb5bd !important;
    }
    .mini-calendar td a {
        color: inherit;
        text-decoration: none;
        display: block;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        line-height: 25px;
        margin: 0 auto;
    }
    .mini-calendar td a:hover {
        background-color: #e9ecef;
    }
    .mini-calendar .today a {
        background-color: #28a745;
        color: white;
    }
    .mini-calendar .selected-week a {
        background-color: #007bff;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-3">
        <!-- Monthly Calendar Card -->
        <div class="card card-primary card-outline">
            <div class="card-header border-0">
                <h3 class="card-title w-100 d-flex justify-content-between align-items-center">
                    <a href="{{ url('/admin/schedules?date=' . $prevMonth) }}" class="btn btn-sm btn-link"><i class="fas fa-chevron-left"></i></a>
                    <strong>{{ $monthName }}</strong>
                    <a href="{{ url('/admin/schedules?date=' . $nextMonth) }}" class="btn btn-sm btn-link"><i class="fas fa-chevron-right"></i></a>
                </h3>
            </div>
            <div class="card-body p-2">
                <table class="mini-calendar">
                    <thead>
                        <tr>
                            <th>Mo</th>
                            <th>Tu</th>
                            <th>We</th>
                            <th>Th</th>
                            <th>Fr</th>
                            <th>Sa</th>
                            <th>Su</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(array_chunk($calendarDays, 7) as $week)
                            <tr>
                                @foreach($week as $day)
                                    <td class="{{ !$day['is_current_month'] ? 'text-muted' : '' }} {{ $day['is_today'] ? 'today' : '' }} {{ $day['is_selected_week'] && !$day['is_today'] ? 'selected-week' : '' }}">
                                        <a href="{{ url('/admin/schedules?date=' . $day['date']) }}">{{ $day['day'] }}</a>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Quick Actions</h3>
            </div>
            <div class="card-body">
                <a href="{{ url('/admin/schedules/create') }}" class="btn btn-primary btn-block mb-3">
                    <i class="fas fa-plus mr-1"></i> Add Schedule
                </a>
                <p class="text-muted">Manage schedules globally across all weeks.</p>
                <hr>
                <h5>All Schedules</h5>
                <ul class="list-unstyled">
                    @forelse($schedules as $schedule)
                        <li class="mb-3 border-bottom pb-2">
                            <i class="fas fa-clock text-primary"></i> 
                            <strong>{{ $schedule->course->course_name ?? 'Course' }}</strong> - {{ $schedule->laboratory->lab_name ?? 'Lab' }}<br>
                            <small class="text-muted">{{ $schedule->day_of_week }}, {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }}</small>
                            <div class="mt-1">
                                <a href="{{ url('/admin/schedules/' . $schedule->id . '/edit') }}" class="text-info"><i class="fas fa-edit"></i> Edit</a> | 
                                <form action="{{ url('/admin/schedules/' . $schedule->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this schedule?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link text-danger p-0 m-0 align-baseline"><i class="fas fa-trash"></i> Delete</button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="text-muted">No schedules found.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="card card-primary">
            <div class="card-header d-flex p-0">
                <h3 class="card-title p-3">Weekly Timetable</h3>
                <ul class="nav nav-pills ml-auto p-2">
                    <li class="nav-item"><a class="nav-link" href="{{ url('/admin/schedules?date=' . $prevWeek) }}"><i class="fas fa-chevron-left"></i> Prev Week</a></li>
                    <li class="nav-item"><a class="nav-link active" href="{{ url('/admin/schedules') }}">Current Week</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/admin/schedules?date=' . $nextWeek) }}">Next Week <i class="fas fa-chevron-right"></i></a></li>
                </ul>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table calendar-table m-0">
                    <thead>
                        <tr>
                            <th class="time-col">Time</th>
                            @php
                                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            @endphp
                            @foreach($days as $day)
                                <th class="{{ isset($weekDates[$day]) && $weekDates[$day]['is_today'] ? 'bg-primary text-white' : '' }}">
                                    {{ $day }}<br>
                                    <small>{{ $weekDates[$day]['label'] ?? '' }}</small>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @for($hour = 8; $hour <= 20; $hour++)
                            <tr>
                                <td class="time-col">{{ sprintf('%02d:00', $hour) }}</td>
                                @foreach($days as $day)
                                    <td class="{{ isset($weekDates[$day]) && $weekDates[$day]['is_today'] ? 'bg-light' : '' }}">
                                        @foreach($schedules as $schedule)
                                            @php
                                                $startHour = (int)substr($schedule->start_time, 0, 2);
                                                $endHour = (int)substr($schedule->end_time, 0, 2);
                                                $endMin = (int)substr($schedule->end_time, 3, 2);
                                                if($endMin > 0) $endHour++; // to cover partial hours
                                                $isInHour = $schedule->day_of_week === $day && $startHour <= $hour && $endHour > $hour;
                                            @endphp
                                            @if($isInHour)
                                                <div class="schedule-block">
                                                    <strong>{{ $schedule->course->course_name ?? 'Course' }}</strong><br>
                                                    <small>{{ $schedule->laboratory->lab_name ?? 'Lab' }}</small>
                                                    <div class="schedule-actions">
                                                        <a href="{{ url('/admin/schedules/' . $schedule->id . '/edit') }}" class="text-white mx-1"><i class="fas fa-edit"></i></a>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </td>
                                @endforeach
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
