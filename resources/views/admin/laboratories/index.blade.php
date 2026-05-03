@extends('layouts.admin')

@section('title', 'Laboratory Management')
@section('page-title', 'Laboratory Management')
@section('breadcrumb', 'Laboratories')

@section('content')
<div class="row mb-3">
    <div class="col-md-6">
        <div class="card card-info card-outline card-tabs">
            <div class="card-header p-0 pt-1 border-bottom-0">
                <ul class="nav nav-tabs" id="labTabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" id="all-labs-tab" data-toggle="pill" href="#all-labs" role="tab">All Laboratories</a></li>
                    <li class="nav-item"><a class="nav-link" id="filter-tab" data-toggle="pill" href="#filter" role="tab">Filter</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="labTabContent">
                    <div class="tab-pane fade show active" id="all-labs" role="tabpanel" aria-labelledby="all-labs-tab">
                        <p class="text-muted mb-2">Total Laboratories: <strong>{{ count($laboratories) }}</strong></p>
                    </div>
                    <div class="tab-pane fade" id="filter" role="tabpanel" aria-labelledby="filter-tab">
                        <form method="GET" action="{{ url('/admin/laboratories') }}">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="custom-select">
                                    <option value="">All statuses</option>
                                    @foreach ($statuses as $key => $label)
                                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Apply Filter</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-flask-vial mr-2"></i>Quick Actions</h3>
            </div>
            <div class="card-body">
                <a href="{{ url('/admin/laboratories/create') }}" class="btn btn-success mb-2">
                    <i class="fas fa-plus mr-1"></i> New Laboratory
                </a>
                <p class="text-muted mt-3">Create and manage laboratories with capacity and status controls.</p>
            </div>
        </div>
    </div>
</div>

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list mr-2"></i>Laboratory List</h3>
    </div>
    <div class="card-body table-responsive p-0">
        @if ($laboratories->count())
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th style="width: 10%">ID</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Capacity</th>                        <th>Equipment</th>
                        <th>Software</th>                        <th style="width: 25%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($laboratories as $laboratory)
                        <tr>
                            <td><span class="badge badge-secondary">{{ $laboratory->id }}</span></td>
                            <td>{{ $laboratory->lab_name }}</td>
                            <td>
                                @php
                                    $statusClasses = [
                                        1 => 'success',
                                        2 => 'warning',
                                        3 => 'danger',
                                    ];
                                @endphp
                                <span class="badge badge-{{ $statusClasses[$laboratory->status] ?? 'secondary' }}">{{ $statuses[$laboratory->status] ?? 'Unknown' }}</span>
                            </td>
                            <td>{{ $laboratory->capacity }} seats</td>
                            <td><span class="badge badge-info">{{ $laboratory->equipments_count }}</span></td>
                            <td><span class="badge badge-warning">{{ $laboratory->softwares_count }}</span></td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ url('/admin/laboratories/' . $laboratory->id . '/edit') }}" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ url('/admin/laboratories/' . $laboratory->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this laboratory?');">
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
                <strong>No laboratories found.</strong> Create a new laboratory to get started.
            </div>
        @endif
    </div>
    <div class="card-footer text-muted">
        <small>Total Laboratories: {{ $laboratories->count() }}</small>
    </div>
</div>
@endsection
