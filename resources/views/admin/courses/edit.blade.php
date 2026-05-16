@extends('layouts.admin')

@section('title', 'Edit Course')
@section('page-title', 'Edit Course')
@section('breadcrumb', 'Edit Course')

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Edit Course</h3>
    </div>
    <form action="{{ url('/admin/courses/' . $course->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Course Name</label>
                    <input type="text" name="course_name" class="form-control" value="{{ $course->course_name }}" required>
                </div>
                <div class="col-md-6 form-group">
                    <label>Lecturer in Charge</label>
                    <select name="user_id" class="form-control">
                        <option value="">None / Unassigned</option>
                        @foreach($lecturers as $user)
                            <option value="{{ $user->id }}" {{ $course->user_id == $user->id ? 'selected' : '' }}>{{ $user->username }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="4">{{ $course->description }}</textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Update Course</button>
            <a href="{{ url('/admin/courses') }}" class="btn btn-default float-right">Cancel</a>
        </div>
    </form>
</div>
@endsection
