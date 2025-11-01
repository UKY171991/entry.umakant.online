@extends('layouts.main')

@section('title', 'Settings')
@section('page-title', 'User Settings')

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Target Income Settings Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bullseye mr-2"></i>
                    Daily Target Income
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">
                    Set your daily target income amount. This will automatically populate the total amount field when creating new income entries.
                </p>
                
                <form id="targetIncomeForm">
                    @csrf
                    <div class="form-group">
                        <label for="daily_target_income">
                            <i class="fas fa-rupee-sign mr-1"></i>
                            Daily Target Income
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="fas fa-rupee-sign"></i>
                                </span>
                            </div>
                            <input 
                                type="number" 
                                step="0.01" 
                                class="form-control" 
                                id="daily_target_income" 
                                name="daily_target_income" 
                                placeholder="Enter your daily target income"
                                value="{{ $user->daily_target_income ?? '' }}"
                                min="0"
                                max="999999.99"
                            >
                        </div>
                        <small class="form-text text-muted">
                            Leave empty to disable auto-fill functionality. Maximum value: ₹999,999.99
                        </small>
                        <div class="invalid-feedback" id="target-income-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" id="saveTargetIncomeBtn">
                            <i class="fas fa-save mr-1"></i>
                            Save Target Income
                        </button>
                        <button type="button" class="btn btn-secondary ml-2" id="clearTargetIncomeBtn">
                            <i class="fas fa-times mr-1"></i>
                            Clear Target Income
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Current Settings Info Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-2"></i>
                    Current Settings
                </h3>
            </div>
            <div class="card-body">
                <div class="info-box">
                    <span class="info-box-icon bg-info">
                        <i class="fas fa-rupee-sign"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Target Income</span>
                        <span class="info-box-number" id="current-target-display">
                            ₹{{ $user->formatted_target_income }}
                        </span>
                    </div>
                </div>
                
                <div class="mt-3">
                    <h6><i class="fas fa-lightbulb mr-1"></i> How it works:</h6>
                    <ul class="list-unstyled text-sm">
                        <li><i class="fas fa-check text-success mr-1"></i> Set your daily income goal</li>
                        <li><i class="fas fa-check text-success mr-1"></i> Auto-fills when creating income entries</li>
                        <li><i class="fas fa-check text-success mr-1"></i> You can still modify the amount</li>
                        <li><i class="fas fa-check text-success mr-1"></i> Streamlines your workflow</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        .info-box {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card {
            transition: transform 0.2s ease-in-out;
        }
        
        .card:hover {
            transform: translateY(-2px);
        }
        
        .input-group-text {
            background-color: #f8f9fa;
            border-color: #ced4da;
        }
        
        .btn {
            border-radius: 5px;
        }
        
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(document).ready(function() {
            // Configure toastr
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "3000"
            };

            // Save target income
            $('#targetIncomeForm').on('submit', function(e) {
                e.preventDefault();
                
                const saveBtn = $('#saveTargetIncomeBtn');
                const originalText = saveBtn.html();
                saveBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...').prop('disabled', true);
                
                // Clear previous errors
                $('#daily_target_income').removeClass('is-invalid');
                $('#target-income-error').text('');
                
                $.ajax({
                    url: '{{ route("settings.target-income.update") }}',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        daily_target_income: $('#daily_target_income').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#current-target-display').text('₹' + response.formatted_target_income);
                        } else {
                            toastr.error(response.message || 'An error occurred');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            if (errors.daily_target_income) {
                                $('#daily_target_income').addClass('is-invalid');
                                $('#target-income-error').text(errors.daily_target_income[0]);
                            }
                            toastr.error('Please fix the validation errors');
                        } else {
                            const message = xhr.responseJSON && xhr.responseJSON.message ? 
                                xhr.responseJSON.message : 'An error occurred while saving';
                            toastr.error(message);
                        }
                    },
                    complete: function() {
                        saveBtn.html(originalText).prop('disabled', false);
                    }
                });
            });

            // Clear target income
            $('#clearTargetIncomeBtn').on('click', function() {
                if (confirm('Are you sure you want to clear your target income? This will disable auto-fill functionality.')) {
                    const clearBtn = $(this);
                    const originalText = clearBtn.html();
                    clearBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Clearing...').prop('disabled', true);
                    
                    $.ajax({
                        url: '{{ route("settings.target-income.clear") }}',
                        type: 'DELETE',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                $('#daily_target_income').val('');
                                $('#current-target-display').text('₹' + response.formatted_target_income);
                                $('#daily_target_income').removeClass('is-invalid');
                                $('#target-income-error').text('');
                            } else {
                                toastr.error(response.message || 'An error occurred');
                            }
                        },
                        error: function(xhr) {
                            const message = xhr.responseJSON && xhr.responseJSON.message ? 
                                xhr.responseJSON.message : 'An error occurred while clearing';
                            toastr.error(message);
                        },
                        complete: function() {
                            clearBtn.html(originalText).prop('disabled', false);
                        }
                    });
                }
            });

            // Real-time validation
            $('#daily_target_income').on('input', function() {
                const value = parseFloat($(this).val());
                const input = $(this);
                
                input.removeClass('is-invalid');
                $('#target-income-error').text('');
                
                if ($(this).val() !== '' && (isNaN(value) || value < 0)) {
                    input.addClass('is-invalid');
                    $('#target-income-error').text('Target income must be a positive number');
                } else if (value > 999999.99) {
                    input.addClass('is-invalid');
                    $('#target-income-error').text('Target income cannot exceed ₹999,999.99');
                }
            });
        });
    </script>
@endsection