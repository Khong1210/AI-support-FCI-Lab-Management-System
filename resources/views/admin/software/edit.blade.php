@extends('layouts.admin')

@section('title', 'Edit Software')
@section('page-title', 'Edit Software')
@section('breadcrumb', 'Edit Software')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Software</h3>
        </div>
        <div class="card-body">
            <form action="{{ url('/admin/software/' . $software->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="lab_id">Laboratory</label>
                    <select name="lab_id" id="lab_id" class="form-control @error('lab_id') is-invalid @enderror" required>
                        <option value="">Select laboratory</option>
                        @foreach($laboratories as $lab)
                            <option value="{{ $lab->id }}" {{ old('lab_id', $software->lab_id) == $lab->id ? 'selected' : '' }}>{{ $lab->lab_name }}</option>
                        @endforeach
                    </select>
                    @error('lab_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="software_name">Software Name</label>
                    <input type="text" name="software_name" id="software_name" class="form-control @error('software_name') is-invalid @enderror" value="{{ old('software_name', $software->software_name) }}" required>
                    @error('software_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="version">Version</label>
                    <input type="text" name="version" id="version" class="form-control @error('version') is-invalid @enderror" value="{{ old('version', $software->version) }}" required>
                    @error('version')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="expiry_date">Expiry Date</label>
                    <input type="date" name="expiry_date" id="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" value="{{ old('expiry_date', $software->expiry_date) }}" required>
                    @error('expiry_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="">Select status</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" {{ old('status', $software->status) == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <button class="btn btn-success">Update Software</button>
                <a href="{{ url('/admin/software') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
