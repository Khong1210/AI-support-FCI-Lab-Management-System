@extends('layouts.admin')

@section('title', 'Course Management')
@section('page-title', 'Course Management')
@section('breadcrumb', 'Courses')

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list mr-2"></i>Course List</h3>
        <div class="card-tools">
            <a href="{{ url('/admin/courses/add') }}" class="btn btn-sm btn-success">
                <i class="fas fa-plus mr-1"></i> Add Course
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        @if ($courses->count())
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th style="width: 10%">ID</th>
                        <th>Name</th>
                        <th>Lecturer in Charge</th>
                        <th>Description</th>
                        <th style="width: 20%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($courses as $course)
                        <tr>
                            <td><span class="badge badge-secondary">{{ $course->id }}</span></td>
                            <td><strong>{{ $course->course_name }}</strong></td>
                            <td>
                                @if($course->user)
                                    <i class="fas fa-user text-info mr-1"></i> {{ $course->user->username }}
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </td>
                            <td>{{ Str::limit($course->description, 50) }}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ url('/admin/courses/' . $course->id . '/edit') }}" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ url('/admin/courses/' . $course->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this course?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-info m-3">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>No courses found.</strong> No records are available yet.
            </div>
        @endif
    </div>
    <div class="card-footer text-muted">
        <small>Total Courses: {{ $courses->count() }}</small>
    </div>
</div>
@endsection
