@extends('layouts.admin')

@section('title', 'Add Course')
@section('page-title', 'Add Course')
@section('breadcrumb', 'Add Course')

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-plus mr-2"></i>New Course</h3>
    </div>
    <form action="{{ url('/admin/courses') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Course Name</label>
                    <input type="text" name="course_name" class="form-control" placeholder="Enter course name" required>
                </div>
                <div class="col-md-6 form-group">
                    <label>Lecturer in Charge</label>
                    <select name="user_id" class="form-control">
                        <option value="">None / Unassigned</option>
                        @foreach($lecturers as $user)
                            <option value="{{ $user->id }}">{{ $user->username }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Course description..."></textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Course</button>
            <a href="{{ url('/admin/courses') }}" class="btn btn-default float-right">Cancel</a>
        </div>
    </form>
</div>
@endsection
