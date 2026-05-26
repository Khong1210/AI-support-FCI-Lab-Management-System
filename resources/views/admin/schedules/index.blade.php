@extends('layouts.admin')

@section('title', 'Schedule Management')
@section('page-title', 'Schedule Management')
@section('breadcrumb', 'Schedules')

@push('styles')
<style>
    .calendar-table th, .calendar-table td {
        border: 1px solid #dee2e6;
        text-align: center;
        vertical-align: top;
        height: 76px;
    }
    .calendar-table th {
        background-color: #f4f6f9;
        font-weight: bold;
        min-width: 140px;
    }
    .time-col {
        width: 90px;
        min-width: 90px !important;
        background-color: #f4f6f9;
        font-weight: bold;
        text-align: center !important;
        vertical-align: middle !important;
    }
    .schedule-block {
        background-color: #007bff;
        color: white;
        border-radius: 4px;
        padding: 8px;
        font-size: 0.82rem;
        margin: 2px;
        position: relative;
        text-align: left;
        line-height: 1.35;
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
    
    .schedule-block.enroll {
        background-color: #007bff; /* Blue */
    }
    .schedule-block.booking {
        background-color: #28a745; /* Green */
    }
    .schedule-block.maintenance {
        background-color: #dc3545; /* Red */
    }
    .schedule-block.none {
        background-color: #f8f9fa; /* Grey */
        color: #adb5bd;
        border: 1px dashed #dee2e6;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        min-height: 40px;
    }
    .schedule-block.h-100 {
        height: 100%;
        min-height: 40px;
    }
    .schedule-program {
        display: block;
        font-weight: 700;
        margin-bottom: 4px;
    }
    .schedule-meta {
        display: block;
        font-size: 0.76rem;
        opacity: 0.95;
    }
    .schedule-panel-small .card-body {
        padding: 0.75rem;
    }
    .schedule-panel-small .card-header {
        padding: 0.75rem;
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
    .nav-link.disabled {
        color: #ccc !important;
        pointer-events: none;
        cursor: not-allowed;
        opacity: 0.65;
    }
</style>
@endpush

@section('content')
<div class="card card-outline card-info mb-3">
    <div class="card-body py-2">
        <form action="{{ url('/admin/schedules') }}" method="GET" class="form-inline w-100">
            <input type="hidden" name="date" value="{{ $currentDate->format('Y-m-d') }}">
            <label class="mr-2 mb-0" for="lab_id"><i class="fas fa-door-open mr-1"></i> Laboratory</label>
            <select name="lab_id" id="lab_id" class="form-control form-control-sm mr-4" onchange="this.form.submit()">
                @foreach($laboratories as $lab)
                    <option value="{{ $lab->id }}" {{ $selectedLabId == $lab->id ? 'selected' : '' }}>
                        {{ $lab->lab_name }}
                    </option>
                @endforeach
            </select>

            <label class="mr-2 mb-0" for="semester_id"><i class="fas fa-filter mr-1"></i> Semester (Trimester)</label>
            <select name="semester_id" id="semester_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">All Semesters</option>
                @foreach($semesters as $semester)
                    <option value="{{ $semester->id }}" {{ $selectedSemesterId == $semester->id ? 'selected' : '' }}>
                        {{ $semester->name }}
                    </option>
                @endforeach
            </select>
            @if($selectedSemesterId && $selectedSemester)
                <span class="text-muted small ml-2">
                    <i class="fas fa-calendar mr-1"></i>
                    <strong>From:</strong> {{ $semesterStartDate->format('M d, Y') }} 
                    <strong>To:</strong> {{ $semesterEndDate->format('M d, Y') }}
                </span>
                <a href="{{ url('/admin/schedules?date=' . $currentDate->format('Y-m-d') . ($selectedLabId ? '&lab_id=' . $selectedLabId : '')) }}" class="btn btn-sm btn-secondary ml-2">Clear Filter</a>
            @endif
        </form>
    </div>
</div>

<div class="card card-primary">
    <div class="card-header d-flex p-0">
        <h3 class="card-title p-3">
            Weekly Schedule
            <small class="d-block">{{ $selectedLab->lab_name ?? 'No room selected' }}</small>
        </h3>
        <ul class="nav nav-pills ml-auto p-2">
            <li class="nav-item"><a class="nav-link" href="{{ url('/admin/schedules/add') }}"><i class="fas fa-plus"></i> Add Schedule</a></li>
            @if($selectedSemesterId && !($canGoPrevWeek ?? true))
                <li class="nav-item"><a class="nav-link disabled" href="#"><i class="fas fa-chevron-left"></i> Prev Week</a></li>
            @else
                <li class="nav-item"><a class="nav-link" href="{{ url('/admin/schedules?date=' . $prevWeek) }}"><i class="fas fa-chevron-left"></i> Prev Week</a></li>
            @endif
            <li class="nav-item"><a class="nav-link active" href="{{ url('/admin/schedules' . ($selectedSemesterId || $selectedLabId ? '?' . http_build_query(array_filter(['semester_id' => $selectedSemesterId, 'lab_id' => $selectedLabId])) : '')) }}">Current Week</a></li>
            @if($selectedSemesterId && !($canGoNextWeek ?? true))
                <li class="nav-item"><a class="nav-link disabled" href="#">Next Week <i class="fas fa-chevron-right"></i></a></li>
            @else
                <li class="nav-item"><a class="nav-link" href="{{ url('/admin/schedules?date=' . $nextWeek) }}">Next Week <i class="fas fa-chevron-right"></i></a></li>
            @endif
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
                @foreach($timetable as $timeSlot => $daysRow)
                    <tr>
                        <td class="time-col">{{ $timeSlot }}</td>
                        @foreach($days as $day)
                            @php $slot = $daysRow[$day]; @endphp
                            @if($slot['type'] === 'skip')
                                @continue
                            @endif

                            <td class="{{ isset($weekDates[$day]) && $weekDates[$day]['is_today'] ? 'bg-light' : '' }} p-1" rowspan="{{ $slot['rowspan'] }}">
                                @if($slot['type'] === 'none')
                                    <div class="schedule-block none">
                                        <span>-</span>
                                    </div>
                                @elseif($slot['type'] === 'maintenance')
                                    @php
                                        $maintenanceStart = is_string($slot['data']) ? $timeSlot : substr($slot['data']->start_time, 0, 5);
                                        $maintenanceEnd = is_string($slot['data']) ? '' : substr($slot['data']->end_time, 0, 5);
                                    @endphp
                                    <div class="schedule-block maintenance h-100">
                                        <span class="schedule-program">Maintenance / Closed</span>
                                        <span class="schedule-meta"><i class="fas fa-clock mr-1"></i>{{ $maintenanceEnd ? $maintenanceStart . ' - ' . $maintenanceEnd : $maintenanceStart }}</span>
                                        <span class="schedule-meta"><i class="fas fa-door-open mr-1"></i>{{ $selectedLab->lab_name ?? 'Room' }}</span>
                                    </div>
                                @elseif($slot['type'] === 'booking')
                                    <div class="schedule-block booking h-100">
                                        <span class="schedule-program">{{ $slot['data']->purpose }}</span>
                                        <span class="schedule-meta"><i class="fas fa-clock mr-1"></i>{{ substr($slot['data']->start_time, 0, 5) }} - {{ substr($slot['data']->end_time, 0, 5) }}</span>
                                        <span class="schedule-meta"><i class="fas fa-door-open mr-1"></i>{{ $selectedLab->lab_name ?? 'Room' }}</span>
                                    </div>
                                @elseif($slot['type'] === 'enroll')
                                    <div class="schedule-block enroll h-100">
                                        <span class="schedule-program">{{ $slot['data']->course->course_name ?? 'Program' }}</span>
                                        <span class="schedule-meta"><i class="fas fa-clock mr-1"></i>{{ substr($slot['data']->start_time, 0, 5) }} - {{ substr($slot['data']->end_time, 0, 5) }}</span>
                                        <span class="schedule-meta"><i class="fas fa-door-open mr-1"></i>{{ $slot['data']->laboratory->lab_name ?? ($selectedLab->lab_name ?? 'Room') }}</span>
                                        <span class="badge badge-light mt-1">{{ $slot['data']->semester->name ?? 'No Semester' }}</span>
                                        <div class="schedule-actions">
                                            <a href="{{ url('/admin/schedules/' . $slot['data']->id . '/edit') }}" class="text-white mx-1"><i class="fas fa-edit"></i></a>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline schedule-panel-small">
            <div class="card-header border-0">
                <h3 class="card-title w-100 d-flex justify-content-between align-items-center">
                    @php
                        $querySuffix = http_build_query(array_filter([
                            'semester_id' => $selectedSemesterId,
                            'lab_id' => $selectedLabId,
                        ]));
                        $qs = $querySuffix ? '&' . $querySuffix : '';
                    @endphp
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
                                        <a href="{{ url('/admin/schedules?date=' . $day['date'] . $qs) }}">{{ $day['day'] }}</a>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card card-secondary card-outline schedule-panel-small">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Current Selection</h3>
            </div>
            <div class="card-body">
                <div class="text-muted small">
                    <strong>Current lab:</strong><br>
                    {{ $selectedLab->lab_name ?? 'None' }}<br>
                    <strong>Semester/Trimester:</strong><br>
                    @if($selectedSemesterId && $selectedSemester)
                        {{ $selectedSemester->name }}<br>
                        <i class="fas fa-calendar mr-1"></i>
                        <strong>Start:</strong> {{ $semesterStartDate->format('M d, Y') }}<br>
                        <i class="fas fa-calendar mr-1"></i>
                        <strong>End:</strong> {{ $semesterEndDate->format('M d, Y') }}
                    @else
                        All Semesters
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-secondary card-outline schedule-panel-small">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Legend</h3>
            </div>
            <div class="card-body">
                <h6>Legend</h6>
                <ul class="list-unstyled mb-0">
                    <li><span class="badge badge-primary">&nbsp;</span> Scheduled Class</li>
                    <li><span class="badge badge-success">&nbsp;</span> Booking</li>
                    <li><span class="badge badge-danger">&nbsp;</span> Maintenance</li>
                    <li><span class="badge badge-light text-muted border">&nbsp;</span> Empty slot</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
