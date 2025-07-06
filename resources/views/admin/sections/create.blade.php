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
                <a href="javascript:void(0)">Add/Edit Section</a>
            </li>
        </ul>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Add/Edit Section</h4>
                </div>
                <div class="card-body">
                    <form action="{{ isset($section) ? route('admin.sections.update', $section->id) : route('admin.sections.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @isset($section)
                            @method('PUT')
                        @endisset



                        <div class="form-group">
                            <label for="section_type">Section Type</label>
                            <select name="section_type" id="section_type" class="form-control" required>
                                @foreach([
                                    'select_section_type', 'multiproduct_banner', 'single_banner', 'scrollable_product', 'lighting_deals', 'tag_box',
                                    'fancy_3x_box_grid', 'poster_section', 'scrollable_banners', 'smaller_4x_box_grid',
                                    'best_brands', '6x_box_grid', '2x_box_grid', 'u_shape_section', 'fancy_6x_product_grid',
                                    '8x_box_grid', 'problem_specific', 'spotlight_deals', '4x_box_section'
                                    ] as $type)
                                    <option value="{{ $type }}" {{ (isset($section) && $section->section_type == $type) ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $type)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Common Fields -->
                        <div class="form-group">
                            <label for="section_name">Section Name</label>
                            <input type="text" name="section_name" id="section_name" class="form-control" value="{{ old('section_name', $section->section_name ?? '') }}" required>
                        </div>

                        <!-- Common Fields -->
                        <div class="form-group">
                            <label for="section_title">Section Title</label>
                            <input type="text" name="section_title" id="section_title" class="form-control" value="{{ old('section_title', $section->section_title ?? '') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="category">Category</label>
                            <select name="category_id" id="category" class="form-control" required>
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ (isset($section) && $section->category_id == $category->id) ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <div class="form-group">
                            <label for="section_index">Section Index</label>
                            <input type="number" name="index" id="section_index" class="form-control" value="{{ old('section_index', $section->index ?? '') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="visibility">Visibility</label>
                            <input type="checkbox" name="visibility" id="visibility" value="1" {{ (isset($section) && $section->visibility) ? 'checked' : '' }}>
                        </div>


                        <!-- Dynamic Fields for Section Type -->
                        <div id="dynamicFieldsContainer"></div>

                        <button type="submit" class="btn btn-success">Save Section</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ensure jQuery is included -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        $('#section_type').change(function() {
            const sectionType = $(this).val();
            handleSectionTypeChange(sectionType);
        });

    $('#category').change(function() {
        const categoryId = $(this).val(); // Get selected category ID

        if (categoryId) {
            // Make an AJAX request to fetch grandchildren (tags) based on the selected category
            $.ajax({
                url: `/categories/${categoryId}/tags`, // The API endpoint
                type: 'GET',
                success: function(data) {
                    // Clear the existing options in the tag dropdown
                    $('#tag_id').empty();
                    $('#box1_tag').empty();
                    $('#box2_tag').empty();
                    $('#box3_tag').empty();

                    // Check if there are tags (grandchildren) returned
                    if (data.status) {
                        // Populate the tag dropdown with fetched grandchildren (tags)
                        $('#tag_id').append('<option value="">Select Tag</option>');
                        $('#box1_tag').append('<option value="">Select Tag</option>');
                        $('#box2_tag').append('<option value="">Select Tag</option>');
                        $('#box3_tag').append('<option value="">Select Tag</option>');

                        // Add each tag as an option in the select dropdown
                        data.tags.forEach(function(tag) {
                            $('#tag_id').append(new Option(tag.name, tag.id));
                             $('#box1_tag').append(new Option(tag.name, tag.id));
                            $('#box2_tag').append(new Option(tag.name, tag.id));
                            $('#box3_tag').append(new Option(tag.name, tag.id));
                        });
                    } else {
                        // If no tags are found, display a message or handle accordingly
                        $('#tag_id').append('<option value="">No tags found</option>');
                         $('#box1_tag').append('<option value="">No tags found</option>');
                        $('#box2_tag').append('<option value="">No tags found</option>');
                        $('#box3_tag').append('<option value="">No tags found</option>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching tags:', error);
                    alert('Something went wrong while fetching tags.');
                }
            });
        }
    });


        function handleSectionTypeChange(sectionType) {
            $('#dynamicFieldsContainer').empty(); // Clear dynamic fields

           switch (sectionType) {
            case 'multiproduct_banner':
                $('#dynamicFieldsContainer').append(`
                    <div class="form-group">
                        <label for="background_image">Background Image</label>
                        <input type="file" name="background_image" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="box1_image">Box 1 Image</label>
                        <input type="file" name="items[0][image]" class="form-control">
                        <select name="items[0][tag_id]" id="box1_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[0][index]" class="form-control" placeholder="Index" required>
                      <input type="checkbox" name="items[0][visibility]" value="1" {{ old('items[0][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                    <div class="form-group">
                        <label for="box2_image">Box 2 Image</label>
                        <input type="file" name="items[1][image]" class="form-control">
                        <select name="items[1][tag_id]" id="box2_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[1][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[1][visibility]" value="1" {{ old('items[1][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility

                    </div>
                    <div class="form-group">
                        <label for="box3_image">Box 3 Image</label>
                        <input type="file" name="items[2][image]" class="form-control">
                        <select name="items[2][tag_id]" id="box3_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[2][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[2][visibility]" value="1" {{ old('items[2][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                `);
                break;

            case 'single_banner':
                $('#dynamicFieldsContainer').append(`
                    <div class="form-group">
                        <label for="banner_image">Banner Image</label>
                        <input type="file" name="banner_image" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="tag_id">Tag ID</label>
                        <select name="tag_id" id="tag_id" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                    </div>
                `);
                break;

            case 'scrollable_product':
                $('#dynamicFieldsContainer').append(`
                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <input type="text" name="bio" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="tag_id">Tag ID</label>
                        <select name="tag_id" id="tag_id" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                    </div>
                `);
                break;

            case 'lighting_deals':
                $('#dynamicFieldsContainer').append(`
                    <div class="form-group">
                        <label for="background_image">Background Image</label>
                        <input type="file" name="background_image" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="tag_id">Tag ID</label>
                        <select name="tag_id" id="tag_id" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                    </div>
                `);
                break;

            case 'tag_box':
                $('#dynamicFieldsContainer').append(`
                    <div class="form-group">
                        <label for="box1_image">Box 1 Image</label>
                        <input type="file" name="items[0][image]" class="form-control">
                        <select name="items[0][tag_id]" id="box1_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[0][index]" class="form-control" placeholder="Index" required>
                      <input type="checkbox" name="items[0][visibility]" value="1" {{ old('items[0][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                    <div class="form-group">
                        <label for="box2_image">Box 2 Image</label>
                        <input type="file" name="items[1][image]" class="form-control">
                        <select name="items[1][tag_id]" id="box2_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[1][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[1][visibility]" value="1" {{ old('items[1][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility

                    </div>
                `);
                break;

            // Add similar cases for other section types here...
        }

        }

        // Initial call to populate fields based on selected type
        handleSectionTypeChange($('#section_type').val());
    });
</script>
@endsection
