@extends('layouts.admin')

@section('title', 'Semester Management')
@section('page-title', 'Semester Management')
@section('breadcrumb', 'Semesters')

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Quick Actions</h3>
            </div>
            <div class="card-body">
                <a href="{{ url('/admin/semesters/create') }}" class="btn btn-success mb-2">
                    <i class="fas fa-plus mr-1"></i> New Semester
                </a>
                <p class="text-muted mb-0">Create, edit, and remove academic semesters used for schedule assignments.</p>
            </div>
        </div>
    </div>
</div>

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list mr-2"></i>Semester List</h3>
    </div>
    <div class="card-body table-responsive p-0">
        @if ($semesters->count())
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th style="width: 10%">ID</th>
                        <th>Name</th>
                        <th style="width: 20%">Start Date</th>
                        <th style="width: 20%">End Date</th>
                        <th style="width: 20%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($semesters as $semester)
                        <tr>
                            <td><span class="badge badge-secondary">{{ $semester->id }}</span></td>
                            <td>{{ $semester->name }}</td>
                            <td>{{ $semester->start_date }}</td>
                            <td>{{ $semester->end_date }}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ url('/admin/semesters/' . $semester->id . '/edit') }}" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ url('/admin/semesters/' . $semester->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this semester?');">
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
                <strong>No semesters found.</strong> Add a semester to start assigning schedules.
            </div>
        @endif
    </div>
    <div class="card-footer text-muted">
        <small>Total Semesters: {{ $semesters->count() }}</small>
    </div>
</div>
@endsection
