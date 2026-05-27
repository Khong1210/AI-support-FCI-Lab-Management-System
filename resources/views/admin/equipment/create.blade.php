@extends('layouts.admin')

@section('title', 'Add Equipment')
@section('page-title', 'Add Equipment')
@section('breadcrumb', 'Add Equipment')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-desktop mr-2"></i>Add Equipment</h3>
            </div>
            <form action="{{ url('/admin/equipment') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="equipment_name">Equipment Name</label>
                            <input type="text" name="equipment_name" id="equipment_name" class="form-control @error('equipment_name') is-invalid @enderror" value="{{ old('equipment_name') }}" placeholder="Enter equipment name" required>
                            @error('equipment_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="serial_number">Serial Number</label>
                            <input type="text" name="serial_number" id="serial_number" class="form-control @error('serial_number') is-invalid @enderror" value="{{ old('serial_number') }}" placeholder="Enter serial number" required>
                            @error('serial_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
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
                            <label for="type">Type</label>
                            <input type="text" name="type" id="type" class="form-control @error('type') is-invalid @enderror" value="{{ old('type') }}" placeholder="Example: Desktop, Projector" required>
                            @error('type')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="purchase_date">Purchase Date</label>
                            <input type="date" name="purchase_date" id="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror" value="{{ old('purchase_date') }}" required>
                            @error('purchase_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-6 form-group">
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
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Save Equipment
                    </button>
                    <a href="{{ url('/admin/equipment') }}" class="btn btn-secondary float-right">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
