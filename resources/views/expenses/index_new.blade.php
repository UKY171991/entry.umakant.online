@extends('layouts.main')

@section('title', 'Expenses')
@section('page-title', 'Expenses Management')

@section('content')
<!-- Main content -->
<div class="row">
    <div class="col-12">
        <!-- Filter Section -->
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter mr-1"></i>
                    Filter Options
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="monthFilter">Month</label>
                            <input type="month" class="form-control" id="monthFilter" value="{{ date('Y-m') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="yearFilter">Year</label>
                            <select class="form-control" id="yearFilter">
                                @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                                    <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="categoryFilter">Category</label>
                            <input type="text" class="form-control" id="categoryFilter" placeholder="Search by category...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="expenseNameFilter">Expense Name</label>
                            <input type="text" class="form-control" id="expenseNameFilter" placeholder="Search by expense name...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="d-flex">
                                <button class="btn btn-primary mr-2" id="filterBtn">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <button class="btn btn-warning mr-2" id="clearFilterBtn">
                                    <i class="fas fa-clear"></i> Clear
                                </button>
                                <button class="btn btn-success" id="createNewExpense">
                                    <i class="fas fa-plus"></i> Add Expense
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-receipt mr-1"></i>
                    Expenses List
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table id="expensesTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Expense Name</th>
                            <th>Amount</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be populated by DataTables -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" style="text-align:right">Total:</th>
                            <th><span class="currency-amount currency-negative"><i class="fas fa-rupee-sign rupee-icon"></i>{{ number_format($totalExpenses ?? 0, 2) }}</span></th>
                            <th colspan="3"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modals')
<!-- Modal -->
<div class="modal fade" id="ajaxModel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modelHeading">
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
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="expense_name">
                                    <i class="fas fa-tag mr-1"></i>
                                    Expense Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="expense_name" name="expense_name" placeholder="Enter expense name..." maxlength="50" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount">
                                    <i class="fas fa-rupee-sign mr-1"></i>
                                    Amount <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-rupee-sign"></i></span>
                                    </div>
                                    <input type="number" step="0.01" class="form-control" id="amount" name="amount" placeholder="Enter amount..." required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category">
                                    <i class="fas fa-list mr-1"></i>
                                    Category <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="category" name="category" placeholder="Enter category..." maxlength="50" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date">
                                    <i class="fas fa-calendar mr-1"></i>
                                    Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cancel
                </button>
                <button type="button" class="btn btn-primary" id="saveBtn">
                    <i class="fas fa-save mr-1"></i>
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
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
        #expensesTable td:last-child {
            text-align: center;
            white-space: nowrap;
        }
    </style>
@endsection

@section('scripts')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
<script type="text/javascript">
    $(function () {
        // Initialize DataTable
        var table = $('#expensesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/expenses",
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                data: function(d) {
                    d.month = $('#monthFilter').val();
                    d.year = $('#yearFilter').val();
                    d.category = $('#categoryFilter').val();
                    d.expense_name = $('#expenseNameFilter').val();
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'expense_name', name: 'expense_name'},
                {data: 'amount', name: 'amount', orderable: false},
                {data: 'category', name: 'category'},
                {data: 'date', name: 'date'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            responsive: true,
            language: {
                processing: '<i class="fas fa-spinner fa-spin"></i> Loading...'
            }
        });

        // Create New Expense
        $('#createNewExpense').click(function () {
            $('#saveBtn').val("create-expense");
            $('#expense_id').val('');
            $('#expenseForm').trigger("reset");
            $('#modelHeading').html('<i class="fas fa-receipt mr-2"></i> Add New Expense');
            $('#saveBtn').html('<i class="fas fa-save mr-1"></i> Save Changes');
            $('#ajaxModel').modal('show');
            toastr.info('Ready to add new expense');
        });

        // Reset modal state on close
        $('#ajaxModel').on('hidden.bs.modal', function () {
            $('#expenseForm').trigger("reset");
            $('#expense_id').val('');
            $('#saveBtn').val("create-expense");
            $('#saveBtn').html('<i class="fas fa-save mr-1"></i> Save Changes');
            $('#modelHeading').html('<i class="fas fa-receipt mr-2"></i> Add New Expense');
        });

        // Edit Expense
        $('body').on('click', '.editExpense', function () {
            var expense_id = $(this).data('id');
            $.get("/expenses/" + expense_id + '/edit', function (data) {
                $('#modelHeading').html('<i class="fas fa-edit mr-2"></i> Edit Expense');
                $('#saveBtn').val("edit-expense");
                $('#saveBtn').html('<i class="fas fa-save mr-1"></i> Update Expense');
                $('#ajaxModel').modal('show');
                $('#expense_id').val(data.id);
                $('#expense_name').val(data.expense_name);
                $('#amount').val(data.amount);
                $('#category').val(data.category);
                $('#date').val(data.date);
                toastr.info('Expense data loaded for editing');
            })
            .fail(function(xhr) {
                let message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error loading expense data';
                toastr.error(message);
            });
        });

        // Save Expense (Add or Edit)
        $('#saveBtn').click(function (e) {
            e.preventDefault();
            var saveBtn = $(this);
            var originalText = saveBtn.html();
            saveBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            saveBtn.prop('disabled', true);

            var expenseId = $('#expense_id').val();
            var url = "/expenses";
            var type = "POST";
            if (saveBtn.val() === "edit-expense") {
                url = "/expenses/" + expenseId;
                type = "PUT";
            }

            $.ajax({
                data: $('#expenseForm').serialize(),
                url: url,
                type: type,
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
                        saveBtn.html('<i class="fas fa-save mr-1"></i> Save Changes');
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

        // Filter functionality
        $('#filterBtn').click(function() {
            table.draw();
            toastr.info('Filter applied');
        });

        // Clear filter functionality
        $('#clearFilterBtn').click(function() {
            $('#monthFilter').val('{{ date('Y-m') }}');
            $('#yearFilter').val('{{ date('Y') }}');
            $('#categoryFilter').val('');
            $('#expenseNameFilter').val('');
            table.draw();
            toastr.info('Filters cleared');
        });
    });
</script>
@endsection
