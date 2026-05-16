@extends('layouts.admin')

@section('title', 'Edit Laboratory')
@section('page-title', 'Edit Laboratory')
@section('breadcrumb', 'Laboratories')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Edit Laboratory #{{ $laboratory->id }}</h3>
            </div>
            <form action="{{ url('/admin/laboratories/' . $laboratory->id) }}" method="POST">
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
                        <label for="lab_name">Laboratory Name</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-flask"></i></span>
                            </div>
                            <input type="text" name="lab_name" id="lab_name" class="form-control {{ $errors->has('lab_name') ? 'is-invalid' : '' }}" value="{{ old('lab_name', $laboratory->lab_name) }}" placeholder="Enter laboratory name" required>
                        </div>
                        @error('lab_name') <small class="form-text text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label for="capacity">Capacity</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-users"></i></span>
                            </div>
                            <input type="number" name="capacity" id="capacity" class="form-control {{ $errors->has('capacity') ? 'is-invalid' : '' }}" value="{{ old('capacity', $laboratory->capacity) }}" min="1" placeholder="Enter capacity" required>
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
                                    <option value="{{ $key }}" {{ old('status', $laboratory->status) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('status') <small class="form-text text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save mr-1"></i> Update Laboratory
                    </button>
                    <a href="{{ url('/admin/laboratories') }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card card-secondary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-desktop mr-2"></i>Assigned Equipment</h3>
                    </div>
                    <div class="card-body p-0">
                        @if ($laboratory->equipments->count())
                            <table class="table table-hover table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($laboratory->equipments as $equipment)
                                        <tr>
                                            <td>{{ $equipment->id }}</td>
                                            <td>{{ $equipment->equipment_name }}</td>
                                            <td>{{ $equipment->type }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="alert alert-info m-3">
                                <i class="fas fa-info-circle mr-2"></i>
                                No equipment assigned to this laboratory yet.
                            </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        <small>Total Equipment: {{ $laboratory->equipments->count() }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-secondary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-cube mr-2"></i>Assigned Software</h3>
                    </div>
                    <div class="card-body p-0">
                        @if ($laboratory->softwares->count())
                            <table class="table table-hover table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Software</th>
                                        <th>Version</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($laboratory->softwares as $software)
                                        <tr>
                                            <td>{{ $software->id }}</td>
                                            <td>{{ $software->software_name }}</td>
                                            <td>{{ $software->version }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="alert alert-info m-3">
                                <i class="fas fa-info-circle mr-2"></i>
                                No software assigned to this laboratory yet.
                            </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        <small>Total Software: {{ $laboratory->softwares->count() }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
