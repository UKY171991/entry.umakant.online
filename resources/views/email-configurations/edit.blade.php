@extends('layouts.main')

@section('title', 'Edit Email Configuration')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Edit Email Configuration</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('email-configurations.index') }}">Email Configurations</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('email-configurations.show', $emailConfiguration) }}">{{ $emailConfiguration->name }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
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
                        <h3 class="card-title">Update Email Account Details</h3>
                    </div>
                    <form method="POST" action="{{ route('email-configurations.update', $emailConfiguration) }}">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="form-group">
                                <label for="name">Configuration Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $emailConfiguration->name) }}" 
                                       placeholder="e.g., My Bank Account, SBI Primary">
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $emailConfiguration->email) }}" 
                                       placeholder="your.email@gmail.com">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password">Email Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" 
                                       placeholder="Leave blank to keep current password">
                                <small class="form-text text-muted">
                                    Leave blank to keep the current password. For Gmail, use an App Password.
                                </small>
                                @error('password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="imap_host">IMAP Host <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('imap_host') is-invalid @enderror" 
                                               id="imap_host" name="imap_host" value="{{ old('imap_host', $emailConfiguration->imap_host) }}" 
                                               placeholder="imap.gmail.com">
                                        @error('imap_host')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="imap_port">IMAP Port <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('imap_port') is-invalid @enderror" 
                                               id="imap_port" name="imap_port" value="{{ old('imap_port', $emailConfiguration->imap_port) }}" 
                                               min="1" max="65535">
                                        @error('imap_port')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="imap_encryption">Encryption <span class="text-danger">*</span></label>
                                <select class="form-control @error('imap_encryption') is-invalid @enderror" 
                                        id="imap_encryption" name="imap_encryption">
                                    <option value="ssl" {{ old('imap_encryption', $emailConfiguration->imap_encryption) == 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="tls" {{ old('imap_encryption', $emailConfiguration->imap_encryption) == 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="none" {{ old('imap_encryption', $emailConfiguration->imap_encryption) == 'none' ? 'selected' : '' }}>None</option>
                                </select>
                                @error('imap_encryption')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Bank-Specific Patterns (Optional)</label>
                                <div id="bank-patterns">
                                    @php
                                        $patterns = old('bank_patterns', $emailConfiguration->bank_patterns ?: ['']);
                                    @endphp
                                    @foreach($patterns as $index => $pattern)
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control" name="bank_patterns[]" 
                                                   value="{{ $pattern }}" placeholder="e.g., sbi, hdfc, transaction">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-danger remove-pattern">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-secondary btn-sm" id="add-pattern">
                                    <i class="fas fa-plus"></i> Add Pattern
                                </button>
                                <small class="form-text text-muted">
                                    Add keywords to help identify bank transaction emails (e.g., bank name, transaction keywords).
                                </small>
                            </div>

                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                                       value="1" {{ old('is_active', $emailConfiguration->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active (Process emails from this configuration)
                                </label>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Configuration
                            </button>
                            <a href="{{ route('email-configurations.show', $emailConfiguration) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="button" class="btn btn-success test-connection" 
                                    data-url="{{ route('email-configurations.test-connection', $emailConfiguration) }}">
                                <i class="fas fa-plug"></i> Test Connection
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Configuration Info</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Created:</strong> {{ $emailConfiguration->created_at->format('M d, Y H:i') }}</p>
                        <p><strong>Last Updated:</strong> {{ $emailConfiguration->updated_at->format('M d, Y H:i') }}</p>
                        @if($emailConfiguration->last_sync_at)
                            <p><strong>Last Sync:</strong> {{ $emailConfiguration->last_sync_at->diffForHumans() }}</p>
                        @endif
                        <p><strong>Total Transactions:</strong> {{ $emailConfiguration->emailTransactions()->count() }}</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Security Note</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-shield-alt"></i>
                            <strong>Security:</strong> Passwords are encrypted. Leave the password field blank to keep your current password unchanged.
                        </div>
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
    // Add pattern functionality
    $('#add-pattern').click(function() {
        const patternHtml = `
            <div class="input-group mb-2">
                <input type="text" class="form-control" name="bank_patterns[]" 
                       placeholder="e.g., sbi, hdfc, transaction">
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger remove-pattern">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
        `;
        $('#bank-patterns').append(patternHtml);
    });

    // Remove pattern functionality
    $(document).on('click', '.remove-pattern', function() {
        if ($('#bank-patterns .input-group').length > 1) {
            $(this).closest('.input-group').remove();
        }
    });

    // Test connection functionality
    $('.test-connection').click(function() {
        const button = $(this);
        const url = button.data('url');
        const originalHtml = button.html();
        
        button.html('<i class="fas fa-spinner fa-spin"></i> Testing...').prop('disabled', true);
        
        $.post(url)
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
});
</script>
@endpush