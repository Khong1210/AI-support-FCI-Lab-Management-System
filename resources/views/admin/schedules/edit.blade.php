@extends('layouts.admin')

@section('title', 'Edit Schedule')
@section('page-title', 'Edit Schedule')
@section('breadcrumb', 'Edit Schedule')

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Edit Schedule</h3>
    </div>
    
    <form action="{{ url('/schedules/' . $schedule->id) }}" method="POST" id="edit-schedule-form">
    @csrf
    @method('PUT')
    
    <input type="hidden" name="return_lab_id" value="{{ request('lab_id', $schedule->lab_id) }}">
    <input type="hidden" name="return_semester_id" value="{{ request('semester_id', $schedule->semester_id) }}">
    <input type="hidden" name="return_date" value="{{ request('date', $schedule->date) }}">
    <input type="hidden" id="schedule-type" value="{{ $schedule->schedule_type }}">
    
    <div class="card-body">
        {{-- ========== SEMESTER ========== --}}
        @if($schedule->schedule_type === 'enroll')
        <div class="row">
            <div class="col-md-12 form-group" id="semester-wrapper">
                <label>Semester <span class="text-muted small">(Locked during edit)</span></label>
                <input type="text" class="form-control bg-light" value="{{ $schedule->semester->name ?? 'N/A' }} ({{ $schedule->semester->start_date ?? '' }} - {{ $schedule->semester->end_date ?? '' }})" readonly style="cursor: not-allowed;">
                <input type="hidden" id="semester-id" name="semester_id" value="{{ $schedule->semester_id }}" data-start-date="{{ $schedule->semester->start_date ?? '' }}" data-end-date="{{ $schedule->semester->end_date ?? '' }}">
            </div>
        </div>
        @else
        <div class="row">
            <div class="col-md-12 form-group" id="semester-wrapper">
                <label>Semester</label>
                <select id="semester-id-select" name="semester_id" class="form-control @error('semester_id') is-invalid @enderror">
                    <option value="">Select Semester</option>
                    @foreach($semesters as $semester)
                        <option value="{{ $semester->id }}" 
                                data-start-date="{{ $semester->start_date }}" 
                                data-end-date="{{ $semester->end_date }}"
                                {{ $schedule->semester_id == $semester->id ? 'selected' : '' }}>
                            {{ $semester->name }} ({{ $semester->start_date }} - {{ $semester->end_date }})
                        </option>
                    @endforeach
                </select>
                @error('semester_id')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        @endif
        
        {{-- ========== LABORATORY + COURSE ========== --}}
        <div class="row">
            <div class="col-md-6 form-group">
                <label>Laboratory</label>
                <select id="lab-id-select" name="lab_id" class="form-control" required>
                    <option value="">Select Laboratory</option>
                    @foreach($laboratories as $lab)
                        <option value="{{ $lab->id }}" {{ $schedule->lab_id == $lab->id ? 'selected' : '' }}>{{ $lab->lab_name }}</option>
                    @endforeach
                </select>
            </div>
            
            @if($schedule->schedule_type === 'enroll')
            <div class="col-md-6 form-group" id="course-wrapper">
                <label>Course <span class="text-muted small">(Locked during edit)</span></label>
                <input type="text" class="form-control bg-light" value="{{ $schedule->course->course_name ?? 'Program' }}" readonly style="cursor: not-allowed;">
                <input type="hidden" id="course-select" name="course_id" value="{{ $schedule->course_id }}" data-hours="{{ $schedule->course->hours ?? 1 }}">
            </div>
            @else
            {{-- Booking/Maintenance: no course, just fill the column to keep layout --}}
            <div class="col-md-6 form-group" id="course-wrapper">
            </div>
            @endif
        </div>
        
        {{-- ========== DAY OF WEEK + DATE + RECURRING ========== --}}
        <div class="row">
            @if($schedule->schedule_type === 'enroll')
            {{-- ENROLL: Editable Day-of-Week select + recomputable date --}}
            <div class="col-md-4 form-group" id="enroll-day-group">
                <label>Day of Week</label>
                <select id="enroll-day-select" class="form-control" name="day_of_week">
                    <option value="">Select Day</option>
                    <option value="Monday" {{ $schedule->day_of_week == 'Monday' ? 'selected' : '' }}>Monday</option>
                    <option value="Tuesday" {{ $schedule->day_of_week == 'Tuesday' ? 'selected' : '' }}>Tuesday</option>
                    <option value="Wednesday" {{ $schedule->day_of_week == 'Wednesday' ? 'selected' : '' }}>Wednesday</option>
                    <option value="Thursday" {{ $schedule->day_of_week == 'Thursday' ? 'selected' : '' }}>Thursday</option>
                    <option value="Friday" {{ $schedule->day_of_week == 'Friday' ? 'selected' : '' }}>Friday</option>
                </select>
            </div>
            <div class="col-md-4 form-group" id="enroll-date-group">
                <label>Date</label>
                <input id="schedule-date" type="date" name="date" class="form-control" value="{{ $schedule->date }}">
                <small class="form-text text-muted">Auto-calculated from semester start + day of week. Change the day above to recalculate.</small>
            </div>
            @else
            {{-- BOOKING / MAINTENANCE: Read-only day-of-week derived from date + editable date --}}
            <div class="col-md-4 form-group" id="regular-day-group">
                <label>Day of Week</label>
                <input type="text" id="day-of-week-display" class="form-control" value="{{ \Carbon\Carbon::parse($schedule->date)->format('l') }}" readonly>
                <input type="hidden" name="day_of_week" id="day-of-week" value="{{ \Carbon\Carbon::parse($schedule->date)->format('l') }}">
            </div>
            <div class="col-md-4 form-group" id="regular-date-group">
                <label>Date</label>
                <input id="schedule-date" type="date" name="date" class="form-control" value="{{ $schedule->date }}" required>
            </div>
            @endif
            
            <div class="col-md-4 form-group" id="recurring-wrapper">
                @if($schedule->schedule_type === 'enroll')
                {{-- ENROLL: forced checked + disabled + hidden companion input --}}
                <input type="hidden" name="is_recurring" value="1">
                <div class="custom-control custom-checkbox mt-4 pt-2">
                    <input type="checkbox" class="custom-control-input" id="is-recurring-checkbox" name="is_recurring_display" value="1" checked disabled>
                    <label class="custom-control-label" for="is-recurring-checkbox">Repeat weekly</label>
                </div>
                <small class="form-text text-muted">Enrollment schedules are automatically recurring for the entire semester.</small>
                @else
                {{-- BOOKING / MAINTENANCE: forced unchecked + disabled + hidden companion input --}}
                <input type="hidden" name="is_recurring" value="0">
                <div class="custom-control custom-checkbox mt-4 pt-2">
                    <input type="checkbox" class="custom-control-input" id="is-recurring-checkbox" name="is_recurring_display" value="1" disabled>
                    <label class="custom-control-label" for="is-recurring-checkbox">Repeat weekly</label>
                </div>
                <small class="form-text text-muted">
                    @if($schedule->schedule_type === 'booking')
                    Booking schedules are single-day only.
                    @else
                    Maintenance schedules are single-day only.
                    @endif
                </small>
                @endif
            </div>
        </div>
        <input type="hidden" id="exclude-schedule-id" value="{{ $schedule->id }}">
        
        {{-- ========== START TIME + END TIME ========== --}}
        <div class="row">
            <div class="col-md-4 form-group">
                <label>Start Time (08:00 - 18:00)</label>
                <select id="start-time-select" name="start_time" class="form-control @error('start_time') is-invalid @enderror" data-old-time="{{ substr($schedule->start_time, 0, 5) }}" required>
                    <option value="">Select Time</option>
                    @for($h = 8; $h <= 18; $h++)
                        @php $time = sprintf('%02d:00', $h); @endphp
                        <option value="{{ $time }}" {{ substr($schedule->start_time, 0, 5) == $time ? 'selected' : '' }}>{{ $time }}</option>
                    @endfor
                </select>
                <small id="start-time-warning" class="form-text text-danger"></small>
                @error('start_time')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
            <div class="col-md-8 form-group">
                <label>End Time</label>
                <select id="end-time-select" name="end_time" class="form-control @error('end_time') is-invalid @enderror" required>
                    <option value="">Select End Time</option>
                    @for($h = 8; $h <= 18; $h++)
                        @php $time = sprintf('%02d:00', $h); @endphp
                        <option value="{{ $time }}" {{ substr($schedule->end_time,0,5) == $time ? 'selected' : '' }}>{{ $time }}</option>
                    @endfor
                </select>
                <input type="hidden" id="hidden-start-time" value="{{ substr($schedule->start_time,0,5) }}">
                <input type="hidden" id="hidden-end-time" value="{{ substr($schedule->end_time,0,5) }}">
                <small id="end-time-help" class="form-text text-muted">
                    @if($schedule->schedule_type === 'enroll')
                        Auto-calculated from course hours + start time.
                    @else
                        For booking/maintenance you may adjust it.
                    @endif
                </small>
                @error('end_time')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
            </div>
        </div>
        
        {{-- ========== TYPE-SPECIFIC FIELDS ========== --}}
        {{-- Booker Name: visible for both booking and maintenance --}}
        @if($schedule->schedule_type !== 'maintenance')
            <div class="form-group" id="booker-name-wrapper" style="display:{{ in_array($schedule->schedule_type, ['booking','maintenance']) ? 'block' : 'none' }};">
                <label>{{ $schedule->schedule_type === 'maintenance' ? 'Requested By' : 'Booker Name' }}</label>
                <input type="text" name="booker_name" id="booker-name" value="{{ old('booker_name', $schedule->booking->booker_name ?? '') }}" class="form-control">
            </div>
        @endif

        {{-- Purpose: visible for both booking and maintenance --}}
        <div class="form-group" id="purpose-wrapper" style="display:{{ in_array($schedule->schedule_type, ['booking','maintenance']) ? 'block' : 'none' }};">
            <label>Purpose / Reason</label>
            <input type="text" name="purpose" id="purpose" value="{{ old('purpose', $schedule->booking->purpose ?? '') }}" class="form-control">
        </div>

        {{-- Technician: visible for maintenance only --}}
        <div class="form-group" id="technician-wrapper" style="display:{{ $schedule->schedule_type === 'maintenance' ? 'block' : 'none' }};">
            <label>Technician In Charge</label>
            <select name="technician_id" id="technician-id-select" class="form-control">
                <option value="">Select Technician</option>
                @foreach($technicians as $tech)
                    <option value="{{ $tech->id }}" {{ (old('technician_id', $schedule->booking->user_id ?? '') == $tech->id) ? 'selected' : '' }}>
                        {{ $tech->name }} ({{ $tech->email }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- All Day: visible for maintenance only --}}
        <div class="form-group" id="all-day-wrapper" style="display:{{ $schedule->schedule_type === 'maintenance' ? 'block' : 'none' }};">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="all-day-checkbox" name="all_day" value="1">
                <label class="custom-control-label" for="all-day-checkbox">All Day Maintenance (08:00 - 18:00)</label>
            </div>
        </div>
    </div>
    
    <div class="card-footer d-flex align-items-center">
        <button type="submit" class="btn btn-dark">Update Schedule</button>
        
        @php
            $cancelParams = [
                'date' => request('date', $schedule->date),
                'lab_id' => request('lab_id', $schedule->lab_id),
                'semester_id' => request('semester_id', $schedule->semester_id)
            ];
        @endphp
        <a href="{{ url('/schedules?' . http_build_query($cancelParams)) }}" class="btn btn-secondary ml-2">Cancel</a>
        
        <button type="button" class="btn btn-outline-danger ml-auto" onclick="confirmDeleteSchedule()">
            <i class="fas fa-trash-alt mr-1"></i> Delete Schedule
        </button>
    </div>
</form>
</div>

<form id="delete-schedule-form" action="{{ url('/schedules/' . $schedule->id) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')

    <input type="hidden" name="redirect_date" value="{{ $schedule->date }}">
    <input type="hidden" name="redirect_lab_id" value="{{ $schedule->lab_id }}">
    @if(request('view_target'))
        <input type="hidden" name="view_target" value="{{ request('view_target') }}">
    @endif
</form>

@push('styles')
<style>
    select option.disabled-slot {
        background-color: #e9ecef !important;
        color: #6c757d !important;
        cursor: not-allowed !important;
    }
    select option.warning-slot {
        background-color: #fff3cd !important;
        color: #856404 !important;
        cursor: not-allowed !important;
    }
    .form-group .invalid-feedback-ajax {
        display: block;
        color: #dc3545;
        font-size: 0.875em;
        margin-top: 0.25rem;
    }
    .readonly-select-override {
        pointer-events: none !important;
        background-color: #e9ecef !important;
        color: #495057 !important;
        touch-action: none;
    }
</style>
@endpush

@push('scripts')
<script>
    // ========== UTILITY FUNCTIONS ==========
    function parseTimeToMinutes(time) {
        if (!time) return 0;
        const parts = time.split(':');
        return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
    }

    function minutesToTime(minutes) {
        const h = Math.floor(minutes / 60);
        return (h < 10 ? '0' : '') + h + ':00';
    }

    function getFirstDateOfWeekday(startDateStr, targetDayName) {
        if (!startDateStr || !targetDayName) return '';
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const targetDayIndex = days.indexOf(targetDayName);
        if (targetDayIndex === -1) return '';

        const parts = startDateStr.split('-');
        let current = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
        
        for (let i = 0; i < 7; i++) {
            if (current.getDay() === targetDayIndex) {
                const y = current.getFullYear();
                const m = String(current.getMonth() + 1).padStart(2, '0');
                const d = String(current.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            }
            current.setDate(current.getDate() + 1);
        }
        return '';
    }

    function updateScheduleDay() {
        const typeInput = document.getElementById('schedule-type');
        if (typeInput && typeInput.value === 'enroll') return;

        const dateInput = document.getElementById('schedule-date');
        const display = document.getElementById('day-of-week-display');
        const hidden = document.getElementById('day-of-week');

        if (!dateInput || !display || !hidden) return;
        const dateValue = dateInput.value;
        if (!dateValue) {
            display.value = '';
            hidden.value = '';
            return;
        }

        const parts = dateValue.split('-');
        const date = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const dayName = days[date.getDay()];
        display.value = dayName;
        hidden.value = dayName;
    }

    // ========== ENROLL: Recalculate date from day-of-week + semester start ==========
    function updateEnrollDate() {
        const typeInput = document.getElementById('schedule-type');
        if (!typeInput || typeInput.value !== 'enroll') return;

        const semesterHidden = document.getElementById('semester-id');
        const daySelect = document.getElementById('enroll-day-select');
        const dateInput = document.getElementById('schedule-date');

        if (!semesterHidden || !daySelect || !dateInput) return;

        const startDate = semesterHidden.getAttribute('data-start-date');
        const dayName = daySelect.value;

        if (startDate && dayName) {
            const calculatedDate = getFirstDateOfWeekday(startDate, dayName);
            if (calculatedDate) {
                dateInput.value = calculatedDate;
                updateStartTimeOptions();
            }
        }
    }

    function updateStartTimeOptions() {
        const dateInput = document.getElementById('schedule-date');
        const labSelect = document.getElementById('lab-id-select');
        const startSelect = document.getElementById('start-time-select');
        const endSelect = document.getElementById('end-time-select');
        const typeInput = document.getElementById('schedule-type');
        const courseSelect = document.getElementById('course-select');

        if (!dateInput || !labSelect || !startSelect) return;

        const dateValue = dateInput.value;
        const labId = labSelect.value;
        const currentStartValue = startSelect.value;
        const scheduleType = typeInput ? typeInput.value : 'enroll';

        // 🌟【第一步】利用 Blade 直接把当前排课原本的原始时间注入给 JS
        const originalStart = "{{ substr($schedule->start_time, 0, 5) }}"; // 例如: "10:00"
        const originalEnd = "{{ substr($schedule->end_time, 0, 5) }}";     // 例如: "12:00"

        // 安全兼容：无论 course-select 是下拉框还是隐藏输入框，都能精准识别真实课时
        let courseHours = 1;
        if (courseSelect) {
            if (courseSelect.tagName === 'SELECT' && courseSelect.selectedIndex >= 0) {
                const selectedOpt = courseSelect.options[courseSelect.selectedIndex];
                if (selectedOpt && selectedOpt.value) {
                    courseHours = parseInt(selectedOpt.dataset.hours || '1', 10);
                }
            } else {
                courseHours = parseInt(courseSelect.dataset.hours || courseSelect.getAttribute('data-hours') || '1', 10);
            }
        }

        if (!dateValue || !labId) return;

        // 依然请求原本的后端接口，无需改动后端代码
        fetch(`/schedules/check-occupied-slots?date=${dateValue}&laboratory_id=${labId}`)
            .then(res => res.json())
            .then(occupiedSlots => {
                if (!Array.isArray(occupiedSlots)) occupiedSlots = [];

                // 🌟【第二步：核心黑魔法】在前端直接过滤掉“过去的自己”
                // 如果某条占用记录的开始和结束时间跟当前排课的原始时间完全一致，说明就是它自己，直接从敌人列表中删掉！
                occupiedSlots = occupiedSlots.filter(slot => {
                    if (!slot) return false;
                    let rawStart = (slot.start_time || slot.start || '').substring(0, 5);
                    let rawEnd = (slot.end_time || slot.end || '').substring(0, 5);
                    return !(rawStart === originalStart && rawEnd === originalEnd);
                });

                // 🌟 重绘开始时间列表（漂亮的灰色 UI 架构）
                startSelect.innerHTML = '<option value="">Select Time</option>';
                
                for (let h = 8; h <= 17; h++) {
                    const time = (h < 10 ? '0' : '') + h + ':00';
                    const opt = document.createElement('option');
                    opt.value = time;
                    opt.textContent = time;
                    
                    let isDisabled = false;
                    let reasonText = '';

                    // 1. 越界防错：排课时间 + 课程课时如果超过 18:00，则不允许选择
                    if (scheduleType === 'enroll') {
                        const startMin = h * 60;
                        const endMin = startMin + courseHours * 60;
                        if (endMin > 18 * 60) {
                            isDisabled = true;
                            reasonText = ' (not enough time before close)';
                        }
                    }

                    // 2. 连续区间碰撞检查（已经排除了自己，不会再误伤了）
                    if (!isDisabled) {
                        const hoursToCheck = (scheduleType === 'enroll') ? courseHours : 1;

                        for (let i = 0; i < hoursToCheck; i++) {
                            const checkH = h + i;
                            const checkTime = (checkH < 10 ? '0' : '') + checkH + ':00';

                            const hasConflict = occupiedSlots.some(slot => {
                                let rawStart = (slot.start_time || slot.start || '').substring(0, 5);
                                let rawEnd = (slot.end_time || slot.end || '').substring(0, 5);
                                return checkTime >= rawStart && checkTime < rawEnd;
                            });

                            if (hasConflict) {
                                isDisabled = true;
                                reasonText = ' (occupied)';
                                break; 
                            }
                        }
                    }

                    // 渲染漂亮的不可选灰色 UI
                    if (isDisabled) {
                        opt.disabled = true;
                        opt.classList.add('disabled-slot'); 
                        opt.textContent = time + reasonText;
                    }

                    // 完美回显当前选中的值（因为自己没被变灰，这里可以顺利被高亮选中）
                    if (time === currentStartValue) {
                        opt.selected = true;
                    }
                    startSelect.appendChild(opt);
                }

                // 🌟 结束时间同步重绘
                if (endSelect) {
                    const chosenStart = startSelect.value;
                    const endCurrentValue = endSelect.value;

                    endSelect.innerHTML = '<option value="">Select End Time</option>';
                    
                    for (let h = 9; h <= 18; h++) {
                        const time = (h < 10 ? '0' : '') + h + ':00';
                        const opt = document.createElement('option');
                        opt.value = time;
                        opt.textContent = time;

                        let isEndDisabled = false;

                        if (chosenStart && time <= chosenStart) {
                            isEndDisabled = true;
                        }

                        const endTimeOccupied = occupiedSlots.some(slot => {
                            let rawStart = (slot.start_time || slot.start || '').substring(0, 5);
                            return chosenStart && rawStart >= chosenStart && rawStart < time;
                        });

                        if (endTimeOccupied) {
                            isEndDisabled = true;
                            opt.textContent = time + ' (occupied)';
                        }

                        if (isEndDisabled) {
                            opt.disabled = true;
                            opt.classList.add('disabled-slot');
                        }

                        if (time === endCurrentValue) {
                            opt.selected = true;
                        }
                        endSelect.appendChild(opt);
                    }

                    // 如果是课程（enroll），强制自动校准并锁定
                    if (scheduleType === 'enroll' && chosenStart) {
                        const startMin = parseInt(chosenStart.substring(0,2), 10) * 60;
                        const endMin = Math.min(18 * 60, startMin + courseHours * 60);
                        const checkH = Math.floor(endMin / 60);
                        const expectedEndTime = (checkH < 10 ? '0' : '') + checkH + ':00';
                        endSelect.value = expectedEndTime;
                        endSelect.disabled = true;
                    } else {
                        endSelect.disabled = false;
                    }
                }
            })
            .catch(err => {
                console.error('Render fallback error:', err);
            });
    }
    // ========== AUTO END TIME (ENROLL ONLY) ==========
    function calculateEndTime() {
        const courseSelect = document.getElementById('course-select');
        const startSelect = document.getElementById('start-time-select');
        const endSelect = document.getElementById('end-time-select');
        const typeInput = document.getElementById('schedule-type');
        const scheduleType = typeInput ? typeInput.value : 'enroll';

        if (!endSelect || !startSelect) return;

        if (scheduleType === 'enroll') {
            if (!courseSelect) return;
            const hours = parseInt(courseSelect.dataset.hours || '1', 10);
            const startTime = startSelect.value;
            if (!startTime) return;
            
            const startMin = parseTimeToMinutes(startTime);
            const endMin = Math.min(18 * 60, startMin + hours * 60);
            const endTime = minutesToTime(endMin);

            endSelect.value = endTime;
            endSelect.disabled = true;
        } else {
            endSelect.disabled = false;
        }
    }

    // ========== FORM SUBMISSION ==========
    function clearFieldErrors() {
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback-ajax').forEach(el => el.remove());
    }

    function displayFieldErrors(errors) {
        Object.keys(errors).forEach(field => {
            const input = document.querySelector(`[name="${field}"]`);
            if (!input) return;
            input.classList.add('is-invalid');
            errors[field].forEach(msg => {
                const fb = document.createElement('div');
                fb.className = 'invalid-feedback-ajax';
                fb.textContent = msg;
                input.parentNode.appendChild(fb);
            });
        });
    }

    function handleAjaxFormSubmit(event) {
        event.preventDefault();
        clearFieldErrors();

        const endSelect = document.getElementById('end-time-select');
        if (endSelect && endSelect.disabled) {
            endSelect.disabled = false;
        }

        const form = event.target;
        const formData = new FormData(form);
        const typeInput = document.getElementById('schedule-type');
        const scheduleType = typeInput ? typeInput.value : 'enroll';

        // Defensive is_recurring values for each type
        if (scheduleType === 'enroll') {
            formData.set('is_recurring', '1');
        } else {
            formData.set('is_recurring', '0');
        }

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(resp => {
            if (!resp.ok && resp.status === 422) {
                return resp.json().then(d => { throw { status: 422, errors: d.errors }; });
            }
            return resp.json();
        })
        .then(data => {
            if (data.success) {
                if (typeof reloadMatrix === 'function') reloadMatrix();
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show';
                alertDiv.innerHTML = `${data.message}<button type="button" class="close" data-dismiss="alert">&times;</button>`;
                form.parentNode.insertBefore(alertDiv, form);
                setTimeout(() => alertDiv.remove(), 3000);

                const returnParams = new URLSearchParams();
                const returnDate = document.querySelector('input[name="return_date"]')?.value;
                const returnLabId = document.querySelector('input[name="return_lab_id"]')?.value;
                const returnSemesterId = document.querySelector('input[name="return_semester_id"]')?.value;
                if (returnDate) returnParams.append('date', returnDate);
                if (returnLabId) returnParams.append('lab_id', returnLabId);
                if (returnSemesterId) returnParams.append('semester_id', returnSemesterId);

                setTimeout(() => {
                    window.location.href = `/schedules${returnParams.toString() ? '?' + returnParams.toString() : ''}`;
                }, 1500);
            }
        })
        .catch(err => {
            if (err.status === 422) displayFieldErrors(err.errors);
            else {
                console.error(err);
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                alertDiv.innerHTML = `An error occurred while updating the schedule.<button type="button" class="close" data-dismiss="alert">&times;</button>`;
                form.parentNode.insertBefore(alertDiv, form);
            }
        });
    }

    function confirmDeleteSchedule() {
        if (confirm('Are you sure you want to delete this schedule? This action cannot be undone.')) {
            const deleteForm = document.getElementById('delete-schedule-form');
            if (deleteForm) {
                deleteForm.submit();
            }
        }
    }

    // ========== ALL-DAY MAINTENANCE HANDLER ==========
    function handleAllDayCheckbox() {
        const typeInput = document.getElementById('schedule-type');
        const allDayCheckbox = document.getElementById('all-day-checkbox');
        const startSelect = document.getElementById('start-time-select');
        const endSelect = document.getElementById('end-time-select');

        if (!typeInput || !allDayCheckbox || !startSelect || !endSelect) return;

        const isMaintenance = (typeInput.value === 'maintenance');

        if (isMaintenance && allDayCheckbox.checked) {
            startSelect.value = '08:00';
            endSelect.value = '18:00';

            startSelect.required = false;
            endSelect.required = false;

            startSelect.classList.add('readonly-select-override');
            endSelect.classList.add('readonly-select-override');
        } else {
            if (!isMaintenance) {
                allDayCheckbox.checked = false;
            }
            startSelect.value = '';
            endSelect.value = '';

            startSelect.required = true;
            endSelect.required = true;

            startSelect.classList.remove('readonly-select-override');
            endSelect.classList.remove('readonly-select-override');
        }
    }

    // ========== INITIALIZATION ==========
    document.addEventListener('DOMContentLoaded', function () {
        const typeInput = document.getElementById('schedule-type');
        const scheduleType = typeInput ? typeInput.value : 'enroll';

        const dateInput = document.getElementById('schedule-date');
        const labSelect = document.getElementById('lab-id-select');
        const startSelect = document.getElementById('start-time-select');
        const endSelect = document.getElementById('end-time-select');
        const form = document.getElementById('edit-schedule-form');
        const allDayCheckbox = document.getElementById('all-day-checkbox');
        const allDayWrapper = document.getElementById('all-day-wrapper');

        // --- Enroll-specific initialization ---
        if (scheduleType === 'enroll') {
            const enrollDaySelect = document.getElementById('enroll-day-select');
            if (enrollDaySelect) {
                enrollDaySelect.addEventListener('change', function () {
                    updateEnrollDate();
                    updateStartTimeOptions();
                    calculateEndTime();
                });
            }

            updateStartTimeOptions();
            calculateEndTime();

            if (allDayWrapper) allDayWrapper.style.display = 'none';
        }
        // --- Booking / Maintenance initialization ---
        else {
            updateScheduleDay();
            updateStartTimeOptions();
            calculateEndTime(); // sets endSelect.disabled = false for non-enroll

            if (dateInput) {
                dateInput.addEventListener('change', function () {
                    updateScheduleDay();
                    updateStartTimeOptions();
                });
            }

            // Maintenance: pre-check all-day if the schedule spans full day
            if (scheduleType === 'maintenance' && allDayWrapper) {
                allDayWrapper.style.display = 'block';
                const currentStart = startSelect ? startSelect.value : '';
                if (currentStart === '08:00') {
                    if (endSelect && endSelect.value === '18:00') {
                        if (allDayCheckbox) allDayCheckbox.checked = true;
                        handleAllDayCheckbox();
                    }
                }
            }

            if (allDayCheckbox) {
                allDayCheckbox.addEventListener('change', handleAllDayCheckbox);
            }
        }

        // --- Common event listeners ---
        if (labSelect) labSelect.addEventListener('change', updateStartTimeOptions);
        if (startSelect) {
            startSelect.addEventListener('change', function () {
                calculateEndTime();
                updateStartTimeOptions(); // re-evaluate end-time options when start changes
            });
        }

        const courseSelect = document.getElementById('course-select');
        if (courseSelect) {
            courseSelect.addEventListener('change', function () {
                updateStartTimeOptions();
                calculateEndTime();
            });
        }

        if (form) {
            form.addEventListener('submit', function(e) {
                const allDay = document.getElementById('all-day-checkbox');
                const start = document.getElementById('start-time-select');
                const end = document.getElementById('end-time-select');
                if (allDay && allDay.checked) {
                    if (start) start.required = false;
                    if (end) end.required = false;
                }
                const lockedSelects = document.querySelectorAll('.readonly-select-override');
                lockedSelects.forEach(select => select.classList.remove('readonly-select-override'));
            });
            form.addEventListener('submit', handleAjaxFormSubmit);
        }
    });
</script>
@endpush
@endsection