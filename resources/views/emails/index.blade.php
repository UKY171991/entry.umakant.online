@extends('layouts.main')

@section('title', 'Emails')
@section('page-title', 'Email Management')

@section('content')
<!-- Filter Section -->
<div class="filter-section">
    <div class="row">
        <div class="col-md-3">
            <input type="text" class="form-control" id="clientFilter" placeholder="Client Name">
        </div>
        <div class="col-md-3">
            <input type="email" class="form-control" id="emailFilter" placeholder="Email">
        </div>
        <div class="col-md-3">
        </div>
        <div class="col-md-3 text-end">
            <button class="btn btn-danger" id="filterBtn"><i class="fas fa-filter"></i> Filter</button>
            <button class="btn btn-add text-white" id="createNewEmail"><i class="fas fa-plus"></i> Add Email</button>
        </div>
    </div>
</div>

<!-- Table Section -->
<div class="table-section">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>Emails Table</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered data-table">
            <thead class="table-header">
                <tr>
                    <th>#</th>
                    <th>CLIENT NAME</th>
                    <th>EMAIL</th>
                    <th>UPDATED AT</th>
                    <th>Action</th>
                    <th>Send Email</th>
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
                <form id="emailForm" name="emailForm">
                    <input type="hidden" name="email_id" id="email_id">
                    
                    <div class="mb-3">
                        <label for="client_name" class="form-label">Client Name</label>
                        <input type="text" class="form-control" id="client_name" name="client_name" placeholder="Enter Client Name" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email" required>
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
    <script>
        $(function () {
            // Initialize DataTable
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "/emails",
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    data: function(d) {
                        d.client_name = $('#clientFilter').val();
                        d.email = $('#emailFilter').val();
                    },
                    error: function(xhr, error, code) {
                        console.log('DataTables Ajax Error:', xhr.responseText);
                        toastr.error('Error loading data: ' + xhr.statusText);
                    }
                },
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'client_name', name: 'client_name'},
                    {data: 'email', name: 'email'},
                    {data: 'updated_at', name: 'updated_at'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                    {data: 'send_email', name: 'send_email', orderable: false, searchable: false}
                ],
                responsive: true,
                language: {
                    processing: '<i class="fas fa-spinner fa-spin"></i> Loading...'
                }
            });

            // Create New Email
            $('#createNewEmail').click(function () {
                $('#saveBtn').val("create-email");
                $('#email_id').val('');
                $('#emailForm').trigger("reset");
                $('#modelHeading').html("Create New Email");
                $('#ajaxModel').modal('show');
            });

            // Reset modal state on close
            $('#ajaxModel').on('hidden.bs.modal', function () {
                $('#emailForm').trigger("reset");
                $('#email_id').val('');
                $('#saveBtn').val("create-email");
                $('#modelHeading').html("Create New Email");
            });

            // Edit Email
            $('body').on('click', '.editEmail', function () {
                var email_id = $(this).data('id');
                $.get("/emails/" + email_id + '/edit', function (data) {
                    $('#modelHeading').html("Edit Email");
                    $('#saveBtn').val("edit-email");
                    $('#ajaxModel').modal('show');
                    $('#email_id').val(data.id);
                    $('#client_name').val(data.client_name);
                    $('#email').val(data.email);
                })
                .fail(function(xhr) {
                    let message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error loading email data';
                    toastr.error(message);
                });
            });

            // Save Email (Add or Edit)
            $('#saveBtn').click(function (e) {
                e.preventDefault();
                var saveBtn = $(this);
                var originalText = saveBtn.html();
                saveBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                saveBtn.prop('disabled', true);

                var emailId = $('#email_id').val();
                var url = "/emails";
                var type = "POST";
                if (saveBtn.val() === "edit-email") {
                    url = "/emails/" + emailId;
                    type = "PUT";
                }

                $.ajax({
                    data: $('#emailForm').serialize(),
                    url: url,
                    type: type,
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data) {
                        $('#emailForm').trigger("reset");
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

            // Delete Email
            $('body').on('click', '.deleteEmail', function () {
                var email_id = $(this).data("id");
                if(confirm("Are you sure you want to delete this email?")) {
                    $.ajax({
                        type: "DELETE",
                        url: "/emails/" + email_id,
                        data: { _token: $('meta[name="csrf-token"]').attr('content') },
                        success: function (data) {
                            table.draw();
                            toastr.success(data.success || data.message || 'Email deleted successfully');
                        },
                        error: function (xhr) {
                            let message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred';
                            toastr.error(message);
                        }
                    });
                }
            });

            // Send Email
            $('body').on('click', '.sendEmail', function () {
                var email_id = $(this).data('id');
                var email_address = $(this).data('email');
                if(confirm("Are you sure you want to send an email to " + email_address + "?")) {
                    $.ajax({
                        type: "POST",
                        url: "/emails/send/" + email_id,
                        data: { _token: $('meta[name="csrf-token"]').attr('content') },
                        success: function (data) {
                            toastr.success(data.success || data.message || 'Email sent successfully');
                        },
                        error: function (xhr) {
                            let message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred while sending email';
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
