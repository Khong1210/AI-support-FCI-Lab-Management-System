@extends('layouts.admin')

@section('title', 'Course Management')
@section('page-title', 'Course Management')
@section('breadcrumb', 'Courses')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Course List</h3>
            <a href="{{ url('/admin/courses/add') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> Add Course
            </a>
        </div>
        <div class="card-body">
            <form method="GET" class="form-inline mb-3">
                <div class="form-group mr-2">
                    <input type="search" name="search" class="form-control" placeholder="Search course" value="{{ request('search') }}">
                </div>
                <div class="form-group mr-2">
                    <select name="user_id" class="form-control">
                        <option value="">All Lecturers</option>
                        @foreach($lecturers ?? [] as $lecturer)
                            <option value="{{ $lecturer->id }}" {{ request('user_id') == $lecturer->id ? 'selected' : '' }}>
                                {{ $lecturer->username }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-secondary">Filter</button>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Hours</th>
                            <th>Lecturer in Charge</th>
                            <th>Description</th>
                                @if ((int)auth()->user()->user_role === 1 || (int)auth()->user()->user_role === 2)
                                <th>Actions</th>
                                 @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($courses as $course)
                            <tr>
                                <td>{{ $course->id }}</td>
                                <td>{{ $course->course_name }}</td>
                                <td>{{ $course->hours }}</td>
                                <td>
                                    @if($course->user)
                                        {{ $course->user->username }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ Str::limit($course->description, 50) }}</td>

                                @if ((int)auth()->user()->user_role === 1 || (int)auth()->user()->user_role === 2)
                                    <td>
                                        <a href="{{ url('/admin/courses/' . $course->id . '/edit') }}" class="btn btn-sm btn-info">Edit</a>
                                        <form action="{{ url('/admin/courses/' . $course->id) }}" method="POST" class="d-inline-block delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No courses found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection