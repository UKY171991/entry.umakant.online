@extends('layouts.main')

@section('title', 'Websites')
@section('page-title', 'Website Status Checker')

@section('content')
<!-- Header Section -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="text-white bg-danger p-2 rounded w-100 text-center">Website Status Checker</h5>
</div>

<!-- Table Section -->
<div class="table-section">
    <div class="table-responsive">
        <table class="table table-bordered data-table">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Client Name</th>
                    <th>Website URL</th>
                    <th>Status</th>
                    <th>Last Updated</th>
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
                <form id="websiteForm" name="websiteForm">
                    <input type="hidden" name="website_id" id="website_id">
                    
                    <div class="mb-3">
                        <label for="client_name" class="form-label">Client Name</label>
                        <input type="text" class="form-control" id="client_name" name="client_name" placeholder="Enter Client Name" maxlength="255" required>
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
                    url: "/websites",
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
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

            // Edit Website
            $('body').on('click', '.editWebsite', function () {
                var website_id = $(this).data('id');
                $.get("/websites/" + website_id + '/edit', function (data) {
                    $('#modelHeading').html("Edit Website");
                    $('#saveBtn').val("edit-website");
                    $('#ajaxModel').modal('show');
                    $('#website_id').val(data.id);
                    $('#client_name').val(data.client_name);
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

                $.ajax({
                    data: $('#websiteForm').serialize(),
                    url: "/websites",
                    type: "POST",
                    dataType: 'json',
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
