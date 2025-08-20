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

                        <!-- Parent Category: Display logic based on the category's level -->
                        <div class="form-group">
                            @if ($grandChildCategory)
                                <!-- Grandchild: Show parent and grandparent as disabled -->
                                <label for="parent_id">Category</label>
                                <input type="text" class="form-control mt-2 mb-2" value="{{ $grandChildCategory->name }}" readonly>
                                <label for="parent_id">Sub Category</label>
                                <input type="text" class="form-control mt-2 mb-2" value="{{ $parentCategory->name }}" readonly>
                                <input type="hidden" name="parent_id" value="{{ $parentCategory->id }}">
                            @elseif ($parentCategory)
                                <!-- Parent with no grandparent: Show parent as readonly -->
                                <label for="parent_id">Category</label>
                               <input type="text" class="form-control mt-2 mb-2" value="{{ $parentCategory->name }}" readonly>
                               <input type="hidden" name="parent_id" value="{{ $parentCategory->id }}">
                            @else
                                <!-- Top level category: Show parent dropdown -->
                            @endif
                        </div>

                        <!-- Category Name (Always editable) -->
                        <div class="form-group">
                            @if ($grandChildCategory)
                            <label for="name" id="nameLabel">Tag Name <span class="text-danger">*</span></label>
                            @elseif ($parentCategory)
                            <label for="name" id="nameLabel">Sub Category Name <span class="text-danger">*</span></label>
                            @else
                            <label for="name" id="nameLabel">Category Name <span class="text-danger">*</span></label>
                            @endif
                            <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required
                                   {{ $grandChildCategory ? '' : '' }}> <!-- Name is always editable -->
                        </div>

                        <!-- Category Order -->
                        <div class="form-group">
                            <label for="order" id="orderLabel">Index</label>
                            @php
                                // Determine parent_id for current category
                                $parentId = $category->parent_id ?? null;

                                // Fetch all sibling categories (same parent)
                                $siblings = \App\Models\Category::where('parent_id', $parentId)
                                            ->orderBy('order', 'asc')
                                            ->get();
                            @endphp

                            <select name="order" class="form-control">
                                @foreach ($siblings as $sibling)
                                    <option value="{{ $sibling->order }}" {{ $category->order == $sibling->order ? 'selected' : '' }}>
                                        {{ $sibling->order }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Store Title (Only for grandchildren, and allow editing) -->
                        @if ($grandChildCategory)
                            <div class="form-group" id="store_titleField">
                                <label for="store_title" id="store_titleLabel">Store Title</label>
                                <input type="text" name="store_title" id="store_title" class="form-control"
                                       value="{{ old('store_title', $category->store_title) }}">
                            </div>

                            <!-- Description (Only for grandchildren, and allow editing) -->
                            <div class="form-group" id="descriptionField">
                                <label for="description" id="descriptionLabel">Description</label>
                                <textarea name="description" id="description" class="form-control">{{ old('description', $category->description) }}</textarea>
                            </div>
                        @endif

                        <!-- Profile Image (Hide for grandparent and parent categories) -->
                        @if ($grandChildCategory)
                            <div class="form-group" id="profileImgField">
                                <label for="profile_img">Profile Image</label>
                                <input type="file" name="profile_img" id="profile_img" class="form-control">
                                @if ($category->profile_img)
                                    <div class="mt-2">
                                        <img src="{{ asset($category->profile_img) }}" alt="Profile Image" style="width: 150px;">
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Cover Image (Hide for grandparent and parent categories) -->
                        @if ($grandChildCategory)
                            <div class="form-group" id="coverImgField">
                                <label for="cover_img">Cover Image</label>
                                <input type="file" name="cover_img" id="cover_img" class="form-control">
                                @if ($category->cover_img)
                                    <div class="mt-2">
                                        <img src="{{ asset($category->cover_img) }}" alt="Cover Image" style="width: 150px;">
                                    </div>
                                @endif
                            </div>
                        @endif

                        <button type="submit" class="btn btn-success">Update Category</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script to handle dynamic form behavior -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Get the parent select field
        var parentSelect = document.getElementById('parent_id');
        var nameInput = document.getElementById('name');
        var orderInput = document.getElementById('order');
        var storeTitleField = document.getElementById('store_titleField');
        var descriptionField = document.getElementById('descriptionField');
        var profileImgField = document.getElementById('profileImgField');
        var coverImgField = document.getElementById('coverImgField');

        // Initially hide store title and description fields if there is a grandchild (third layer)
        if ({{ $grandChildCategory ? 'true' : 'false' }}) {
            storeTitleField.style.display = 'block';
            descriptionField.style.display = 'block';
            profileImgField.style.display = 'block';
            coverImgField.style.display = 'block';
        }

        // Disable fields if the category is a grandchild
        if ({{ $grandChildCategory ? 'true' : 'false' }}) {
            nameInput.disabled = false;  // Name should always be editable
            orderInput.disabled = false;  // Order should be editable for grandchildren
        }

        // Logic to show/hide fields dynamically based on the parent category
        parentSelect.addEventListener('change', function() {
            if (parentSelect.value) {
                storeTitleField.style.display = 'block';  // Show store title
                descriptionField.style.display = 'block'; // Show description
                profileImgField.style.display = 'none';   // Hide profile image for parents and grandparent
                coverImgField.style.display = 'none';    // Hide cover image for parents and grandparent
            } else {
                storeTitleField.style.display = 'none';   // Hide store title
                descriptionField.style.display = 'none';  // Hide description
            }
        });
    });
</script>
@endsection
