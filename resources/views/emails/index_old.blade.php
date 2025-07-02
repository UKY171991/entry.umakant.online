@extends('layouts.main')

@section('title', 'Emails')
@section('page-title', 'Clients Records')

@section('content')
<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="text-white bg-danger p-2 rounded w-100 text-center">Website Status Checker</h5>
    <button class="btn btn-dark text-white" id="createNewEmail"><i class="fas fa-plus"></i> Add Client</button>
</div>

<!-- Table Section -->
<div class="table-section">
    <div class="table-responsive">
        <table class="table table-bordered data-table">
            <thead class="table-header">
                <tr>
                    <th>ID</th>
                    <th>Client Name</th>
                    <th>Email</th>
                    <th>Updated At</th>
                    <th>Actions</th>
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
                        <input type="text" class="form-control" id="client_name" name="client_name" placeholder="Enter Client Name" maxlength="255" required>
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

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endsection

@section('js')
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
                    }
                },
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'client_name', name: 'client_name'},
                    {data: 'email', name: 'email'},
                    {data: 'updated_at', name: 'updated_at'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
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
                $('#modelHeading').html("Create New Email Record");
                $('#ajaxModel').modal('show');
            });

            // Save Email (Add or Edit)
            $('#saveBtn').click(function (e) {
                e.preventDefault();
                var saveBtn = $(this);
                var originalText = saveBtn.html();
                saveBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                saveBtn.prop('disabled', true);

                $.ajax({
                    data: $('#emailForm').serialize(),
                    url: "/emails",
                    type: "POST",
                    dataType: 'json',
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
                if(confirm("Are you sure you want to delete this email record?")) {
                    $.ajax({
                        type: "DELETE",
                        url: "/emails/" + email_id,
                        data: { _token: $('meta[name="csrf-token"]').attr('content') },
                        success: function (data) {
                            table.draw();
                            toastr.success(data.success || data.message || 'Email record deleted successfully');
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
                var email_id = $(this).data("id");
                toastr.info('Email sending functionality will be implemented soon');
            });
        });
    </script>
@endsection
