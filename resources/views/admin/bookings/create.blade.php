@extends('layouts.admin')

@section('title', 'Add Booking')
@section('page-title', 'Add Booking Request')
@section('breadcrumb', 'Add Booking')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-plus mr-2"></i>New Booking Request</h3>
            </div>
            <form action="{{ url('/bookings') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>User</label>
                            <select name="user_id" class="custom-select @error('user_id') is-invalid @enderror" required>
                                <option value="">Select user</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->username }}</option>
                                @endforeach
                            </select>
                            @error('user_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Laboratory</label>
                            <select name="lab_id" class="custom-select @error('lab_id') is-invalid @enderror" required>
                                <option value="">Select laboratory</option>
                                @foreach($laboratories as $lab)
                                    <option value="{{ $lab->id }}" {{ old('lab_id') == $lab->id ? 'selected' : '' }}>{{ $lab->lab_name }}</option>
                                @endforeach
                            </select>
                            @error('lab_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Purpose</label>
                        <input type="text" name="purpose" class="form-control @error('purpose') is-invalid @enderror" value="{{ old('purpose') }}" placeholder="Example: Lab class, workshop, maintenance" required>
                        @error('purpose')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date') }}" required>
                            @error('date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Start Time (08:00 - 18:00)</label>
                            <select name="start_time" class="custom-select @error('start_time') is-invalid @enderror" required>
                                <option value="">Select Time</option>
                                @for($h = 8; $h <= 18; $h++)
                                    @php $time = sprintf('%02d:00', $h); @endphp
                                    <option value="{{ $time }}" {{ old('start_time') == $time ? 'selected' : '' }}>{{ $time }}</option>
                                @endfor
                            </select>
                            @error('start_time')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-4 form-group">
                            <label>End Time (08:00 - 18:00)</label>
                            <select name="end_time" class="custom-select @error('end_time') is-invalid @enderror" required>
                                <option value="">Select Time</option>
                                @for($h = 8; $h <= 18; $h++)
                                    @php $time = sprintf('%02d:00', $h); @endphp
                                    <option value="{{ $time }}" {{ old('end_time') == $time ? 'selected' : '' }}>{{ $time }}</option>
                                @endfor
                            </select>
                            @error('end_time')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Submit Request</button>
                    <a href="{{ url('/bookings') }}" class="btn btn-secondary float-right"><i class="fas fa-times mr-1"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
