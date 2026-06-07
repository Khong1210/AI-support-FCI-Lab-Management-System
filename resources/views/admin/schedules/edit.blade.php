@extends('layouts.admin')

@section('title', 'Edit Schedule')
@section('page-title', 'Edit Schedule')
@section('breadcrumb', 'Edit Schedule')

@section('content')
<div class="card card-primary card-outline shadow-sm">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Edit Schedule</h3>
    </div>
    
    <form action="{{ url('/admin/schedules/' . $schedule->id) }}" method="POST" id="edit-schedule-form">
        @csrf
        @method('PUT')
        
        <input type="hidden" name="return_lab_id" value="{{ request('lab_id', $schedule->lab_id) }}">
        <input type="hidden" name="return_semester_id" value="{{ request('semester_id', $schedule->semester_id) }}">
        <input type="hidden" name="return_date" value="{{ request('date', $schedule->date) }}">
        
        <div class="card-body">
            <div class="row">
                <div class="col-md-12 form-group">
                    <label>Semester</label>
                    <select id="semester-id-select" name="semester_id" class="form-control @error('semester_id') is-invalid @enderror" required>
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
                
                <div class="col-md-6 form-group">
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
                <div class="col-md-4 form-group">
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
                    <div class="form-control-plaintext text-muted">
                        <i class="fas fa-info-circle mr-1"></i> Automatically calculated from the selected course's hours to avoid lab overlap.
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-footer d-flex align-items-center">
            <button type="submit" class="btn btn-primary">Update Schedule</button>
            
            @php
                $cancelParams = [
                    'date' => request('date', $schedule->date),
                    'lab_id' => request('lab_id', $schedule->lab_id),
                    'semester_id' => request('semester_id', $schedule->semester_id)
                ];
            @endphp
            <a href="{{ url('/admin/schedules?' . http_build_query($cancelParams)) }}" class="btn btn-secondary ml-2">Cancel</a>
            
            <button type="button" class="btn btn-outline-danger ml-auto" onclick="confirmDeleteSchedule()">
                <i class="fas fa-trash-alt mr-1"></i> Delete Schedule
            </button>
        </div>
    </form>
</div>

<form id="delete-schedule-form" action="{{ url('/admin/schedules/' . $schedule->id) }}" method="POST" style="display: none;">
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
        const courseInput = document.getElementById('course-select');
        const labSelect = document.getElementById('lab-id-select');
        const semesterSelect = document.getElementById('semester-id-select');
        const dateInput = document.getElementById('schedule-date');
        const startSelect = document.getElementById('start-time-select');
        const warning = document.getElementById('start-time-warning');
        const excludeId = document.getElementById('exclude-schedule-id')?.value || '';

        if (!courseInput || !labSelect || !semesterSelect || !dateInput || !startSelect || !warning) {
            return;
        }

        const hours = parseInt(courseInput.dataset.hours || '1', 10);
        const dateValue = dateInput.value;
        const labId = labSelect.value;
        const semesterId = semesterSelect.value;
        const selectedValue = startSelect.value;
        const latestHour = Math.max(8, 18 - hours);

        getOccupiedSlots(dateValue, labId, semesterId, excludeId)
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
                        if (selectedValue === time) {
                            option.selected = true;
                        }
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
            method: 'POST',
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
                // Close modal if in modal context
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
                    An error occurred while updating the schedule.
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                `;
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

    document.addEventListener('DOMContentLoaded', function () {
        updateScheduleDay();
        renderStartTimeOptions();

        const dateInput = document.getElementById('schedule-date');
        const labSelect = document.getElementById('lab-id-select');
        const semesterSelect = document.getElementById('semester-id-select');
        const recurringCheckbox = document.getElementById('is-recurring-checkbox');
        const form = document.getElementById('edit-schedule-form');

        if (dateInput) {
            dateInput.addEventListener('change', function () {
                updateScheduleDay();
                renderStartTimeOptions();
            });
        }
        if (labSelect) {
            labSelect.addEventListener('change', renderStartTimeOptions);
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