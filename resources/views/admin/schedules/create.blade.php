@extends('layouts.admin')

@section('title', 'Add Schedule')
@section('page-title', 'Add Schedule')
@section('breadcrumb', 'Add Schedule')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" id="top-error-alert" style="display: none;">
            <h5><i class="icon fas fa-ban mr-2"></i> Please fix the errors below:</h5>
            <ul class="mb-0 pl-4" id="top-error-list">
                </ul>
            <button type="button" class="close" id="close-error-alert" aria-hidden="true">×</button>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clock mr-2"></i>Add Schedule</h3>
            </div>

            <div id="top-error-alert" class="alert alert-danger alert-dismissible fade show" style="display: none; margin-bottom: 20px;" role="alert">
                <h5 class="alert-heading"><i class="fas fa-exclamation-triangle mr-2"></i> Form Submission Error</h5>
                <ul id="top-error-list" class="mb-0 pl-3">
                </ul>
                <button type="button" class="close" id="close-error-alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form action="{{ url('/schedules') }}" method="POST" id="add-schedule-form">
                @csrf
                
                <input type="hidden" name="return_lab_id" value="{{ request('lab_id', old('return_lab_id')) }}">
                <input type="hidden" name="return_semester_id" value="{{ request('semester_id', old('return_semester_id')) }}">
                <input type="hidden" name="return_date" value="{{ request('date', old('return_date')) }}">

                <div class="card-body">
                    
                    <div class="form-group">
                        <label>Schedule Type</label>
                        <select id="schedule-type-select" name="schedule_type" class="form-control @error('schedule_type') is-invalid @enderror" required>
                            <option value="enroll" {{ old('schedule_type') == 'enroll' ? 'selected' : '' }}>Enroll</option>
                            <option value="booking" {{ old('schedule_type') == 'booking' ? 'selected' : '' }}>Booking</option>
                            <option value="maintenance" {{ old('schedule_type') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        </select>
                        @error('schedule_type')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

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
                        
                        <div class="col-md-6 form-group" id="course-form-group">
                            <label>Course</label>
                            <select id="course-select" name="course_id" class="form-control @error('course_id') is-invalid @enderror">
                                <option value="">Select Course</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" data-hours="{{ $course->hours }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->course_name }} ({{ $course->hours }} Hours)
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                        </div>

                        <div class="col-md-6 form-group" id="maintenance-user-form-group" style="display: none;">
                            <label>Assign to User (Staff/Technician)</label>
                            <select id="maintenance-user-select" name="user_id" class="form-control @error('user_id') is-invalid @enderror">
                                <option value="">Select User</option>
                                @if(isset($technicians))
                                    @foreach($technicians as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('user_id')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                        </div>
                    </div>

                    <div id="booking-fields-group" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Booked By (Name)</label>
                                <input type="text" id="booking-name-input" name="booked_by" class="form-control @error('booked_by') is-invalid @enderror" value="{{ old('booked_by') }}" placeholder="Enter applicant name">
                                @error('booked_by')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Booking Purpose</label>
                                <input type="text" id="booking-purpose-input" name="purpose" class="form-control @error('purpose') is-invalid @enderror" value="{{ old('purpose') }}" placeholder="e.g., Workshop, Event, Replacement Class">
                                @error('purpose')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                            </div>
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
                        <div class="col-md-6 form-group">
                            <input type="hidden" name="is_recurring" value="0">
                            <div class="custom-control custom-checkbox pt-2">
                                <input type="checkbox" class="custom-control-input" id="is-recurring-checkbox" name="is_recurring" value="1" {{ old('is_recurring', '1') == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is-recurring-checkbox">Repeat weekly</label>
                            </div>
                            <small class="form-text text-muted">Untick to create a one-time single-day schedule.</small>
                        </div>

                        <div class="col-md-6 form-group" id="all-day-checkbox-group" style="display: none;">
                           <label for="all-day-checkbox" class="font-weight-bold text-dark">All Day Maintenance</label>
                            <div class="d-flex align-items-center" style="min-height: 38px;">
                                <div class="custom-control custom-checkbox-color custom-control-inline">
                                    <input type="checkbox" 
                                        name="all_day" 
                                        value="1" 
                                        class="custom-control-input" 
                                        id="all-day-checkbox">
                                    <label class="custom-control-label" for="all-day-checkbox">
                                        Enable full day maintenance (08:00 - 18:00)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Start Time (08:00 - 18:00)</label>
                            <select id="start-time-select" name="start_time" class="form-control" required>
                                <option value="">Select Time</option>
                            </select>
                            <small id="start-time-help" class="form-text text-muted">Disabled times are already occupied.</small>
                            <small id="start-time-warning" class="form-text text-danger"></small>
                            @error('start_time')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 form-group">
                            <label>End Time</label>
                            <select id="end-time-select" class="form-control">
                                <option value="">Select End Time</option>
                                @for($h = 9; $h <= 18; $h++)
                                    @php $time = sprintf('%02d:00', $h); @endphp
                                    <option value="{{ $time }}">{{ $time }}</option>
                                @endfor
                            </select>
                            
                            <input type="hidden" name="end_time" id="end-time-hidden" value="{{ old('end_time') }}">
                            <small class="form-text text-muted" id="end-time-help-text"><i class="fas fa-info-circle mr-1"></i> Automatically calculated.</small>
                            @error('end_time')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
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
                    <a href="{{ url('/schedules?' . http_build_query($cancelParams)) }}" class="btn btn-secondary float-right">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
</div> @endsection
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
    // 在 script 最上方添加这个初始化函数
function initializeTimeOptions() {
    const startTimeSelect = document.getElementById('start-time-select');
    // 如果是第一次加载，先填满基础数据
    if (startTimeSelect.options.length <= 1) {
        for (let h = 8; h <= 18; h++) {
            const time = (h < 10 ? '0' : '') + h + ':00';
            const opt = document.createElement('option');
            opt.value = time;
            opt.textContent = time;
            startTimeSelect.appendChild(opt);
        }
    }
}
    function parseTimeToMinutes(time) {
        if (!time) return 0;
        const parts = time.split(':');
        return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
    }

    function minutesToTime(minutes) {
        const h = Math.floor(minutes / 60);
        return (h < 10 ? '0' : '') + h + ':00';
    }

    function isOverlapping(candStart, candEnd, occupStart, occupEnd) {
        return candStart < occupEnd && candEnd > occupStart;
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

    // ========== 核心：时间冲突检测与下拉框变灰 ==========
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

   function updateStartTimeOptions() {
    const date = document.getElementById('schedule-date').value;
    const labId = document.getElementById('lab-id-select').value;
    const startTimeSelect = document.getElementById('start-time-select');

    if (!date || !labId) return;

    // Fetch occupied slots from backend (queries both schedules + bookings with proper recurring logic)
    fetch(`/schedules/check-occupied-slots?date=${date}&laboratory_id=${labId}`)
        .then(res => res.json())
        .then(occupiedSlots => {
            const currentValue = startTimeSelect.value; // preserve current selection if possible
            startTimeSelect.innerHTML = '<option value="">Select Time</option>';
            
            // Build all time options from 08:00 to 17:00
            for (let h = 8; h <= 17; h++) {
                const time = (h < 10 ? '0' : '') + h + ':00';
                const opt = document.createElement('option');
                opt.value = time;
                opt.textContent = time;
                
                // Check if this hour slot falls within any occupied range
                const isOccupied = occupiedSlots.some(slot => {
                    // slot.start_time and slot.end_time are "HH:mm" format
                    return time >= slot.start_time && time < slot.end_time;
                });
                
                if (isOccupied) {
                    opt.disabled = true;
                    opt.classList.add('disabled-slot');
                    opt.textContent = time + ' (occupied)';
                }
                
                if (time === currentValue && !isOccupied) {
                    opt.selected = true;
                }
                
                startTimeSelect.appendChild(opt);
            }
        })
        .catch(() => {
            // Fallback: just reload available slots
            fetch(`/schedules/get-available-time-slots?date=${date}&laboratory_id=${labId}`)
                .then(res => res.json())
                .then(availableSlots => {
                    startTimeSelect.innerHTML = '<option value="">Select Time</option>';
                    availableSlots.forEach(time => {
                        const opt = document.createElement('option');
                        opt.value = time;
                        opt.textContent = time;
                        startTimeSelect.appendChild(opt);
                    });
                });
        });
}

    // ========== 动态控制不同模式字段显示/隐藏 ==========
    function handleFormFormattingBasedOnType() {
        const typeSelect = document.getElementById('schedule-type-select');
        
        const courseGroup = document.getElementById('course-form-group');
        const maintenanceUserGroup = document.getElementById('maintenance-user-form-group');
        const bookingFieldsGroup = document.getElementById('booking-fields-group');
        const allDayCheckboxGroup = document.getElementById('all-day-checkbox-group');
        
        const courseSelect = document.getElementById('course-select');
        const maintUserSelect = document.getElementById('maintenance-user-select');
        const bookingNameInput = document.getElementById('booking-name-input');
        const bookingPurposeInput = document.getElementById('booking-purpose-input');
        const recurringCheckbox = document.getElementById('is-recurring-checkbox');
        const allDayCheckbox = document.getElementById('all-day-checkbox');
        
        const startSelect = document.getElementById('start-time-select');
        const endSelect = document.getElementById('end-time-select');
        const endHidden = document.getElementById('end-time-hidden');
        const helpText = document.getElementById('end-time-help-text');

        if (!typeSelect || !endSelect) return;

        const currentType = typeSelect.value;

        if (courseGroup) courseGroup.style.display = 'none';
        if (courseSelect) { courseSelect.required = false; }
        
        if (maintenanceUserGroup) maintenanceUserGroup.style.display = 'none';
        if (maintUserSelect) { maintUserSelect.required = false; }
        
        if (bookingFieldsGroup) bookingFieldsGroup.style.display = 'none';
        if (bookingNameInput) { bookingNameInput.required = false; }
        if (bookingPurposeInput) { bookingPurposeInput.required = false; }
        
        if (allDayCheckboxGroup) allDayCheckboxGroup.style.display = 'none';

        if (startSelect) startSelect.classList.remove('readonly-select-override');
        if (endSelect) {
            endSelect.classList.remove('readonly-select-override', 'bg-light');
            endSelect.disabled = false;
        }

        if (currentType === 'enroll') {
            if (courseGroup) courseGroup.style.display = 'block';
            if (courseSelect) courseSelect.required = true;
            if (helpText) helpText.innerHTML = '<i class="fas fa-info-circle mr-1"></i> Automatically calculated from the selected course\'s hours.';
            
            endSelect.classList.add('bg-light');

            if (!courseSelect) return;
            const selectedCourse = courseSelect.options[courseSelect.selectedIndex];
            if (!selectedCourse || !selectedCourse.value) {
                endSelect.value = "";
                endSelect.disabled = true;
                return;
            }

            const hours = parseInt(selectedCourse.dataset.hours || '1', 10);
            const startTime = startSelect.value;
            if (!startTime) return;

            const startMin = parseTimeToMinutes(startTime);
            const endMin = Math.min(18 * 60, startMin + hours * 60);
            const endTime = minutesToTime(endMin);

            endSelect.value = endTime;
            if (endHidden) endHidden.value = endTime;
            endSelect.disabled = true;

        } else if (currentType === 'booking') {
            if (bookingFieldsGroup) bookingFieldsGroup.style.display = 'block';
            if (bookingNameInput) bookingNameInput.required = true;
            if (bookingPurposeInput) bookingPurposeInput.required = true;
            if (helpText) helpText.innerHTML = '<i class="fas fa-info-circle mr-1"></i> Please manually select the expected conclusion time.';

            if (recurringCheckbox) recurringCheckbox.checked = false;

            endSelect.onchange = function() {
                if (endHidden) endHidden.value = this.value;
            };

        } else if (currentType === 'maintenance') {
            if (maintenanceUserGroup) maintenanceUserGroup.style.display = 'block';
            if (maintUserSelect) maintUserSelect.required = true;
            if (allDayCheckboxGroup) allDayCheckboxGroup.style.display = 'block';
            if (helpText) helpText.innerHTML = '<i class="fas fa-info-circle mr-1"></i> Manual selection or overridden by All Day option.';

            if (recurringCheckbox) recurringCheckbox.checked = false;

            // All-Day Maintenance: lock times to 08:00-18:00 and visually disable time selectors
            if (allDayCheckbox && allDayCheckbox.checked) {
                startSelect.value = "08:00";
                endSelect.value = "18:00";
                if (endHidden) endHidden.value = "18:00";

                // Remove HTML5 required constraint — values are auto-filled by JS, not user selection
                startSelect.required = false;
                endSelect.required = false;
                
                startSelect.classList.add('readonly-select-override');
                endSelect.classList.add('readonly-select-override');
            } else {
                // Manual maintenance: reset times and re-enable for user selection
                startSelect.value = '';
                endSelect.value = '';
                if (endHidden) endHidden.value = '';

                // Restore required so normal form validation applies
                startSelect.required = true;
                endSelect.required = true;
                
                startSelect.classList.remove('readonly-select-override');
                endSelect.classList.remove('readonly-select-override');
                
                endSelect.onchange = function() {
                    if (endHidden) endHidden.value = this.value;
                };
            }
        } else {
            // Non-maintenance type: fully reset all-day state and re-enable time selects
            if (allDayCheckbox) allDayCheckbox.checked = false;
            if (startSelect) {
                startSelect.value = '';
                startSelect.required = true;
                startSelect.classList.remove('readonly-select-override');
            }
            if (endSelect) {
                endSelect.value = '';
                endSelect.required = true;
                endSelect.classList.remove('readonly-select-override', 'bg-light');
                endSelect.disabled = false;
            }
            if (endHidden) endHidden.value = '';
        }
    }

    // ========== AJAX 错误控制（绝不改变DOM，不破坏排版） ==========
   function clearFieldErrors() {
    // 移除所有输入框的红框高亮
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    // 强行把页面里所有零散的、会导致排版崩塌的报错文本碎片全部删掉
    document.querySelectorAll('.text-danger, [id^="error-"]').forEach(el => {
        if(el.tagName === 'SPAN' || el.tagName === 'SMALL' || el.textContent.includes('invalid')) {
            el.remove();
        }
    });

    // 隐藏顶部错误框
    const topBox = document.getElementById('top-error-alert');
    const topList = document.getElementById('top-error-list');
    if (topBox) topBox.style.display = 'none';
    if (topList) topList.innerHTML = '';
}

function displayFieldErrors(errors) {
    const topBox = document.getElementById('top-error-alert');
    const topList = document.getElementById('top-error-list');
    
    if (topBox && topList) {
        topBox.style.display = 'block';
        topList.innerHTML = ''; 

        // 遍历后端传过来的所有验证错误
        Object.keys(errors).forEach(field => {
            errors[field].forEach(msg => {
                const li = document.createElement('li');
                // 完美的将 "all_day: The selected all day is invalid" 塞进顶部红框
                li.innerHTML = `<strong>${field.replace('_', ' ').toUpperCase()}:</strong> ${msg}`;
                topList.appendChild(li);
            });
        });
    }
    // 平滑滚动回顶部让导师/你清晰看到错误
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

    function handleAjaxFormSubmit(event) {
        event.preventDefault();
        clearFieldErrors();

        // Submit safeguard: if all-day is checked, ensure required is off so HTML5 validator doesn't block
        const allDayCheckbox = document.getElementById('all-day-checkbox');
        const startSelect = document.getElementById('start-time-select');
        const endSelect = document.getElementById('end-time-select');
        if (allDayCheckbox && allDayCheckbox.checked) {
            if (startSelect) startSelect.required = false;
            if (endSelect) endSelect.required = false;
        }
        
        const form = event.target;
        const currentType = document.getElementById('schedule-type-select').value;
        const formData = new FormData(form);

        if (currentType === 'maintenance') {
            formData.set('purpose', 'Lab Maintenance');
            formData.set('booked_by', 'Technician Staff');
        } else if (currentType === 'enroll') {
            formData.set('purpose', 'Academic Class');
            formData.set('booked_by', 'Lecturer');
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
            if (!resp.ok) {
                throw { status: resp.status, message: 'Form saving error.' };
            }
            return resp.json();
        })
        .then(data => {
            // 🚀 【核心修复点】保存成功后不再刷新当前页，直接跳转回 schedules 列表页
            window.location.href = '/schedules';
        })
        .catch(err => {
            if (err.status === 422 && err.errors) {
                displayFieldErrors(err.errors);
            } else {
                const topBox = document.getElementById('top-error-alert');
                const topList = document.getElementById('top-error-list');
                if (topBox && topList) {
                    topBox.style.display = 'block';
                    topList.innerHTML = `<li><strong>Error:</strong> Time slot conflict detected or invalid option.</li>`;
                }
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }

    // ========== INITIALIZATION ==========
    document.addEventListener('DOMContentLoaded', function () {
        initializeTimeOptions();

        updateScheduleDay();
        handleFormFormattingBasedOnType();
        updateStartTimeOptions();

        const typeSelect = document.getElementById('schedule-type-select');
        const dateInput = document.getElementById('schedule-date');
        const labSelect = document.getElementById('lab-id-select');
        const courseSelect = document.getElementById('course-select');
        const startSelect = document.getElementById('start-time-select');
        const allDayCheckbox = document.getElementById('all-day-checkbox');
        const form = document.getElementById('add-schedule-form');
        const closeAlertBtn = document.getElementById('close-error-alert');

        if (typeSelect) {
            typeSelect.addEventListener('change', function() {
                handleFormFormattingBasedOnType();
                updateStartTimeOptions();
            });
        }
        
        if (dateInput) dateInput.addEventListener('change', function () { updateScheduleDay(); updateStartTimeOptions(); });
        if (labSelect) labSelect.addEventListener('change', updateStartTimeOptions);
        if(document.getElementById('schedule-date').value && document.getElementById('lab-id-select').value) {
        updateStartTimeOptions();
    }
        if (courseSelect) {
            courseSelect.addEventListener('change', function () { 
                updateStartTimeOptions(); 
                handleFormFormattingBasedOnType(); 
            });
        }
        if (startSelect) startSelect.addEventListener('change', handleFormFormattingBasedOnType);
        
        if (allDayCheckbox) {
            allDayCheckbox.addEventListener('change', function() {
                handleFormFormattingBasedOnType();
                updateStartTimeOptions();
            });
        }
        if (closeAlertBtn) {
            closeAlertBtn.addEventListener('click', function() {
                document.getElementById('top-error-alert').style.display = 'none';
            });
        }
       
        if (form) {
            form.addEventListener('submit', handleAjaxFormSubmit);
        }
    });
</script>
@endpush