@extends('layouts.main')

@section('title', 'Expenses')
@section('page-title', 'Expenses Management')

@section('content')
<!-- Filter Section -->
<div class="filter-section card mb-4">
    <div class="card-header py-2">
        <h6 class="m-0 font-weight-bold text-primary">Filter Expenses</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="expenseNameFilter">Search</label>
                    <input type="text" class="form-control form-control-sm" id="expenseNameFilter" placeholder="Search expenses...">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="categoryFilter">Category</label>
                    <select class="form-control form-control-sm" id="categoryFilter">
                        <option value="" selected>All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="monthFilter">Month</label>
                    <select class="form-control form-control-sm" id="monthFilter">
                        <option value="">All Time</option>
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
                        @foreach($months as $key => $month)
                            <option value="{{ $key }}" {{ $key == $currentMonth ? 'selected' : '' }}>
                                {{ $month }} {{ $currentYear }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" id="yearFilter" value="{{ $currentYear }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="statusFilter">Status</label>
                    <select class="form-control form-control-sm" id="statusFilter">
                        <option value="" selected>All Status</option>
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                        <option value="recurring">Recurring</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 text-right">
                <button class="btn btn-sm btn-danger" id="filterBtn" type="button">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
                <button class="btn btn-sm btn-success" id="createNewExpense">
                    <i class="fas fa-plus"></i> Add New Expense
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Table Section -->
<div class="table-section">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>Expense Records</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered data-table">
            <thead class="table-header">
                <tr>
                    <th>#</th>
                    <th>EXPENSE NAME</th>
                    <th>AMOUNT</th>
                    <th>CATEGORY</th>
                    <th>STATUS</th>
                    <th>DATE</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be loaded by DataTables -->
            </tbody>
            <tfoot>
                <tr class="table-totals">
                    <th colspan="2" style="text-align:right; font-weight: bold;">Totals:</th>
                    <th id="total-amount-footer" style="font-weight: bold;">
                        <span class="currency-amount currency-negative">
                            <i class="fas fa-rupee-sign rupee-icon"></i>0.00
                        </span>
                    </th>
                    <th colspan="4"></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

@section('modals')
<!-- Add/Edit Expense Modal -->
<div class="modal fade" id="ajaxModel" tabindex="-1" role="dialog" aria-labelledby="expenseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="expenseModalLabel">
                    <i class="fas fa-receipt mr-2"></i>
                    Add New Expense
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="expenseForm" name="expenseForm">
                    @csrf
                    <input type="hidden" name="expense_id" id="expense_id">
                    
                    <div class="form-group">
                        <label for="expense_name">
                            <i class="fas fa-tag mr-1"></i>
                            Expense Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="expense_name" name="expense_name" placeholder="Enter expense name" required>
                    </div>

                    <div class="form-group">
                        <label for="amount">
                            <i class="fas fa-rupee-sign mr-1"></i>
                            Amount <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-rupee-sign"></i></span>
                            </div>
                            <input type="number" step="0.01" class="form-control" id="amount" name="amount" placeholder="Enter amount" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">
                            <i class="fas fa-list mr-1"></i>
                            Category <span class="text-danger">*</span>
                        </label>
                        <select class="form-control select2" id="category" name="category" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">
                            <i class="fas fa-info-circle mr-1"></i>
                            Status
                        </label>
                        <select class="form-control" id="status" name="status">
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                            <option value="recurring">Recurring</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date">
                            <i class="fas fa-calendar mr-1"></i>
                            Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>

                    <div class="form-group">
                        <label for="notes">
                            <i class="fas fa-sticky-note mr-1"></i>
                            Notes
                        </label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Add any additional notes"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cancel
                </button>
                <button type="button" class="btn btn-primary" id="saveBtn" value="create-expense">
                    <i class="fas fa-save mr-1"></i>
                    Save Expense
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
        /* Button group styling for action columns (match incomes page) */
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
        .currency-amount { font-weight: bold; }
        .currency-positive { color: #28a745; }
        .currency-warning { color: #ffc107; }
        .currency-info { color: #17a2b8; }
        .currency-negative { color: #e74a3b; }
        .rupee-icon { margin-right: 2px; }
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
    </style>
@endsection

@section('scripts')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script type="text/javascript">
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
        // Initialize Select2 ONLY for modal form dropdowns (category select in add/edit form)
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Select Category',
            allowClear: true,
            dropdownParent: $('#ajaxModel') // Ensure dropdown works in modal
        });

        // Set default date to today
        $('#date').val(new Date().toISOString().split('T')[0]);
        // Initialize DataTable
        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            stateSave: true,
            autoWidth: false,
            pageLength: 25,
            order: [[5, 'desc']], // Default sort by date desc
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            searching: false, // Disable the default search box
            language: {
                processing: '<i class="fas fa-spinner fa-spin"></i> Loading...',
                emptyTable: 'No expenses found',
                zeroRecords: 'No matching expenses found',
                info: 'Showing _START_ to _END_ of _TOTAL_ expenses',
                infoEmpty: 'Showing 0 to 0 of 0 expenses',
                infoFiltered: '(filtered from _MAX_ total expenses)',
                search: '_INPUT_',
                searchPlaceholder: 'Search expenses...',
                paginate: {
                    first: '«',
                    last: '»',
                    next: '›',
                    previous: '‹'
                },
                lengthMenu: 'Show _MENU_ entries',
                loadingRecords: 'Loading...',
            },
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            ajax: {
                url: "{{ route('expenses.index') }}",
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: function(d) {
                    let month = $('#monthFilter').val();
                    let year = $('#yearFilter').val();
                    const searchTerm = $('#expenseNameFilter').val() || '';
                    
                    // If month is in YYYY-MM format, split it
                    if (month && month.includes('-')) {
                        const parts = month.split('-');
                        if (parts.length === 2) {
                            year = parts[0];
                            month = parts[1];
                        }
                    }
                    
                    const dateRange = getMonthRange(month, year);
                    
                    var params = {
                        category: $('#categoryFilter').val() || '',
                        status: $('#statusFilter').val() || '',
                        month: month,
                        year: year,
                        search: searchTerm,
                        draw: d.draw,
                        start: d.start,
                        length: d.length,
                        order: d.order,
                        _token: '{{ csrf_token() }}'
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
                    if (json.totals && json.totals.total_amount) {
                        // Use server-provided total (comes as formatted string like "8,000.00")
                        // Remove commas before parsing to get correct number
                        let totalString = json.totals.total_amount.toString();
                        let totalNumber = parseFloat(totalString.replace(/,/g, ''));
                        
                        const formattedTotal = totalNumber.toLocaleString('en-IN', {
                            maximumFractionDigits: 2,
                            minimumFractionDigits: 2
                        });
                        
                        $('#total-amount-footer').html(`
                            <span class="currency-amount currency-negative">
                                <i class="fas fa-rupee-sign rupee-icon"></i>${formattedTotal}
                            </span>
                        `);
                    } else {
                        $('#total-amount-footer').html(`
                            <span class="currency-amount currency-negative">
                                <i class="fas fa-rupee-sign rupee-icon"></i>0.00
                            </span>
                        `);
                    }
                    return json.data || [];
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                {data: 'expense_name', name: 'expense_name'},
                {
                    data: 'amount', 
                    name: 'amount',
                    className: 'text-right',
                    render: function(data, type, row) {
                        // Handle both HTML and raw number formats
                        const amount = typeof data === 'string' && data.includes('rupee-icon') 
                            ? data 
                            : `<span class="currency-amount currency-negative">
                                <i class="fas fa-rupee-sign rupee-icon"></i>${parseFloat(data || 0).toLocaleString('en-IN', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                })}
                               </span>`;
                        return amount;
                    }
                },
                {
                    data: 'category',
                    name: 'category',
                    render: function(data) {
                        return data || '';
                    }
                },
                {
                    data: 'status', 
                    name: 'status', 
                    className: 'text-center',
                    render: function(data) {
                        // Return empty string if status is not available
                        return data || '';
                    }
                },
                {
                    data: 'date', 
                    name: 'date', 
                    className: 'text-center',
                    render: function(data) {
                        // Format date if needed
                        if (!data) return '';
                        const date = new Date(data);
                        return isNaN(date) ? data : date.toLocaleDateString('en-IN');
                    }
                },
                {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'}
            ],
            initComplete: function() {
                // Auto-apply current month filter on page load (month is already pre-selected in HTML)
                // This will show expenses for the current month by default
            }
        });

        // Filter functionality
        function applyFilters() {
            // Reset to first page when applying filters
            if (table.page.info()) {
                table.page('first');
            }
            table.draw();
            toastr.info('Applying filters...');
        }
        
        // Handle filter button click
        $('#filterBtn').click(function(e) {
            e.preventDefault();
            applyFilters();
        });
        
        // Handle changes in all filter dropdowns
        $('#categoryFilter, #monthFilter, #statusFilter').on('change', function() {
            applyFilters();
        });
        
        // Debounced search for expense name filter
        let searchTimeout;
        $('#expenseNameFilter').on('keyup', function(e) {
            clearTimeout(searchTimeout);
            
            // Apply immediately on Enter key
            if (e.which === 13) {
                applyFilters();
                return false;
            }
            
            // Otherwise debounce the search
            searchTimeout = setTimeout(function() {
                applyFilters();
            }, 500);
        });
        
        // DataTable's search box is disabled in favor of our custom search
        
        // Add CSRF token to all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Create New Expense
        $('#createNewExpense').click(function () {
            $('#expenseForm').trigger("reset");
            $('#expenseModalLabel').html('<i class="fas fa-receipt mr-2"></i> Add New Expense');
            $('#saveBtn').val("create-expense");
            $('#saveBtn').html('<i class="fas fa-save mr-1"></i> Save Expense');
            $('#expense_id').val('');
            
            // Reset Select2
            $('.select2').val(null).trigger('change');
            
            // Clear validation errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            
            // Set default date to today
            $('#date').val(new Date().toISOString().split('T')[0]);
            
            $('#ajaxModel').modal('show');
            
            // Set default date to today
            document.getElementById('date').valueAsDate = new Date();
            
            toastr.info('Ready to add new expense');
        });

        // Reset modal state on close
        $('#ajaxModel').on('hidden.bs.modal', function () {
            $('#expenseForm').trigger("reset");
            $('#expense_id').val('');
            $('#saveBtn').val("create-expense");
            $('#saveBtn').html('<i class="fas fa-save mr-1"></i> Save Expense');
            $('#expenseModalLabel').html('<i class="fas fa-receipt mr-2"></i> Add New Expense');
            // Reset Select2
            $('.select2').val(null).trigger('change');
            // Clear validation errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
        });

        // Edit Expense
        $('body').on('click', '.editExpense', function () {
            var expense_id = $(this).data('id');
            $.get("/expenses/" + expense_id + '/edit', function (data) {
                $('#expenseModalLabel').html('<i class="fas fa-edit mr-2"></i> Edit Expense');
                $('#saveBtn').val("edit-expense");
                $('#saveBtn').html('<i class="fas fa-save mr-1"></i> Update Expense');
                $('#ajaxModel').modal('show');
                
                // Populate form fields
                $('#expense_id').val(data.id);
                $('#expense_name').val(data.expense_name);
                $('#amount').val(parseFloat(data.amount).toFixed(2));
                
                // Set Select2 value for category
                if (data.category) {
                    $('#category').val(data.category).trigger('change');
                }
                
                // Set status
                if (data.status) {
                    $('#status').val(data.status);
                }
                
                // Format date to YYYY-MM-DD
                if (data.date) {
                    const date = new Date(data.date);
                    const formattedDate = date.toISOString().split('T')[0];
                    $('#date').val(formattedDate);
                }
                
                // Set notes if available
                if (data.notes) {
                    $('#notes').val(data.notes);
                }
                
                toastr.info('Expense data loaded for editing');
            })
            .fail(function(xhr) {
                let message = xhr.responseJSON && xhr.responseJSON.message ? 
                    xhr.responseJSON.message : 'Error loading expense data';
                toastr.error(message);
            });
        });

        // Save button click handler - triggers form submission
        $('#saveBtn').click(function(e) {
            e.preventDefault();
            $('#expenseForm').submit();
        });

        // Save Expense (Add or Edit)
        $('#expenseForm').on('submit', function (e) {
            e.preventDefault();
            
            var saveBtn = $('#saveBtn');
            var originalText = saveBtn.html();
            saveBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            saveBtn.prop('disabled', true);
            
            // Reset validation
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            var expenseId = $('#expense_id').val();
            var url = "/expenses";
            var method = "POST";
            
            if (expenseId) {
                url = "/expenses/" + expenseId;
                method = "PUT";
            }

            $.ajax({
                url: url,
                type: method,
                data: $('#expenseForm').serialize(),
                dataType: 'json',
                success: function (data) {
                    $('#expenseForm').trigger("reset");
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
                    if (saveBtn.val() === "edit-expense") {
                        saveBtn.html('<i class="fas fa-save mr-1"></i> Update Expense');
                    } else {
                        saveBtn.html('<i class="fas fa-save mr-1"></i> Save Expense');
                    }
                    saveBtn.prop('disabled', false);
                }
            });
        });

        // Delete Expense
        $('body').on('click', '.deleteExpense', function () {
            var expense_id = $(this).data("id");
            if(confirm("Are you sure you want to delete this expense?")) {
                $.ajax({
                    type: "DELETE",
                    url: "/expenses/" + expense_id,
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function (data) {
                        table.draw();
                        toastr.success(data.success || data.message || 'Expense deleted successfully');
                    },
                    error: function (xhr) {
                        let message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred';
                        toastr.error(message);
                    }
                });
            }
        });

    });
</script>
@endsection
