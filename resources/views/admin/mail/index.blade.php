@extends('layouts.admin')

@section('title', 'Mail / Inbox')
@section('page-title', 'Mail / Inbox')
@section('breadcrumb', 'Mail')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Pending Booking Requests ({{ $pendingBookings->count() }})</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Lab</th>
                                <th>Date / Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingBookings as $booking)
                                <tr>
                                    <td>{{ $booking->user->username ?? '-' }}</td>
                                    <td>{{ $booking->laboratory->lab_name ?? '-' }}</td>
                                    <td>
                                        {{ $booking->date }}<br>
                                        <small class="text-muted">{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <form action="{{ url('/admin/bookings/' . $booking->id . '/accept') }}" method="POST" class="d-inline-block">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-sm btn-success">Accept</button>
                                            </form>
                                            <form action="{{ url('/admin/bookings/' . $booking->id . '/reject') }}" method="POST" class="d-inline-block" style="margin-left: 2px;">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-sm btn-warning">Reject</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No pending booking requests.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ url('/admin/bookings?status=1') }}" class="btn btn-sm btn-secondary">View All Pending</a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Open Problem Reports ({{ $openReports->count() }})</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Issue</th>
                                <th>Lab</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($openReports as $report)
                                <tr>
                                    <td>
                                        <strong>{{ $report->issues_type }}</strong><br>
                                        <small class="text-muted">{{ Str::limit($report->description, 55) }}</small>
                                    </td>
                                    <td>{{ $report->laboratory->lab_name ?? '-' }}</td>
                                    <td>{{ $report->reported_date }}</td>
                                    <td>
                                        <form action="{{ url('/admin/reports/' . $report->id . '/resolve') }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-success">Resolve</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No open reports.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ url('/admin/reports') }}" class="btn btn-sm btn-secondary">View Reports</a>
            </div>
        </div>
    </div>
</div>
@endsection