@extends('layouts.main')

@section('title', 'Websites')
@section('page-title', 'Website Status Checker')

@section('content')
<!-- Main content -->
<div class="row">
    <div class="col-12">
        <!-- Table Section -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-globe mr-1"></i>
                    Website Status List
                </h3>
                <div class="card-tools">
                    <button class="btn btn-success btn-sm" id="createNewWebsite">
                        <i class="fas fa-plus"></i> Add Website
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table id="websitesTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Client Name</th>
                            <th>Website URL</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th width="150px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be populated by DataTables -->
                    </tbody>
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
                    <i class="fas fa-globe mr-2"></i>
                    Add New Website
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="websiteForm" name="websiteForm">
                    @csrf
                    <input type="hidden" name="website_id" id="website_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="client_id">
                                    <i class="fas fa-user mr-1"></i>
                                    Client <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="client_id" name="client_id" required>
                                    <option value="">Select Client</option>
                                    @foreach($clients ?? [] as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="website_url">
                                    <i class="fas fa-link mr-1"></i>
                                    Website URL <span class="text-danger">*</span>
                                </label>
                                <input type="url" class="form-control" id="website_url" name="website_url" placeholder="https://example.com" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Status <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Under Maintenance">Under Maintenance</option>
                                </select>
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function () {
            // Initialize DataTable
            var table = $('#websitesTable').DataTable({
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
                    {data: 'website_url', name: 'website_url', render: function(data, type, row) {
                        return '<a href="' + data + '" target="_blank" class="text-primary"><i class="fas fa-external-link-alt mr-1"></i>' + data + '</a>';
                    }},
                    {data: 'status', name: 'status', render: function(data, type, row) {
                        if (data === 'Active' || data === 'Online') {
                            return '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>' + data + '</span>';
                        } else if (data === 'Inactive' || data === 'Offline') {
                            return '<span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i>' + data + '</span>';
                        } else {
                            return '<span class="badge badge-warning"><i class="fas fa-question-circle mr-1"></i>' + data + '</span>';
                        }
                    }},
                    {data: 'last_updated', name: 'last_updated'},
                    {
                        data: null,
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="btn-group" role="group">
                                    <button class="btn btn-info btn-sm editWebsite" data-id="${row.id}" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-success btn-sm checkStatus" data-id="${row.id}" title="Check Status">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm deleteWebsite" data-id="${row.id}" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
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
                $('#modelHeading').html('<i class="fas fa-globe mr-2"></i> Add New Website');
                $('#ajaxModel').modal('show');
            });

            // Reset modal state on close
            $('#ajaxModel').on('hidden.bs.modal', function () {
                $('#websiteForm').trigger("reset");
                $('#website_id').val('');
                $('#saveBtn').val("create-website");
                $('#modelHeading').html('<i class="fas fa-globe mr-2"></i> Add New Website');
            });

            // Edit Website
            $('body').on('click', '.editWebsite', function () {
                var website_id = $(this).data('id');
                $.get("/websites/" + website_id + '/edit', function (data) {
                    $('#modelHeading').html('<i class="fas fa-edit mr-2"></i> Edit Website');
                    $('#saveBtn').val("edit-website");
                    $('#ajaxModel').modal('show');
                    $('#website_id').val(data.id);
                    $('#client_id').val(data.client_id);
                    $('#website_url').val(data.website_url);
                    $('#status').val(data.status);
                }).fail(function(xhr) {
                    let message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error loading website data';
                    toastr.error(message);
                });
            });

            // Check Website Status
            $('body').on('click', '.checkStatus', function () {
                var website_id = $(this).data('id');
                var btn = $(this);
                
                btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
                
                $.ajax({
                    url: "/websites/" + website_id + "/check-status",
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data) {
                        table.draw();
                        toastr.success(data.message || 'Website status checked successfully');
                    },
                    error: function (xhr) {
                        let message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error checking website status';
                        toastr.error(message);
                    },
                    complete: function() {
                        btn.html('<i class="fas fa-sync-alt"></i>').prop('disabled', false);
                    }
                });
            });

            // Save Website
            $('#saveBtn').click(function (e) {
                e.preventDefault();
                $(this).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
                
                $.ajax({
                    data: $('#websiteForm').serialize(),
                    url: "/websites",
                    type: "POST",
                    dataType: 'json',
                    success: function (data) {
                        $('#websiteForm').trigger("reset");
                        $('#ajaxModel').modal('hide');
                        table.draw();
                        toastr.success(data.success || 'Website saved successfully');
                    },
                    error: function (xhr) {
                        console.log('Error:', xhr);
                        let message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred';
                        toastr.error(message);
                    },
                    complete: function () {
                        $('#saveBtn').html('<i class="fas fa-save mr-1"></i> Save Changes');
                    }
                });
            });

            // Delete Website with SweetAlert confirmation
            $('body').on('click', '.deleteWebsite', function () {
                var website_id = $(this).data("id");
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "DELETE",
                            url: "/websites/" + website_id,
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (data) {
                                table.draw();
                                Swal.fire(
                                    'Deleted!',
                                    data.success || 'Website has been deleted.',
                                    'success'
                                );
                            },
                            error: function (xhr) {
                                console.log('Error:', xhr);
                                let message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'An error occurred';
                                Swal.fire(
                                    'Error!',
                                    message,
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
