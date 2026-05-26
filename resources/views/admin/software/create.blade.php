@extends('layouts.admin')

@section('title', 'Add Software')
@section('page-title', 'Add Software')
@section('breadcrumb', 'Add Software')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cube mr-2"></i>Add Software</h3>
            </div>
            <form action="{{ url('/admin/software') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="software_name">Software Name</label>
                            <input type="text" name="software_name" id="software_name" class="form-control @error('software_name') is-invalid @enderror" value="{{ old('software_name') }}" placeholder="Enter software name" required>
                            @error('software_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="version">Version</label>
                            <input type="text" name="version" id="version" class="form-control @error('version') is-invalid @enderror" value="{{ old('version') }}" placeholder="Enter version" required>
                            @error('version')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="lab_id">Laboratory</label>
                            <select name="lab_id" id="lab_id" class="custom-select @error('lab_id') is-invalid @enderror" required>
                                <option value="">Select laboratory</option>
                                @foreach($laboratories as $lab)
                                    <option value="{{ $lab->id }}" {{ old('lab_id') == $lab->id ? 'selected' : '' }}>{{ $lab->lab_name }}</option>
                                @endforeach
                            </select>
                            @error('lab_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="expiry_date">Expiry Date</label>
                            <input type="date" name="expiry_date" id="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" value="{{ old('expiry_date') }}" required>
                            @error('expiry_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="custom-select @error('status') is-invalid @enderror" required>
                            <option value="">Select status</option>
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" {{ old('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Save Software
                    </button>
                    <a href="{{ url('/admin/software') }}" class="btn btn-secondary float-right">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
