@extends('layouts.main')

@section('title', 'Inbox Dashboard')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Inbox Dashboard</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Inbox</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $stats['total_configurations'] }}</h3>
                        <p>Email Accounts</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <a href="{{ route('email-configurations.index') }}" class="small-box-footer">
                        Manage Accounts <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $stats['processed_transactions'] }}</h3>
                        <p>Processed Transactions</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        View Details <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['pending_transactions'] }}</h3>
                        <p>Pending Transactions</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Process Now <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $stats['failed_transactions'] }}</h3>
                        <p>Failed Transactions</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Retry Failed <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Email Configurations -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Email Configurations</h3>
                        <div class="card-tools">
                            <a href="{{ route('email-configurations.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Configuration
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($configurations->isEmpty())
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Email Configurations</h5>
                                <p class="text-muted">Add your first email configuration to start processing bank transaction emails.</p>
                                <a href="{{ route('email-configurations.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add Email Configuration
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Last Sync</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($configurations as $config)
                                            <tr>
                                                <td>
                                                    <strong>{{ $config->name }}</strong>
                                                    <br><small class="text-muted">{{ $config->emailTransactions->count() }} transactions</small>
                                                </td>
                                                <td>{{ $config->email }}</td>
                                                <td>
                                                    @if($config->is_active)
                                                        <span class="badge badge-success">Active</span>
                                                    @else
                                                        <span class="badge badge-secondary">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($config->last_sync_at)
                                                        {{ $config->last_sync_at->diffForHumans() }}
                                                    @else
                                                        <span class="text-muted">Never</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('email-configurations.show', $config) }}" 
                                                           class="btn btn-info btn-xs" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-success btn-xs manual-sync" 
                                                                data-url="{{ route('email-configurations.manual-sync', $config) }}" 
                                                                title="Manual Sync">
                                                            <i class="fas fa-sync"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('email-configurations.create') }}" class="btn btn-primary btn-block mb-2">
                                <i class="fas fa-plus"></i> Add Email Configuration
                            </a>
                            <button type="button" class="btn btn-success btn-block mb-2" id="sync-all">
                                <i class="fas fa-sync"></i> Sync All Active Accounts
                            </button>
                            <button type="button" class="btn btn-warning btn-block mb-2" id="retry-failed">
                                <i class="fas fa-redo"></i> Retry Failed Transactions
                            </button>
                            <a href="{{ route('email-configurations.index') }}" class="btn btn-info btn-block">
                                <i class="fas fa-cog"></i> Manage Configurations
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Processing Status</h3>
                    </div>
                    <div class="card-body">
                        <div class="progress-group">
                            <span class="progress-text">Active Configurations</span>
                            <span class="float-right"><b>{{ $stats['active_configurations'] }}</b>/{{ $stats['total_configurations'] }}</span>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-primary" 
                                     style="width: {{ $stats['total_configurations'] > 0 ? ($stats['active_configurations'] / $stats['total_configurations']) * 100 : 0 }}%"></div>
                            </div>
                        </div>

                        <div class="progress-group">
                            <span class="progress-text">Success Rate</span>
                            @php
                                $totalProcessed = $stats['processed_transactions'] + $stats['failed_transactions'];
                                $successRate = $totalProcessed > 0 ? ($stats['processed_transactions'] / $totalProcessed) * 100 : 0;
                            @endphp
                            <span class="float-right"><b>{{ number_format($successRate, 1) }}%</b></span>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-success" style="width: {{ $successRate }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        @if($recentTransactions->isNotEmpty())
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Email Transactions</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Configuration</th>
                                            <th>Subject</th>
                                            <th>Type</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentTransactions as $transaction)
                                            <tr>
                                                <td>{{ $transaction->transaction_date->format('M d, Y') }}</td>
                                                <td>{{ $transaction->emailConfiguration->name }}</td>
                                                <td>{{ Str::limit($transaction->subject, 40) }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $transaction->transaction_type == 'income' ? 'success' : 'warning' }}">
                                                        {{ ucfirst($transaction->transaction_type) }}
                                                    </span>
                                                </td>
                                                <td>₹{{ number_format($transaction->amount, 2) }}</td>
                                                <td>
                                                    @if($transaction->processing_status == 'processed')
                                                        <span class="badge badge-success">Processed</span>
                                                    @elseif($transaction->processing_status == 'failed')
                                                        <span class="badge badge-danger">Failed</span>
                                                    @else
                                                        <span class="badge badge-info">Pending</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Manual sync functionality
    $('.manual-sync').click(function() {
        const button = $(this);
        const url = button.data('url');
        const originalHtml = button.html();
        
        button.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
        
        $.ajax({
            url: url,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .done(function(response) {
            toastr.success(response.message);
            setTimeout(function() {
                location.reload();
            }, 2000);
        })
        .fail(function(xhr) {
            const response = xhr.responseJSON;
            toastr.error(response ? response.message : 'Sync failed');
        })
        .always(function() {
            button.html(originalHtml).prop('disabled', false);
        });
    });

    // Sync all active accounts
    $('#sync-all').click(function() {
        const button = $(this);
        const originalHtml = button.html();
        
        button.html('<i class="fas fa-spinner fa-spin"></i> Syncing...').prop('disabled', true);
        
        // This would need a dedicated endpoint for syncing all accounts
        toastr.info('Sync all functionality will be implemented in the next phase');
        
        setTimeout(function() {
            button.html(originalHtml).prop('disabled', false);
        }, 2000);
    });

    // Retry failed transactions
    $('#retry-failed').click(function() {
        const button = $(this);
        const originalHtml = button.html();
        
        button.html('<i class="fas fa-spinner fa-spin"></i> Retrying...').prop('disabled', true);
        
        // This would need a dedicated endpoint for retrying failed transactions
        toastr.info('Retry failed functionality will be implemented in the next phase');
        
        setTimeout(function() {
            button.html(originalHtml).prop('disabled', false);
        }, 2000);
    });
});
</script>
@endpush