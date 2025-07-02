@extends('layouts.main')

@section('title', 'Expenses')
@section('page-title', 'Expenses Management')

@section('content')</a>
                    <a class="nav-link" href="/emails"><i class="fas fa-envelope"></i> Email</a>
                    <a class="nav-link" href="/websites"><i class="fas fa-globe"></i> websites</a>
                    <a class="nav-link" href="/pending-tasks"><i class="fas fa-tasks"></i> Pending Task</a>
                </nav>
                <div class="mt-auto p-3">
                    <button class="btn btn-outline-light btn-sm w-100">Logout</button>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10">
                <!-- Header -->
                <div class="content-header">
                    <h2>Income Records</h2>
                </div>
                
                <!-- Filter Section -->
                <div class="filter-section">
                    <div class="row">
                        <div class="col-md-3">
                            <input type="month" class="form-control" id="monthFilter" value="2025-06">
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control" id="dateFilter" placeholder="dd-mm-yyyy">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="expenseNameFilter" placeholder="Expense Name">
                        </div>
                        <div class="col-md-3 text-end">
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
            </div>
        </div>
    </div>

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

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script type="text/javascript">
        $(function () {
            // CSRF Token Setup
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Toastr Configuration
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            // Initialize DataTable
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "/expenses",
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
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
                .fail(function() {
                    toastr.error('Error loading expense data');
                });
            });

            // Save Expense
            $('#saveBtn').click(function (e) {
                e.preventDefault();
                var saveBtn = $(this);
                var originalText = saveBtn.html();
                saveBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                saveBtn.prop('disabled', true);

                $.ajax({
                    data: $('#expenseForm').serialize(),
                    url: "/expenses",
                    type: "POST",
                    dataType: 'json',
                    success: function (data) {
                        $('#expenseForm').trigger("reset");
                        $('#ajaxModel').modal('hide');
                        table.draw();
                        toastr.success(data.success);
                    },
                    error: function (data) {
                        console.log('Error:', data);
                        var message = data.responseJSON ? data.responseJSON.message : 'An error occurred';
                        toastr.error(message);
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
                        success: function (data) {
                            table.draw();
                            toastr.success(data.success);
                        },
                        error: function (data) {
                            console.log('Error:', data);
                            var message = data.responseJSON ? data.responseJSON.message : 'An error occurred';
                            toastr.error(message);
                        }
                    });
                }
            });

            // Filter functionality
            $('#filterBtn').click(function() {
                // Add filter logic here if needed
                table.draw();
            });
        });
    </script>
</body>
</html>