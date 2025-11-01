@extends('adminlte::page')

@section('title', 'Email Transactions')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Email Transactions</h1>
        <div>
            <button type="button" class="btn btn-primary" onclick="triggerManualSync()">
                <i class="fas fa-sync"></i> Manual Sync
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filter Transactions</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('email-transactions.index') }}" class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="date_from">Date From</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                               value="{{ request('date_from') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="date_to">Date To</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                               value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="type">Type</label>
                        <select class="form-control" id="type" name="type">
                            <option value="">All Types</option>
                            <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>Income</option>
                            <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>Expense</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processed" {{ request('status') === 'processed' ? 'selected' : '' }}>Processed</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('email-transactions.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
            
            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="amount_min">Min Amount</label>
                        <input type="number" class="form-control" id="amount_min" name="amount_min" 
                               step="0.01" value="{{ request('amount_min') }}" form="filter-form">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="amount_max">Max Amount</label>
                        <input type="number" class="form-control" id="amount_max" name="amount_max" 
                               step="0.01" value="{{ request('amount_max') }}" form="filter-form">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="search">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Search in description, subject, or sender..." 
                               value="{{ request('search') }}" form="filter-form">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Transactions ({{ $transactions->total() }} total)</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Sender</th>
                        <th>Status</th>
                        <th>Email Config</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                            <td>
                                <span class="badge badge-{{ $transaction->transaction_type === 'income' ? 'success' : 'danger' }}">
                                    {{ ucfirst($transaction->transaction_type) }}
                                </span>
                            </td>
                            <td>${{ number_format($transaction->amount, 2) }}</td>
                            <td>{{ Str::limit($transaction->description, 50) }}</td>
                            <td>{{ Str::limit($transaction->sender, 30) }}</td>
                            <td>
                                @switch($transaction->processing_status)
                                    @case('processed')
                                        <span class="badge badge-success">Processed</span>
                                        @break
                                    @case('pending')
                                        <span class="badge badge-warning">Pending</span>
                                        @break
                                    @case('failed')
                                        <span class="badge badge-danger">Failed</span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary">Unknown</span>
                                @endswitch
                            </td>
                            <td>{{ $transaction->emailConfiguration->name ?? 'N/A' }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('email-transactions.show', $transaction) }}" 
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('email-transactions.edit', $transaction) }}" 
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($transaction->processing_status === 'failed')
                                        <form method="POST" action="{{ route('email-transactions.reprocess', $transaction) }}" 
                                              style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary" 
                                                    title="Reprocess" onclick="return confirm('Reprocess this transaction?')">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('email-transactions.destroy', $transaction) }}" 
                                          style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                title="Delete" onclick="return confirm('Are you sure you want to delete this transaction?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No email transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="card-footer">
                {{ $transactions->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
@stop

@section('js')
<script>
function triggerManualSync() {
    if (confirm('This will fetch and process new emails. Continue?')) {
        // Show loading state
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';
        button.disabled = true;
        
        // Make AJAX request to trigger sync
        fetch('{{ route("email-transactions.manual-sync") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            // Reset button
            button.innerHTML = originalText;
            button.disabled = false;
            
            // Show success message
            if (data.success) {
                toastr.success(data.message || 'Manual sync triggered successfully');
                // Optionally reload the page after a delay
                setTimeout(() => location.reload(), 2000);
            } else {
                toastr.error(data.message || 'Failed to trigger manual sync');
            }
        })
        .catch(error => {
            // Reset button
            button.innerHTML = originalText;
            button.disabled = false;
            
            console.error('Error:', error);
            toastr.error('An error occurred while triggering manual sync');
        });
    }
}

// Auto-submit form when amount or search fields change (with debounce)
let searchTimeout;
document.getElementById('search').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        this.form.submit();
    }, 500);
});

document.getElementById('amount_min').addEventListener('change', function() {
    this.form.submit();
});

document.getElementById('amount_max').addEventListener('change', function() {
    this.form.submit();
});
</script>
@stop