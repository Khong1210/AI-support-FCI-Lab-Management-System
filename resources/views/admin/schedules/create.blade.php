@extends('layouts.admin')

@section('title', 'Add Schedule')
@section('page-title', 'Add Schedule')
@section('breadcrumb', 'Add Schedule')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clock mr-2"></i>Add Schedule</h3>
            </div>
            
            <form action="{{ url('/admin/schedules') }}" method="POST" id="add-schedule-form">
                @csrf
                
                <input type="hidden" name="return_lab_id" value="{{ request('lab_id', old('return_lab_id')) }}">
                <input type="hidden" name="return_semester_id" value="{{ request('semester_id', old('return_semester_id')) }}">
                <input type="hidden" name="return_date" value="{{ request('date', old('return_date')) }}">

                <div class="card-body">
                    <div class="form-group">
                        <label>Semester</label>
                        <select id="semester-id-select" name="semester_id" class="form-control @error('semester_id') is-invalid @enderror" required>
                            <option value="">Select Semester</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}" {{ old('semester_id', request('semester_id')) == $semester->id ? 'selected' : '' }}>
                                    {{ $semester->name }} ({{ $semester->start_date }} - {{ $semester->end_date }})
                                </option>
                            @endforeach
                        </select>
                        @error('semester_id')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Laboratory</label>
                            <select id="lab-id-select" name="lab_id" class="form-control @error('lab_id') is-invalid @enderror" required>
                                <option value="">Select Laboratory</option>
                                @foreach($laboratories as $lab)
                                    <option value="{{ $lab->id }}" {{ old('lab_id', request('lab_id')) == $lab->id ? 'selected' : '' }}>
                                        {{ $lab->lab_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('lab_id')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                        </div>
                        
                        <div class="col-md-6 form-group">
                            <label>Course</label>
                            <select id="course-select" name="course_id" class="form-control @error('course_id') is-invalid @enderror" required>
                                <option value="">Select Course</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" data-hours="{{ $course->hours }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->course_name }} ({{ $course->hours }} Hours)
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Day of Week</label>
                            <input type="text" id="day-of-week-display" class="form-control bg-light" value="{{ old('date') ? \Carbon\Carbon::parse(old('date'))->format('l') : '' }}" readonly>
                            <input type="hidden" name="day_of_week" id="day-of-week" value="{{ old('date') ? \Carbon\Carbon::parse(old('date'))->format('l') : '' }}">
                        </div>
                        
                        <div class="col-md-6 form-group">
                            <label>Date</label>
                            <input id="schedule-date" type="date" name="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', request('date')) }}" required>
                            @error('date')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 form-group">
                            <input type="hidden" name="is_recurring" value="0">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is-recurring-checkbox" name="is_recurring" value="1" {{ old('is_recurring', '1') == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is-recurring-checkbox">Repeat weekly</label>
                            </div>
                            <small class="form-text text-muted">Untick to create a one-time single-day add-on schedule.</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Start Time (08:00 - 18:00)</label>
                            <select id="start-time-select" name="start_time" class="form-control @error('start_time') is-invalid @enderror" data-old-time="{{ old('start_time') }}" required>
                                <option value="">Select Time</option>
                                @for($h = 8; $h <= 18; $h++)
                                    @php $time = sprintf('%02d:00', $h); @endphp
                                    <option value="{{ $time }}" {{ old('start_time') == $time ? 'selected' : '' }}>{{ $time }}</option>
                                @endfor
                            </select>
                            <small id="start-time-help" class="form-text text-muted">Disabled times are already occupied or too late for this course's duration.</small>
                            <small id="start-time-warning" class="form-text text-danger"></small>
                            @error('start_time')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 form-group">
                            <label>End Time</label>
                            <div class="form-control-plaintext text-muted">
                                <i class="fas fa-info-circle mr-1"></i> Automatically calculated from the selected course's hours to avoid lab overlap.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        Save Schedule
                    </button>
                    
                    @php
                        $cancelParams = array_filter([
                            'date' => request('date', old('return_date')),
                            'lab_id' => request('lab_id', old('return_lab_id')),
                            'semester_id' => request('semester_id', old('return_semester_id'))
                        ]);
                    @endphp
                    <a href="{{ url('/admin/schedules?' . http_build_query($cancelParams)) }}" class="btn btn-secondary float-right">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

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

    function getOccupiedSlots(date, labId, semesterId, excludeScheduleId = '') {
        if (!date || !labId || !semesterId) {
            return Promise.resolve([]);
        }

        const params = new URLSearchParams({
            date,
            lab_id: labId,
            semester_id: semesterId,
        });

        if (excludeScheduleId) {
            params.append('exclude_schedule_id', excludeScheduleId);
        }

        return fetch(`/admin/schedules/check-occupied?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
            },
        })
        .then(response => response.ok ? response.json() : [])
        .catch(() => []);
    }

    function isOverlapping(candidateStart, candidateEnd, occupiedStart, occupiedEnd) {
        return candidateStart < occupiedEnd && candidateEnd > occupiedStart;
    }

    function renderStartTimeOptions() {
        const courseSelect = document.getElementById('course-select');
        const labSelect = document.getElementById('lab-id-select');
        const semesterSelect = document.querySelector('select[name="semester_id"]');
        const dateInput = document.getElementById('schedule-date');
        const recurringCheckbox = document.getElementById('is-recurring-checkbox');
        const startSelect = document.getElementById('start-time-select');
        const warning = document.getElementById('start-time-warning');

        if (!courseSelect || !labSelect || !semesterSelect || !dateInput || !startSelect || !warning) {
            return;
        }

        const selectedOption = courseSelect.options[courseSelect.selectedIndex];
        if (!selectedOption || selectedOption.value === '') {
            startSelect.innerHTML = '<option value="">Select Course First</option>';
            warning.textContent = 'Please choose a course to see available time slots.';
            return;
        }

        const hours = parseInt(selectedOption.dataset.hours || '1', 10);
        const dateValue = dateInput.value;
        const labId = labSelect.value;
        const semesterId = semesterSelect.value;

        const latestHour = Math.max(8, 18 - hours);
        const selectedValue = startSelect.value;

        getOccupiedSlots(dateValue, labId, semesterId)
            .then(occupiedSlots => {
                const occupiedMinutes = occupiedSlots.map(slot => ({
                    start: parseTimeToMinutes(slot.start_time),
                    end: parseTimeToMinutes(slot.end_time)
                }));

                startSelect.innerHTML = '<option value="">Select Time</option>';
                let availableCount = 0;

                for (let h = 8; h <= latestHour; h++) {
                    const time = (h < 10 ? '0' : '') + h + ':00';
                    const candidateStart = parseTimeToMinutes(time);
                    const candidateEnd = candidateStart + hours * 60;
                    const disabled = occupiedMinutes.some(slot => isOverlapping(candidateStart, candidateEnd, slot.start, slot.end));

                    const option = document.createElement('option');
                    option.value = time;
                    option.textContent = disabled ? `${time} (occupied)` : time;
                    if (disabled) {
                        option.disabled = true;
                        option.className = 'disabled-slot';
                        option.style.cssText = 'background-color:#e9ecef; color:#6c757d; cursor:not-allowed;';
                    } else {
                        availableCount += 1;
                    }
                    if (!disabled && selectedValue === time) {
                        option.selected = true;
                    }
                    startSelect.appendChild(option);
                }

                if (availableCount === 0) {
                    warning.textContent = 'No available start times for this course and lab on the selected date.';
                } else {
                    warning.textContent = 'Blocked times are unavailable because they overlap with existing recurring or one-time schedules.';
                }
            });
    }

    function clearFieldErrors() {
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('.invalid-feedback-ajax').forEach(el => {
            el.remove();
        });
    }

    function displayFieldErrors(errors) {
        Object.keys(errors).forEach(fieldName => {
            const errorMessages = errors[fieldName];
            const input = document.querySelector(`[name="${fieldName}"]`);
            
            if (input) {
                input.classList.add('is-invalid');
                
                errorMessages.forEach(msg => {
                    const feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback-ajax';
                    feedback.textContent = msg;
                    input.parentNode.appendChild(feedback);
                });
            }
        });
    }

    function handleAjaxFormSubmit(event) {
        event.preventDefault();
        clearFieldErrors();

        const form = event.target;
        const formData = new FormData(form);

        fetch(form.action, {
            method: form.method.toUpperCase() === 'POST' ? 'POST' : 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok && response.status === 422) {
                return response.json().then(data => {
                    throw { status: 422, errors: data.errors };
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Close modal if in modal context, or redirect
                const modal = form.closest('.modal');
                if (modal) {
                    const bootstrapModal = typeof bootstrap !== 'undefined' 
                        ? new bootstrap.Modal(modal)
                        : null;
                    if (bootstrapModal) bootstrapModal.hide();
                }

                // Trigger custom event or call reloadMatrix if available
                if (typeof reloadMatrix === 'function') {
                    reloadMatrix();
                }

                // Show success message
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show';
                alertDiv.innerHTML = `
                    ${data.message}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                `;
                form.parentNode.insertBefore(alertDiv, form);
                
                // Auto-dismiss after 3 seconds
                setTimeout(() => alertDiv.remove(), 3000);

                // Optionally redirect
                const returnParams = new URLSearchParams();
                const returnDate = document.querySelector('input[name="return_date"]')?.value;
                const returnLabId = document.querySelector('input[name="return_lab_id"]')?.value;
                const returnSemesterId = document.querySelector('input[name="return_semester_id"]')?.value;

                if (returnDate) returnParams.append('date', returnDate);
                if (returnLabId) returnParams.append('lab_id', returnLabId);
                if (returnSemesterId) returnParams.append('semester_id', returnSemesterId);

                setTimeout(() => {
                    window.location.href = `/admin/schedules${returnParams.toString() ? '?' + returnParams.toString() : ''}`;
                }, 1500);
            }
        })
        .catch(error => {
            if (error.status === 422 && error.errors) {
                displayFieldErrors(error.errors);
            } else {
                console.error('Error:', error);
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                alertDiv.innerHTML = `
                    An error occurred while saving the schedule.
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                `;
                form.parentNode.insertBefore(alertDiv, form);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateScheduleDay();
        renderStartTimeOptions();

        const dateInput = document.getElementById('schedule-date');
        const labSelect = document.getElementById('lab-id-select');
        const courseSelect = document.getElementById('course-select');
        const semesterSelect = document.getElementById('semester-id-select');
        const recurringCheckbox = document.getElementById('is-recurring-checkbox');
        const form = document.getElementById('add-schedule-form');

        if (dateInput) {
            dateInput.addEventListener('change', function () {
                updateScheduleDay();
                renderStartTimeOptions();
            });
        }
        if (labSelect) {
            labSelect.addEventListener('change', renderStartTimeOptions);
        }
        if (courseSelect) {
            courseSelect.addEventListener('change', renderStartTimeOptions);
        }
        if (semesterSelect) {
            semesterSelect.addEventListener('change', renderStartTimeOptions);
        }
        if (recurringCheckbox) {
            recurringCheckbox.addEventListener('change', renderStartTimeOptions);
        }

        if (form) {
            form.addEventListener('submit', handleAjaxFormSubmit);
        }
    });
</script>
@endpush
@endsection