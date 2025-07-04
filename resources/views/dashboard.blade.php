@extends('layouts.main')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- Info boxes -->
<div class="row">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Clients</span>
                <span class="info-box-number">{{ App\Models\Client::count() ?? 0 }}</span>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-rupee-sign"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Income</span>
                <span class="info-box-number">₹{{ number_format(App\Models\Income::sum('total_amount') ?? 0, 2) }}</span>
            </div>
        </div>
    </div>
    
    <div class="clearfix hidden-md-up"></div>
    
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-receipt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Expenses</span>
                <span class="info-box-number">₹{{ number_format(App\Models\Expense::sum('amount') ?? 0, 2) }}</span>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-chart-line"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Net Profit</span>
                <span class="info-box-number">₹{{ number_format((App\Models\Income::sum('total_amount') ?? 0) - (App\Models\Expense::sum('amount') ?? 0), 2) }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Main row -->
<div class="row">
    <!-- Left col -->
    <section class="col-lg-7 connectedSortable">
        <!-- Custom tabs (Charts with tabs)-->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-1"></i>
                    Monthly Overview
                </h3>
                <div class="card-tools">
                    <ul class="nav nav-pills ml-auto">
                        <li class="nav-item">
                            <a class="nav-link active" href="#revenue-chart" data-toggle="tab">Income</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#expense-chart" data-toggle="tab">Expenses</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div class="tab-content p-0">
                    <!-- Chart content would go here -->
                    <div class="chart tab-pane active" id="revenue-chart" style="position: relative; height: 300px;">
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <div class="text-center">
                                <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
                                <p class="text-muted">Income chart will be displayed here</p>
                            </div>
                        </div>
                    </div>
                    <div class="chart tab-pane" id="expense-chart" style="position: relative; height: 300px;">
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <div class="text-center">
                                <i class="fas fa-chart-bar fa-4x text-muted mb-3"></i>
                                <p class="text-muted">Expense chart will be displayed here</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.card -->
    </section>
    <!-- /.Left col -->
    
    <!-- right col (We are only adding the ID to make the widgets sortable)-->
    <section class="col-lg-5 connectedSortable">
        <!-- Quick Actions Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bolt mr-1"></i>
                    Quick Actions
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <a href="/clients" class="btn btn-app">
                            <i class="fas fa-users"></i> Clients
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="/incomes" class="btn btn-app">
                            <i class="fas fa-coins"></i> Income
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="/expenses" class="btn btn-app">
                            <i class="fas fa-receipt"></i> Expenses
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="/emails" class="btn btn-app">
                            <i class="fas fa-envelope"></i> Emails
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="/websites" class="btn btn-app">
                            <i class="fas fa-globe"></i> Websites
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="/pending-tasks" class="btn btn-app">
                            <i class="fas fa-tasks"></i> Tasks
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.card -->

        <!-- Recent Activity Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clock mr-1"></i>
                    Recent Activity
                </h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="time-label">
                        <span class="bg-red">Today</span>
                    </div>
                    <div>
                        <i class="fas fa-user bg-blue"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> 2 hours ago</span>
                            <h3 class="timeline-header"><a href="#">New client</a> added</h3>
                            <div class="timeline-body">
                                A new client has been registered in the system.
                            </div>
                        </div>
                    </div>
                    <div>
                        <i class="fas fa-coins bg-green"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> 5 hours ago</span>
                            <h3 class="timeline-header"><a href="#">Income</a> recorded</h3>
                            <div class="timeline-body">
                                New income entry of $500 has been added.
                            </div>
                        </div>
                    </div>
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.card -->
    </section>
    <!-- right col -->
</div>
<!-- /.row -->
@endsection
