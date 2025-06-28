@extends('adminlte::page')

@section('title', 'Clients')

@section('content_header')
    <h1>Clients List</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Clients</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addClientModal">
                    Add New Client
                </button>
            </div>
        </div>
        <div class="card-body">
            <table id="clientsTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clients as $client)
                    <tr>
                        <td>{{ $client->id }}</td>
                        <td>{{ $client->name }}</td>
                        <td>{{ $client->email }}</td>
                        <td>{{ $client->phone }}</td>
                        <td>
                            <button class="btn btn-sm btn-info edit-client" data-id="{{ $client->id }}" data-name="{{ $client->name }}" data-email="{{ $client->email }}" data-phone="{{ $client->phone }}">Edit</button>
                            <button class="btn btn-sm btn-danger delete-client" data-id="{{ $client->id }}">Delete</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Client Modal -->
    <!-- Edit Client Modal -->
    <div class="modal fade" id="editClientModal" tabindex="-1" role="dialog" aria-labelledby="editClientModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editClientModalLabel">Edit Client</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editClientForm">
                    <div class="modal-body">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit-client-id" name="id">
                        <div class="form-group">
                            <label for="edit-name">Name</label>
                            <input type="text" class="form-control" id="edit-name" name="name" required>
                            <span class="text-danger" id="edit-name-error"></span>
                        </div>
                        <div class="form-group">
                            <label for="edit-email">Email</label>
                            <input type="email" class="form-control" id="edit-email" name="email" required>
                            <span class="text-danger" id="edit-email-error"></span>
                        </div>
                        <div class="form-group">
                            <label for="edit-phone">Phone</label>
                            <input type="text" class="form-control" id="edit-phone" name="phone">
                            <span class="text-danger" id="edit-phone-error"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="addClientModal" tabindex="-1" role="dialog" aria-labelledby="addClientModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addClientModalLabel">Add New Client</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addClientForm">
                    <div class="modal-body">
                        @csrf
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <span class="text-danger" id="name-error"></span>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <span class="text-danger" id="email-error"></span>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                            <span class="text-danger" id="phone-error"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#addClientForm').on('submit', function(e) {
                e.preventDefault();

                // Clear previous errors
                $('#name-error').text('');
                $('#email-error').text('');
                $('#phone-error').text('');

                $.ajax({
                    url: '{{ route('clients.store') }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        toastr.success(response.message);
                        $('#addClientModal').modal('hide');
                        $('#addClientForm')[0].reset();
                        // Optionally, add the new client to the table dynamically
                        var newRow = '<tr>' +
                            '<td>' + response.client.id + '</td>' +
                            '<td>' + response.client.name + '</td>' +
                            '<td>' + response.client.email + '</td>' +
                            '<td>' + (response.client.phone || '') + '</td>' +
                            '<td>' +
                                '<button class="btn btn-sm btn-info edit-client" data-id="' + response.client.id + '" data-name="' + response.client.name + '" data-email="' + response.client.email + '" data-phone="' + (response.client.phone || '') + '">Edit</button> ' +
                                '<button class="btn btn-sm btn-danger delete-client" data-id="' + response.client.id + '">Delete</button>' +
                            '</td>'
                            '</tr>';
                        $('#clientsTable tbody').append(newRow);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            if (errors.name) {
                                $('#name-error').text(errors.name[0]);
                            }
                            if (errors.email) {
                                $('#email-error').text(errors.email[0]);
                            }
                            if (errors.phone) {
                                $('#phone-error').text(errors.phone[0]);
                            }
                            toastr.error('Please correct the form errors.');
                        } else {
                            toastr.error('An error occurred. Please try again.');
                        }
                    }
                });
            });

            // Handle Edit button click
            $(document).on('click', '.edit-client', function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                var email = $(this).data('email');
                var phone = $(this).data('phone');

                $('#edit-client-id').val(id);
                $('#edit-name').val(name);
                $('#edit-email').val(email);
                $('#edit-phone').val(phone);

                $('#editClientModal').modal('show');
            });

            // Handle Edit form submission
            $('#editClientForm').on('submit', function(e) {
                e.preventDefault();

                // Clear previous errors
                $('#edit-name-error').text('');
                $('#edit-email-error').text('');
                $('#edit-phone-error').text('');

                var clientId = $('#edit-client-id').val();

                $.ajax({
                    url: '/clients/' + clientId,
                    method: 'PUT',
                    data: $(this).serialize(),
                    success: function(response) {
                        toastr.success(response.message);
                        $('#editClientModal').modal('hide');
                        // Update the row in the table
                        var row = $('button.edit-client[data-id="' + clientId + '"]').closest('tr');
                        row.find('td:eq(1)').text(response.client.name);
                        row.find('td:eq(2)').text(response.client.email);
                        row.find('td:eq(3)').text(response.client.phone || '');
                        // Update data attributes for the edit button
                        row.find('.edit-client').data('name', response.client.name);
                        row.find('.edit-client').data('email', response.client.email);
                        row.find('.edit-client').data('phone', response.client.phone);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            if (errors.name) {
                                $('#edit-name-error').text(errors.name[0]);
                            }
                            if (errors.email) {
                                $('#edit-email-error').text(errors.email[0]);
                            }
                            if (errors.phone) {
                                $('#edit-phone-error').text(errors.phone[0]);
                            }
                            toastr.error('Please correct the form errors.');
                        } else {
                            toastr.error('An error occurred. Please try again.');
                        }
                    }
                });
            });

            // Handle Delete button click
            $(document).on('click', '.delete-client', function() {
                var clientId = $(this).data('id');
                if (confirm('Are you sure you want to delete this client?')) {
                    $.ajax({
                        url: '/clients/' + clientId,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            toastr.success(response.message);
                            $('button.delete-client[data-id="' + clientId + '"]').closest('tr').remove();
                        },
                        error: function(xhr) {
                            toastr.error('An error occurred. Please try again.');
                        }
                    });
                }
            });
        });
    </script>
@stop