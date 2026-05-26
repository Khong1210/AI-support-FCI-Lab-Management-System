@extends('layouts.admin')

@section('title', 'Add Laboratory')
@section('page-title', 'Add Laboratory')
@section('breadcrumb', 'Add Laboratory')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus mr-2"></i>Add Laboratory</h3>
            </div>
            <form action="{{ url('/admin/laboratories') }}" method="POST">
                @csrf
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
                        <label for="lab_name">Laboratory Name</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-flask"></i></span>
                            </div>
                            <input type="text" name="lab_name" id="lab_name" class="form-control {{ $errors->has('lab_name') ? 'is-invalid' : '' }}" value="{{ old('lab_name') }}" placeholder="Enter laboratory name" required>
                        </div>
                        @error('lab_name') <small class="form-text text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label for="capacity">Capacity</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-users"></i></span>
                            </div>
                            <input type="number" name="capacity" id="capacity" class="form-control {{ $errors->has('capacity') ? 'is-invalid' : '' }}" value="{{ old('capacity') }}" min="1" placeholder="Enter capacity" required>
                        </div>
                        @error('capacity') <small class="form-text text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                            </div>
                            <select name="status" id="status" class="custom-select {{ $errors->has('status') ? 'is-invalid' : '' }}" required>
                                <option value="">Select status</option>
                                @foreach ($statuses as $key => $label)
                                    <option value="{{ $key }}" {{ old('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('status') <small class="form-text text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-1"></i> Add Laboratory
                    </button>
                    <a href="{{ url('/admin/laboratories') }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
