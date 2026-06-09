@extends('layouts.admin')

@section('title', 'User Management')
@section('page-title', 'User Management')
@section('breadcrumb', 'Users')

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">User List</h3>
        <a href="{{ url('/admin/users/add') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Add User
        </a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ url('/admin/users') }}" class="mb-3">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label class="small text-muted">Search</label>
                    <input type="search" name="search" class="form-control" placeholder="Search name/email" value="{{ request('search') }}">
                </div>
                
                <div class="col-md-2">
                    <label class="small text-muted">Role</label>
                    <select name="role" class="form-control">
                        <option value="">All roles</option>
                        @foreach ($roles as $key => $label)
                            <option value="{{ $key }}" {{ (string)request('role') === (string)$key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary mr-2">Filter</button>
                    <a href="{{ url('/admin/users') }}" class="btn btn-link">Clear</a>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID 
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'id', 'order' => request('order') == 'asc' ? 'desc' : 'asc']) }}">
                                <i class="fas fa-sort"></i>
                            </a>
                        </th>
                        <th>Username 
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'username', 'order' => request('order') == 'asc' ? 'desc' : 'asc']) }}">
                                <i class="fas fa-sort"></i>
                            </a>
                        </th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge badge-{{ $roleBadges[$user->user_role] ?? 'light' }}">
                                    {{ $roles[$user->user_role] ?? 'Unknown' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ url('/admin/users/' . $user->id . '/edit') }}" class="btn btn-sm btn-info">Edit</a>
                                <form action="{{ url('/admin/users/' . $user->id) }}" method="POST" class="d-inline-block delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
