@extends('layouts.admin')

@section('title', 'Booking Requests')
@section('page-title', 'Booking Requests')
@section('breadcrumb', 'Booking Requests')

@push('styles')
<style>
    /* ── Status Badges ── */
    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.3px;
    }
    .badge-pending  { background: #fef3c7; color: #92400e; border: 1px solid #f59e0b; }
    .badge-approved { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
    .badge-rejected { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }

    /* ── Reject Modal ── */
    .modal-reason textarea { resize: vertical; min-height: 90px; }

    /* ── Filters Bar ── */
    .filter-bar {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
    }

    /* ── Stats Row ── */
    .stat-chip {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .stat-chip.pending  { background: #fef3c7; color: #92400e; }
    .stat-chip.approved { background: #d1fae5; color: #065f46; }
    .stat-chip.rejected { background: #fee2e2; color: #991b1b; }
    .stat-chip.total    { background: #ede9fe; color: #5b21b6; }

    /* ── Table ── */
    .table th { font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; font-weight: 600; }
    .table td { vertical-align: middle; font-size: 0.875rem; }
    .time-pill {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        color: #0369a1;
        padding: 2px 9px;
        border-radius: 5px;
        font-size: 0.8rem;
        font-family: monospace;
    }

    /* ── Action Buttons ── */
    .btn-approve { background: #059669; color: #fff; border: none; border-radius: 6px; padding: 4px 12px; font-size: 0.8rem; font-weight: 600; }
    .btn-approve:hover { background: #047857; color: #fff; }
    .btn-reject  { background: #dc2626; color: #fff; border: none; border-radius: 6px; padding: 4px 12px; font-size: 0.8rem; font-weight: 600; }
    .btn-reject:hover  { background: #b91c1c; color: #fff; }
</style>
@endpush

@section('content')

{{-- ── Error Flash ── --}}
@if(session('error'))
    <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
        <i class="fas fa-exclamation-circle"></i>
        {{ session('error') }}
    </div>
@endif

{{-- ── Stats Summary Row ── --}}
@php
    $totalCount    = $bookingRequests->count();
    $pendingCount  = $bookingRequests->where('status', 'pending')->count();
    $approvedCount = $bookingRequests->where('status', 'approved')->count();
    $rejectedCount = $bookingRequests->where('status', 'rejected')->count();
@endphp
<div class="d-flex flex-wrap gap-2 mb-3">
    <span class="stat-chip total"><i class="fas fa-list"></i> Total: {{ $totalCount }}</span>
    <span class="stat-chip pending"><i class="fas fa-hourglass-half"></i> Pending: {{ $pendingCount }}</span>
    <span class="stat-chip approved"><i class="fas fa-check-circle"></i> Approved: {{ $approvedCount }}</span>
    <span class="stat-chip rejected"><i class="fas fa-times-circle"></i> Rejected: {{ $rejectedCount }}</span>
</div>

{{-- ── Filter Bar ── --}}
<div class="filter-bar">
    <form method="GET" action="{{ url('/admin/booking-requests') }}" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label mb-1 small fw-semibold text-muted">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label mb-1 small fw-semibold text-muted">Laboratory</label>
            <select name="lab_id" class="form-select form-select-sm">
                <option value="">All Laboratories</option>
                @foreach($laboratories as $lab)
                    <option value="{{ $lab->id }}" {{ request('lab_id') == $lab->id ? 'selected' : '' }}>
                        {{ $lab->lab_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-filter me-1"></i>Filter
            </button>
            <a href="{{ url('/admin/booking-requests') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-redo me-1"></i>Clear
            </a>
            <a href="{{ url('/admin/booking-requests/create') }}" class="btn btn-outline-info btn-sm ms-auto">
                <i class="fas fa-plus me-1"></i>Add Request
            </a>
        </div>
    </form>
</div>

{{-- ── Booking Requests Balanced View Grid ── --}}
<div class="card shadow-sm border-0" style="border-radius:12px; overflow:hidden;">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 px-4">
        <h5 class="mb-0 fw-semibold" style="font-size:0.95rem;">
            <i class="fas fa-calendar-check me-2 text-primary"></i>Booking Request List
        </h5>
        <span class="badge bg-primary rounded-pill">{{ $bookingRequests->count() }} records</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">#</th>
                    <th>Requester</th>
                    <th>Laboratory</th>
                    <th>Date &amp; Time</th>
                    <th>Reason</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    @if(in_array((int)auth()->user()->user_role, [1, 2]))
                        <th class="text-center">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($bookingRequests as $req)
                    <tr>
                        <td class="ps-4 text-muted">{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-semibold" style="font-size:0.875rem;">{{ $req->user->username ?? 'System User' }}</div>
                            <div class="text-muted" style="font-size:0.75rem;">{{ $req->user->email ?? 'N/A' }}</div>
                        </td>
                        <td>
                            <span class="fw-medium">{{ $req->laboratory->lab_name ?? '—' }}</span>
                        </td>
                        <td>
                            <div class="fw-medium">{{ \Carbon\Carbon::parse($req->date)->format('d M Y') }}</div>
                            <span class="time-pill">
                                {{ substr($req->start_time, 0, 5) }} – {{ substr($req->end_time, 0, 5) }}
                            </span>
                        </td>
                        <td>
                            <span class="text-muted" title="{{ $req->reason }}" style="cursor:help;">
                                {{ \Str::limit($req->reason, 50) }}
                            </span>
                            @if($req->rejection_reason)
                                <br><small class="text-danger">
                                    <i class="fas fa-comment-slash me-1"></i>{{ \Str::limit($req->rejection_reason, 45) }}
                                </small>
                            @endif
                        </td>
                        <td class="text-muted" style="font-size:0.8rem;">
                            {{ $req->created_at->format('d M Y') }}<br>
                            {{ $req->created_at->format('H:i') }}
                        </td>
                        <td>
                            <span class="badge-status badge-{{ $req->status }}">
                                @if($req->status === 'pending')
                                    <i class="fas fa-hourglass-half"></i> Pending
                                @elseif($req->status === 'approved')
                                    <i class="fas fa-check-circle"></i> Approved
                                @else
                                    <i class="fas fa-times-circle"></i> Rejected
                                @endif
                            </span>
                        </td>
                        
                        @if(in_array((int)auth()->user()->user_role, [1, 2]))
                            <td class="text-center">
                                @if($req->status === 'pending')
                                    {{-- Approve Action Trigger --}}
                                    <form action="{{ url('/admin/booking-requests/' . $req->id . '/approve') }}" method="POST" class="d-inline-block approve-form">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn-approve me-1" title="Approve this request">
                                            <i class="fas fa-check me-1"></i>Approve
                                        </button>
                                    </form>

                                    {{-- Reject Action Trigger --}}
                                    <form action="{{ url('/admin/booking-requests/' . $req->id . '/reject') }}" method="POST" class="d-inline-block reject-form">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Reject this request" onclick="return confirm('Are you sure you want to reject this request?')">
                                            <i class="fas fa-times me-1"></i>Reject
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ in_array((int)auth()->user()->user_role, [1, 2]) ? '8' : '7' }}" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                            No booking requests found matching your filter options.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Reject Modal ── --}}
<div class="modal fade modal-reason" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:14px; overflow:hidden;">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="fas fa-times-circle me-2"></i>Reject Booking Request
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-3" style="font-size:0.85rem;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            Rejecting request from <strong id="modal-requester-name">—</strong>
                            for <strong id="modal-lab-name">—</strong> on <strong id="modal-date">—</strong>.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="rejection_reason">
                            Rejection Reason
                            <span class="text-muted fw-normal">(optional — will be logged in the notification)</span>
                        </label>
                        <textarea name="rejection_reason" id="rejection_reason"
                                  class="form-control"
                                  placeholder="e.g. The requested time slot conflicts with a scheduled class. Please choose another time.">The requested time slot is not available. Please choose another date or time.</textarea>
                    </div>

                    <div class="bg-light border rounded p-3" style="font-size:0.78rem; color:#6b7280;">
                        <i class="fas fa-info-circle me-1 text-primary"></i>
                        <strong>Demo Mode:</strong> A rejection notification will be written to
                        <code>storage/logs/laravel.log</code> with the full email content.
                        Configure SMTP in <code>.env</code> to activate real email delivery.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="fas fa-arrow-left me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-times me-1"></i>Confirm Reject &amp; Notify
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function () {
    // ── Populate Reject Modal with row data ──
    $('#rejectModal').on('show.bs.modal', function (event) {
        const btn = $(event.relatedTarget);
        const reqId     = btn.data('request-id');
        const requester = btn.data('requester');
        const lab       = btn.data('lab');
        const date      = btn.data('date');

        $('#modal-requester-name').text(requester);
        $('#modal-lab-name').text(lab);
        $('#modal-date').text(date);

        // Set the form action dynamically
        $('#rejectForm').attr('action', '/admin/booking-requests/' + reqId + '/reject');
    });

    // ── SweetAlert2 confirm for Approve ──
    document.querySelectorAll('.approve-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Approve this request?',
                text: 'This will create a booking and add it to the schedule.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Approve',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) form.submit();
            });
        });
    });
});
</script>
@endpush
