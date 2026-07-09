@extends('layouts.admin')

@section('title', 'New Software Request')
@section('page-title', 'New Software Request')
@section('breadcrumb', 'New Software Request')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-paper-plane mr-2"></i>Request New Software</h3>
            </div>
            
            <form action="{{ route('software-requests.store') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                
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
                        <label for="software_name">Software Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-cube"></i></span>
                            </div>
                            <input type="text" name="software_name" id="software_name" 
                                   class="form-control {{ $errors->has('software_name') ? 'is-invalid' : '' }}" 
                                   value="{{ old('software_name') }}" 
                                   placeholder="e.g. Adobe Photoshop, MATLAB, Visual Studio" 
                                   required maxlength="255">
                        </div>
                        @error('software_name') 
                            <small class="form-text text-danger">{{ $message }}</small> 
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="version">Version</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-code-branch"></i></span>
                            </div>
                            <input type="text" name="version" id="version" 
                                   class="form-control {{ $errors->has('version') ? 'is-invalid' : '' }}" 
                                   value="{{ old('version') }}" 
                                   placeholder="e.g. 2026, v2.3, Latest" 
                                   maxlength="100">
                        </div>
                        @error('version') 
                            <small class="form-text text-danger">{{ $message }}</small> 
                        @enderror
                    </div>

                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-1"></i> Submit Request
                    </button>
                 
                </div>
            </form>
        </div>
    </div>
</div>
@endsection