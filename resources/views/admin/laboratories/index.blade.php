@extends('layouts.admin')

@section('title', 'Laboratory Management')
@section('page-title', 'Laboratory Management')
@section('breadcrumb', 'Laboratories')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Laboratory List</h3>
            <a href="{{ url('/laboratories/add') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> Add Laboratory
            </a>
        </div>
        <div class="card-body">
            <form method="GET" class="form-inline mb-3">
                <div class="form-group mr-2">
                    <input type="search" name="search" class="form-control" placeholder="Search laboratory" value="{{ request('search') }}">
                </div>
                <div class="form-group mr-2">
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $key => $value)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $value }}</option>
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
                            <th>Capacity</th>
                            <th>Equipment Count</th>
                            <th>Software Count</th>
                            <th>Status</th>
                            @if ((int)auth()->user()->user_role === 1 || (int)auth()->user()->user_role === 2)
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laboratories as $laboratory)
                            <tr>
                                <td>{{ $laboratory->id }}</td>
                                <td>{{ $laboratory->lab_name }}</td>
                                <td>{{ $laboratory->capacity }} seats</td>
                                <td>{{ $laboratory->equipments_count }}</td>
                                <td>{{ $laboratory->softwares_count }}</td>
                                <td>{{ $statuses[$laboratory->status] ?? 'Unknown' }}</td>
                                @if ((int)auth()->user()->user_role === 1 || (int)auth()->user()->user_role === 2)
                                    <td>
                                        <a href="{{ url('/laboratories/' . $laboratory->id . '/edit') }}" class="btn btn-sm btn-info">Edit</a>
                                    @if ((int)auth()->user()->user_role === 1)
                                        <form action="{{ url('/laboratories/' . $laboratory->id) }}" method="POST" class="d-inline-block delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    @endif
                                    </td> 
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No laboratories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection