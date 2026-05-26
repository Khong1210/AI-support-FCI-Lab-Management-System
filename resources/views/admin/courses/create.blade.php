@extends('layouts.admin')

@section('title', 'Add Course')
@section('page-title', 'Add Course')
@section('breadcrumb', 'Add Course')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-book mr-2"></i>Add Course</h3>
            </div>
            <form action="{{ url('/admin/courses') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Course Name</label>
                            <input type="text" name="course_name" class="form-control @error('course_name') is-invalid @enderror" value="{{ old('course_name') }}" placeholder="Enter course name" required>
                            @error('course_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Lecturer in Charge</label>
                            <select name="user_id" class="custom-select @error('user_id') is-invalid @enderror">
                                <option value="">None / Unassigned</option>
                                @foreach($lecturers as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->username }}</option>
                                @endforeach
                            </select>
                            @error('user_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4" placeholder="Course description">{{ old('description') }}</textarea>
                        @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Save Course
                    </button>
                    <a href="{{ url('/admin/courses') }}" class="btn btn-secondary float-right">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
