@extends('admin.admin_dashboard')
@section('main')
<div class="page-inner">
    <div class="page-header">
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="icon-home"></i>
                    Dashboard
                </a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="javascript:void(0)">Users</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <!-- Toggle Buttons -->
                    <div class="mb-4">
                        <button class="btn btn-primary toggle-btn" data-role="user">Customers</button>
                        <button class="btn btn-primary toggle-btn" data-role="vendor">Vendors</button>
                        <button class="btn btn-primary toggle-btn" data-role="admin">Admins</button>
                        <button class="btn btn-primary toggle-btn" data-role="rider">Riders</button>
                    </div>

                    <!-- Add New Button (Hidden by Default) -->
                    <div id="add-new-section" class="mb-4" style="display: none;">
                        <button id="add-new-btn" class="btn btn-success">Add New</button>
                    </div>

                    <!-- Registration Form (Hidden by Default) -->
                    <div id="registration-form" class="mb-4" style="display: none;">
                        <form id="user-registration-form" action="{{ route('admin.user.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input type="text" name="name" id="name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" name="email" id="email" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">Phone</label>
                                        <input type="text" name="phone" id="phone" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="address">Address</label>
                                        <input type="text" name="address" id="address" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password">Password</label>
                                        <input type="password" name="password" id="password" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="role">Role</label>
                                        <select name="role" id="role" class="form-control" required>
                                            <option value="vendor">vendor</option>
                                            <option value="admin">admin</option>
                                            <option value="rider">rider</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">Register</button>
                                    <button type="button" id="cancel-registration" class="btn btn-secondary">Cancel</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Users Table -->
                    <div class="table-responsive">
                        <table id="basic-datatables" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>SN</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $key => $user)
                                <tr data-role="{{ $user->role }}" data-id="{{ $user->id }}">
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->phone_number }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ ucfirst($user->role) }}</td>
                                    <td>{{ $user->status }}</td>
                                    <td>
                                        <!-- Edit Button -->
                                        @if($user->role === 'vendor' || $user->role === 'admin' || $user->role === 'rider')
                                        <button class="btn btn-sm btn-primary edit-btn" data-id="{{ $user->id }}">Edit</button>
                                        @endif

                                        <!-- Delete Button -->
                                        @if($user->role === 'vendor' || $user->role === 'admin' || $user->role === 'rider')
                                        <form action="{{ route('admin.user.destroy', $user->id) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Show all users by default
        $('tbody tr').show();

        // Toggle button click event
        $('.toggle-btn').click(function() {
            const role = $(this).data('role'); // Get the role from the button

            // Hide all rows
            $('tbody tr').hide();

            // Show rows with the selected role
            $(`tbody tr[data-role="${role}"]`).show();

            // Show "Add New" button and hide registration form for vendors and admins
            if (role === 'vendor' || role === 'admin' || role === 'rider') {
                $('#add-new-section').show();
                $('#registration-form').hide(); // Hide registration form
            } else {
                // Hide both "Add New" button and registration form for customers
                $('#add-new-section').hide();
                $('#registration-form').hide();
            }

            // Set the role in the registration form
            $('#role').val(role);
        });

        // Show registration form when "Add New" button is clicked
        $('#add-new-btn').click(function() {
            $('#registration-form').show();
            $('#add-new-section').hide();
        });

        // Hide registration form when "Cancel" button is clicked
        $('#cancel-registration').click(function() {
            $('#registration-form').hide();
            $('#add-new-section').show();
        });

        // Edit Button Click Event
        $('.edit-btn').click(function() {
            const userId = $(this).data('id');
            const editUrl = `/admin/user/${userId}/edit`; // Route to fetch user data
            const $row = $(this).closest('tr'); // Get the current row

            // Remove any existing edit forms
            $('.edit-form').remove();

            // Fetch user data via AJAX
            $.ajax({
                url: editUrl,
                method: 'GET',
                success: function(response) {
                    // Create the edit form HTML
                    const editForm = `
                        <tr class="edit-form">
                            <td colspan="7">
                                <form action="/admin/user/${userId}" method="POST" class="p-3 bg-light">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="edit-name">Name</label>
                                                <input type="text" name="name" id="edit-name" class="form-control" value="${response.name}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="edit-email">Email</label>
                                                <input type="email" name="email" id="edit-email" class="form-control" value="${response.email}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="edit-phone">Phone</label>
                                                <input type="text" name="phone" id="edit-phone" class="form-control" value="${response.phone_number}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="edit-address">Address</label>
                                                <input type="text" name="address" id="edit-address" class="form-control" value="${response.address}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="edit-role">Role</label>
                                                <select name="role" id="edit-role" class="form-control" required>
                                                    <option value="vendor" ${response.role === 'vendor' ? 'selected' : ''}>Vendor</option>
                                                    <option value="admin" ${response.role === 'admin' ? 'selected' : ''}>Admin</option>
                                                    <option value="rider" ${response.role === 'rider' ? 'selected' : ''}>Rider</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="edit-status">Status</label>
                                                <select name="status" id="edit-status" class="form-control" required>
                                                    <option value="pending" ${response.status === 'pending' ? 'selected' : ''}>Pending</option>
                                                    <option value="approved" ${response.status === 'approved' ? 'selected' : ''}>Approved</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <button type="submit" class="btn btn-primary">Update</button>
                                            <button type="button" class="btn btn-secondary cancel-edit">Cancel</button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    `;

                    // Insert the edit form below the current row
                    $row.after(editForm);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching user data:', error);
                }
            });
        });

        // Cancel Edit Button Click Event
        $(document).on('click', '.cancel-edit', function() {
            $('.edit-form').remove(); // Remove the edit form
        });
    });
</script>
@endsection