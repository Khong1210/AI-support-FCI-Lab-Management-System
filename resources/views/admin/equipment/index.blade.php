@extends('layouts.admin')

@section('title', 'Equipment Management')
@section('page-title', 'Equipment Management')
@section('breadcrumb', 'Equipment')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Equipment List</h3>
            <a href="{{ url('/admin/equipment/create') }}" class="btn btn-primary btn-sm">Create Equipment</a>
        </div>
        <div class="card-body">
            <form method="GET" class="form-inline mb-3">
                <div class="form-group mr-2">
                    <input type="search" name="search" class="form-control" placeholder="Search equipment" value="{{ request('search') }}">
                </div>
                <div class="form-group mr-2">
                    <select name="lab_id" class="form-control">
                        <option value="">All Laboratories</option>
                        @foreach($laboratories as $lab)
                            <option value="{{ $lab->id }}" {{ request('lab_id') == $lab->id ? 'selected' : '' }}>{{ $lab->lab_name }}</option>
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
                            <th>Serial</th>
                            <th>Type</th>
                            <th>Lab</th>
                            <th>Purchase Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($equipment as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->equipment_name }}</td>
                                <td>{{ $item->serial_number }}</td>
                                <td>{{ $item->type }}</td>
                                <td>{{ $item->laboratory?->lab_name ?? '-' }}</td>
                                <td>{{ $item->purchase_date }}</td>
                                <td>{{ $statuses[$item->status] ?? 'Unknown' }}</td>
                                <td>
                                    <a href="{{ url('/admin/equipment/' . $item->id . '/edit') }}" class="btn btn-sm btn-info">Edit</a>
                                    <form action="{{ url('/admin/equipment/' . $item->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this equipment?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No equipment found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
