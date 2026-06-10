@extends('layouts.admin')

@section('title', 'Mail / Announcements')
@section('page-title', 'Mail / Announcements')
@section('breadcrumb', 'Mail')

@push('styles')
<style>
    .mail-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        margin-bottom: 1rem;
        border: 1px solid #e5e7eb;
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .mail-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    }
    .mail-card.unread {
        border-left: 4px solid var(--brand-primary);
        background: #f8fafc;
    }
    .mail-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .mail-subject {
        font-weight: 600;
        color: #1e293b;
        font-size: 1.05rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .mail-date {
        font-size: 0.8rem;
        color: #64748b;
    }
    .mail-body {
        padding: 1.25rem;
        color: #334155;
        font-size: 0.9rem;
        white-space: pre-line; /* Renders \n as line breaks */
    }
    .badge-type {
        font-size: 0.7rem;
        padding: 3px 8px;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
    }
    .type-booking_status { background: #dbeafe; color: #1e40af; }
    .type-maintenance    { background: #fef9c3; color: #854d0e; }
    .type-general        { background: #f1f5f9; color: #475569; }
</style>
@endpush

@section('content')

<div class="row">
    <div class="col-12">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-inbox me-2 text-primary"></i>Your Notifications
            </h5>
            <span class="text-muted small">
                All unread notifications are automatically marked as read.
            </span>
        </div>

        @forelse($mails as $mail)
            <div class="mail-card {{ !$mail->is_read ? 'unread' : '' }}">
                <div class="mail-header">
                    <div class="mail-subject">
                        @if(!$mail->is_read)
                            <span class="badge bg-primary rounded-circle p-1 me-1" title="New message" style="width:10px;height:10px;display:inline-block;"></span>
                        @endif
                        {{ $mail->subject }}
                        
                        <span class="badge-type type-{{ $mail->type }}">
                            {{ str_replace('_', ' ', $mail->type) }}
                        </span>
                    </div>
                    <div class="mail-date">
                        <i class="far fa-clock me-1"></i>
                        {{ $mail->created_at->format('d M Y, H:i') }}
                        ({{ $mail->created_at->diffForHumans() }})
                    </div>
                </div>
                <div class="mail-body">{{ $mail->body }}</div>
            </div>
        @empty
            <div class="text-center py-5">
                <i class="fas fa-envelope-open-text fa-3x text-muted mb-3 opacity-50"></i>
                <h5 class="text-muted">No announcements yet.</h5>
                <p class="text-muted small">System notifications and booking updates will appear here.</p>
            </div>
        @endforelse

    </div>
</div>

@endsection