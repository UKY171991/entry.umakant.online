@extends('layouts.main')

@section('title', 'Expenses')
@section('page-title', 'Expenses Management')

@section('content')
<!-- Filter Section -->
<div class="filter-section">
    <div class="row">
        <div class="col-md-3">
            <label for="monthFilter">Month</label>
            <input type="month" class="form-control" id="monthFilter" value="2025-06">
        </div>
        <div class="col-md-3">
            <label for="categoryFilter">Category</label>
            <input type="text" class="form-control" id="categoryFilter" placeholder="Category">
        </div>
        <div class="col-md-3">
            <label for="expenseNameFilter">Expense Name</label>
            <input type="text" class="form-control" id="expenseNameFilter" placeholder="Expense Name">
        </div>
        <div class="col-md-3 text-end">
            <br>
            <button class="btn btn-danger" id="filterBtn"><i class="fas fa-filter"></i> Filter</button>
            <button class="btn btn-add text-white" id="createNewExpense"><i class="fas fa-plus"></i> Add Expense</button>
        </div>
    </div>
</div>

<!-- Table Section -->
<div class="table-section">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>Expenses Table</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered data-table">
            <thead class="table-header">
                <tr>
                    <th>#</th>
                    <th>EXPENSE NAME</th>
                    <th>AMOUNT</th>
                    <th>CATEGORY</th>
                    <th>DATE</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('modals')
<!-- Modal -->
<div class="modal fade" id="ajaxModel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modelHeading"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="expenseForm" name="expenseForm">
                    <input type="hidden" name="expense_id" id="expense_id">
                    
                    <div class="mb-3">
                        <label for="expense_name" class="form-label">Expense Name</label>
                        <input type="text" class="form-control" id="expense_name" name="expense_name" placeholder="Enter Expense Name" maxlength="50" required>
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="amount" name="amount" placeholder="Enter Amount" required>
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control" id="category" name="category" placeholder="Enter Category" maxlength="50" required>
                    </div>

                    <div class="mb-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveBtn">Save changes</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
    $(function () {
        // Initialize DataTable
        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/expenses",
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                data: function(d) {
                    d.month = $('#monthFilter').val();
                    d.category = $('#categoryFilter').val();
                    d.expense_name = $('#expenseNameFilter').val();
                }
            },
            columns: [
                {data: 'sr_no', name: 'sr_no', orderable: false, searchable: false},
                {data: 'expense_name', name: 'expense_name'},
                {data: 'amount', name: 'amount'},
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
            $('#modelHeading').html("Create New Expense");
            $('#ajaxModel').modal('show');
        });

        // Reset modal state on close
        $('#ajaxModel').on('hidden.bs.modal', function () {
            $('#expenseForm').trigger("reset");
            $('#expense_id').val('');
            $('#saveBtn').val("create-expense");
            $('#modelHeading').html("Create New Expense");
        });

        // Edit Expense
        $('body').on('click', '.editExpense', function () {
            var expense_id = $(this).data('id');
            $.get("/expenses/" + expense_id + '/edit', function (data) {
                $('#modelHeading').html("Edit Expense");
                $('#saveBtn').val("edit-expense");
                $('#ajaxModel').modal('show');
                $('#expense_id').val(data.id);
                $('#expense_name').val(data.expense_name);
                $('#amount').val(data.amount);
                $('#category').val(data.category);
                $('#date').val(data.date);
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
                    saveBtn.html(originalText);
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
    });
</script>
@endsection
