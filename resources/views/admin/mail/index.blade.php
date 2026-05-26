@extends('layouts.admin')

@section('title', 'Mail / Inbox')
@section('page-title', 'Mail / Inbox')
@section('breadcrumb', 'Mail')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-check mr-2"></i>Pending Booking Requests</h3>
                <span class="badge badge-warning">{{ $pendingBookings->count() }}</span>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Lab</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingBookings as $booking)
                            <tr>
                                <td>{{ $booking->user->username ?? '-' }}</td>
                                <td>{{ $booking->laboratory->lab_name ?? '-' }}</td>
                                <td>{{ $booking->date }}<br><small>{{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}</small></td>
                                <td>
                                    <form action="{{ url('/admin/bookings/' . $booking->id . '/accept') }}" method="POST" class="d-inline-block">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                                    </form>
                                    <form action="{{ url('/admin/bookings/' . $booking->id . '/reject') }}" method="POST" class="d-inline-block">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">No pending booking requests.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <a href="{{ url('/admin/bookings?status=1') }}" class="btn btn-sm btn-warning">View All Pending</a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-danger card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exclamation-circle mr-2"></i>Open Problem Reports</h3>
                <span class="badge badge-danger">{{ $openReports->count() }}</span>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
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
                                <td>{{ $report->issues_type }}<br><small>{{ Str::limit($report->description, 55) }}</small></td>
                                <td>{{ $report->laboratory->lab_name ?? '-' }}</td>
                                <td>{{ $report->reported_date }}</td>
                                <td>
                                    <form action="{{ url('/admin/reports/' . $report->id . '/resolve') }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i> Resolve</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">No open reports.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <a href="{{ url('/admin/reports') }}" class="btn btn-sm btn-danger">View Reports</a>
            </div>
        </div>
    </div>
</div>
@endsection
