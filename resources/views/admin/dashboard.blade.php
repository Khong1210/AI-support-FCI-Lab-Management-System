@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $counts['users'] }}</h3>
                    <p>Users</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $counts['equipment'] }}</h3>
                    <p>Equipment</p>
                </div>
                <div class="icon">
                    <i class="fas fa-desktop"></i>
                </div>
                <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $counts['software'] }}</h3>
                    <p>Software</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-code"></i>
                </div>
                <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $counts['bookings'] }}</h3>
                    <p>Bookings</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="row">
        <section class="col-lg-7 connectedSortable">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line mr-1"></i> System Overview</h3>
                </div>
                <div class="card-body">
                    <p>Welcome to the AdminLTE management panel for the laboratory system. Use the sidebar to navigate between modules and inspect counts for the active entities.</p>
                    <div class="row">
                        <div class="col-sm-4 col-6">
                            <div class="description-block border-right">
                                <h5 class="description-header">{{ $counts['laboratories'] }}</h5>
                                <span class="description-text">LABS</span>
                            </div>
                        </div>
                        <div class="col-sm-4 col-6">
                            <div class="description-block border-right">
                                <h5 class="description-header">{{ $counts['courses'] }}</h5>
                                <span class="description-text">COURSES</span>
                            </div>
                        </div>
                        <div class="col-sm-4 col-6">
                            <div class="description-block">
                                <h5 class="description-header">{{ $counts['reports'] }}</h5>
                                <span class="description-text">REPORTS</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="col-lg-5 connectedSortable">
            <div class="card bg-gradient-success">
                <div class="card-header border-0">
                    <h3 class="card-title">
                        <i class="fas fa-th mr-1"></i>
                        Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon text-success"><i class="fas fa-user-plus"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">New User</span>
                                    <span class="info-box-number">Create</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon text-warning"><i class="fas fa-server"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Inventory</span>
                                    <span class="info-box-number">Review</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
