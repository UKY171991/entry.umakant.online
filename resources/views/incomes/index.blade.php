@extends('adminlte::page')

@section('title', 'Incomes')

@section('content_header')
    <h1>Incomes</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Income List</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addIncomeModal">
                    Add Income
                </button>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover" id="incomes-table">
                <thead>
                    <tr>
                        <th>SR No.</th>
                        <th>Client Name</th>
                        <th>Total Amount</th>
                        <th>Pending Amount</th>
                        <th>Received Amount</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Income Modal -->
    <div class="modal fade" id="addIncomeModal" tabindex="-1" role="dialog" aria-labelledby="addIncomeModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addIncomeModalLabel">Add New Income</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addIncomeForm">
                    <div class="modal-body">
                        @csrf
                        <div class="form-group">
                            <label for="client_id">Client Name</label>
                            <select class="form-control" id="client_id" name="client_id" required>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="total_amount">Total Amount</label>
                            <input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" required>
                        </div>
                        <div class="form-group">
                            <label for="pending_amount">Pending Amount</label>
                            <input type="number" step="0.01" class="form-control" id="pending_amount" name="pending_amount" required>
                        </div>
                        <div class="form-group">
                            <label for="received_amount">Received Amount</label>
                            <input type="number" step="0.01" class="form-control" id="received_amount" name="received_amount" required>
                        </div>
                        <div class="form-group">
                            <label for="date">Date</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Income</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Income Modal -->
    <div class="modal fade" id="editIncomeModal" tabindex="-1" role="dialog" aria-labelledby="editIncomeModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editIncomeModalLabel">Edit Income</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editIncomeForm">
                    <div class="modal-body">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_income_id" name="_method" value="PUT">
                        <div class="form-group">
                            <label for="edit_client_id">Client Name</label>
                            <select class="form-control" id="edit_client_id" name="client_id" required>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_total_amount">Total Amount</label>
                            <input type="number" step="0.01" class="form-control" id="edit_total_amount" name="total_amount" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_pending_amount">Pending Amount</label>
                            <input type="number" step="0.01" class="form-control" id="edit_pending_amount" name="pending_amount" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_received_amount">Received Amount</label>
                            <input type="number" step="0.01" class="form-control" id="edit_received_amount" name="received_amount" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_date">Date</label>
                            <input type="date" class="form-control" id="edit_date" name="date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Income</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(function() {
            $('#incomes-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('incomes.index') }}',
                columns: [
                    { data: 'sr_no', name: 'sr_no', orderable: false, searchable: false },
                    { data: 'client_name', name: 'client.name' },
                    { data: 'total_amount', name: 'total_amount' },
                    { data: 'pending_amount', name: 'pending_amount' },
                    { data: 'received_amount', name: 'received_amount' },
                    { data: 'date', name: 'date' },
                    { data: null, render: function (data, type, row) {
                        return '<button class="btn btn-xs btn-primary edit-income" data-id="' + row.id + '" data-client_id="' + row.client_id + '" data-total_amount="' + row.total_amount + '" data-pending_amount="' + row.pending_amount + '" data-received_amount="' + row.received_amount + '" data-date="' + row.date + '">Edit</button> ' +
                               '<button class="btn btn-xs btn-danger delete-income" data-id="' + row.id + '">Delete</button>';
                    }, orderable: false, searchable: false }
                ]
            });
            // Add Income
            $('#addIncomeForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('incomes.store') }}",
                    method: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        toastr.success(response.message);
                        $('#addIncomeModal').modal('hide');
                        $('#incomes-table').DataTable().ajax.reload();
                    },
                    error: function(response) {
                        toastr.error('Error adding income.');
                        console.log(response);
                    }
                });
            });

            // Edit Income - Populate Modal
            $(document).on('click', '.edit-income', function() {
                var id = $(this).data('id');
                var client_id = $(this).data('client_id');
                var total_amount = $(this).data('total_amount');
                var pending_amount = $(this).data('pending_amount');
                var received_amount = $(this).data('received_amount');
                var date = $(this).data('date');

                $('#edit_income_id').val(id);
                $('#edit_client_id').val(client_id);
                $('#edit_total_amount').val(total_amount);
                $('#edit_pending_amount').val(pending_amount);
                $('#edit_received_amount').val(received_amount);
                $('#edit_date').val(date);
                $('#editIncomeModal').modal('show');
            });

            // Update Income
            $('#editIncomeForm').on('submit', function(e) {
                e.preventDefault();
                var id = $('#edit_income_id').val();
                $.ajax({
                    url: "/incomes/" + id,
                    method: "POST", // Use POST for PUT/PATCH with _method field
                    data: $(this).serialize(),
                    success: function(response) {
                        toastr.success(response.message);
                        $('#editIncomeModal').modal('hide');
                        $('#incomes-table').DataTable().ajax.reload();
                    },
                    error: function(response) {
                        toastr.error('Error updating income.');
                        console.log(response);
                    }
                });
            });

            // Delete Income
            $(document).on('click', '.delete-income', function() {
                var id = $(this).data('id');
                if (confirm('Are you sure you want to delete this income?')) {
                    $.ajax({
                        url: "/incomes/" + id,
                        method: "POST", // Use POST for DELETE with _method field
                        data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            toastr.success(response.message);
                            $('#incomes-table').DataTable().ajax.reload();
                        },
                        error: function(response) {
                            toastr.error('Error deleting income.');
                            console.log(response);
                        }
                    });
                }
            });
        });
    </script>
@stop