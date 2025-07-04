@extends('adminlte::page')

@section('title', 'Expenses')

@section('content_header')
    <h1>Expenses</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                    <h3 class="card-title">Expenses List</h3>
                        <div class="card-tools">
                            <a class="btn btn-success" href="javascript:void(0)" id="createNewExpense"> Add New Expense</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered data-table">
                            <thead>
                                <tr>
                                    <th>SR No.</th>
                                    <th>Expense Name</th>
                                    <th>Amount</th>
                                    <th>Category</th>
                                    <th>Date</th>
                                    <th width="280px">Action</th>
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

    <div class="modal fade" id="ajaxModel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modelHeading"></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="expenseForm" name="expenseForm" class="form-horizontal">
                        @csrf
                        <input type="hidden" name="expense_id" id="expense_id">
                        <div class="form-group">
                            <label for="expense_name" class="col-sm-4 control-label">Expense Name</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" id="expense_name" name="expense_name" placeholder="Enter Expense Name" value="" maxlength="50" required="">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="amount" class="col-sm-4 control-label">
                                <i class="fas fa-rupee-sign mr-1"></i>
                                Amount
                            </label>
                            <div class="col-sm-12">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-rupee-sign"></i></span>
                                    </div>
                                    <input type="number" step="0.01" class="form-control" id="amount" name="amount" placeholder="Enter Amount" value="" required="">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="category" class="col-sm-4 control-label">Category</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" id="category" name="category" placeholder="Enter Category" value="" maxlength="50" required="">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="date" class="col-sm-4 control-label">Date</label>
                            <div class="col-sm-12">
                                <input type="date" class="form-control" id="date" name="date" value="" required="">
                            </div>
                        </div>

                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-primary" id="saveBtn" value="create-expense">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
    <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
@stop

@section('js')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script type="text/javascript">
        $(function () {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var table = $('.data-table').DataTable({
                processing: true,
                     serverSide: true,
                     ajax: {
                         url: "{{ route('expenses.index') }}",
                         headers: {
                             'X-Requested-With': 'XMLHttpRequest'
                         }
                     },
                columns: [
                    {data: 'sr_no', name: 'sr_no', orderable: false, searchable: false},
                    {data: 'expense_name', name: 'expense_name'},
                    {data: 'amount', name: 'amount', render: function(data, type, row) {
                        return data; // This will render HTML as-is
                    }},
                    {data: 'category', name: 'category'},
                    {data: 'date', name: 'date'},
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ]
            });

            $('#createNewExpense').click(function () {
                $('#saveBtn').val("create-expense");
                $('#expense_id').val('');
                $('#expenseForm').trigger("reset");
                $('#modelHeading').html("Create New Expense");
                $('#ajaxModel').modal('show');
            });

            $('body').on('click', '.editExpense', function () {
                var expense_id = $(this).data('id');
                $.get("{{ route('expenses.index') }}" + '/' + expense_id + '/edit', function (data) {
                    $('#modelHeading').html("Edit Expense");
                    $('#saveBtn').val("edit-expense");
                    $('#ajaxModel').modal('show');
                    $('#expense_id').val(data.id);
                    $('#expense_name').val(data.expense_name);
                    $('#amount').val(data.amount);
                    $('#category').val(data.category);
                    $('#date').val(data.date);
                })
            });

            $('#saveBtn').click(function (e) {
                e.preventDefault();
                $(this).html('Sending..');

                $.ajax({
                    data: $('#expenseForm').serialize(),
                    url: "{{ route('expenses.store') }}",
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
                        $('#saveBtn').html('Save Changes');
                        toastr.error(data.responseJSON.message);
                    }
                });
            });

            $('body').on('click', '.deleteExpense', function () {
                var expense_id = $(this).data("id");
                if(confirm("Are You sure want to delete ?")){
                    $.ajax({
                        type: "DELETE",
                        url: "{{ route('expenses.store') }}" + '/' + expense_id,
                        success: function (data) {
                            table.draw();
                            toastr.success(data.success);
                        },
                        error: function (data) {
                            console.log('Error:', data);
                            toastr.error(data.responseJSON.message);
                        }
                    });
                }
            });

        });
    </script>
@stop