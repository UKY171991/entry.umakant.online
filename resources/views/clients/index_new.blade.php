@extends('layouts.main')

@section('title', 'Clients')
@section('page-title', 'Clients Management')

@section('content')
<!-- Main content -->
<div class="row">
    <div class="col-12">
        <!-- Filter Section -->
        <div class="card card-primary">
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
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="nameFilter">Client Name</label>
                            <input type="text" class="form-control" id="nameFilter" placeholder="Search by name...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="emailFilter">Email</label>
                            <input type="email" class="form-control" id="emailFilter" placeholder="Search by email...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="phoneFilter">Phone</label>
                            <input type="text" class="form-control" id="phoneFilter" placeholder="Search by phone...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="d-flex">
                                <button class="btn btn-primary mr-2" id="filterBtn">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <button class="btn btn-success" id="createNewClient">
                                    <i class="fas fa-plus"></i> Add Client
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
                    <i class="fas fa-users mr-1"></i>
                    Clients List
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
                <table id="clientsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be populated by DataTables -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Actions</th>
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
                    <i class="fas fa-user-plus mr-2"></i>
                    Add New Client
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="clientForm" name="clientForm">
                    <input type="hidden" name="client_id" id="client_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">
                                    <i class="fas fa-user mr-1"></i>
                                    Client Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter client name..." maxlength="100" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope mr-1"></i>
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter email address..." required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">
                                    <i class="fas fa-phone mr-1"></i>
                                    Phone <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter phone number..." maxlength="20" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="address">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    Address
                                </label>
                                <input type="text" class="form-control" id="address" name="address" placeholder="Enter address..." maxlength="255">
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
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(function () {
            // Initialize DataTable
            var table = $('#clientsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "/clients",
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    data: function(d) {
                        d.name = $('#nameFilter').val();
                        d.email = $('#emailFilter').val();
                        d.phone = $('#phoneFilter').val();
                    }
                },
                columns: [
                    {data: 'sr_no', name: 'sr_no', orderable: false, searchable: false},
                    {data: 'name', name: 'name'},
                    {data: 'email', name: 'email'},
                    {data: 'phone', name: 'phone'},
                    {data: 'address', name: 'address'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                responsive: true,
                language: {
                    processing: '<i class="fas fa-spinner fa-spin"></i> Loading...'
                }
            });

            // Filter functionality
            $('#filterBtn').click(function() {
                table.draw();
            });

            // Create New Client
            $('#createNewClient').click(function () {
                $('#saveBtn').val("create-client");
                $('#client_id').val('');
                $('#clientForm').trigger("reset");
                $('#modelHeading').html('<i class="fas fa-user-plus mr-2"></i> Add New Client');
                $('#ajaxModel').modal('show');
            });

            // Reset modal state on close
            $('#ajaxModel').on('hidden.bs.modal', function () {
                $('#clientForm').trigger("reset");
                $('#client_id').val('');
                $('#saveBtn').val("create-client");
                $('#modelHeading').html('<i class="fas fa-user-plus mr-2"></i> Add New Client');
            });

            // Edit Client
            $('body').on('click', '.editClient', function () {
                var client_id = $(this).data('id');
                $.get("/clients/" + client_id + '/edit', function (data) {
                    $('#modelHeading').html('<i class="fas fa-user-edit mr-2"></i> Edit Client');
                    $('#saveBtn').val("edit-client");
                    $('#ajaxModel').modal('show');
                    $('#client_id').val(data.id);
                    $('#name').val(data.name);
                    $('#email').val(data.email);
                    $('#phone').val(data.phone);
                    $('#address').val(data.address);
                })
            });

            // Save Client
            $('#saveBtn').click(function (e) {
                e.preventDefault();
                $(this).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
                
                $.ajax({
                    data: $('#clientForm').serialize(),
                    url: "/clients",
                    type: "POST",
                    dataType: 'json',
                    success: function (data) {
                        $('#clientForm').trigger("reset");
                        $('#ajaxModel').modal('hide');
                        table.draw();
                        toastr.success(data.success || 'Client saved successfully');
                    },
                    error: function (data) {
                        console.log('Error:', data);
                        let message = data.responseJSON && data.responseJSON.message ? data.responseJSON.message : 'An error occurred';
                        toastr.error(message);
                    },
                    complete: function () {
                        $('#saveBtn').html('<i class="fas fa-save mr-1"></i> Save Changes');
                    }
                });
            });

            // Delete Client
            $('body').on('click', '.deleteClient', function () {
                var client_id = $(this).data("id");
                if (confirm("Are you sure you want to delete this client?")) {
                    $.ajax({
                        type: "DELETE",
                        url: "/clients/" + client_id,
                        success: function (data) {
                            table.draw();
                            toastr.success(data.success || 'Client deleted successfully');
                        },
                        error: function (data) {
                            console.log('Error:', data);
                            let message = data.responseJSON && data.responseJSON.message ? data.responseJSON.message : 'An error occurred';
                            toastr.error(message);
                        }
                    });
                }
            });
        });
    </script>
@endsection
