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
                <a href="javascript:void(0)">Shop Categories</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Shop Categories</h4>
                        <button class="btn btn-primary btn-round ms-auto" id="toggleAddCategoryForm">
                            <i class="fa fa-plus"></i>
                            Add New Category
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div id="addCategoryFormSection" style="display: none;">
                        <form class="myForm" action="{{ route('admin.shop-categories.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="name">Category Name</label>
                                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" placeholder="Category Name">
                                        @error('name')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="description">Category Description</label>
                                        <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Category Description"></textarea>
                                        @error('description')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <button type="submit" class="btn btn-primary">Save Category</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="display table table-striped table-hover">
                            <thead>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </thead>
                            <tbody>
                                @foreach ($categories as $category)
                                <tr>
                                    <td>{{ $category->id }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $category->description ?? 'N/A' }}</td>
                                    <td>
                                        <button class="btn btn-warning btn-sm" id="toggleEditForm_{{ $category->id }}">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </button>

                                        <form action="{{ route('admin.shop-categories.destroy', $category->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this category?')">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <tr id="editCategoryRow_{{ $category->id }}" style="display: none;">
                                    <td colspan="4">
                                        <form class="myForm" action="{{ route('admin.shop-categories.update', $category->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="name">Category Name</label>
                                                        <input type="text" name="name" id="name" class="form-control" value="{{ $category->name }}" placeholder="Category Name">
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="description">Category Description</label>
                                                        <textarea name="description" id="description" rows="3" class="form-control" placeholder="Category Description">{{ $category->description }}</textarea>
                                                    </div>

                                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination links if needed -->
                    <div class="pagination-container">
                        {{ $categories->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    $(document).ready(function() {
        $('#toggleAddCategoryForm').click(function() {
            $('#addCategoryFormSection').toggle();
        });

        @foreach($categories as $category)
        $('#toggleEditForm_{{ $category->id }}').click(function() {
            $('#editCategoryRow_{{ $category->id }}').toggle();
        });
        @endforeach
    });
</script>
@endpush
