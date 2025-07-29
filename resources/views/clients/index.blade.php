@extends('layouts.main')

@section('title', 'Clients')
@section('page-title', 'Clients Management')

@section('content')
<!-- Filter Section -->
<div class="filter-section">
    <div class="row">
        <div class="col-md-3">
            <input type="text" class="form-control" id="nameFilter" placeholder="Client Name">
        </div>
        <div class="col-md-3">
            <input type="email" class="form-control" id="emailFilter" placeholder="Email">
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" id="phoneFilter" placeholder="Phone">
        </div>
        <div class="col-md-3 text-end">
            <button class="btn btn-danger" id="filterBtn"><i class="fas fa-filter"></i> Filter</button>
            <button class="btn btn-success" id="createNewClient"><i class="fas fa-plus"></i> Add Client</button>
        </div>
    </div>
</div>

<!-- Table Section -->
<div class="table-section">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>Clients Table</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered data-table">
            <thead class="table-header">
                <tr>
                    <th>#</th>
                    <th>NAME</th>
                    <th>EMAIL</th>
                    <th>PHONE</th>
                    <th>ADDRESS</th>
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
<!-- View Client Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-eye mr-2"></i>
                    View Client Details
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-user mr-1"></i> Client Name:</label>
                            <p id="view_client_name" class="form-control-plaintext border p-2 bg-light"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-envelope mr-1"></i> Email:</label>
                            <p id="view_client_email" class="form-control-plaintext border p-2 bg-light"></p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-phone mr-1"></i> Phone:</label>
                            <p id="view_client_phone" class="form-control-plaintext border p-2 bg-light"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-calendar mr-1"></i> Created:</label>
                            <p id="view_client_created" class="form-control-plaintext border p-2 bg-light"></p>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt mr-1"></i> Address:</label>
                    <p id="view_client_address" class="form-control-plaintext border p-2 bg-light" style="min-height: 60px;"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Client Modal -->
<div class="modal fade" id="ajaxModel" tabindex="-1" role="dialog" aria-labelledby="modelHeading" aria-hidden="true">
    <div class="modal-dialog" role="document">
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
                    @csrf
                    <input type="hidden" name="client_id" id="client_id">
                    
                    <div class="form-group">
                        <label for="name">
                            <i class="fas fa-user mr-1"></i>
                            Client Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter Client Name" maxlength="100" required>
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope mr-1"></i>
                            Email <span class="text-danger">*</span>
                        </label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email Address" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">
                            <i class="fas fa-phone mr-1"></i>
                            Phone <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter Phone Number" maxlength="20" required>
                    </div>

                    <div class="form-group">
                        <label for="address">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            Address
                        </label>
                        <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter Address (Optional)" maxlength="255"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cancel
                </button>
                <button type="button" class="btn btn-primary" id="saveBtn" value="create-client">
                    <i class="fas fa-save mr-1"></i>
                    Save Client
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
        .data-table td:last-child {
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
    <script>
        $(function () {
            // Initialize DataTable
            var table = $('.data-table').DataTable({
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

            // NOTE: Your server-side should return the following HTML for the action column:
            // <button class="btn btn-info editClient" data-id="CLIENT_ID">Edit</button>
            // <button class="btn btn-danger deleteClient" data-id="CLIENT_ID">Delete</button>

            // View Client
            $('body').on('click', '.viewClient', function () {
                var client_id = $(this).data('id');
                $.get("/clients/" + client_id, function (data) {
                    console.log('Data received for view:', data);
                    $('#view_client_name').text(data.name);
                    $('#view_client_email').text(data.email);
                    $('#view_client_phone').text(data.phone || 'N/A');
                    $('#view_client_address').text(data.address || 'No address provided');
                    $('#view_client_created').text(new Date(data.created_at).toLocaleDateString());
                    $('#viewModal').modal('show');
                })
                .fail(function(xhr) {
                    let message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error loading client data';
                    toastr.error(message);
                });
            });

            // Create New Client
            $('#createNewClient').click(function () {
                $('#saveBtn').val("create-client");
                $('#client_id').val('');
                $('#clientForm').trigger("reset");
                $('#modelHeading').html('<i class="fas fa-user-plus mr-2"></i> Add New Client');
                $('#ajaxModel').modal('show');
                toastr.info('Ready to add new client');
            });

            // Reset modal state on close
            $('#ajaxModel').on('hidden.bs.modal', function () {
                $('#clientForm').trigger("reset");
                $('#client_id').val('');
                $('#saveBtn').val("create-client");
                $('#saveBtn').html('<i class="fas fa-save mr-1"></i> Save Client');
                $('#modelHeading').html('<i class="fas fa-user-plus mr-2"></i> Add New Client');
            });

            // Edit Client
            $('body').on('click', '.editClient', function () {
                var client_id = $(this).data('id');
                $.get("/clients/" + client_id + '/edit', function (data) {
                    $('#modelHeading').html('<i class="fas fa-user-edit mr-2"></i> Edit Client');
                    $('#saveBtn').val("edit-client");
                    $('#saveBtn').html('<i class="fas fa-save mr-1"></i> Update Client');
                    $('#ajaxModel').modal('show');
                    $('#client_id').val(data.id);
                    $('#name').val(data.name);
                    $('#email').val(data.email);
                    $('#phone').val(data.phone);
                    $('#address').val(data.address);
                    toastr.info('Client data loaded for editing');
                })
                .fail(function(xhr) {
                    let message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error loading client data';
                    toastr.error(message);
                });
            });

            // Save Client (Add or Edit)
            $('#saveBtn').click(function (e) {
                e.preventDefault();
                var saveBtn = $(this);
                var originalText = saveBtn.html();
                saveBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                saveBtn.prop('disabled', true);

                var clientId = $('#client_id').val();
                var url = "/clients";
                var type = "POST";
                if (saveBtn.val() === "edit-client") {
                    url = "/clients/" + clientId;
                    type = "PUT";
                }

                $.ajax({
                    data: $('#clientForm').serialize(),
                    url: url,
                    type: type,
                    dataType: 'json',
                    success: function (data) {
                        $('#clientForm').trigger("reset");
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
                        if (saveBtn.val() === "edit-client") {
                            saveBtn.html('<i class="fas fa-save mr-1"></i> Update Client');
                        } else {
                            saveBtn.html('<i class="fas fa-save mr-1"></i> Save Client');
                        }
                        saveBtn.prop('disabled', false);
                    }
                });
            });

            // Delete Client
            $('body').on('click', '.deleteClient', function () {
                var client_id = $(this).data("id");
                if(confirm("Are you sure you want to delete this client?")) {
                    $.ajax({
                        type: "DELETE",
                        url: "/clients/" + client_id,
                        data: { _token: $('meta[name="csrf-token"]').attr('content') },
                        success: function (data) {
                            table.draw();
                            toastr.success(data.success || data.message || 'Client deleted successfully');
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