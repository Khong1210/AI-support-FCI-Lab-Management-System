@extends('layouts.admin')

@section('title', 'Semester Management')
@section('page-title', 'Semester Management')
@section('breadcrumb', 'Semesters')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Semester List</h3>
            @auth @if(in_array((int)auth()->user()->user_role, [1, 2]))
            <a href="{{ url('/management/semesters/add') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> Add Semester
            </a>
            @endif @endauth
        </div>
        <div class="card-body">
            <form method="GET" class="form-inline mb-3">
                <div class="form-group mr-2">
                    <input type="search" name="search" class="form-control" placeholder="Search semester" value="{{ request('search') }}">
                </div>
                <button class="btn btn-secondary">Filter</button>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            @if ((int)auth()->user()->user_role === 1 || (int)auth()->user()->user_role === 2)
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($semesters as $semester)
                            <tr>
                                <td>{{ $semester->id }}</td>
                                <td>{{ $semester->name }}</td>
                                <td>{{ $semester->start_date }}</td>
                                <td>{{ $semester->end_date }}</td>
                                @if ((int)auth()->user()->user_role === 1 || (int)auth()->user()->user_role === 2)
                                    <td>
                                        <a href="{{ url('/management/semesters/' . $semester->id . '/edit') }}" class="btn btn-sm btn-info">Edit</a>
                                        <form action="{{ url('/management/semesters/' . $semester->id) }}" method="POST" class="d-inline-block delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No semesters found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection