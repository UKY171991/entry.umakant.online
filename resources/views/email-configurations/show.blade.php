@extends('layouts.main')

@section('title', 'Email Configuration Details')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ $emailConfiguration->name }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('email-configurations.index') }}">Email Configurations</a></li>
                    <li class="breadcrumb-item active">{{ $emailConfiguration->name }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Configuration Details</h3>
                        <div class="card-tools">
                            <a href="{{ route('email-configurations.edit', $emailConfiguration) }}" 
                               class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button type="button" class="btn btn-success btn-sm test-connection" 
                                    data-url="{{ route('email-configurations.test-connection', $emailConfiguration) }}">
                                <i class="fas fa-plug"></i> Test Connection
                            </button>
                            <button type="button" class="btn btn-info btn-sm manual-sync" 
                                    data-url="{{ route('email-configurations.manual-sync', $emailConfiguration) }}">
                                <i class="fas fa-sync"></i> Manual Sync
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Name:</th>
                                        <td>{{ $emailConfiguration->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td>{{ $emailConfiguration->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>IMAP Host:</th>
                                        <td>{{ $emailConfiguration->imap_host }}</td>
                                    </tr>
                                    <tr>
                                        <th>IMAP Port:</th>
                                        <td>{{ $emailConfiguration->imap_port }}</td>
                                    </tr>
                                    <tr>
                                        <th>Encryption:</th>
                                        <td>
                                            <span class="badge badge-info">{{ strtoupper($emailConfiguration->imap_encryption) }}</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Status:</th>
                                        <td>
                                            @if($emailConfiguration->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-secondary">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Last Sync:</th>
                                        <td>
                                            @if($emailConfiguration->last_sync_at)
                                                {{ $emailConfiguration->last_sync_at->format('M d, Y H:i') }}
                                                <br><small class="text-muted">{{ $stats['last_sync'] }}</small>
                                            @else
                                                <span class="text-muted">Never synced</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created:</th>
                                        <td>{{ $emailConfiguration->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Updated:</th>
                                        <td>{{ $emailConfiguration->updated_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        @if($emailConfiguration->bank_patterns)
                            <div class="mt-3">
                                <h6>Bank Patterns:</h6>
                                <div>
                                    @foreach($emailConfiguration->bank_patterns as $pattern)
                                        <span class="badge badge-secondary mr-1">{{ $pattern }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Email Transactions</h3>
                    </div>
                    <div class="card-body">
                        @if($emailConfiguration->emailTransactions->isEmpty())
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-2x text-muted mb-3"></i>
                                <h6 class="text-muted">No Transactions Yet</h6>
                                <p class="text-muted">No email transactions have been processed for this configuration.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Subject</th>
                                            <th>Type</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($emailConfiguration->emailTransactions as $transaction)
                                            <tr>
                                                <td>{{ $transaction->transaction_date->format('M d, Y') }}</td>
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
                            @if($stats['total_transactions'] > 10)
                                <div class="text-center mt-3">
                                    <small class="text-muted">Showing latest 10 transactions out of {{ $stats['total_transactions'] }} total</small>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Statistics</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-envelope"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Transactions</span>
                                <span class="info-box-number">{{ $stats['total_transactions'] }}</span>
                            </div>
                        </div>

                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Processed</span>
                                <span class="info-box-number">{{ $stats['processed_transactions'] }}</span>
                            </div>
                        </div>

                        <div class="info-box">
                            <span class="info-box-icon bg-danger"><i class="fas fa-times"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Failed</span>
                                <span class="info-box-number">{{ $stats['failed_transactions'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-{{ $emailConfiguration->is_active ? 'secondary' : 'success' }} btn-block mb-2 toggle-status-show" 
                                data-url="{{ route('email-configurations.toggle-status', $emailConfiguration) }}"
                                data-current-status="{{ $emailConfiguration->is_active ? 'active' : 'inactive' }}">
                            <i class="fas fa-{{ $emailConfiguration->is_active ? 'pause' : 'play' }}"></i>
                            {{ $emailConfiguration->is_active ? 'Deactivate' : 'Activate' }}
                        </button>

                        <a href="{{ route('email-configurations.edit', $emailConfiguration) }}" 
                           class="btn btn-warning btn-block mb-2">
                            <i class="fas fa-edit"></i> Edit Configuration
                        </a>

                        <button type="button" class="btn btn-danger btn-block delete-config-show" 
                                data-url="{{ route('email-configurations.destroy', $emailConfiguration) }}"
                                data-config-name="{{ $emailConfiguration->name }}">
                            <i class="fas fa-trash"></i> Delete Configuration
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Test connection functionality
    $('.test-connection').click(function() {
        const button = $(this);
        const url = button.data('url');
        const originalHtml = button.html();
        
        button.html('<i class="fas fa-spinner fa-spin"></i> Testing...').prop('disabled', true);
        
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
        })
        .fail(function(xhr) {
            const response = xhr.responseJSON;
            toastr.error(response ? response.message : 'Connection test failed');
        })
        .always(function() {
            button.html(originalHtml).prop('disabled', false);
        });
    });

    // Manual sync functionality
    $('.manual-sync').click(function() {
        const button = $(this);
        const url = button.data('url');
        const originalHtml = button.html();
        
        button.html('<i class="fas fa-spinner fa-spin"></i> Syncing...').prop('disabled', true);
        
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
            // Reload page after 2 seconds to show new transactions
            setTimeout(function() {
                location.reload();
            }, 2000);
        })
        .fail(function(xhr) {
            const response = xhr.responseJSON;
            toastr.error(response ? response.message : 'Manual sync failed');
        })
        .always(function() {
            button.html(originalHtml).prop('disabled', false);
        });
    });

    // Toggle status functionality for show page
    $('.toggle-status-show').click(function() {
        const button = $(this);
        const url = button.data('url');
        const currentStatus = button.data('current-status');
        const originalHtml = button.html();
        
        button.html('<i class="fas fa-spinner fa-spin"></i> Updating...').prop('disabled', true);
        
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
            
            // Update button appearance and page elements
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            button.data('current-status', newStatus);
            
            if (newStatus === 'active') {
                button.removeClass('btn-success').addClass('btn-secondary');
                button.find('i').removeClass('fa-play').addClass('fa-pause');
                button.html('<i class="fas fa-pause"></i> Deactivate');
                // Update status badge in the details table
                $('.badge').removeClass('badge-secondary').addClass('badge-success').text('Active');
            } else {
                button.removeClass('btn-secondary').addClass('btn-success');
                button.find('i').removeClass('fa-pause').addClass('fa-play');
                button.html('<i class="fas fa-play"></i> Activate');
                // Update status badge in the details table
                $('.badge').removeClass('badge-success').addClass('badge-secondary').text('Inactive');
            }
        })
        .fail(function(xhr) {
            const response = xhr.responseJSON;
            toastr.error(response ? response.message : 'Failed to update status');
            button.html(originalHtml).prop('disabled', false);
        });
    });

    // Delete configuration functionality for show page
    $('.delete-config-show').click(function() {
        const button = $(this);
        const url = button.data('url');
        const configName = button.data('config-name');
        
        // Show confirmation dialog
        if (!confirm(`Are you sure you want to delete the email configuration "${configName}"? This action cannot be undone.`)) {
            return;
        }
        
        const originalHtml = button.html();
        button.html('<i class="fas fa-spinner fa-spin"></i> Deleting...').prop('disabled', true);
        
        $.ajax({
            url: url,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .done(function(response) {
            toastr.success(response.message);
            
            // Redirect to index page after successful deletion
            setTimeout(function() {
                window.location.href = '{{ route("email-configurations.index") }}';
            }, 1500);
        })
        .fail(function(xhr) {
            const response = xhr.responseJSON;
            toastr.error(response ? response.message : 'Failed to delete configuration');
            button.html(originalHtml).prop('disabled', false);
        });
    });
});
</script>
@endpush