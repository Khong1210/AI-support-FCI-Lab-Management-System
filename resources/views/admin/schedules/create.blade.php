@extends('layouts.admin')

@section('title', 'Add Schedule')
@section('page-title', 'Add Schedule')
@section('breadcrumb', 'Add Schedule')

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-plus mr-2"></i>New Schedule</h3>
    </div>
    <form action="{{ url('/admin/schedules') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Laboratory</label>
                    <select name="lab_id" class="form-control" required>
                        <option value="">Select Laboratory</option>
                        @foreach($laboratories as $lab)
                            <option value="{{ $lab->id }}">{{ $lab->lab_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label>Course</label>
                    <select name="course_id" class="form-control" required>
                        <option value="">Select Course</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->course_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Day of Week</label>
                    <select name="day_of_week" class="form-control" required>
                        <option value="">Select Day</option>
                        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                            <option value="{{ $day }}">{{ $day }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 form-group">
                    <label>Date</label>
                    <input type="date" name="date" class="form-control" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 form-group">
                    <label>Start Time (08:00 - 20:00)</label>
                    <select name="start_time" class="form-control @error('start_time') is-invalid @enderror" required>
                        <option value="">Select Time</option>
                        @for($h = 8; $h <= 20; $h++)
                            @foreach(['00', '30'] as $m)
                                @if($h == 20 && $m == '30') @continue @endif
                                @php $time = sprintf('%02d:%s', $h, $m); @endphp
                                <option value="{{ $time }}">{{ $time }}</option>
                            @endforeach
                        @endfor
                    </select>
                    @error('start_time')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
                <div class="col-md-4 form-group">
                    <label>End Time (08:00 - 20:00)</label>
                    <select name="end_time" class="form-control @error('end_time') is-invalid @enderror" required>
                        <option value="">Select Time</option>
                        @for($h = 8; $h <= 20; $h++)
                            @foreach(['00', '30'] as $m)
                                @if($h == 20 && $m == '30') @continue @endif
                                @php $time = sprintf('%02d:%s', $h, $m); @endphp
                                <option value="{{ $time }}">{{ $time }}</option>
                            @endforeach
                        @endfor
                    </select>
                    @error('end_time')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Schedule</button>
            <a href="{{ url('/admin/schedules') }}" class="btn btn-default float-right">Cancel</a>
        </div>
    </form>
</div>
@endsection
