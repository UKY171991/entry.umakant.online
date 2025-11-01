@extends('layouts.main')

@section('title', 'Income')
@section('page-title', 'Income Management')

@section('content')
<!-- Filter Section -->
<div class="filter-section">
    <div class="row">
        <div class="col-md-3">
            <select class="form-control filter-select" id="clientFilter" data-placeholder="Select Client">
                <option value=""></option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-control filter-select" id="monthFilter">
                <option value="">All Months</option>
                @php
                    $months = [
                        '01' => 'January', '02' => 'February', '03' => 'March', 
                        '04' => 'April', '05' => 'May', '06' => 'June', 
                        '07' => 'July', '08' => 'August', '09' => 'September', 
                        '10' => 'October', '11' => 'November', '12' => 'December'
                    ];
                    $currentMonth = date('m');
                    $currentYear = date('Y');
                @endphp
                @php
                    $currentMonth = date('m');
                    $currentYear = date('Y');
                @endphp
                @foreach($months as $key => $month)
                    @php
                        $isCurrentMonth = ($key == $currentMonth);
                    @endphp
                    <option value="{{ $key }}" {{ $isCurrentMonth ? 'selected' : '' }}>
                        {{ $month }} {{ $currentYear }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1" style="display: none;">
            <input type="hidden" class="form-control filter-input" id="yearFilter" value="{{ $currentYear }}">
        </div>
        <div class="col-md-2">
            <select class="form-control filter-select" id="statusFilter" data-placeholder="Select Status">
                <option value=""></option>
                <option value="pending">Pending</option>
                <option value="received">Received</option>
                <option value="partial">Partial</option>
            </select>
        </div>
        <div class="col-md-3 text-end">
            <button class="btn btn-danger" id="filterBtn"><i class="fas fa-filter"></i> Filter</button>
            <button class="btn btn-success" id="createNewIncome"><i class="fas fa-plus"></i> Add Income</button>
        </div>
    </div>
</div>

<!-- Table Section -->
<div class="table-section">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>Income Records</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered data-table">
            <thead class="table-header">
                <tr>
                    <th>#</th>
                    <th>CLIENT</th>
                    <th>TOTAL AMOUNT</th>
                    <th>PENDING AMOUNT</th>
                    <th>RECEIVED AMOUNT</th>
                    <th>DATE</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
                <tr class="table-totals">
                    <th colspan="2" style="text-align:right; font-weight: bold;">Totals:</th>
                    <th id="total-amount-footer" style="font-weight: bold;">
                        <span class="currency-amount currency-positive">
                            <i class="fas fa-rupee-sign rupee-icon"></i>0.00
                        </span>
                    </th>
                    <th id="total-pending-footer" style="font-weight: bold;">
                        <span class="currency-amount currency-warning">
                            <i class="fas fa-rupee-sign rupee-icon"></i>0.00
                        </span>
                    </th>
                    <th id="total-received-footer" style="font-weight: bold;">
                        <span class="currency-amount currency-info">
                            <i class="fas fa-rupee-sign rupee-icon"></i>0.00
                        </span>
                    </th>
                    <th colspan="2"></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

@section('modals')
<!-- Add/Edit Income Modal -->
<div class="modal fade" id="ajaxModel" tabindex="-1" role="dialog" aria-labelledby="modelHeading" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modelHeading">
                    <i class="fas fa-rupee-sign mr-2"></i>
                    Add New Income
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="incomeForm" name="incomeForm">
                    @csrf
                    <input type="hidden" name="income_id" id="income_id">
                    
                    <div class="form-group">
                        <label for="client_id">
                            <i class="fas fa-user mr-1"></i>
                            Client <span class="text-danger">*</span>
                        </label>
                        <select class="form-control" id="client_id" name="client_id" required>
                            <option value="">Select Client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="total_amount">
                            <i class="fas fa-rupee-sign mr-1"></i>
                            Total Amount <span class="text-danger">*</span>
                            <small id="target-income-indicator" class="badge badge-info ml-2" style="display: none;">
                                <i class="fas fa-bullseye"></i> Target Amount
                            </small>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-rupee-sign"></i></span>
                            </div>
                            <input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" placeholder="Enter Total Amount" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="pending_amount">
                            <i class="fas fa-clock mr-1"></i>
                            Pending Amount <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-rupee-sign"></i></span>
                            </div>
                            <input type="number" step="0.01" class="form-control" id="pending_amount" name="pending_amount" placeholder="Enter Pending Amount" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="received_amount">
                            <i class="fas fa-check-circle mr-1"></i>
                            Received Amount <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-rupee-sign"></i></span>
                            </div>
                            <input type="number" step="0.01" class="form-control" id="received_amount" name="received_amount" placeholder="Enter Received Amount" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="date">
                            <i class="fas fa-calendar mr-1"></i>
                            Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cancel
                </button>
                <button type="button" class="btn btn-primary" id="saveBtn" value="create-income">
                    <i class="fas fa-save mr-1"></i>
                    Save Income
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        /* Button group styling for action columns */
        .btn-group .btn {
            border-radius: 0;
            margin: 0;
        }
        .btn-group .btn:first-child {
            border-top-left-radius: 4px;
            border-bottom-left-radius: 4px;
        }
        .btn-group .btn:last-child {
            border-top-right-radius: 4px;
            border-bottom-right-radius: 4px;
        }
        .btn-group .btn + .btn {
            border-left: 1px solid rgba(255,255,255,0.2);
        }
        /* Ensure consistent button sizing */
        .btn-group .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            min-width: 32px;
        }
        /* Action column styling */
        .data-table td:last-child {
            text-align: center;
            white-space: nowrap;
        }
        
        /* Totals row styling */
        .table-totals {
            background-color: #f8f9fa;
            border-top: 2px solid #dee2e6;
        }
        
        .table-totals th {
            border-top: 2px solid #dee2e6;
            font-size: 1.1em;
        }
        
        /* Currency amount styling */
        .currency-amount {
            font-weight: bold;
        }
        
        .currency-positive {
            color: #28a745;
        }
        
        .currency-warning {
            color: #ffc107;
        }
        
        .currency-info {
            color: #17a2b8;
        }
        
        .rupee-icon {
            margin-right: 2px;
        }
        
        /* Style for Select2 dropdowns */
        .select2-container--bootstrap-5 .select2-selection {
            min-height: 38px;
            display: flex;
            align-items: center;
        }
        
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            padding-left: 12px;
        }
        
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        
        /* Target income indicator styling */
        #target-income-indicator {
            font-size: 0.75rem;
            vertical-align: middle;
            background-color: #17a2b8;
            color: white;
            border-radius: 12px;
            padding: 2px 8px;
        }
        
        #target-income-indicator i {
            margin-right: 3px;
        }
    </style>
@endsection

@section('scripts')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Make user target income available to JavaScript
        window.userTargetIncome = {{ $userTargetIncome ?? 'null' }};
        
        // Function to get first and last day of a month
        function getMonthRange(month, year) {
            if (!month) return { from: null, to: null };
            
            const firstDay = new Date(year, month - 1, 1);
            const lastDay = new Date(year, month, 0);
            
            // Format as YYYY-MM-DD for date inputs
            const format = date => date.toISOString().split('T')[0];
            
            return {
                from: format(firstDay),
                to: format(lastDay)
            };
        }

        $(function () {
            // Initialize Select2
            $('.filter-select').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: function() {
                    return $(this).data('placeholder') || 'Select an option';
                },
                allowClear: true
            });

            // Initialize DataTable
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "/incomes",
                    type: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    data: function(d) {
                        const month = $('#monthFilter').val();
                        const year = $('#yearFilter').val();
                        const dateRange = getMonthRange(month, year);
                        
                        var params = {
                            client_id: $('#clientFilter').val() || '',
                            status: $('#statusFilter').val() || '',
                            month: month,
                            year: year,
                            draw: d.draw,
                            start: d.start,
                            length: d.length,
                            search: d.search,
                            order: d.order
                        };
                        
                        // Add date range if month is selected
                        if (month) {
                            params.date_from = dateRange.from;
                            params.date_to = dateRange.to;
                        }
                        
                        return params;
                    },
                    dataSrc: function(json) {
                        // Update totals in footer when data is loaded
                        if (json.totals) {
                            $('#total-amount-footer').html(json.totals.total_amount);
                            $('#total-pending-footer').html(json.totals.total_pending);
                            $('#total-received-footer').html(json.totals.total_received);
                        }
                        return json.data;
                    }
                },
                columns: [
                    {data: 'sr_no', name: 'sr_no', orderable: false, searchable: false},
                    {data: 'client_name', name: 'client.name'},
                    {data: 'total_amount', name: 'total_amount'},
                    {data: 'pending_amount', name: 'pending_amount'},
                    {data: 'received_amount', name: 'received_amount'},
                    {data: 'date', name: 'date'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                responsive: true,
                language: {
                    processing: '<i class="fas fa-spinner fa-spin"></i> Loading...'
                }
            });

            // NOTE: Your server-side should return the following HTML for the action column:
            // <button class="btn btn-info editIncome" data-id="INCOME_ID">Edit</button>
            // <button class="btn btn-danger deleteIncome" data-id="INCOME_ID">Delete</button>

            // Create New Income
            $('#createNewIncome').click(function () {
                $('#saveBtn').val("create-income");
                $('#income_id').val('');
                $('#incomeForm').trigger("reset");
                $('#modelHeading').html('<i class="fas fa-rupee-sign mr-2"></i> Add New Income');
                $('#ajaxModel').modal('show');
                
                // Auto-fill target income if available (only for new entries)
                if (window.userTargetIncome && window.userTargetIncome > 0) {
                    $('#total_amount').val(window.userTargetIncome);
                    // Show visual indicator that target income was auto-filled
                    $('#target-income-indicator').show();
                    // Trigger calculation for pending/received amounts
                    $('#total_amount').trigger('input');
                    toastr.info('Ready to add new income (target amount auto-filled)');
                } else {
                    // Hide indicator if no target income
                    $('#target-income-indicator').hide();
                    toastr.info('Ready to add new income');
                }
            });

            // Reset modal state on close
            $('#ajaxModel').on('hidden.bs.modal', function () {
                $('#incomeForm').trigger("reset");
                $('#income_id').val('');
                $('#saveBtn').val("create-income");
                $('#saveBtn').html('<i class="fas fa-save mr-1"></i> Save Income');
                $('#modelHeading').html('<i class="fas fa-rupee-sign mr-2"></i> Add New Income');
                // Reset client dropdown to placeholder
                $('#client_id').val('');
                // Hide target income indicator
                $('#target-income-indicator').hide();
            });

            // Edit Income
            $('body').on('click', '.editIncome', function () {
                var income_id = $(this).data('id');
                $.get("/incomes/" + income_id + '/edit', function (data) {
                    $('#modelHeading').html('<i class="fas fa-edit mr-2"></i> Edit Income');
                    $('#saveBtn').val("edit-income");
                    $('#saveBtn').html('<i class="fas fa-save mr-1"></i> Update Income');
                    $('#ajaxModel').modal('show');
                    $('#income_id').val(data.id);
                    $('#client_id').val(data.client_id);
                    $('#total_amount').val(data.total_amount);
                    $('#pending_amount').val(data.pending_amount);
                    $('#received_amount').val(data.received_amount);
                    $('#date').val(data.date);
                    // Hide target income indicator for edits (no auto-fill for existing entries)
                    $('#target-income-indicator').hide();
                    toastr.info('Income data loaded for editing');
                })
                .fail(function(xhr) {
                    let message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error loading income data';
                    toastr.error(message);
                });
            });

            // Save Income (Add or Edit)
            $('#saveBtn').click(function (e) {
                e.preventDefault();
                var saveBtn = $(this);
                var originalText = saveBtn.html();
                saveBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                saveBtn.prop('disabled', true);

                var incomeId = $('#income_id').val();
                var url = "/incomes";
                var type = "POST";
                if (saveBtn.val() === "edit-income") {
                    url = "/incomes/" + incomeId;
                    type = "PUT";
                }

                $.ajax({
                    data: $('#incomeForm').serialize(),
                    url: url,
                    type: type,
                    dataType: 'json',
                    success: function (data) {
                        $('#incomeForm').trigger("reset");
                        $('#ajaxModel').modal('hide');
                        table.draw();
                        toastr.success(data.success || data.message || 'Operation successful');
                    },
                    error: function (xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            let messages = Object.values(errors).map(arr => arr.join('<br>')).join('<br>');
                            toastr.error(messages);
                        } else {
                            let message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred';
                            toastr.error(message);
                        }
                    },
                    complete: function() {
                        if (saveBtn.val() === "edit-income") {
                            saveBtn.html('<i class="fas fa-save mr-1"></i> Update Income');
                        } else {
                            saveBtn.html('<i class="fas fa-save mr-1"></i> Save Income');
                        }
                        saveBtn.prop('disabled', false);
                    }
                });
            });

            // Delete Income
            $('body').on('click', '.deleteIncome', function () {
                var income_id = $(this).data("id");
                if(confirm("Are you sure you want to delete this income record?")) {
                    $.ajax({
                        type: "DELETE",
                        url: "/incomes/" + income_id,
                        data: { _token: $('meta[name="csrf-token"]').attr('content') },
                        success: function (data) {
                            table.draw();
                            toastr.success(data.success || data.message || 'Income deleted successfully');
                        },
                        error: function (xhr) {
                            let message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred';
                            toastr.error(message);
                        }
                    });
                }
            });

            // Filter functionality
            function applyFilters() {
                table.ajax.reload(null, false); // false means don't reset paging
                toastr.info('Applying filters...');
            }
            
            // Handle filter button click
            $('#filterBtn').click(applyFilters);
            
            // Handle changes in select elements
            $('.filter-select').on('change', function() {
                applyFilters();
            });
            
            // Handle Enter key in filter inputs
            $('.filter-input').on('keypress', function(e) {
                if (e.which === 13) {
                    applyFilters();
                }
            });
            
            // Initialize with current month selected
            const currentMonth = '{{ date('m') }}';
            if ($('#monthFilter').val() === '') {
                $('#monthFilter').val(currentMonth).trigger('change');
            }

            // Auto calculate amounts
            $('#total_amount, #received_amount').on('input', function() {
                var total = parseFloat($('#total_amount').val()) || 0;
                var received = parseFloat($('#received_amount').val()) || 0;
                var pending = total - received;
                if (pending >= 0) {
                    $('#pending_amount').val(pending.toFixed(2));
                }
            });

            $('#pending_amount').on('input', function() {
                var total = parseFloat($('#total_amount').val()) || 0;
                var pending = parseFloat($('#pending_amount').val()) || 0;
                var received = total - pending;
                if (received >= 0) {
                    $('#received_amount').val(received.toFixed(2));
                }
            });

            // Hide target income indicator when user manually changes the total amount
            $('#total_amount').on('input', function() {
                // Check if the current value is different from the target income
                var currentValue = parseFloat($(this).val()) || 0;
                var targetIncome = parseFloat(window.userTargetIncome) || 0;
                
                // Hide indicator if user has modified the auto-filled value
                if (currentValue !== targetIncome && $('#target-income-indicator').is(':visible')) {
                    $('#target-income-indicator').fadeOut();
                }
            });
        });
    </script>
@endsection