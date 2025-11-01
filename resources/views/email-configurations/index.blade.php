@extends('layouts.main')

@section('title', 'Email Configurations')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Email Configurations</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Email Configurations</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Manage Email Accounts for Transaction Processing</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-success btn-sm" id="test-jquery" onclick="testJQuery()">
                                Test jQuery
                            </button>
                            <button type="button" class="btn btn-warning btn-sm" id="test-vanilla" onclick="testVanilla()">
                                Test Vanilla JS
                            </button>
                            <a href="{{ route('email-configurations.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Email Configuration
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($configurations->isEmpty())
                            <div class="text-center py-4">
                                <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Email Configurations Found</h5>
                                <p class="text-muted">Add your first email configuration to start processing bank transaction emails automatically.</p>
                                <a href="{{ route('email-configurations.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add Email Configuration
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="configurationsTable">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Last Sync</th>
                                            <th>Transactions</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($configurations as $config)
                                            <tr>
                                                <td>
                                                    <strong>{{ $config->name }}</strong>
                                                    @if($config->bank_patterns)
                                                        <br><small class="text-muted">{{ count($config->bank_patterns) }} patterns</small>
                                                    @endif
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
                                                        <span title="{{ $config->last_sync_at }}">
                                                            {{ $config->last_sync_at->diffForHumans() }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">Never</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">{{ $config->emailTransactions->count() }}</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('email-configurations.show', $config) }}" 
                                                           class="btn btn-info btn-sm" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('email-configurations.edit', $config) }}" 
                                                           class="btn btn-warning btn-sm" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-success btn-sm test-connection" 
                                                                data-url="{{ route('email-configurations.test-connection', $config) }}" 
                                                                title="Test Connection">
                                                            <i class="fas fa-plug"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-{{ $config->is_active ? 'secondary' : 'primary' }} btn-sm toggle-status" 
                                                                data-url="{{ route('email-configurations.toggle-status', $config) }}"
                                                                data-config-id="{{ $config->id }}"
                                                                data-current-status="{{ $config->is_active ? 'active' : 'inactive' }}"
                                                                title="{{ $config->is_active ? 'Deactivate' : 'Activate' }}">
                                                            <i class="fas fa-{{ $config->is_active ? 'pause' : 'play' }}"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-info btn-sm manual-sync" 
                                                                data-url="{{ route('email-configurations.manual-sync', $config) }}"
                                                                title="Manual Sync">
                                                            <i class="fas fa-sync"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-sm delete-config" 
                                                                data-url="{{ route('email-configurations.destroy', $config) }}"
                                                                data-config-name="{{ $config->name }}"
                                                                data-config-id="{{ $config->id }}"
                                                                title="Delete">
                                                            <i class="fas fa-trash"></i>
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
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
// Test functions
function testVanilla() {
    alert('Vanilla JavaScript is working!');
    console.log('Vanilla JS test successful');
}

function testJQuery() {
    if (typeof $ !== 'undefined') {
        alert('jQuery is working! Version: ' + $.fn.jquery);
        console.log('jQuery test successful');
    } else {
        alert('jQuery is not loaded!');
        console.error('jQuery not found');
    }
}

// Test vanilla JavaScript first
document.addEventListener('DOMContentLoaded', function() {
    console.log('Vanilla JS loaded');
    
    // Test if buttons exist
    const testButtons = document.querySelectorAll('.test-connection');
    const toggleButtons = document.querySelectorAll('.toggle-status');
    console.log('Vanilla JS - Test buttons found:', testButtons.length);
    console.log('Vanilla JS - Toggle buttons found:', toggleButtons.length);
    
    // Add simple click listeners
    testButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            alert('Vanilla JS - Test connection clicked!');
            console.log('Vanilla JS click worked');
        });
    });
    
    toggleButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            alert('Vanilla JS - Toggle status clicked!');
            console.log('Vanilla JS toggle click worked');
        });
    });
});

$(document).ready(function() {
    console.log('Email configurations JavaScript loaded');
    console.log('jQuery version:', $.fn.jquery);
    console.log('Test connection buttons found:', $('.test-connection').length);
    console.log('Toggle status buttons found:', $('.toggle-status').length);
    console.log('Manual sync buttons found:', $('.manual-sync').length);
    console.log('Delete config buttons found:', $('.delete-config').length);
    
    // Initialize DataTable if available
    if ($.fn.DataTable) {
        $('#configurationsTable').DataTable({
            responsive: true,
            order: [[0, 'asc']]
        });
        console.log('DataTable initialized');
    } else {
        console.warn('DataTable not available');
    }

    // Test connection functionality
    $('.test-connection').click(function(e) {
        e.preventDefault();
        console.log('Test connection clicked');
        alert('Test connection button clicked!');
        
        const button = $(this);
        const url = button.data('url');
        const originalHtml = button.html();
        
        console.log('Button URL:', url);
        
        if (!url) {
            alert('No URL found for this button');
            return;
        }
        
        button.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
        
        // Simple AJAX call
        $.post({
            url: url,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
        .done(function(response) {
            console.log('Success response:', response);
            if (typeof toastr !== 'undefined') {
                toastr.success(response.message || 'Operation successful');
            } else {
                alert('Success: ' + (response.message || 'Operation successful'));
            }
        })
        .fail(function(xhr) {
            console.log('Error response:', xhr);
            const response = xhr.responseJSON;
            const message = response ? response.message : 'Connection test failed';
            if (typeof toastr !== 'undefined') {
                toastr.error(message);
            } else {
                alert('Error: ' + message);
            }
        })
        .always(function() {
            button.html(originalHtml).prop('disabled', false);
        });
    });

    // Toggle status functionality
    $('.toggle-status').click(function(e) {
        e.preventDefault();
        console.log('Toggle status clicked');
        alert('Toggle status button clicked!');
        
        const button = $(this);
        const url = button.data('url');
        const configId = button.data('config-id');
        const currentStatus = button.data('current-status');
        const originalHtml = button.html();
        
        console.log('Toggle URL:', url);
        console.log('Config ID:', configId);
        console.log('Current Status:', currentStatus);
        
        if (!url) {
            alert('No URL found for toggle button');
            return;
        }
        
        button.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
        
        $.post({
            url: url,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
        .done(function(response) {
            console.log('Toggle response:', response);
            const message = response.message || 'Status updated successfully';
            if (typeof toastr !== 'undefined') {
                toastr.success(message);
            } else {
                alert('Success: ' + message);
            }
            
            // Update button appearance
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            button.data('current-status', newStatus);
            
            if (newStatus === 'active') {
                button.removeClass('btn-primary').addClass('btn-secondary');
                button.attr('title', 'Deactivate');
                button.find('i').removeClass('fa-play').addClass('fa-pause');
                // Update status badge in the row
                button.closest('tr').find('.badge').removeClass('badge-secondary').addClass('badge-success').text('Active');
            } else {
                button.removeClass('btn-secondary').addClass('btn-primary');
                button.attr('title', 'Activate');
                button.find('i').removeClass('fa-pause').addClass('fa-play');
                // Update status badge in the row
                button.closest('tr').find('.badge').removeClass('badge-success').addClass('badge-secondary').text('Inactive');
            }
        })
        .fail(function(xhr) {
            console.log('Toggle error:', xhr);
            const response = xhr.responseJSON;
            const message = response ? response.message : 'Failed to update status';
            if (typeof toastr !== 'undefined') {
                toastr.error(message);
            } else {
                alert('Error: ' + message);
            }
        })
        .always(function() {
            if (button.html().includes('fa-spinner')) {
                button.html(originalHtml);
            }
            button.prop('disabled', false);
        });
    });

    // Manual sync functionality
    $('.manual-sync').click(function(e) {
        e.preventDefault();
        console.log('Manual sync clicked');
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
            toastr.success(response.message || `Found ${response.count || 0} new emails to process`);
            
            // Optionally refresh the page or update last sync time
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

    // Delete configuration functionality
    $('.delete-config').click(function(e) {
        e.preventDefault();
        console.log('Delete config clicked');
        const button = $(this);
        const url = button.data('url');
        const configName = button.data('config-name');
        const configId = button.data('config-id');
        
        // Show confirmation dialog
        if (!confirm(`Are you sure you want to delete the email configuration "${configName}"? This action cannot be undone.`)) {
            return;
        }
        
        const originalHtml = button.html();
        button.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
        
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
            toastr.success(response.message || 'Configuration deleted successfully');
            
            // Remove the row from the table
            button.closest('tr').fadeOut(500, function() {
                $(this).remove();
                
                // Check if table is empty and show empty state
                if ($('#configurationsTable tbody tr:visible').length === 0) {
                    location.reload();
                }
            });
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