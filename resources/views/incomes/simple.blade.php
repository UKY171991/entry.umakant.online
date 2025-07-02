@extends('layouts.main')

@section('title', 'Income')
@section('page-title', 'Income Records')

@section('content')
<!-- Filter Section -->
<div class="filter-section">
    <div class="row">
        <div class="col-md-3">
            <label for="monthFilter">Month</label>
            <input type="month" class="form-control" id="monthFilter" value="2025-06">
        </div>
        <div class="col-md-3">
            <label for="pendingFilter">Pending Amount</label>
            <select class="form-control" id="pendingFilter">
                <option value="">All</option>
                <option value="0">No Pending</option>
                <option value=">0">Has Pending</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="receivedFilter">Received Amount</label>
            <select class="form-control" id="receivedFilter">
                <option value="">All</option>
                <option value="0">Not Received</option>
                <option value=">0">Received</option>
            </select>
        </div>
        <div class="col-md-3 text-end">
            <br>
            <button class="btn btn-danger" id="filterBtn"><i class="fas fa-filter"></i> Filter</button>
            <button class="btn btn-add text-white" id="createNewIncome"><i class="fas fa-plus"></i> Add Income</button>
        </div>
    </div>
</div>

<!-- Table Section -->
<div class="table-section">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>Income Table</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered data-table">
            <thead class="table-header">
                <tr>
                    <th>CLIENT NAME</th>
                    <th>TOTAL AMOUNT</th>
                    <th>PENDING AMOUNT</th>
                    <th>RECEIVED AMOUNT</th>
                    <th>DATE</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    
    <!-- Totals Row -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="bg-light p-3 rounded">
                <strong>Totals: <span id="totalAmount">0.00</span></strong>
                <span class="ms-3">Pending: <span id="totalPending">0.00</span></span>
                <span class="ms-3">Received: <span id="totalReceived">0.00</span></span>
            </div>
        </div>
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
                <form id="incomeForm" name="incomeForm">
                    <input type="hidden" name="income_id" id="income_id">
                    
                    <div class="mb-3">
                        <label for="client_id" class="form-label">Client</label>
                        <select class="form-control" id="client_id" name="client_id" required>
                            <option value="">Select Client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="total_amount" class="form-label">Total Amount</label>
                        <input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" placeholder="Enter Total Amount" required>
                    </div>

                    <div class="mb-3">
                        <label for="pending_amount" class="form-label">Pending Amount</label>
                        <input type="number" step="0.01" class="form-control" id="pending_amount" name="pending_amount" placeholder="Enter Pending Amount" value="0">
                    </div>

                    <div class="mb-3">
                        <label for="received_amount" class="form-label">Received Amount</label>
                        <input type="number" step="0.01" class="form-control" id="received_amount" name="received_amount" placeholder="Enter Received Amount" required>
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

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endsection

@section('scripts')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    <script type="text/javascript">
    $(function () {
        // Initialize DataTable
        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/incomes",
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                data: function (d) {
                    d.month = $('#monthFilter').val();
                    d.pending_filter = $('#pendingFilter').val();
                    d.received_filter = $('#receivedFilter').val();
                },
                error: function(xhr, error, code) {
                    console.log('DataTables Ajax Error:', xhr.responseText);
                    toastr.error('Error loading data: ' + xhr.statusText);
                }
            },
            columns: [
                {data: 'client_name', name: 'client_name'},
                {data: 'total_amount', name: 'total_amount'},
                {data: 'pending_amount', name: 'pending_amount'},
                {data: 'received_amount', name: 'received_amount'},
                {data: 'date', name: 'date'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            responsive: true,
            language: {
                processing: '<i class="fas fa-spinner fa-spin"></i> Loading...'
            },
            drawCallback: function(settings) {
                // Update totals when table is redrawn
                var api = this.api();
                var response = api.ajax.json();
                if (response && response.totals) {
                    $('#totalAmount').text(response.totals.total_amount);
                    $('#totalPending').text(response.totals.total_pending);
                    $('#totalReceived').text(response.totals.total_received);
                }
            }
        });

        // Create New Income
        $('#createNewIncome').click(function () {
            $('#saveBtn').val("create-income");
            $('#income_id').val('');
            $('#incomeForm').trigger("reset");
            $('#modelHeading').html("Create New Income");
            $('#ajaxModel').modal('show');
        });

        // Reset modal state on close
        $('#ajaxModel').on('hidden.bs.modal', function () {
            $('#incomeForm').trigger("reset");
            $('#income_id').val('');
            $('#saveBtn').val("create-income");
            $('#modelHeading').html("Create New Income");
        });

        // Edit Income
        $('body').on('click', '.editIncome', function () {
            var income_id = $(this).data('id');
            $.get("/incomes/" + income_id + '/edit', function (data) {
                $('#modelHeading').html("Edit Income");
                $('#saveBtn').val("edit-income");
                $('#ajaxModel').modal('show');
                $('#income_id').val(data.id);
                $('#client_id').val(data.client_id);
                $('#total_amount').val(data.total_amount);
                $('#pending_amount').val(data.pending_amount);
                $('#received_amount').val(data.received_amount);
                $('#date').val(data.date);
            })
            .fail(function() {
                toastr.error('Error loading income data');
            });
        });

        // Save Income
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
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data) {
                    $('#incomeForm').trigger("reset");
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

        // Delete Income
        $('body').on('click', '.deleteIncome', function () {
            var income_id = $(this).data("id");
            
            if(confirm("Are you sure you want to delete this income record?")) {
                $.ajax({
                    type: "DELETE",
                    url: "/incomes/" + income_id,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
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
            table.draw();
        });
    });
</script>
@endsection
