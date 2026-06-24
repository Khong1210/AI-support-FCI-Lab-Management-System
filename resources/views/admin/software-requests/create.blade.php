@extends('layouts.admin')

@section('title', 'New Software Request')
@section('page-title', 'New Software Request')
@section('breadcrumb', 'New Software Request')

@section('content')
<div class="bg-white shadow rounded p-5 max-w-2xl">

    <h4 class="text-lg font-semibold text-gray-700 mb-4">
        <i class="fas fa-paper-plane mr-2 text-blue-500"></i> Request New Software
    </h4>

    <form action="{{ route('software-requests.store') }}" method="POST">
        @csrf

        {{-- Software Name --}}
        <div class="mb-3">
            <label for="software_name" class="form-label fw-semibold">Software Name <span class="text-danger">*</span></label>
            <input type="text" name="software_name" id="software_name"
                   class="form-control @error('software_name') is-invalid @enderror"
                   value="{{ old('software_name') }}"
                   placeholder="e.g. Adobe Photoshop, MATLAB, Visual Studio"
                   required maxlength="255">
            @error('software_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Version (optional) --}}
        <div class="mb-3">
            <label for="version" class="form-label fw-semibold">Version</label>
            <input type="text" name="version" id="version"
                   class="form-control @error('version') is-invalid @enderror"
                   value="{{ old('version') }}"
                   placeholder="e.g. 2026, v2.3, Latest"
                   maxlength="100">
            @error('version')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Optional: Link to existing software --}}
        <div class="mb-3">
            <label for="software_id" class="form-label fw-semibold">Link to Existing Software (optional)</label>
            <select name="software_id" id="software_id" class="form-select @error('software_id') is-invalid @enderror">
                <option value="">— None (new software request) —</option>
                @foreach($existingSoftware as $sw)
                    <option value="{{ $sw->id }}" {{ old('software_id') == $sw->id ? 'selected' : '' }}>
                        {{ $sw->software_name }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted">If this is for an existing software package, select it here.</small>
            @error('software_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <a href="{{ route('software-requests.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-paper-plane mr-1"></i> Submit Request
            </button>
        </div>
    </form>

</div>
@stop