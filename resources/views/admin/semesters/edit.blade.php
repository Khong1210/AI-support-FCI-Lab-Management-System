@extends('layouts.admin')

@section('title', 'Edit Semester')
@section('page-title', 'Edit Semester')
@section('breadcrumb', 'Semesters')

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Edit Semester</h3>
    </div>
    <form action="{{ url('/admin/semesters/' . $semester->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $semester->name) }}" required>
                @error('name')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $semester->start_date) }}" required>
                    @error('start_date')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
                <div class="col-md-6 form-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $semester->end_date) }}" required>
                    @error('end_date')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update Semester</button>
            <a href="{{ url('/admin/semesters') }}" class="btn btn-secondary float-right">Cancel</a>
        </div>
    </form>
</div>
@endsection
