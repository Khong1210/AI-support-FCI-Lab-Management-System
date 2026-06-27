@extends('layouts.admin')

@section('title', 'Renew Password')

@section('page-title', 'Renew Password')

@section('breadcrumb', 'Renew Password')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-lock mr-2"></i> Change Your Password
                </h3>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('password.update') }}" method="POST">
                    @csrf

                    <div class="form-group mb-3">
                        <label for="current_password" class="font-weight-bold">Current Password</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                            </div>
                            <input type="password" name="current_password" id="current_password"
                                   class="form-control" placeholder="Enter current password" required>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="new_password" class="font-weight-bold">New Password</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            </div>
                            <input type="password" name="new_password" id="new_password"
                                   class="form-control" placeholder="Enter new password (min. 8 characters)" required minlength="8">
                        </div>
                        <small class="text-muted">Password must be at least 8 characters.</small>
                    </div>

                    <div class="form-group mb-4">
                        <label for="new_password_confirmation" class="font-weight-bold">Confirm New Password</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            </div>
                            <input type="password" name="new_password_confirmation" id="new_password_confirmation"
                                   class="form-control" placeholder="Confirm new password" required minlength="8">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection