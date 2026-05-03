@extends('layouts.admin')

@section('title', 'Edit User')
@section('page-title', 'Edit User')
@section('breadcrumb', 'Users')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-edit mr-2"></i>Edit User: {{ $user->username }}</h3>
            </div>
            <form action="{{ url('/admin/users/' . $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Validation Error!</strong> Please fix the errors below.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <ul class="mt-2 mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="username">Username <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                            <input type="text" name="username" id="username" class="form-control {{ $errors->has('username') ? 'is-invalid' : '' }}" value="{{ old('username', $user->username) }}" placeholder="Enter username" required>
                        </div>
                        @error('username') <small class="form-text text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            </div>
                            <input type="email" name="email" id="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ old('email', $user->email) }}" placeholder="Enter email" required>
                        </div>
                        @error('email') <small class="form-text text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password <span class="text-muted">(Leave blank to keep current password)</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            </div>
                            <input type="password" name="password" id="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" placeholder="Enter new password (min 8 characters)">
                        </div>
                        @error('password') <small class="form-text text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label for="user_role">Role <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-shield-alt"></i></span>
                            </div>
                            <select name="user_role" id="user_role" class="custom-select {{ $errors->has('user_role') ? 'is-invalid' : '' }}" required>
                                <option value="">Select a role</option>
                                @foreach ($roles as $key => $label)
                                    <option value="{{ $key }}" {{ old('user_role', $user->user_role) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('user_role') <small class="form-text text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>User ID:</strong> {{ $user->id }} | <strong>Created:</strong> {{ $user->created_at->format('M d, Y H:i') }}
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                    <a href="{{ url('/admin/users') }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
