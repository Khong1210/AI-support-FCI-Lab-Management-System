@extends('layouts.admin')

@section('title', 'Software Requests')
@section('page-title', 'Software Requests')
@section('breadcrumb', 'Software Requests')

@section('content')
<div class="bg-white shadow rounded p-5">

    <div class="flex items-center justify-between mb-4">
        <h4 class="text-lg font-semibold text-gray-700">
            <i class="fas fa-clipboard-check mr-2 text-blue-500"></i> Software Approval Queue
        </h4>
        <a href="{{ route('software-requests.create') }}" class="btn btn-primary text-sm px-4 py-1.5">
            <i class="fas fa-paper-plane mr-1"></i> New Request
        </a>
    </div>

    @if($requests->isEmpty())
        <p class="text-gray-500 text-center py-6">No software requests yet.</p>
    @else
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Requester</th>
                        <th>Software</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                    <tr>
                        <td>{{ $req->software_request_id }}</td>
                        <td>
                            <div class="fw-semibold">{{ $req->user->name ?? 'N/A' }}</div>
                            <small class="text-muted">{{ $req->user->email ?? '' }}</small>
                        </td>
                        <td>
                            @if($req->software)
                                <span class="badge bg-info">{{ $req->software->software_name }}</span>
                            @else
                                <span class="text-muted">{{ $req->version }}</span>
                            @endif
                        </td>
                        <td>
                            @if($req->isPending())
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($req->isApproved())
                                <span class="badge bg-success">Approved</span>
                            @elseif($req->isRejected())
                                <span class="badge bg-danger">Rejected</span>
                            @endif
                        </td>
                        <td><small>{{ $req->created_at->format('d M Y, h:i A') }}</small></td>
                        <td>
                            <div class="d-flex justify-content-center gap-1">
                                @if($req->isPending())
                                    <form action="{{ route('software-requests.approve', $req->software_request_id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success" title="Approve & Add to Inventory">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('software-requests.reject', $req->software_request_id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-danger" title="Reject">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted" style="font-size: 12px;">— No actions —</span>
                                @endif

                                <form action="{{ route('software-requests.destroy', $req->software_request_id) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-secondary" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</div>
@stop