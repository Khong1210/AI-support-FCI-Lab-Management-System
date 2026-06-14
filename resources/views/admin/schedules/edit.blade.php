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
        <div class="row">
            <div class="col-md-12 form-group" id="semester-wrapper">
                <label>Semester</label>
                <select id="semester-id-select" name="semester_id" class="form-control @error('semester_id') is-invalid @enderror">
                    <option value="">Select Semester</option>
                    @foreach($semesters as $semester)
                        <option value="{{ $semester->id }}" {{ $schedule->semester_id == $semester->id ? 'selected' : '' }}>
                            {{ $semester->name }} ({{ $semester->start_date }} - {{ $semester->end_date }})
                        </option>
                    @endforeach
                </select>
                @error('semester_id')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
        
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
            
            <div class="col-md-6 form-group" id="course-wrapper">
                <label>Course <span class="text-muted small">(Locked during edit)</span></label>
                <input type="text" class="form-control bg-light" value="{{ $schedule->course->course_name ?? 'Program' }}" readonly style="cursor: not-allowed;">
                <input type="hidden" id="course-select" name="course_id" value="{{ $schedule->course_id }}" data-hours="{{ $schedule->course->hours ?? 1 }}">
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 form-group">
                <label>Day of Week</label>
                <input type="text" id="day-of-week-display" class="form-control" value="{{ \Carbon\Carbon::parse($schedule->date)->format('l') }}" readonly>
                <input type="hidden" name="day_of_week" id="day-of-week" value="{{ \Carbon\Carbon::parse($schedule->date)->format('l') }}">
            </div>
            <div class="col-md-4 form-group">
                <label>Date</label>
                <input id="schedule-date" type="date" name="date" class="form-control" value="{{ $schedule->date }}" required>
            </div>
            
            <div class="col-md-4 form-group" id="recurring-wrapper">
                <input type="hidden" name="is_recurring" value="0">
                <div class="custom-control custom-checkbox mt-4 pt-2">
                    <input type="checkbox" class="custom-control-input" id="is-recurring-checkbox" name="is_recurring" value="1" {{ old('is_recurring', $schedule->is_recurring) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="is-recurring-checkbox">Repeat weekly</label>
                </div>
            </div>
        </div>
        <input type="hidden" id="exclude-schedule-id" value="{{ $schedule->id }}">
        
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
                <small id="end-time-help" class="form-text text-muted">For enroll the end time is auto-calculated; for booking/maintenance you may adjust it.</small>
                @error('end_time')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
            </div>
        </div>
        
        <div class="form-group" id="booker-name-wrapper" style="display:none;">
            <label>Booker Name</label>
            <input type="text" name="booker_name" id="booker-name" value="{{ old('booker_name', $schedule->booking->booker_name ?? '') }}" class="form-control">
        </div>

        <div class="form-group" id="purpose-wrapper" style="display:none;">
            <label>Purpose / Reason</label>
            <input type="text" name="purpose" id="purpose" value="{{ old('purpose', $schedule->booking->purpose ?? '') }}" class="form-control">
        </div>

        <div class="form-group" id="technician-wrapper" style="display:none;">
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

        <div class="form-group" id="all-day-wrapper" style="display:none;">
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
    <input type="hidden" name="return_lab_id" value="{{ request('lab_id', $schedule->lab_id) }}">
    <input type="hidden" name="return_semester_id" value="{{ request('semester_id', $schedule->semester_id) }}">
    <input type="hidden" name="return_date" value="{{ request('date', $schedule->date) }}">
</form>

@push('styles')
<style>
    select option.disabled-slot {
        background-color: #e9ecef !important;
        color: #6c757d !important;
        cursor: not-allowed !important;
    }
    .form-group .invalid-feedback-ajax {
        display: block;
        color: #dc3545;
        font-size: 0.875em;
        margin-top: 0.25rem;
    }
</style>
@endpush

@push('scripts')
<script>
    // ========== UTILITY FUNCTIONS ==========
    function parseTimeToMinutes(time) {
        const parts = time.split(':');
        return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
    }

    function updateScheduleDay() {
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

    function minutesToTime(minutes) {
        const h = Math.floor(minutes / 60);
        return (h < 10 ? '0' : '') + h + ':00';
    }

    function isOverlapping(candStart, candEnd, occupStart, occupEnd) {
        return candStart < occupEnd && candEnd > occupStart;
    }

    // ========== CONFLICT CHECKING ==========
    function fetchConflicts(date, labId, excludeId = '') {
        if (!date || !labId) return Promise.resolve([]);

        const params = new URLSearchParams({
            date: date,
            lab_id: labId,
            exclude_id: excludeId
        });

        return fetch(`/schedules/check-conflicts?${params.toString()}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(resp => resp.ok ? resp.json() : [])
        .catch(() => []);
    }

   // 修改这个函数，直接调用你刚才在 Controller 写好的新方法
function updateStartTimeOptions() {
    const dateInput = document.getElementById('schedule-date');
    const labSelect = document.getElementById('lab-id-select');
    const startSelect = document.getElementById('start-time-select');
    const excludeIdEl = document.getElementById('exclude-schedule-id'); // 确保编辑时排除自己

    if (!dateInput || !labSelect || !startSelect) return;

    const dateValue = dateInput.value;
    const labId = labSelect.value;
    const excludeId = excludeIdEl ? excludeIdEl.value : '';
    const currentStartValue = startSelect.value;

    if (!dateValue || !labId) return;

    // 这里调用你刚才 Controller 写的那个能剔除占用时段的方法
    // 记得在 URL 里加上 exclude_id，这样编辑时就不会把自己算作冲突
    fetch(`/schedules/get-available-time-slots?date=${dateValue}&laboratory_id=${labId}&exclude_schedule_id=${excludeId}`)
        .then(res => res.json())
        .then(availableSlots => {
            startSelect.innerHTML = '<option value="">Select Time</option>';
            
            availableSlots.forEach(time => {
                const opt = document.createElement('option');
                opt.value = time;
                opt.textContent = time;
                
                // 如果是当前编辑的那个原始时间，保持选中
                if (time === currentStartValue) {
                    opt.selected = true;
                }
                startSelect.appendChild(opt);
            });
        });
}

    // ========== AUTO END TIME ==========
    function calculateEndTime() {
        const courseSelect = document.getElementById('course-select');
        const startSelect = document.getElementById('start-time-select');
        const endSelect = document.getElementById('end-time-select');

        if (!endSelect || !startSelect || !courseSelect) return;

        const hours = parseInt(courseSelect.dataset.hours || '1', 10);
        const startTime = startSelect.value;
        if (!startTime) return;

        const startMin = parseTimeToMinutes(startTime);
        const endMin = Math.min(18 * 60, startMin + hours * 60);
        const endTime = minutesToTime(endMin);

        endSelect.value = endTime;
        endSelect.disabled = true;
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

    // ========== INITIALIZATION ==========
    document.addEventListener('DOMContentLoaded', function () {
        updateScheduleDay();
        updateStartTimeOptions();
        calculateEndTime();

        const dateInput = document.getElementById('schedule-date');
        const labSelect = document.getElementById('lab-id-select');
        const courseSelect = document.getElementById('course-select');
        const startSelect = document.getElementById('start-time-select');
        const endSelect = document.getElementById('end-time-select');
        const form = document.getElementById('edit-schedule-form');

        if (dateInput) dateInput.addEventListener('change', function () { updateScheduleDay(); updateStartTimeOptions(); });
        if (labSelect) labSelect.addEventListener('change', updateStartTimeOptions);
        if (courseSelect) courseSelect.addEventListener('change', function () { updateStartTimeOptions(); calculateEndTime(); });
        if (startSelect) startSelect.addEventListener('change', calculateEndTime);
        if (form) form.addEventListener('submit', handleAjaxFormSubmit);
    });
</script>
@endpush
@endsection