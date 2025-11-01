@extends('layouts.main')

@section('title', 'Add Email Configuration')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Add Email Configuration</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('email-configurations.index') }}">Email Configurations</a></li>
                    <li class="breadcrumb-item active">Add New</li>
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
                        <h3 class="card-title">Email Account Details</h3>
                    </div>
                    <form method="POST" action="{{ route('email-configurations.store') }}">
                        @csrf
                        <div class="card-body">
                            <div class="form-group">
                                <label for="name">Configuration Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" 
                                       placeholder="e.g., My Bank Account, SBI Primary">
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}" 
                                       placeholder="your.email@gmail.com">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password">Email Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" 
                                       placeholder="App password or email password">
                                <small class="form-text text-muted">
                                    For Gmail, use an App Password. For other providers, use your email password.
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
                                               id="imap_host" name="imap_host" value="{{ old('imap_host', 'imap.gmail.com') }}" 
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
                                               id="imap_port" name="imap_port" value="{{ old('imap_port', 993) }}" 
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
                                    <option value="ssl" {{ old('imap_encryption', 'ssl') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="tls" {{ old('imap_encryption') == 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="none" {{ old('imap_encryption') == 'none' ? 'selected' : '' }}>None</option>
                                </select>
                                @error('imap_encryption')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Bank-Specific Patterns (Optional)</label>
                                <div id="bank-patterns">
                                    @if(old('bank_patterns'))
                                        @foreach(old('bank_patterns') as $index => $pattern)
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
                                    @else
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control" name="bank_patterns[]" 
                                                   placeholder="e.g., sbi, hdfc, transaction">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-danger remove-pattern">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
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
                                       value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active (Start processing emails immediately)
                                </label>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Configuration
                            </button>
                            <a href="{{ route('email-configurations.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Common IMAP Settings</h3>
                    </div>
                    <div class="card-body">
                        <h6>Gmail</h6>
                        <ul class="list-unstyled">
                            <li><strong>Host:</strong> imap.gmail.com</li>
                            <li><strong>Port:</strong> 993</li>
                            <li><strong>Encryption:</strong> SSL</li>
                        </ul>

                        <h6>Outlook/Hotmail</h6>
                        <ul class="list-unstyled">
                            <li><strong>Host:</strong> outlook.office365.com</li>
                            <li><strong>Port:</strong> 993</li>
                            <li><strong>Encryption:</strong> SSL</li>
                        </ul>

                        <h6>Yahoo</h6>
                        <ul class="list-unstyled">
                            <li><strong>Host:</strong> imap.mail.yahoo.com</li>
                            <li><strong>Port:</strong> 993</li>
                            <li><strong>Encryption:</strong> SSL</li>
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Security Note</h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-shield-alt"></i>
                            <strong>Security:</strong> All passwords are encrypted before storage. 
                            For Gmail, we recommend using App Passwords instead of your main password.
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
        $(this).closest('.input-group').remove();
    });

    // Quick fill for common providers
    $('#email').on('blur', function() {
        const email = $(this).val().toLowerCase();
        if (email.includes('@gmail.com')) {
            $('#imap_host').val('imap.gmail.com');
            $('#imap_port').val(993);
            $('#imap_encryption').val('ssl');
        } else if (email.includes('@outlook.com') || email.includes('@hotmail.com')) {
            $('#imap_host').val('outlook.office365.com');
            $('#imap_port').val(993);
            $('#imap_encryption').val('ssl');
        } else if (email.includes('@yahoo.com')) {
            $('#imap_host').val('imap.mail.yahoo.com');
            $('#imap_port').val(993);
            $('#imap_encryption').val('ssl');
        }
    });
});
</script>
@endpush