@extends('layouts.admin')

@section('title', 'Semester Management')
@section('page-title', 'Semester Management')
@section('breadcrumb', 'Semesters')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Semester List</h3>
            <a href="{{ url('/admin/semesters/add') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> Add Semester
            </a>
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($semesters as $semester)
                            <tr>
                                <td>{{ $semester->id }}</td>
                                <td>{{ $semester->name }}</td>
                                <td>{{ $semester->start_date }}</td>
                                <td>{{ $semester->end_date }}</td>
                                <td>
                                    <a href="{{ url('/admin/semesters/' . $semester->id . '/edit') }}" class="btn btn-sm btn-info">Edit</a>
                                    <form action="{{ url('/admin/semesters/' . $semester->id) }}" method="POST" class="d-inline-block delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
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