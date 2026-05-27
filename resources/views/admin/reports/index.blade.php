@extends('layouts.admin')

@section('title', 'Problem Reports')
@section('page-title', 'Problem Reports')
@section('breadcrumb', 'Reports')

@section('content')
<div class="card card-info card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filters</h3>
        <div class="card-tools">
            <a href="{{ url('/admin/reports/add') }}" class="btn btn-sm btn-success">
                <i class="fas fa-plus mr-1"></i> Report Problem
            </a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ url('/admin/reports') }}" class="form-inline">
            <select name="status" class="custom-select mr-2">
                <option value="">All statuses</option>
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="lab_id" class="custom-select mr-2">
                <option value="">All laboratories</option>
                @foreach($laboratories as $lab)
                    <option value="{{ $lab->id }}" {{ request('lab_id') == $lab->id ? 'selected' : '' }}>{{ $lab->lab_name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-info"><i class="fas fa-search mr-1"></i> Filter</button>
            <a href="{{ url('/admin/reports') }}" class="btn btn-secondary ml-2">Clear</a>
        </form>
    </div>
</div>

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-alt mr-2"></i>Report List</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Laboratory</th>
                    <th>Issue Type</th>
                    <th>Description</th>
                    <th>Reported Date</th>
                    <th>Status</th>
                    <th style="width: 24%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    @php
                        $badge = [1 => 'danger', 2 => 'warning', 3 => 'success'][$report->status] ?? 'secondary';
                    @endphp
                    <tr>
                        <td><span class="badge badge-secondary">{{ $report->id }}</span></td>
                        <td>{{ $report->user->username ?? '-' }}</td>
                        <td>{{ $report->laboratory->lab_name ?? '-' }}</td>
                        <td>{{ $report->issues_type }}</td>
                        <td>{{ Str::limit($report->description, 80) }}</td>
                        <td>{{ $report->reported_date }}</td>
                        <td><span class="badge badge-{{ $badge }}">{{ $statuses[$report->status] ?? 'Unknown' }}</span></td>
                        <td>
                            @if($report->status == 1)
                                <form action="{{ url('/admin/reports/' . $report->id . '/progress') }}" method="POST" class="d-inline-block">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-warning"><i class="fas fa-tools"></i> Progress</button>
                                </form>
                            @endif
                            @if($report->status != 3)
                                <form action="{{ url('/admin/reports/' . $report->id . '/resolve') }}" method="POST" class="d-inline-block">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i> Resolve</button>
                                </form>
                            @endif
                            <form action="{{ url('/admin/reports/' . $report->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this report?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
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
@endsection
