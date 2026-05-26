@extends('layouts.admin')

@section('title', 'Add Schedule')
@section('page-title', 'Add Schedule')
@section('breadcrumb', 'Add Schedule')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clock mr-2"></i>Add Schedule</h3>
            </div>
            <form action="{{ url('/admin/schedules') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>Semester</label>
                        <select name="semester_id" class="custom-select @error('semester_id') is-invalid @enderror" required>
                            <option value="">Select Semester</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}" {{ old('semester_id') == $semester->id ? 'selected' : '' }}>{{ $semester->name }} ({{ $semester->start_date }} - {{ $semester->end_date }})</option>
                            @endforeach
                        </select>
                        @error('semester_id')
                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Laboratory</label>
                            <select name="lab_id" class="custom-select @error('lab_id') is-invalid @enderror" required>
                                <option value="">Select Laboratory</option>
                                @foreach($laboratories as $lab)
                                    <option value="{{ $lab->id }}" {{ old('lab_id') == $lab->id ? 'selected' : '' }}>{{ $lab->lab_name }}</option>
                                @endforeach
                            </select>
                            @error('lab_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Course</label>
                            <select name="course_id" class="custom-select @error('course_id') is-invalid @enderror" required>
                                <option value="">Select Course</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>{{ $course->course_name }}</option>
                                @endforeach
                            </select>
                            @error('course_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Day of Week</label>
                            <select name="day_of_week" class="custom-select @error('day_of_week') is-invalid @enderror" required>
                                <option value="">Select Day</option>
                                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                    <option value="{{ $day }}" {{ old('day_of_week') == $day ? 'selected' : '' }}>{{ $day }}</option>
                                @endforeach
                            </select>
                            @error('day_of_week')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date') }}" required>
                            @error('date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Start Time</label>
                            <select name="start_time" class="custom-select @error('start_time') is-invalid @enderror" required>
                                <option value="">Select Time</option>
                                @for($h = 8; $h <= 20; $h++)
                                    @php $time = sprintf('%02d:00', $h); @endphp
                                    <option value="{{ $time }}" {{ old('start_time') == $time ? 'selected' : '' }}>{{ $time }}</option>
                                @endfor
                            </select>
                            @error('start_time')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label>End Time</label>
                            <select name="end_time" class="custom-select @error('end_time') is-invalid @enderror" required>
                                <option value="">Select Time</option>
                                @for($h = 8; $h <= 20; $h++)
                                    @php $time = sprintf('%02d:00', $h); @endphp
                                    <option value="{{ $time }}" {{ old('end_time') == $time ? 'selected' : '' }}>{{ $time }}</option>
                                @endfor
                            </select>
                            @error('end_time')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Save Schedule
                    </button>
                    <a href="{{ url('/admin/schedules') }}" class="btn btn-secondary float-right">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
