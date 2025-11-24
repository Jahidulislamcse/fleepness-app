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
                <a href="javascript:void(0)">Categories</a>
            </li>
        </ul>
    </div>
     @if (session('success'))
        <div class="alert alert-success" id="successMessage">
            {{ session('success') }}
        </div>

        <script>
            setTimeout(function() {
                var successMessage = document.getElementById('successMessage');
                if (successMessage) {
                    successMessage.style.display = 'none';
                }
            }, 3000);
        </script>
    @endif
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Categories</h4>
                        <button class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal"
                            data-bs-target="#addRowModal">
                            <i class="fa fa-plus"></i>
                            Add New
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="modal fade" id="addRowModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">Add New Category</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" id="categoryForm">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="parent_id">Parent Category</label>
                                                    <select name="parent_id" id="parent_id" class="form-control">
                                                        <option value="">None</option>
                                                        @foreach ($categories as $parentCategory)
                                                            <option value="{{ $parentCategory->id }}" {{ old('parent_id') == $parentCategory->id ? 'selected' : '' }}>{{ $parentCategory->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="form-group" id="childCategoryDiv" style="display:none;">
                                                    <label for="child_id">Select Sub Category</label>
                                                    <select name="child_id" id="child_id" class="form-control">
                                                        <option value="">Select a parent category first</option>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label for="name" id="nameLabel">Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                                        value="{{ old('name') }}" required>
                                                    @error('name')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div id="additionalFields" style="display:none;">
                                                    <div class="form-group">
                                                        <label for="store_title">Store Title </label>
                                                        <input type="text" name="store_title" id="store_title" class="form-control @error('store_title') is-invalid @enderror"
                                                            value="{{ old('store_title') }}">
                                                        @error('store_title')
                                                        <div class="alert alert-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="description" id="descriptionLabel">Description</label>
                                                        <textarea name="description" id="description" class="form-control">{{ old('description') }}</textarea>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="profile_img">Profile Image</label>
                                                        <input type="file" name="profile_img" id="profile_img" class="form-control @error('profile_img') is-invalid @enderror">
                                                        @error('profile_img')
                                                        <div class="alert alert-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="cover_img">Cover Image</label>
                                                        <input type="file" name="cover_img" id="cover_img" class="form-control @error('cover_img') is-invalid @enderror">
                                                        @error('cover_img')
                                                        <div class="alert alert-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <input type="hidden" name="parent_id" id="hiddenParentId">
                                                <input type="hidden" name="mark" id="hiddenMark">

                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                   <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Category</th>
                                    <th>Subcategory</th>
                                    <th>Tags</th>
                                    <th>Profile Image</th>
                                    <th>Cover Image</th>
                                    <th>Index</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categories as $key => $category)
                                    <tr class="table-primary">
                                        <td>{{ $category->name }}</td>
                                        <td></td> 
                                        <td></td> 
                                        <td>
                                        </td>
                                        <td>
                                        </td>
                                        <td>{{ $category->order }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" onclick="return confirm('Are you sure you want to delete this category?')">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>

                                    @foreach ($category->children as $childKey => $child)
                                        <tr class="table-warning">
                                            <td></td> 
                                            <td>{{ $child->name }}</td> 
                                            <td></td> 
                                            <td>
                                            </td>
                                            <td>
                                            </td>
                                            <td>{{ $child->order }}</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('admin.categories.edit', $child->id) }}" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>
                                                    <form action="{{ route('admin.categories.destroy', $child->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this subcategory?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>

                                        </tr>

                                        @foreach ($child->children as $grandchildKey => $grandchild)
                                            <tr class="table-secondary">
                                                <td></td>
                                                <td></td> 
                                                <td>{{ $grandchild->name }}</td> 
                                                <td>
                                                    @if($grandchild->profile_img)
                                                        <img src="{{ $grandchild->profile_img }}" alt="{{ $grandchild->name }}" class="img-thumbnail" style="width:50px; height:50px;">
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($grandchild->cover_img)
                                                        <img src="{{ $grandchild->cover_img }}" alt="{{ $grandchild->name }}" class="img-thumbnail" style="width:50px; height:50px;">
                                                    @endif
                                                </td>

                                                <td>{{ $grandchild->order }}</td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('admin.categories.edit', $grandchild->id) }}" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>
                                                        <form action="{{ route('admin.categories.destroy', $grandchild->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete" onclick="return confirm('Are you sure you want to delete this tag?')">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                    <tr>
                                        <td colspan="7"></td>
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
    $('#parent_id').change(function() {
        var parentId = $(this).val();
        if (parentId) {
            $.ajax({
                url: '/admin/categories/children/' + parentId,
                type: 'GET',
                success: function(data) {
                    var childSelect = $('#child_id');
                    childSelect.empty(); 
                    childSelect.append('<option value="">Select Sub Category</option>');
                    data.forEach(function(child) {
                        childSelect.append('<option value="' + child.id + '">' + child.name + '</option>');
                    });

                    $('#childCategoryDiv').show(); 
                }
            });
            $('#hiddenParentId').val(parentId);
        } else {
            $('#childCategoryDiv').hide(); 
            $('#hiddenParentId').val(''); 
        }
    });

    $('#child_id').change(function() {
        if ($(this).val()) {
            $('#hiddenMark').val('T');
            $('#hiddenParentId').val($(this).val());
            $('#additionalFields').show(); 
        } else {
            $('#hiddenMark').val(''); 
            var parentId = $('#parent_id').val();
            if (parentId) {
                $('#hiddenParentId').val(parentId);
            } else {
                $('#hiddenParentId').val(''); 
            }
            $('#additionalFields').hide(); 
        }
    });

    if ($('#parent_id').val()) {
        $('#hiddenParentId').val($('#parent_id').val());
    }
});

</script>

@endsection
