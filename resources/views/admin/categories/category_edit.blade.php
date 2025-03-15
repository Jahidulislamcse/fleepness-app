@extends('admin.admin_dashboard')
@section('main')
<div class="page-inner">
    <div class="page-header">
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="icon-home"></i> Dashboard
                </a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="javascript:void(0)">Categories</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title" id="heading">Edit Category</h4>
                        <a class="btn btn-primary btn-round ms-auto" href="{{ route('admin.categories.index') }}">
                            <i class="fa fa-list"></i> List View
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Parent Category -->
                        <div class="form-group">
                            <label for="parent_id">Parent Category</label>
                            <select name="parent_id" id="parent_id" class="form-control">
                                <option value="">None</option>
                                @foreach ($categories as $parentCategory)
                                <option value="{{ $parentCategory->id }}"
                                    {{ old('parent_id', $category->parent_id) == $parentCategory->id ? 'selected' : '' }}>
                                    {{ $parentCategory->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Category Name -->
                        <div class="form-group">
                            <label for="name" id="nameLabel">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $category->name) }}" required>
                        </div>

                        <!-- Store Title (For Tags Only) -->
                        <div class="form-group" id="store_titleField">
                            <label for="store_title" id="store_titleLabel">Store Title</label>
                            <input type="text" name="store_title" id="store_title" class="form-control" value="{{ old('store_title', $category->store_title) }}">
                        </div>

                        <!-- Description -->
                        <div class="form-group" id="descriptionField">
                            <label for="description" id="descriptionLabel">Description</label>
                            <textarea name="description" id="description" class="form-control">{{ old('description', $category->description) }}</textarea>
                        </div>

                        <!-- Profile Image -->
                        <div class="form-group" id="profileImgField">
                            <label for="profile_img">Profile Image</label>
                            <input type="file" name="profile_img" id="profile_img" class="form-control">
                            @if ($category->profile_img)
                            <div class="mt-2">
                                <img src="{{ asset($category->profile_img) }}" alt="Profile Image" style="width: 150px;">
                            </div>
                            @endif
                        </div>

                        <!-- Cover Image -->
                        <div class="form-group" id="coverImgField">
                            <label for="cover_img">Cover Image</label>
                            <input type="file" name="cover_img" id="cover_img" class="form-control">
                            @if ($category->cover_img)
                            <div class="mt-2">
                                <img src="{{ asset($category->cover_img) }}" alt="Cover Image" style="width: 150px;">
                            </div>
                            @endif
                        </div>

                        <!-- Category Order (For Main Categories Only) -->
                        <div class="form-group" id="orderField">
                            <label for="order" id="orderLabel">Category Order</label>
                            <input type="number" name="order" class="form-control" id="orderInput" value="{{ old('order', $category->order) }}">
                        </div>

                        <button type="submit" class="btn btn-success">Update Category</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript to Dynamically Show/Hide Fields -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let parentSelect = document.getElementById('parent_id');
        let nameLabel = document.getElementById('nameLabel');
        let orderLabel = document.getElementById('orderLabel');
        let orderInput = document.getElementById('orderInput');
        let descriptionField = document.getElementById('descriptionField');
        let store_titleField = document.getElementById('store_titleField');
        let profileImgField = document.getElementById('profileImgField');
        let coverImgField = document.getElementById('coverImgField');

        function toggleFields() {
            if (parentSelect.value) {
                // When it's a child category (Tag)
                nameLabel.innerHTML = "Tag Name <span class='text-danger'>*</span>";
                heading.innerHTML = "Edit Tag";
                store_titleField.style.display = 'block';
                orderLabel.style.display = 'block';
                orderInput.style.display = 'block';
                descriptionField.style.display = 'block';
                profileImgField.style.display = 'block';
                coverImgField.style.display = 'block';
            } else {
                // When it's a main category
                nameLabel.innerHTML = "Category Name <span class='text-danger'>*</span>";
                store_titleField.style.display = 'none';
                orderLabel.style.display = 'block';
                orderInput.style.display = 'block';
                descriptionField.style.display = 'none';
                profileImgField.style.display = 'none';
                coverImgField.style.display = 'none';
            }
        }

        // Initial check on page load
        toggleFields();

        // Add event listener for change event
        parentSelect.addEventListener('change', toggleFields);
    });
</script>
@endsection
