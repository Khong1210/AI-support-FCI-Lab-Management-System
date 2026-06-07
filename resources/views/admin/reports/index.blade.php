@extends('layouts.admin')

@section('title', 'Problem Reports')
@section('page-title', 'Problem Reports')
@section('breadcrumb', 'Reports')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Report List</h3>
            <a href="{{ url('/admin/reports/add') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> Report Problem
            </a>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ url('/admin/reports') }}" class="form-inline mb-3">
                <div class="form-group mr-2">
                    <select name="status" class="form-control">
                        <option value="">All statuses</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mr-2">
                    <select name="lab_id" class="form-control">
                        <option value="">All laboratories</option>
                        @foreach($laboratories as $lab)
                            <option value="{{ $lab->id }}" {{ request('lab_id') == $lab->id ? 'selected' : '' }}>{{ $lab->lab_name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary">Filter</button>
                <a href="{{ url('/admin/reports') }}" class="btn btn-link text-muted ml-2">Clear</a>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Laboratory</th>
                            <th>Issue Type</th>
                            <th>Description</th>
                            <th>Reported Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $report->user->username ?? '-' }}</td>
                                <td>{{ $report->laboratory->lab_name ?? '-' }}</td>
                                <td>{{ $report->issues_type }}</td>
                                <td>{{ Str::limit($report->description, 80) }}</td>
                                <td>{{ $report->reported_date }}</td>
                                <td>{{ $statuses[$report->status] ?? 'Unknown' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        @if($report->status == 1)
                                            <form action="{{ url('/admin/reports/' . $report->id . '/progress') }}" method="POST" class="d-inline-block">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-sm btn-warning">Progress</button>
                                            </form>
                                        @endif
                                        @if($report->status != 3)
                                            <form action="{{ url('/admin/reports/' . $report->id . '/resolve') }}" method="POST" class="d-inline-block" style="margin-left: 2px;">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-sm btn-success">Resolve</button>
                                            </form>
                                        @endif
                                        <form action="{{ url('/admin/reports/' . $report->id) }}" method="POST" class="d-inline-block delete-form" style="margin-left: 2px;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No problem reports found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection