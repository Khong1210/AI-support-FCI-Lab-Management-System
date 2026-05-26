@extends('layouts.admin')

@section('title', 'Booking Requests')
@section('page-title', 'Booking Requests')
@section('breadcrumb', 'Bookings')

@section('content')
<div class="card card-info card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filters</h3>
        <div class="card-tools">
            <a href="{{ url('/admin/bookings/add') }}" class="btn btn-sm btn-success">
                <i class="fas fa-plus mr-1"></i> Add Booking
            </a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ url('/admin/bookings') }}" class="form-inline">
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
            <a href="{{ url('/admin/bookings') }}" class="btn btn-secondary ml-2">Clear</a>
        </form>
    </div>
</div>

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-calendar-check mr-2"></i>Booking Request List</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Laboratory</th>
                    <th>Purpose</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th style="width: 24%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    @php
                        $badge = [1 => 'warning', 2 => 'success', 3 => 'danger', 4 => 'secondary'][$booking->status] ?? 'secondary';
                    @endphp
                    <tr>
                        <td><span class="badge badge-secondary">{{ $booking->id }}</span></td>
                        <td>{{ $booking->user->username ?? '-' }}</td>
                        <td>{{ $booking->laboratory->lab_name ?? '-' }}</td>
                        <td>{{ $booking->purpose }}</td>
                        <td>{{ $booking->date }}</td>
                        <td>{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}</td>
                        <td><span class="badge badge-{{ $badge }}">{{ $statuses[$booking->status] ?? 'Unknown' }}</span></td>
                        <td>
                            @if($booking->status == 1)
                                <form action="{{ url('/admin/bookings/' . $booking->id . '/accept') }}" method="POST" class="d-inline-block">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i> Accept</button>
                                </form>
                                <form action="{{ url('/admin/bookings/' . $booking->id . '/reject') }}" method="POST" class="d-inline-block">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-warning"><i class="fas fa-times"></i> Reject</button>
                                </form>
                            @endif
                            <form action="{{ url('/admin/bookings/' . $booking->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this booking request?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
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
@endsection
