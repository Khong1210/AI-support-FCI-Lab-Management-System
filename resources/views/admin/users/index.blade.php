@extends('layouts.admin')

@section('title', 'User Management')
@section('page-title', 'User Management')
@section('breadcrumb', 'Users')

@section('content')
<div class="row mb-3">
    <div class="col-md-6">
        <div class="card card-info card-outline card-tabs">
            <div class="card-header p-0 pt-1 border-bottom-0">
                <ul class="nav nav-tabs" id="userTabs" role="tablist">
                    <li class="nav-item"><a class="nav-link active" id="all-tab" data-toggle="pill" href="#all" role="tab">All Users</a></li>
                    <li class="nav-item"><a class="nav-link" id="search-tab" data-toggle="pill" href="#search" role="tab">Search</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="userTabContent">
                    <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                        <p class="text-muted mb-2">Total Users: <strong>{{ count($users) }}</strong></p>
                    </div>
                    <div class="tab-pane fade" id="search" role="tabpanel" aria-labelledby="search-tab">
                        <form method="GET" action="{{ url('/admin/users') }}" class="form-inline">
                            <div class="form-group mr-2 flex-grow-1">
                                <input type="search" name="search" class="form-control w-100" placeholder="Username or email..." value="{{ request('search') }}">
                            </div>
                            <button type="submit" class="btn btn-primary">Search</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filter by Role</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ url('/admin/users') }}" class="form-inline">
                    <div class="form-group mr-2 flex-grow-1">
                        <select name="role" class="custom-select w-100">
                            <option value="">All roles</option>
                            @foreach ($roles as $key => $label)
                                <option value="{{ $key }}" {{ request('role') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Filter</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users mr-2"></i>Users List</h3>
        <div class="card-tools">
            <a href="{{ url('/admin/users/create') }}" class="btn btn-sm btn-success">
                <i class="fas fa-user-plus mr-1"></i> New User
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        @if (count($users) > 0)
            <table class="table table-hover table-striped">
                <thead class="bg-light">
                    <tr>
                        <th style="width: 10%">ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th style="width: 20%">Role</th>
                        <th style="width: 25%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td><span class="badge badge-secondary">{{ $user->id }}</span></td>
                            <td>
                                <div class="user-profile-link">
                                    <strong>{{ $user->username }}</strong>
                                </div>
                            </td>
                            <td>
                                <i class="fas fa-envelope mr-1 text-muted"></i>
                                <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                            </td>
                            <td>
                                @php
                                    $roleBadges = [
                                        1 => 'danger',    // Admin
                                        2 => 'warning',   // System Manager
                                        3 => 'info',      // Lab Staff
                                        4 => 'primary',   // Lab Committee
                                        5 => 'secondary', // Lecturer
                                    ];
                                    $badge = $roleBadges[$user->user_role] ?? 'light';
                                @endphp
                                <span class="badge badge-{{ $badge }}">{{ $roles[$user->user_role] ?? 'Unknown' }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ url('/admin/users/' . $user->id . '/edit') }}" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ url('/admin/users/' . $user->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-info m-3">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>No users found.</strong> {{ request('search') || request('role') ? 'Try adjusting your filters.' : 'Create a new user to get started.' }}
            </div>
        @endif
    </div>
    <div class="card-footer text-muted">
        <small>Total Users: {{ count($users) }}</small>
    </div>
</div>
@endsection
