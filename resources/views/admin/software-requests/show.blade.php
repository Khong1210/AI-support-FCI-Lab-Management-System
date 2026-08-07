@extends('layouts.admin')

@section('title', 'Software Request Details')
@section('page-title', 'Software Request Details')
@section('breadcrumb', 'Software Request #' . $softwareRequest->software_request_id)

@section('content')
<div class="bg-white shadow rounded p-5 max-w-2xl">

    <div class="mb-4">
        <a href="{{ route('software-requests.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3">Request #{{ $softwareRequest->software_request_id }}</h5>

            <table class="table table-borderless mb-0">
                <tr>
                    <th class="ps-0 text-muted" style="width: 140px;">Requester</th>
                    <td>{{ $softwareRequest->user->name ?? 'N/A' }} ({{ $softwareRequest->user->email ?? '' }})</td>
                </tr>
                <tr>
                    <th class="ps-0 text-muted">Software</th>
                    <td>
                        @if($softwareRequest->software)
                            <span class="badge bg-info">{{ $softwareRequest->software->software_name }}</span>
                        @else
                            <span>{{ $softwareRequest->version }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="ps-0 text-muted">Status</th>
                    <td>
                        @if($softwareRequest->isPending())
                            <span class="badge bg-warning text-dark">Pending</span>
                        @elseif($softwareRequest->isApproved())
                            <span class="badge bg-success">Approved</span>
                        @elseif($softwareRequest->isRejected())
                            <span class="badge bg-danger">Rejected</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="ps-0 text-muted">Submitted</th>
                    <td>{{ $softwareRequest->created_at->format('d M Y, h:i A') }}</td>
                </tr>
            </table>
        </div>
    </div>

    @if($softwareRequest->isPending())
    <div class="d-flex gap-2 mt-4">
        <form action="{{ route('software-requests.approve', $softwareRequest->software_request_id) }}" method="POST">
            @csrf
            <button class="btn btn-success">
                <i class="fas fa-check mr-1"></i> Approve & Add to Inventory
            </button>
        </form>
        <form action="{{ route('software-requests.reject', $softwareRequest->software_request_id) }}" method="POST">
            @csrf
            <button class="btn btn-danger">
                <i class="fas fa-times mr-1"></i> Reject
            </button>
        </form>
    </div>
    @endif

</div>
@stop