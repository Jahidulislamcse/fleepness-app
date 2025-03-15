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
                <a href="javascript:void(0)">Sliders</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Sliders</h4>
                        <button class="btn btn-primary btn-round ms-auto" id="toggleAddSliderForm">
                            <i class="fa fa-plus"></i>
                            Add New
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="addSliderFormSection" style="display: none;">
                        <form class="myForm" action="{{ route('admin.sliders.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="title">Slider Title </label>
                                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Slider Title">
                                        @error('title')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="photo">Slider Photo <span class="text-danger">*</span> </label>
                                        <input type="file" name="photo" id="photo" class="form-control @error('photo') is-invalid @enderror" multiple required>
                                        @error('photo')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="photo_alt">Photo Alt </label>
                                        <input type="text" name="photo_alt" id="photo_alt" class="form-control @error('photo_alt') is-invalid @enderror" value="{{ old('photo_alt') }}" placeholder="Photo Alt">
                                        @error('photo_alt')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="category_id">Category</label>
                                        <select name="category_id" id="category_id" class="form-control">
                                            <option value="">Select Category</option>
                                            @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="tag_id">Tag</label>
                                        <select name="tag_id" id="tag_id" class="form-control">
                                            <option value="">Select Tag</option>
                                        </select>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Save Slider</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="display table table-striped table-hover">
                            <thead>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Tag</th>
                                <th>Actions</th>
                            </thead>
                            <tbody>
                                @foreach ($sliders as $key => $slider)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>
                                        <img src="{{ asset($slider->photo) }}" alt="" style="width:200px;">
                                    </td>
                                    <td>{{ $slider->title }}</td>
                                    <td>{{ $slider->category->name ?? 'N/A' }}</td>
                                    <td>{{ $slider->tag->name ?? 'N/A' }}</td>
                                    <td>
                                        <button class="btn btn-warning btn-sm" id="toggleEditForm_{{ $slider->id }}">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        <form action="{{ route('admin.sliders.destroy', $slider->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this slider?')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <tr id="editSliderRow_{{ $slider->id }}" style="display: none;">
                                    <td colspan="4">
                                        <form class="myForm" action="{{ route('admin.sliders.update', $slider->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="title">Slider Title </label>
                                                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ $slider->title }}" placeholder="Slider Title">
                                                        @error('title')
                                                        <div class="alert alert-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="photo">Slider Photo <span class="text-danger">*</span> </label>
                                                        <input type="file" name="photo" id="photo" class="form-control @error('photo') is-invalid @enderror" multiple>
                                                        @error('photo')
                                                        <div class="alert alert-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="photo_alt">Photo Alt </label>
                                                        <input type="text" name="photo_alt" id="photo_alt" class="form-control @error('photo_alt') is-invalid @enderror" value="{{ $slider->photo_alt }}" placeholder="Photo Alt">
                                                        @error('photo_alt')
                                                        <div class="alert alert-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="edit_category_id">Category</label>
                                                        <select name="edit_category_id" id="edit_category_id" class="form-control">
                                                            <option value="">Select Category</option>
                                                            @foreach ($categories as $category)
                                                            <option value="{{ $category->id }}" {{ $slider->category_id == $category->id ? 'selected' : '' }}>
                                                                {{ $category->name }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="edit_tag_id">Tag</label>
                                                        <select name="edit_tag_id" id="edit_tag_id" class="form-control" data-selected-tag="{{ $slider->tag_id }}">
                                                            <option value="">Select Tag</option>
                                                            @foreach ($tags as $tag)
                                                            @if ($tag->parent_id == $slider->category_id)
                                                            <option value="{{ $tag->id }}" {{ $slider->tag_id == $tag->id ? 'selected' : '' }}>
                                                                {{ $tag->name }}
                                                            </option>
                                                            @endif
                                                            @endforeach
                                                        </select>

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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    $(document).ready(function() {
        // Toggle Add Slider Form
        $('#toggleAddSliderForm').click(function() {
            $('#addSliderFormSection').toggle();
        });

        @foreach($sliders as $slider)
        // Toggle Edit Slider Form
        $('#toggleEditForm_{{ $slider->id }}').click(function() {
            $('#editSliderRow_{{ $slider->id }}').toggle();
            let row = $('#editSliderRow_{{ $slider->id }}');

            // Get category and tag fields
            let categorySelect = row.find('#edit_category_id');
            let tagSelect = row.find('#edit_tag_id');

            // Fetch and preselect stored tags
            let selectedCategoryId = categorySelect.val();
            let selectedTagId = tagSelect.attr('data-selected-tag');

            fetchTags(selectedCategoryId, tagSelect, selectedTagId);

            // When the category changes, fetch tags dynamically
            categorySelect.on('change', function() {
                let newCategoryId = $(this).val();
                fetchTags(newCategoryId, tagSelect, null); // Reset tag selection
            });
        });
        @endforeach

        // Fetch child tags for Add Slider Form
        $('#category_id').on('change', function() {
            var categoryId = $(this).val();
            fetchTags(categoryId, $('#tag_id'), null);
        });
    });

    // Function to fetch tags dynamically
    function fetchTags(categoryId, tagSelect, selectedTagId = null) {
        if (!categoryId) {
            tagSelect.empty().append('<option value="">Select Tag</option>');
            return;
        }

        $.ajax({
            url: '{{ route("admin.getTags") }}',
            type: 'GET',
            data: {
                category_id: categoryId
            },
            success: function(response) {
                tagSelect.empty().append('<option value="">Select Tag</option>');

                $.each(response, function(index, tag) {
                    let isSelected = (selectedTagId && tag.id == selectedTagId) ? 'selected' : '';
                    tagSelect.append('<option value="' + tag.id + '" ' + isSelected + '>' + tag.name + '</option>');
                });
            }
        });
    }
</script>
@endpush
