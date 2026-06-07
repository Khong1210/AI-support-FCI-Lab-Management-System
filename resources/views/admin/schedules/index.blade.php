@extends('layouts.admin')

@section('title', 'Schedule Management')
@section('page-title', 'Schedule Management')
@section('breadcrumb', 'Schedules')

@push('styles')
<style>
    /* Compact schedule table styles inspired by the prototype */
    .schedule-matrix {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .schedule-matrix th, 
    .schedule-matrix td {
        border: 1px solid #dee2e6;
        padding: 0px;
        text-align: left;
        vertical-align: top;
    }
    .schedule-matrix th {
        background-color: #f4f6f9;
        font-weight: 600;
        text-align: center;
        height: 28px;
        font-size: 0.8rem;
    }
    .schedule-matrix td {
        height: 40px;
        background-color: #ffffff;
    }
    .schedule-matrix .time-col {
        background-color: #f4f6f9;
        font-weight: bold;
        text-align: center;
        vertical-align: middle;
        width: 75px;
        font-size: 0.75rem;
    }
    
    .schedule-block {
        border: 1px solid #dee2e6;
        padding: 6px 8px;
        margin-bottom: 0px;
        font-size: 0.7rem;
        line-height: 1.25;
        display: flex;
        flex-direction: column;
        min-height: 100%; 
        height: 100%;
        position: relative;
        overflow: hidden;
    }
    .schedule-block strong {
        display: block;
        color: #111;
        font-size: 0.75rem;
        margin-bottom: 1px;
        overflow: hidden;
        text-overflow: ellipsis;
        
    }
    .schedule-block span {
        display: block;
        color: #555;
        font-size: 0.65rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .schedule-block.enroll {
        background-color: #f0f7ff;
        border-left: 3px solid #007bff;
    }
    .schedule-block.booking {
        background-color: #f0fff4;
        border-left: 3px solid #28a745;
    }
    .schedule-block.maintenance {
        background-color: #fff5f5;
        border-left: 3px solid #dc3545;
    }
    .schedule-block.none {
        background-color: transparent;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #adb5bd;
    }
    
    .schedule-actions {
        display: none;
        position: absolute;
        top: 4px;
        right: 4px;
    }
    .schedule-block:hover .schedule-actions {
        display: block;
    }
    
    /* Calendar Grid for right column */
    .mini-calendar {
        width: 100%;
        text-align: center;
        border-collapse: collapse;
    }
    .mini-calendar th {
        font-weight: 600;
        color: #495057;
        padding: 5px;
        background-color: #f4f6f9;
        border: 1px solid #dee2e6;
    }
    .mini-calendar td {
        padding: 4px;
        border: 1px solid #dee2e6;
    }
    .mini-calendar .text-muted {
        color: #adb5bd !important;
        background-color: #fafafa;
    }
    .mini-calendar td a {
        color: inherit;
        text-decoration: none;
        display: block;
        width: 100%;
        height: 100%;
    }
    .mini-calendar td:hover {
        background-color: #e9ecef;
    }
    .mini-calendar .today {
        background-color: #e8f0fe;
        font-weight: bold;
    }
    .mini-calendar .selected-week {
        background-color: #d1e7dd;
    }
    .nav-link.disabled {
        color: #ccc !important;
        pointer-events: none;
    }
    
    .filter-bar {
        background-color: transparent;
        border: none;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 20px;
        border-radius: 0;
             background-color: #f4f6f9;
        border: 1px solid #dee2e6;
        padding: 10px 15px;
    }
    
    .filter-bar .form-control {
        height: 36px !important;
        line-height: 1.5 !important;
        padding: 6px 12px !important;
        font-size: 0.87rem !important;
    }

    .filter-bar .btn-filter {
        height: 36px;
        padding: 6px 16px;
        font-size: 0.87rem;
    }
</style>
@endpush

@section('content')
<div class="filter-bar">
    <form action="{{ url('/admin/schedules') }}" method="GET" class="d-flex align-items-center w-100 m-0">
        <input type="hidden" name="date" value="{{ $currentDate->format('Y-m-d') }}">
        
        <div class="d-flex align-items-center mr-4">
            <label class="mr-2 mb-0 font-weight-bold text-nowrap" for="semester_id">Semester:</label>
            <select name="semester_id" id="semester_id" class="form-control form-control-sm" style="width: auto;" onchange="this.form.submit()">
                <option value="">All Semesters</option>
                @foreach($semesters as $semester)
                    <option value="{{ $semester->id }}" {{ $selectedSemesterId == $semester->id ? 'selected' : '' }}>
                        {{ $semester->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="d-flex align-items-center">
            <label class="mr-2 mb-0 font-weight-bold text-nowrap" for="view_target">Schedule View:</label>
            <select name="view_target" id="view_target" class="form-control form-control-sm" style="width: auto;" onchange="this.form.submit()">
                <option value="">-- Select Lab or Lecturer --</option>
                
                <optgroup label="Laboratories">
                    @foreach($laboratories as $lab)
                        <option value="lab_{{ $lab->id }}" {{ ($selectedLabId && $selectedLabId == $lab->id) ? 'selected' : '' }}>
                             {{ $lab->lab_name }}
                        </option>
                    @endforeach
                </optgroup>

                <optgroup label="Lecturers">
                    @foreach($lecturers as $lecturer)
                        <option value="lec_{{ $lecturer->id }}" {{ (($selectedLecturerId ?? null) && $selectedLecturerId == $lecturer->id) ? 'selected' : '' }}>
                             {{ $lecturer->username }}
                        </option>
                    @endforeach
                </optgroup>
            </select>
        </div>

        @if($selectedSemesterId && $selectedSemester)
            <div class="d-flex align-items-center ml-auto small text-muted">
                <strong>{{ $semesterStartDate->format('M d, Y') }}</strong> to <strong>{{ $semesterEndDate->format('M d, Y') }}</strong>
                @php
                    $resetParams = [];
                    if (!empty($selectedLabId)) { $resetParams['view_target'] = 'lab_' . $selectedLabId; }
                    elseif (!empty($selectedLecturerId)) { $resetParams['view_target'] = 'lec_' . $selectedLecturerId; }
                    $resetParams['date'] = $currentDate->format('Y-m-d');
                @endphp
                <a href="{{ url('/admin/schedules?' . http_build_query($resetParams)) }}" class="btn btn-sm btn-outline-secondary ml-3 py-0">Reset</a>
            </div>
        @endif
    </form>
</div>

<div class="row">
    <div class="col-lg-9 col-md-8 transition-all" id="schedule-main-col" style="transition: all 0.3s ease;">
        <div class="card card shadow-sm card-outline card shadow-sm h-100">
            <div class="card-header d-flex align-items-center p-2">
                <h3 class="card-title m-0 font-weight-bold ml-2">
                    Weekly Schedule Matrix 
                    @if(isset($selectedLecturer) && $selectedLecturer)
                        (Lecturer: {{ $selectedLecturer->username }})
                    @else
                        ({{ $selectedLab->lab_name ?? 'All Rooms' }})
                    @endif
                </h3>
                <div class="ml-auto d-flex align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-secondary mr-2" id="toggle-sidebar-btn" title="Toggle Sidebar">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </button>
                    <a href="{{ url('/admin/schedules/add') }}" class="btn btn-sm btn-primary mr-2"><i class="fas fa-plus"></i> Add</a>
                    
                    <div class="btn-group btn-group-sm">
                        @php
                            $linkParams = [];
                            if (!empty($selectedSemesterId)) { $linkParams['semester_id'] = $selectedSemesterId; }
                            
                            if (!empty($selectedLabId)) { $linkParams['view_target'] = 'lab_' . $selectedLabId; }
                            elseif (!empty($selectedLecturerId)) { $linkParams['view_target'] = 'lec_' . $selectedLecturerId; }
                        @endphp

                        @if($selectedSemesterId && !($canGoPrevWeek ?? true))
                            <a href="#" class="btn btn-default disabled"><i class="fas fa-chevron-left"></i> Prev</a>
                        @else
                            <a href="{{ url('/admin/schedules?' . http_build_query(array_merge($linkParams, ['date' => $prevWeek]))) }}" class="btn btn-default">
                                <i class="fas fa-chevron-left"></i> Prev
                            </a>
                        @endif

                        <a href="{{ url('/admin/schedules?' . http_build_query(array_merge($linkParams, ['date' => \Carbon\Carbon::now()->format('Y-m-d')]))) }}" class="btn btn-default">Current</a>

                        @if($selectedSemesterId && !($canGoNextWeek ?? true))
                            <a href="#" class="btn btn-default disabled">Next <i class="fas fa-chevron-right"></i></a>
                        @else
                            <a href="{{ url('/admin/schedules?' . http_build_query(array_merge($linkParams, ['date' => $nextWeek]))) }}" class="btn btn-default">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0 table-responsive">
                <table class="schedule-matrix m-0">
                    <thead>
                        <tr>
                            <th class="time-col">Time Slot</th>
                            @php $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']; @endphp
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
                            @php
                                // 💡 1. 如果時段到了 18:00 或更晚，直接跳過不畫，完美收官在 05:00 PM
                                if (\Carbon\Carbon::createFromFormat('H:i', $timeSlot)->hour >= 18) {
                                    continue;
                                }
                            @endphp

                            <tr>
                                <td class="time-col">
                                    @php
                                        $carbonTime = \Carbon\Carbon::createFromFormat('H:i', $timeSlot);
                                        $curHour = $carbonTime->format('h:i A');
                                    @endphp
                                    {{ $curHour }}
                                </td>
                                @foreach($days as $day)
                                    @php 
                                        $slot = $daysRow[$day]; 
                                    @endphp
                                    
                                    {{-- 如果是被合併的儲存格，直接跳過 --}}
                                    @if($slot['type'] === 'skip')
                                        @continue
                                    @endif

                                    {{-- 💡 2. 動態綁定外層 <td> 的背景色樣式 --}}
                                    <td class="{{ isset($weekDates[$day]) && $weekDates[$day]['is_today'] ? 'bg-light' : '' }} {{ $slot['type'] === 'enroll' ? 'bg-enroll-td' : '' }} {{ $slot['type'] === 'booking' ? 'bg-booking-td' : '' }} {{ $slot['type'] === 'maintenance' ? 'bg-maintenance-td' : '' }}" rowspan="{{ $slot['rowspan'] }}" style="padding: 0; vertical-align: top;">
                                        
                                        @if($slot['type'] === 'none')
                                            <div class="schedule-block none" style="height: 100%; min-height: 50px;"></div>
                                            
                                        @elseif($slot['type'] === 'maintenance')
                                            @php
                                                // 如果 data 是字串（系統全天關閉），或是 Booking 物件
                                                $mPurpose = (is_object($slot['data']) && isset($slot['data']->purpose)) ? $slot['data']->purpose : (is_string($slot['data']) ? $slot['data'] : 'Maintenance');
                                                $mStart = (is_object($slot['data']) && isset($slot['data']->start_time)) ? substr($slot['data']->start_time, 0, 5) : $timeSlot;
                                                $mEnd = (is_object($slot['data']) && isset($slot['data']->end_time)) ? substr($slot['data']->end_time, 0, 5) : '';
                                                $labName = (is_object($slot['data']) && isset($slot['data']->laboratory)) ? ($slot['data']->laboratory->lab_name ?? 'Room') : ($selectedLab->lab_name ?? 'Room');
                                            @endphp
                                            {{-- 使用 Flex 佈局確保 Edit 按鈕永遠靠最下 --}}
                                            <div class="schedule-block maintenance" style="height: 100%; min-height: 60px; padding: 8px; display: flex; flex-direction: column; justify-content: space-between; border: none;">
                                                <div>
                                                    <strong class="d-block">Maintenance ({{ $labName }})</strong>
                                                    <span class="d-block small text-muted">{{ $mStart }} - {{ $mEnd }}</span>
                                                    <span class="d-block small">{{ $mPurpose }}</span>
                                                </div>
                                                {{-- 💡 3. 如果後端有抓到對應的 schedule_id，就渲染 Edit 按鈕 --}}
                                                @if(!empty($slot['schedule_id']))
                                                    <div class="schedule-actions mt-2 text-right">
                                                        <a href="{{ url('/admin/schedules/' . $slot['schedule_id'] . '/edit') }}" class="btn btn-xs btn-primary"><i class="fas fa-edit"></i> Edit</a>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                        @elseif($slot['type'] === 'booking')
                                            @php
                                                $bPurpose = $slot['data']->purpose ?? 'Lab Booking';
                                                $bStart = isset($slot['data']->start_time) ? substr($slot['data']->start_time, 0, 5) : $timeSlot;
                                                $bEnd = isset($slot['data']->end_time) ? substr($slot['data']->end_time, 0, 5) : '';
                                                $labName = isset($slot['data']->laboratory) ? ($slot['data']->laboratory->lab_name ?? 'Room') : ($selectedLab->lab_name ?? 'Room');
                                            @endphp
                                            <div class="schedule-block booking" style="height: 100%; min-height: 60px; padding: 8px; display: flex; flex-direction: column; justify-content: space-between; border: none;">
                                                <div>
                                                    <strong class="d-block">{{ $bPurpose }}</strong>
                                                    <span class="d-block small text-muted">{{ $bStart }} - {{ $bEnd }}</span>
                                                    <span class="d-block small">Venue: {{ $labName }}</span>
                                                </div>
                                                {{-- 💡 4. Booking 的 Edit 按鈕 --}}
                                                @if(!empty($slot['schedule_id']))
                                                    <div class="schedule-actions mt-2 text-right">
                                                        <a href="{{ url('/admin/schedules/' . $slot['schedule_id'] . '/edit') }}" class="btn btn-xs btn-primary"><i class="fas fa-edit"></i> Edit</a>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                        @elseif($slot['type'] === 'enroll')
                                            <div class="schedule-block enroll" style="height: 100%; min-height: 60px; padding: 8px; display: flex; flex-direction: column; justify-content: space-between; border: none;">
                                                <div>
                                                    <strong class="d-block">{{ $slot['data']->course->course_name ?? 'Program' }}</strong>
                                                    <span class="d-block small text-muted">{{ substr($slot['data']->start_time, 0, 5) }} - {{ substr($slot['data']->end_time, 0, 5) }}</span>
                                                    <span class="d-block small">Lecturer: {{ $slot['data']->course->user->username ?? 'None' }}</span>
                                                    <span class="d-block small">Semester: {{ $slot['data']->semester->name ?? 'None' }}</span>
                                                    <span class="d-block small">Venue: {{ $slot['data']->laboratory->lab_name ?? 'None' }}</span>
                                                </div>
                                                {{-- 💡 5. Enroll 課程的 Edit 按鈕 --}}
                                                @if(!empty($slot['schedule_id']))
                                                    <div class="schedule-actions mt-2 text-right">
                                                        <a href="{{ url('/admin/schedules/' . $slot['schedule_id'] . '/edit') }}" class="btn btn-xs btn-primary"><i class="fas fa-edit"></i> Edit</a>
                                                    </div>
                                                @elseif(isset($slot['data']->id))
                                                    <div class="schedule-actions mt-2 text-right">
                                                        <a href="{{ url('/admin/schedules/' . $slot['data']->id . '/edit') }}" class="btn btn-xs btn-primary"><i class="fas fa-edit"></i> Edit</a>
                                                    </div>
                                                @endif
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
    </div>

    <!-- RIGHT COLUMN: 25% via col-lg-3 -->
    <div class="col-lg-3 col-md-4" id="schedule-sidebar-col" style="transition: all 0.3s ease;">
        
        <!-- Calendar Widget -->
      <div class="card card-outline shadow-sm mb-3">
            <div class="card-header border-0 d-flex justify-content-between align-items-center p-2 bg-light">
                @php
                    // 建立乾淨的篩選參數陣列
                    $linkParams = [];
                    if (!empty($selectedSemesterId)) { 
                        $linkParams['semester_id'] = $selectedSemesterId; 
                    }
                    
                    // 【核心修正】小月曆改用二合一的 view_target 來鎖定目前的視角 (Lab 或是 Lecturer)
                    if (!empty($selectedLabId)) { 
                        $linkParams['view_target'] = 'lab_' . $selectedLabId; 
                    } elseif (!empty($selectedLecturerId)) { 
                        $linkParams['view_target'] = 'lec_' . $selectedLecturerId; 
                    }

                    // 使用 http_build_query 打包，前面不手動拼接 ?date=
                    $qs = !empty($linkParams) ? '&' . http_build_query($linkParams) : '';
                @endphp
                
                <a href="{{ url('/admin/schedules?date=' . (Str::contains($prevMonth, '&') ? $prevMonth : $prevMonth . $qs)) }}" class="btn btn-sm btn-default py-0 px-2">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <strong style="font-size: 0.9rem;">{{ $monthName }}</strong>
                <a href="{{ url('/admin/schedules?date=' . (Str::contains($nextMonth, '&') ? $nextMonth : $nextMonth . $qs)) }}" class="btn btn-sm btn-default py-0 px-2">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <table class="mini-calendar m-0">
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
                                    <td class="{{ !$day['is_current_month'] ? 'text-muted' : '' }} {{ $day['is_today'] ? 'today text-primary' : '' }} {{ $day['is_selected_week'] && !$day['is_today'] ? 'selected-week' : '' }}">
                                        <a href="{{ url('/admin/schedules?date=' . $day['date'] . $qs) }}">{{ $day['day'] }}</a>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Legend Widget -->
        <div class="card shadow-sm mb-3 border-0">
            <div class="card-header bg-light p-2">
                <h3 class="card-title m-0" style="font-size: 0.85rem; font-weight: 600;"><i class="fas fa-list mr-1"></i> Legend</h3>
            </div>
            <div class="card-body p-2" style="font-size: 0.8rem;">
                <div class="d-flex align-items-center mb-1">
                    <span style="display:inline-block; width:12px; height:12px; background:#f0f7ff; border-left:2px solid #007bff; margin-right:6px;"></span>
                    Scheduled Class
                </div>
                <div class="d-flex align-items-center mb-1">
                    <span style="display:inline-block; width:12px; height:12px; background:#f0fff4; border-left:2px solid #28a745; margin-right:6px;"></span>
                    Booking Request
                </div>
                <div class="d-flex align-items-center mb-1">
                    <span style="display:inline-block; width:12px; height:12px; background:#fff5f5; border-left:2px solid #dc3545; margin-right:6px;"></span>
                    Maintenance
                </div>
            </div>
        </div>
        
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggle-sidebar-btn');
        const mainCol = document.getElementById('schedule-main-col');
        const sidebarCol = document.getElementById('schedule-sidebar-col');
        
        toggleBtn.addEventListener('click', function() {
            const pushMenuBtn = document.querySelector('[data-widget="pushmenu"]');
            if (sidebarCol.classList.contains('d-none')) {
                // Show right sidebar
                sidebarCol.classList.remove('d-none');
                mainCol.classList.remove('col-lg-12', 'col-md-12');
                mainCol.classList.add('col-lg-9', 'col-md-8');
                toggleBtn.innerHTML = '<i class="fas fa-expand-arrows-alt"></i>';
                toggleBtn.classList.remove('btn-secondary');
                toggleBtn.classList.add('btn-outline-secondary');
                
                // Open AdminLTE main sidebar
                if (pushMenuBtn && document.body.classList.contains('sidebar-collapse')) {
                    pushMenuBtn.click();
                }
            } else {
                // Hide right sidebar
                sidebarCol.classList.add('d-none');
                mainCol.classList.remove('col-lg-9', 'col-md-8');
                mainCol.classList.add('col-lg-12', 'col-md-12');
                toggleBtn.innerHTML = '<i class="fas fa-compress-arrows-alt"></i>';
                toggleBtn.classList.remove('btn-outline-secondary');
                toggleBtn.classList.add('btn-secondary');
                
                // Collapse AdminLTE main sidebar
                if (pushMenuBtn && !document.body.classList.contains('sidebar-collapse')) {
                    pushMenuBtn.click();
                }
            }
        });
    });
</script>
@endpush

@endsection
