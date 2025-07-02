@extends('layouts.main')

@section('title', 'Pending Tasks')
@section('page-title', 'Pending Tasks')

@section('content')
<!-- Header Section -->
<div class="header-section mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 mb-0 text-gray-800">Pending Tasks</h1>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary" id="createNewTask">
                <i class="fas fa-plus"></i> Add Task
            </button>
        </div>
    </div>
</div>

<!-- Data Section -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Pending Tasks List</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered data-table" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Task Name</th>
                        <th>Client Name</th>
                        <th>Description</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Pay Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Task Name</th>
                        <th>Client Name</th>
                        <th>Description</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Pay Status</th>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modelHeading"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="taskForm" name="taskForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="task_id" id="task_id">
                    <input type="hidden" name="existing_image_path" id="existing_image_path">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="task_name" class="form-label">Task Name</label>
                                <input type="text" class="form-control" id="task_name" name="task_name" placeholder="Enter Task Name" maxlength="255" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="client_id" class="form-label">Client</label>
                                <select class="form-control" id="client_id" name="client_id" required>
                                    <option value="">Select Client</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter Description" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" class="form-control" id="due_date" name="due_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="Pending">Pending</option>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Completed">Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment" class="form-label">Payment</label>
                                <input type="number" step="0.01" class="form-control" id="payment" name="payment" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_status" class="form-label">Payment Status</label>
                                <select class="form-control" id="payment_status" name="payment_status" required>
                                    <option value="">Select Payment Status</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Unpaid">Unpaid</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Task Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <small class="form-text text-muted">Upload an image for this task (optional)</small>
                        <div id="current_image_container" class="mt-2" style="display: none;">
                            <h6>Current Image:</h6>
                            <img id="current_image_preview" src="" alt="Current Task Image" style="max-width: 200px; height: auto;">
                        </div>
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
                    url: "/pending-tasks",
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
                    {data: 'image', name: 'image', orderable: false, searchable: false},
                    {data: 'task_name', name: 'task_name'},
                    {data: 'client_name', name: 'client_name'},
                    {data: 'description', name: 'description'},
                    {data: 'due_date', name: 'due_date'},
                    {data: 'status', name: 'status'},
                    {data: 'payment', name: 'payment'},
                    {data: 'payment_status', name: 'payment_status'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                responsive: true,
                language: {
                    processing: '<i class="fas fa-spinner fa-spin"></i> Loading...'
                }
            });

            // Create New Task
            $('#createNewTask').click(function () {
                $('#saveBtn').val("create-task");
                $('#task_id').val('');
                $('#taskForm').trigger("reset");
                $('#modelHeading').html("Create New Task");
                $('#ajaxModel').modal('show');
                $('#current_image_container').hide();
            });

            // Edit Task
            $('body').on('click', '.editTask', function () {
                var task_id = $(this).data('id');
                $.get("/pending-tasks/" + task_id + '/edit', function (data) {
                    console.log('Data received for edit:', data); // Add this line for debugging
                    $('#modelHeading').html("Edit Task");
                    $('#saveBtn').val("edit-task");
                    $('#ajaxModel').modal('show');
                    $('#task_id').val(data.id);
                    $('#task_name').val(data.task_name);
                    $('#client_id').val(data.client_id);
                    $('#description').val(data.description);
                    $('#due_date').val(data.due_date);
                    $('#status').val(data.status);
                    $('#payment').val(data.payment);
                    $('#payment_status').val(data.payment_status);
                    $('#existing_image_path').val(data.image_path);
                    if (data.image_path) {
                        $('#current_image_preview').attr('src', '/storage/' + data.image_path);
                        $('#current_image_container').show();
                    } else {
                        $('#current_image_container').hide();
                    }
                })
                .fail(function(xhr) {
                    let message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error loading task data';
                    toastr.error(message);
                });
            });

            // Save Task (Add or Edit)
            $('#saveBtn').click(function (e) {
                e.preventDefault();
                var saveBtn = $(this);
                var originalText = saveBtn.html();
                saveBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                saveBtn.prop('disabled', true);

                var formData = new FormData($('#taskForm')[0]);
                var taskId = $('#task_id').val();
                var url = "/pending-tasks";
                var type = "POST";
                
                if (saveBtn.val() === "edit-task") {
                    url = "/pending-tasks/" + taskId;
                    type = "POST";
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    data: formData,
                    url: url,
                    type: type,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data) {
                        $('#taskForm').trigger("reset");
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

            // Delete Task
            $('body').on('click', '.deleteTask', function () {
                var task_id = $(this).data("id");
                if(confirm("Are you sure you want to delete this task?")) {
                    $.ajax({
                        type: "DELETE",
                        url: "/pending-tasks/" + task_id,
                        data: { _token: $('meta[name="csrf-token"]').attr('content') },
                        success: function (data) {
                            table.draw();
                            toastr.success(data.success || data.message || 'Task deleted successfully');
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
