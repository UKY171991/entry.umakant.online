@extends('layouts.main')

@section('title', 'Websites')
@section('page-title', 'Website Status Checker')

@section('content')
<!-- Header Section -->
<div class="header-section mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800">Website Status Checker</h1>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary" id="createNewWebsite">
                <i class="fas fa-plus"></i> Add Website
            </button>
        </div>
    </div>
</div>

<!-- Data Section -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Website Status List</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered data-table" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client Name</th>
                        <th>Website URL</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>Client Name</th>
                        <th>Website URL</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </tfoot>
                <tbody>
                </tbody>
            </table>
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
                <form id="websiteForm" name="websiteForm">
                    @csrf
                    <input type="hidden" name="website_id" id="website_id">
                    
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
                        <label for="website_url" class="form-label">Website URL</label>
                        <input type="url" class="form-control" id="website_url" name="website_url" placeholder="Enter Website URL" required>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="">Select Status</option>
                            <option value="UP">UP</option>
                            <option value="DOWN">DOWN</option>
                            <option value="N/A">N/A</option>
                        </select>
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
            // DataTable configuration with CSRF token and error handling
            var table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "/websites",
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    error: function(xhr, error, code) {
                        console.error('DataTables Ajax error:', error, code);
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            toastr.error('Error loading data: ' + xhr.responseJSON.message);
                        } else {
                            toastr.error('Error loading data. Please check the console for details.');
                        }
                    }
                },
                columns: [
                    {data: 'sr_no', name: 'sr_no', orderable: false, searchable: false},
                    {data: 'client_name', name: 'client_name'},
                    {data: 'website_url', name: 'website_url'},
                    {data: 'status', name: 'status'},
                    {data: 'last_updated', name: 'last_updated'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                responsive: true,
                language: {
                    processing: '<i class="fas fa-spinner fa-spin"></i> Loading...'
                }
            });

            // Create New Website
            $('#createNewWebsite').click(function () {
                $('#saveBtn').val("create-website");
                $('#website_id').val('');
                $('#websiteForm').trigger("reset");
                $('#modelHeading').html("Create New Website");
                $('#ajaxModel').modal('show');
            });

            // Reset modal state on close
            $('#ajaxModel').on('hidden.bs.modal', function () {
                $('#websiteForm').trigger("reset");
                $('#website_id').val('');
                $('#saveBtn').val("create-website");
                $('#modelHeading').html("Create New Website");
            });

            // Edit Website
            $('body').on('click', '.editWebsite', function () {
                var website_id = $(this).data('id');
                $.get("/websites/" + website_id + '/edit', function (data) {
                    $('#modelHeading').html("Edit Website");
                    $('#saveBtn').val("edit-website");
                    $('#ajaxModel').modal('show');
                    $('#website_id').val(data.id);
                    $('#client_id').val(data.client_id);
                    $('#website_url').val(data.website_url);
                    $('#status').val(data.status);
                })
                .fail(function(xhr) {
                    let message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error loading website data';
                    toastr.error(message);
                });
            });

            // Save Website (Add or Edit)
            $('#saveBtn').click(function (e) {
                e.preventDefault();
                var saveBtn = $(this);
                var originalText = saveBtn.html();
                saveBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                saveBtn.prop('disabled', true);

                var websiteId = $('#website_id').val();
                var url = "/websites";
                var type = "POST";
                if (saveBtn.val() === "edit-website") {
                    url = "/websites/" + websiteId;
                    type = "PUT";
                }

                $.ajax({
                    data: $('#websiteForm').serialize(),
                    url: url,
                    type: type,
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data) {
                        $('#websiteForm').trigger("reset");
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

            // Delete Website
            $('body').on('click', '.deleteWebsite', function () {
                var website_id = $(this).data("id");
                if(confirm("Are you sure you want to delete this website?")) {
                    $.ajax({
                        type: "DELETE",
                        url: "/websites/" + website_id,
                        data: { _token: $('meta[name="csrf-token"]').attr('content') },
                        success: function (data) {
                            table.draw();
                            toastr.success(data.success || data.message || 'Website deleted successfully');
                        },
                        error: function (xhr) {
                            let message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred';
                            toastr.error(message);
                        }
                    });
                }
            });

            // View Website
            $('body').on('click', '.viewWebsite', function () {
                var url = $(this).data('url');
                window.open(url, '_blank');
            });

            // Test Website
            $('body').on('click', '.testWebsite', function () {
                var website_id = $(this).data('id');
                $.ajax({
                    type: "POST",
                    url: "/websites/" + website_id + "/test",
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function (data) {
                        table.draw();
                        toastr.success('Website tested successfully. Status: ' + data.status);
                    },
                    error: function (xhr) {
                        let message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred';
                        toastr.error(message);
                    }
                });
            });
        });
    </script>
@endsection
