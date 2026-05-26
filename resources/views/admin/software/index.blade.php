@extends('layouts.admin')

@section('title', 'Software Management')
@section('page-title', 'Software Management')
@section('breadcrumb', 'Software')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Software List</h3>
            <a href="{{ url('/admin/software/add') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> Add Software
            </a>
        </div>
        <div class="card-body">
            <form method="GET" class="form-inline mb-3">
                <div class="form-group mr-2">
                    <input type="search" name="search" class="form-control" placeholder="Search software" value="{{ request('search') }}">
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
                            <th>Version</th>
                            <th>Lab</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($software as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->software_name }}</td>
                                <td>{{ $item->version }}</td>
                                <td>{{ $item->laboratory?->lab_name ?? '-' }}</td>
                                <td>{{ $item->expiry_date }}</td>
                                <td>{{ $statuses[$item->status] ?? 'Unknown' }}</td>
                                <td>
                                    <a href="{{ url('/admin/software/' . $item->id . '/edit') }}" class="btn btn-sm btn-info">Edit</a>
                                    <form action="{{ url('/admin/software/' . $item->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this software item?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No software found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
