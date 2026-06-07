@extends('layouts.admin')

@section('title', 'Booking Requests')
@section('page-title', 'Booking Requests')
@section('breadcrumb', 'Bookings')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Booking Request List</h3>
            <a href="{{ url('/admin/bookings/add') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> Add Booking
            </a>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ url('/admin/bookings') }}" class="form-inline mb-3">
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
                <a href="{{ url('/admin/bookings') }}" class="btn btn-link text-muted ml-2">Clear</a>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Laboratory</th>
                            <th>Purpose</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $booking->user->username ?? '-' }}</td>
                                <td>{{ $booking->laboratory->lab_name ?? '-' }}</td>
                                <td>{{ $booking->purpose }}</td>
                                <td>{{ $booking->date }}</td>
                                <td>{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}</td>
                                <td>{{ $statuses[$booking->status] ?? 'Unknown' }}</td>
                                <td>
                                    @if($booking->status == 1)
                                        <form action="{{ url('/admin/bookings/' . $booking->id . '/accept') }}" method="POST" class="d-inline-block">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-success">Accept</button>
                                        </form>
                                        <form action="{{ url('/admin/bookings/' . $booking->id . '/reject') }}" method="POST" class="d-inline-block">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-warning">Reject</button>
                                        </form>
                                    @endif
                                    <form action="{{ url('/admin/bookings/' . $booking->id) }}" method="POST" class="d-inline-block delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No booking requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection