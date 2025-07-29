@extends('layouts.main')

@section('title', 'Pending Tasks')
@section('page-title', 'Pending Tasks')

@section('content')
<!-- Main content -->
<div class="row">
    <div class="col-12">
        <!-- Table Section -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tasks mr-1"></i>
                    Pending Tasks List
                </h3>
                <div class="card-tools">
                    <button class="btn btn-success btn-sm" id="createNewTask">
                        <i class="fas fa-plus"></i> Add Task
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
                <table id="dataTable" class="table table-bordered table-striped">
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
                            <th width="150px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be populated by DataTables -->
                    </tbody>
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
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modals')
<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-eye mr-2"></i>
                    View Task Details
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-clipboard-list mr-1"></i> Task Name:</label>
                            <p id="view_task_name" class="form-control-plaintext border p-2 bg-light"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-user mr-1"></i> Client:</label>
                            <p id="view_client_name" class="form-control-plaintext border p-2 bg-light"></p>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-align-left mr-1"></i> Description:</label>
                    <p id="view_description" class="form-control-plaintext border p-2 bg-light" style="min-height: 80px;"></p>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-calendar mr-1"></i> Due Date:</label>
                            <p id="view_due_date" class="form-control-plaintext border p-2 bg-light"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-flag mr-1"></i> Status:</label>
                            <p id="view_status" class="form-control-plaintext border p-2 bg-light"></p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-rupee-sign mr-1"></i> Payment Amount:</label>
                            <p id="view_payment" class="form-control-plaintext border p-2 bg-light"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><i class="fas fa-credit-card mr-1"></i> Payment Status:</label>
                            <p id="view_payment_status" class="form-control-plaintext border p-2 bg-light"></p>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-image mr-1"></i> Task Image:</label>
                    <div id="view_image_container" class="border p-2 bg-light">
                        <img id="view_image" src="" alt="Task Image" style="max-width: 100%; height: auto; display: none;">
                        <p id="view_no_image" class="text-muted mb-0" style="display: none;">No image available</p>
                    </div>
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

<!-- Edit/Add Modal -->
<div class="modal fade" id="ajaxModel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modelHeading">
                    <i class="fas fa-tasks mr-2"></i>
                    Add New Task
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="taskForm" name="taskForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="task_id" id="task_id">
                    <input type="hidden" name="existing_image_path" id="existing_image_path">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="task_name">
                                    <i class="fas fa-clipboard-list mr-1"></i>
                                    Task Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="task_name" name="task_name" placeholder="Enter Task Name" maxlength="255" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="client_id">
                                    <i class="fas fa-user mr-1"></i>
                                    Client <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="client_id" name="client_id" required>
                                    <option value="">Select Client</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">
                            <i class="fas fa-align-left mr-1"></i>
                            Description <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter Description" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="due_date">
                                    <i class="fas fa-calendar mr-1"></i>
                                    Due Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="due_date" name="due_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">
                                    <i class="fas fa-flag mr-1"></i>
                                    Status <span class="text-danger">*</span>
                                </label>
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
                            <div class="form-group">
                                <label for="payment">
                                    <i class="fas fa-rupee-sign mr-1"></i>
                                    Payment Amount
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-rupee-sign"></i></span>
                                    </div>
                                    <input type="number" step="0.01" class="form-control" id="payment" name="payment" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_status">
                                    <i class="fas fa-credit-card mr-1"></i>
                                    Payment Status <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="payment_status" name="payment_status" required>
                                    <option value="">Select Payment Status</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Unpaid">Unpaid</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="image">
                            <i class="fas fa-image mr-1"></i>
                            Task Image
                        </label>
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
                <button type="button" class="btn btn-primary" id="saveBtn">
                    <i class="fas fa-save mr-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function () {
            // DataTable configuration with CSRF token and error handling
            var table = $('#dataTable').DataTable({
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
                    {data: 'payment', name: 'payment', render: function(data, type, row) {
                        return data; // This will render HTML as-is
                    }},
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
                $('#modelHeading').html('<i class="fas fa-tasks mr-2"></i> Create New Task');
                $('#ajaxModel').modal('show');
                $('#current_image_container').hide();
            });

            // View Task
            $('body').on('click', '.viewTask', function () {
                var task_id = $(this).data('id');
                $.get("/pending-tasks/" + task_id, function (data) {
                    console.log('Data received for view:', data);
                    $('#view_task_name').text(data.task_name);
                    $('#view_client_name').text(data.client ? data.client.name : 'N/A');
                    $('#view_description').text(data.description);
                    $('#view_due_date').text(data.due_date);
                    
                    // Format status with badge
                    var statusBadge = '';
                    switch(data.status) {
                        case 'Completed':
                            statusBadge = '<span class="badge bg-success">Completed</span>';
                            break;
                        case 'Pending':
                            statusBadge = '<span class="badge bg-warning">Pending</span>';
                            break;
                        case 'In Progress':
                            statusBadge = '<span class="badge bg-info">In Progress</span>';
                            break;
                        default:
                            statusBadge = '<span class="badge bg-secondary">' + data.status + '</span>';
                    }
                    $('#view_status').html(statusBadge);
                    
                    // Format payment
                    $('#view_payment').html('<i class="fas fa-rupee-sign"></i> ' + parseFloat(data.payment || 0).toFixed(2));
                    
                    // Format payment status with badge
                    var paymentBadge = data.payment_status == 'Paid' ? 
                        '<span class="badge bg-success">Paid</span>' : 
                        '<span class="badge bg-danger">Unpaid</span>';
                    $('#view_payment_status').html(paymentBadge);
                    
                    // Handle image
                    if (data.image_path) {
                        $('#view_image').attr('src', '/storage/' + data.image_path).show();
                        $('#view_no_image').hide();
                    } else {
                        $('#view_image').hide();
                        $('#view_no_image').show();
                    }
                    
                    $('#viewModal').modal('show');
                })
                .fail(function(xhr) {
                    let message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Error loading task data';
                    toastr.error(message);
                });
            });

            // Edit Task
            $('body').on('click', '.editTask', function () {
                var task_id = $(this).data('id');
                $.get("/pending-tasks/" + task_id + '/edit', function (data) {
                    console.log('Data received for edit:', data); // Add this line for debugging
                    $('#modelHeading').html('<i class="fas fa-edit mr-2"></i> Edit Task');
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

            // Delete Task with SweetAlert confirmation
            $('body').on('click', '.deleteTask', function () {
                var task_id = $(this).data("id");
                
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
                            url: "/pending-tasks/" + task_id,
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (data) {
                                table.draw();
                                Swal.fire(
                                    'Deleted!',
                                    data.success || 'Task has been deleted.',
                                    'success'
                                );
                            },
                            error: function (xhr) {
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

            // Filter functionality
            $('#filterBtn').click(function() {
                table.draw();
                toastr.info('Filter applied');
            });
        });
    </script>
@endsection
