@extends('layouts.main')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="content-section">
    <div class="row">
        <div class="col-md-3">
            <div class="card bg-primary text-white stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>Total Clients</h4>
                            <h2>{{ App\Models\Client::count() ?? 0 }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>Total Income</h4>
                            <h2>${{ number_format(App\Models\Income::sum('amount') ?? 0, 2) }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-coins fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>Total Expenses</h4>
                            <h2>${{ number_format(App\Models\Expense::sum('amount') ?? 0, 2) }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-receipt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>Net Profit</h4>
                            <h2>${{ number_format((App\Models\Income::sum('amount') ?? 0) - (App\Models\Expense::sum('amount') ?? 0), 2) }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <a href="/clients" class="btn btn-primary btn-block mb-2">
                                <i class="fas fa-users"></i><br>Manage Clients
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="/incomes" class="btn btn-success btn-block mb-2">
                                <i class="fas fa-coins"></i><br>Add Income
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="/expenses" class="btn btn-danger btn-block mb-2">
                                <i class="fas fa-receipt"></i><br>Add Expense
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="/emails" class="btn btn-info btn-block mb-2">
                                <i class="fas fa-envelope"></i><br>Email Center
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="/websites" class="btn btn-warning btn-block mb-2">
                                <i class="fas fa-globe"></i><br>Websites
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="/pending-tasks" class="btn btn-secondary btn-block mb-2">
                                <i class="fas fa-tasks"></i><br>Tasks
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
