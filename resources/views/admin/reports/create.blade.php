@extends('layouts.admin')

@section('title', 'Report Problem')
@section('page-title', 'Report Problem')
@section('breadcrumb', 'Report Problem')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-2"></i>New Problem Report</h3>
            </div>
            <form action="{{ url('/admin/reports') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Reporter</label>
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
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Issue Type</label>
                            <input type="text" name="issues_type" class="form-control @error('issues_type') is-invalid @enderror" value="{{ old('issues_type') }}" placeholder="Example: Equipment, Software, Network" required>
                            @error('issues_type')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Reported Date</label>
                            <input type="date" name="reported_date" class="form-control @error('reported_date') is-invalid @enderror" value="{{ old('reported_date', now()->format('Y-m-d')) }}" required>
                            @error('reported_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="5" class="form-control @error('description') is-invalid @enderror" placeholder="Describe the problem clearly" required>{{ old('description') }}</textarea>
                        @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Submit Report</button>
                    <a href="{{ url('/admin/reports') }}" class="btn btn-secondary float-right"><i class="fas fa-times mr-1"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
