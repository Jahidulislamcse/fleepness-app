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
                <a href="javascript:void(0)">Add Section</a>
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
                <div class="card-body  col-md-12">
                    <form action="{{ isset($section) ? route('admin.sections.update', $section->id) : route('admin.sections.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @isset($section)
                            @method('PUT')
                        @endisset

                        <div class="row">
                            <!-- Left Column: Common Fields -->
                            <div class="col-md-4">
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

                                <div class="form-group">
                                    <label for="section_name">Section Name</label>
                                    <input type="text" name="section_name" id="section_name" class="form-control" value="{{ old('section_name', $section->section_name ?? '') }}" required>
                                </div>

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

                                <button type="submit" class="btn btn-success">Save Section</button>
                            </div>

                            <!-- Right Column: Dynamic Fields -->
                            <div class="col-md-8">
                                <div class="form-group">
                                    <div id="dynamicFieldsContainer" class="row"></div>
                                </div>
                            </div>
                        </div>
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
                    $('#box4_tag').empty();
                    $('#box5_tag').empty();
                    $('#box6_tag').empty();
                    $('#box7_tag').empty();
                    $('#box8_tag').empty();



                    // Check if there are tags (grandchildren) returned
                    if (data.status) {
                        // Populate the tag dropdown with fetched grandchildren (tags)
                        $('#tag_id').append('<option value="">Select Tag</option>');
                        $('#box1_tag').append('<option value="">Select Tag</option>');
                        $('#box2_tag').append('<option value="">Select Tag</option>');
                        $('#box3_tag').append('<option value="">Select Tag</option>');
                        $('#box4_tag').append('<option value="">Select Tag</option>');
                        $('#box5_tag').append('<option value="">Select Tag</option>');
                        $('#box6_tag').append('<option value="">Select Tag</option>');
                        $('#box7_tag').append('<option value="">Select Tag</option>');
                        $('#box8_tag').append('<option value="">Select Tag</option>');

                        // Add each tag as an option in the select dropdown
                        data.tags.forEach(function(tag) {
                            $('#tag_id').append(new Option(tag.name, tag.id));
                             $('#box1_tag').append(new Option(tag.name, tag.id));
                            $('#box2_tag').append(new Option(tag.name, tag.id));
                            $('#box3_tag').append(new Option(tag.name, tag.id));
                            $('#box4_tag').append(new Option(tag.name, tag.id));
                            $('#box5_tag').append(new Option(tag.name, tag.id));
                            $('#box6_tag').append(new Option(tag.name, tag.id));
                            $('#box7_tag').append(new Option(tag.name, tag.id));
                            $('#box8_tag').append(new Option(tag.name, tag.id));

                        });
                    } else {
                        // If no tags are found, display a message or handle accordingly
                        $('#tag_id').append('<option value="">No tags found</option>');
                         $('#box1_tag').append('<option value="">No tags found</option>');
                        $('#box2_tag').append('<option value="">No tags found</option>');
                        $('#box3_tag').append('<option value="">No tags found</option>');
                        $('#box4_tag').append('<option value="">No tags found</option>');
                        $('#box5_tag').append('<option value="">No tags found</option>');
                        $('#box6_tag').append('<option value="">No tags found</option>');
                        $('#box7_tag').append('<option value="">No tags found</option>');
                        $('#box8_tag').append('<option value="">No tags found</option>');
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
                    <div class="form-group px-4">
                        <label for="background_image">Background Image</label>
                        <input type="file" name="background_image" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="box1_image">Box 1 Image</label>
                            <input type="file" name="items[0][image]" class="form-control">
                            <select name="items[0][tag_id]" id="box1_tag" class="form-control" required>
                                <option value="">Select Tag</option>
                            </select>
                            <input type="number" name="items[0][index]" class="form-control" placeholder="Index" required>
                            <input type="checkbox" name="items[0][visibility]" value="1" {{ old('items[0][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="box2_image">Box 2 Image</label>
                            <input type="file" name="items[1][image]" class="form-control">
                            <select name="items[1][tag_id]" id="box2_tag" class="form-control" required>
                                <option value="">Select Tag</option>
                            </select>
                            <input type="number" name="items[1][index]" class="form-control" placeholder="Index" required>
                            <input type="checkbox" name="items[1][visibility]" value="1" {{ old('items[1][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="box3_image">Box 3 Image</label>
                            <input type="file" name="items[2][image]" class="form-control">
                            <select name="items[2][tag_id]" id="box3_tag" class="form-control" required>
                                <option value="">Select Tag</option>
                            </select>
                            <input type="number" name="items[2][index]" class="form-control" placeholder="Index" required>
                            <input type="checkbox" name="items[2][visibility]" value="1" {{ old('items[2][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                        </div>
                    </div>
                `);
                break;

            case 'single_banner':
                $('#dynamicFieldsContainer').append(`
                 <div class="col-md-4">
                    <div class="form-group">
                        <label for="banner_image">Banner Image</label>
                        <input type="file" name="banner_image" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tag_id">Tag ID</label>
                        <select name="items[0][tag_id]" id="tag_id" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                    </div>
                </div>

                `);
                break;

            case 'scrollable_product':
                $('#dynamicFieldsContainer').append(`
                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <textarea name="items[0][bio]" class="form-control" rows="5" placeholder="Bio"  required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="tag_id">Tag ID</label>
                        <select name="items[0][tag_id]" id="tag_id" class="form-control" required>
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
                        <select name="items[0][tag_id]" id="tag_id" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                    </div>
                `);
                break;

            case 'tag_box':
                $('#dynamicFieldsContainer').append(`
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box1_image">Box 1 Image</label>
                        <input type="file" name="items[0][image]" class="form-control">
                        <textarea name="items[0][bio]" class="form-control" rows="5" placeholder="Bio"  required></textarea>
                        <select name="items[0][tag_id]" id="box1_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[0][index]" class="form-control" placeholder="Index" required>
                      <input type="checkbox" name="items[0][visibility]" value="1" {{ old('items[0][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 2 Image</label>
                        <input type="file" name="items[1][image]" class="form-control">
                        <textarea name="items[1][bio]" class="form-control" rows="5" placeholder="Bio" required></textarea>
                        <select name="items[1][tag_id]" id="box2_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[1][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[1][visibility]" value="1" {{ old('items[1][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>
                `);
                break;

             case 'fancy_3x_box_grid':
                $('#dynamicFieldsContainer').append(`
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box1_image">Box 1 Image</label>
                        <input type="file" name="items[0][image]" class="form-control">
                        <select name="items[0][tag_id]" id="box1_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[0][index]" class="form-control" placeholder="Index" required>
                      <input type="checkbox" name="items[0][visibility]" value="1" {{ old('items[0][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 2 Image</label>
                        <input type="file" name="items[1][image]" class="form-control">
                        <select name="items[1][tag_id]" id="box2_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[1][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[1][visibility]" value="1" {{ old('items[1][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                 <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 3 Image</label>
                        <input type="file" name="items[2][image]" class="form-control">
                        <select name="items[2][tag_id]" id="box3_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[2][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[2][visibility]" value="1" {{ old('items[2][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>
                `);
                break;

                case 'poster_section':
                $('#dynamicFieldsContainer').append(`
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box1_image">Box 1 Image</label>
                        <input type="file" name="items[0][image]" class="form-control">
                        <select name="items[0][tag_id]" id="box1_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[0][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[0][visibility]" value="1" {{ old('items[0][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 2 Image</label>
                        <input type="file" name="items[1][image]" class="form-control">
                        <select name="items[1][tag_id]" id="box2_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[1][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[1][visibility]" value="1" {{ old('items[1][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>
                `);
                break;

                case 'scrollable_banners':
                $('#dynamicFieldsContainer').append(`
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box1_image">Box 1 Image</label>
                        <input type="file" name="items[0][image]" class="form-control">
                        <select name="items[0][tag_id]" id="box1_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[0][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[0][visibility]" value="1" {{ old('items[0][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 2 Image</label>
                        <input type="file" name="items[1][image]" class="form-control">
                        <select name="items[1][tag_id]" id="box2_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[1][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[1][visibility]" value="1" {{ old('items[1][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>
                `);
                break;

                case 'smaller_4x_box_grid':
                $('#dynamicFieldsContainer').append(`
                <div class="form-group">
                    <label for="background_image">Background Image</label>
                    <input type="file" name="background_image" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box1_image">Box 1 Image</label>
                        <input type="file" name="items[0][image]" class="form-control">
                        <select name="items[0][tag_id]" id="box1_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[0][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[0][visibility]" value="1" {{ old('items[0][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 2 Image</label>
                        <input type="file" name="items[1][image]" class="form-control">
                        <select name="items[1][tag_id]" id="box2_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[1][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[1][visibility]" value="1" {{ old('items[1][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                 <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 3 Image</label>
                        <input type="file" name="items[2][image]" class="form-control">
                        <select name="items[2][tag_id]" id="box3_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[2][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[2][visibility]" value="1" {{ old('items[2][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                 <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 4 Image</label>
                        <input type="file" name="items[3][image]" class="form-control">
                        <select name="items[3][tag_id]" id="box4_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[3][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[3][visibility]" value="1" {{ old('items[3][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>
                `);
                break;

                case 'best_brands':
                $('#dynamicFieldsContainer').append(`
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box1_image">Box 1 Image</label>
                        <input type="file" name="items[0][image]" class="form-control">
                        <select name="items[0][tag_id]" id="box1_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[0][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[0][visibility]" value="1" {{ old('items[0][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 2 Image</label>
                        <input type="file" name="items[1][image]" class="form-control">
                        <select name="items[1][tag_id]" id="box2_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[1][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[1][visibility]" value="1" {{ old('items[1][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>
                `);
                break;

                case '6x_box_grid':
                $('#dynamicFieldsContainer').append(`

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box1_image">Box 1 Image</label>
                        <input type="file" name="items[0][image]" class="form-control">
                        <select name="items[0][tag_id]" id="box1_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[0][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[0][visibility]" value="1" {{ old('items[0][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 2 Image</label>
                        <input type="file" name="items[1][image]" class="form-control">
                        <select name="items[1][tag_id]" id="box2_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[1][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[1][visibility]" value="1" {{ old('items[1][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                 <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 3 Image</label>
                        <input type="file" name="items[2][image]" class="form-control">
                        <select name="items[2][tag_id]" id="box3_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[2][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[2][visibility]" value="1" {{ old('items[2][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                 <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 4 Image</label>
                        <input type="file" name="items[3][image]" class="form-control">
                        <select name="items[3][tag_id]" id="box4_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[3][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[3][visibility]" value="1" {{ old('items[3][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 5 Image</label>
                        <input type="file" name="items[4][image]" class="form-control">
                        <select name="items[4][tag_id]" id="box5_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[4][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[4][visibility]" value="1" {{ old('items[4][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 6 Image</label>
                        <input type="file" name="items[5][image]" class="form-control">
                        <select name="items[5][tag_id]" id="box6_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[5][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[5][visibility]" value="1" {{ old('items[5][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>
                `);
                break;

                case '2x_box_grid':
                $('#dynamicFieldsContainer').append(`
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box1_image">Box 1 Image</label>
                        <input type="file" name="items[0][image]" class="form-control">
                        <select name="items[0][tag_id]" id="box1_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[0][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[0][visibility]" value="1" {{ old('items[0][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 2 Image</label>
                        <input type="file" name="items[1][image]" class="form-control">
                        <select name="items[1][tag_id]" id="box2_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[1][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[1][visibility]" value="1" {{ old('items[1][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>
                `);
                break;

                case 'u_shape_section':
                $('#dynamicFieldsContainer').append(`

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box1_image">Box 1 Image</label>
                        <input type="file" name="items[0][image]" class="form-control">
                        <select name="items[0][tag_id]" id="box1_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[0][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[0][visibility]" value="1" {{ old('items[0][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 2 Image</label>
                        <input type="file" name="items[1][image]" class="form-control">
                        <select name="items[1][tag_id]" id="box2_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[1][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[1][visibility]" value="1" {{ old('items[1][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                 <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 3 Image</label>
                        <input type="file" name="items[2][image]" class="form-control">
                        <select name="items[2][tag_id]" id="box3_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[2][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[2][visibility]" value="1" {{ old('items[2][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                 <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 4 Image</label>
                        <input type="file" name="items[3][image]" class="form-control">
                        <select name="items[3][tag_id]" id="box4_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[3][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[3][visibility]" value="1" {{ old('items[3][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 5 Image</label>
                        <input type="file" name="items[4][image]" class="form-control">
                        <select name="items[4][tag_id]" id="box5_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[4][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[4][visibility]" value="1" {{ old('items[4][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 6 Image</label>
                        <input type="file" name="items[5][image]" class="form-control">
                        <select name="items[5][tag_id]" id="box6_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[5][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[5][visibility]" value="1" {{ old('items[5][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>
                `);
                break;

                case 'fancy_6x_product_grid':
                $('#dynamicFieldsContainer').append(`
                <div class="form-group">
                    <label for="background_image">Background Image</label>
                    <input type="file" name="background_image" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box1_image">Box 1 Image</label>
                        <input type="file" name="items[0][image]" class="form-control">
                        <select name="items[0][tag_id]" id="box1_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[0][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[0][visibility]" value="1" {{ old('items[0][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 2 Image</label>
                        <input type="file" name="items[1][image]" class="form-control">
                        <select name="items[1][tag_id]" id="box2_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[1][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[1][visibility]" value="1" {{ old('items[1][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                 <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 3 Image</label>
                        <input type="file" name="items[2][image]" class="form-control">
                        <select name="items[2][tag_id]" id="box3_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[2][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[2][visibility]" value="1" {{ old('items[2][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                 <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 4 Image</label>
                        <input type="file" name="items[3][image]" class="form-control">
                        <select name="items[3][tag_id]" id="box4_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[3][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[3][visibility]" value="1" {{ old('items[3][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 5 Image</label>
                        <input type="file" name="items[4][image]" class="form-control">
                        <select name="items[4][tag_id]" id="box5_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[4][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[4][visibility]" value="1" {{ old('items[4][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 6 Image</label>
                        <input type="file" name="items[5][image]" class="form-control">
                        <select name="items[5][tag_id]" id="box6_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[5][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[5][visibility]" value="1" {{ old('items[5][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>
                `);
                break;

                case '8x_box_grid':
                $('#dynamicFieldsContainer').append(`

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box1_image">Box 1 Image</label>
                        <input type="file" name="items[0][image]" class="form-control">
                        <textarea name="items[0][bio]" class="form-control" rows="5" placeholder="Bio"  required></textarea>
                        <select name="items[0][tag_id]" id="box1_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[0][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[0][visibility]" value="1" {{ old('items[0][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 2 Image</label>
                        <input type="file" name="items[1][image]" class="form-control">
                        <textarea name="items[1][bio]" class="form-control" rows="5" placeholder="Bio"  required></textarea>
                        <select name="items[1][tag_id]" id="box2_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[1][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[1][visibility]" value="1" {{ old('items[1][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box1_image">Box 1 Image</label>
                        <input type="file" name="items[2][image]" class="form-control">
                        <textarea name="items[2][bio]" class="form-control" rows="5" placeholder="Bio"  required></textarea>
                        <select name="items[2][tag_id]" id="box3_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[2][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[2][visibility]" value="1" {{ old('items[2][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box1_image">Box 1 Image</label>
                        <input type="file" name="items[3][image]" class="form-control">
                        <textarea name="items[3][bio]" class="form-control" rows="5" placeholder="Bio"  required></textarea>
                        <select name="items[3][tag_id]" id="box4_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[3][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[3][visibility]" value="1" {{ old('items[3][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box1_image">Box 1 Image</label>
                        <input type="file" name="items[4][image]" class="form-control">
                        <textarea name="items[4][bio]" class="form-control" rows="5" placeholder="Bio"  required></textarea>
                        <select name="items[4][tag_id]" id="box5_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[4][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[4][visibility]" value="1" {{ old('items[4][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box1_image">Box 1 Image</label>
                        <input type="file" name="items[5][image]" class="form-control">
                        <textarea name="items[5][bio]" class="form-control" rows="5" placeholder="Bio"  required></textarea>
                        <select name="items[5][tag_id]" id="box6_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[5][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[5][visibility]" value="1" {{ old('items[5][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box1_image">Box 1 Image</label>
                        <input type="file" name="items[6][image]" class="form-control">
                        <textarea name="items[6][bio]" class="form-control" rows="5" placeholder="Bio"  required></textarea>
                        <select name="items[6][tag_id]" id="box7_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[6][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[6][visibility]" value="1" {{ old('items[6][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box1_image">Box 1 Image</label>
                        <input type="file" name="items[7][image]" class="form-control">
                        <textarea name="items[7][bio]" class="form-control" rows="5" placeholder="Bio"  required></textarea>
                        <select name="items[7][tag_id]" id="box8_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[7][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[7][visibility]" value="1" {{ old('items[7][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>
                `);
                break;

                case 'problem_specific':
                $('#dynamicFieldsContainer').append(`

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box1_image">Box 1 Image</label>
                        <input type="file" name="items[0][image]" class="form-control">
                        <input type="text" name="items[0][title]" class="form-control" placeholder="Title" required>
                        <textarea name="items[0][bio]" class="form-control" rows="5" placeholder="Bio"  required></textarea>
                        <select name="items[0][tag_id]" id="box1_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[0][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[0][visibility]" value="1" {{ old('items[0][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 2 Image</label>
                        <input type="file" name="items[1][image]" class="form-control">
                        <input type="text" name="items[1][title]" class="form-control" placeholder="Title" required>
                        <textarea name="items[1][bio]" class="form-control" rows="5" placeholder="Bio"  required></textarea>
                        <select name="items[1][tag_id]" id="box2_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[1][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[1][visibility]" value="1" {{ old('items[1][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>
                `);
                break;

                case 'spotlight_deals':
                $('#dynamicFieldsContainer').append(`
                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <textarea name="items[0][bio]" class="form-control" rows="5" placeholder="Bio"  required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="tag_id">Tag ID</label>
                        <select name="items[0][tag_id]" id="tag_id" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                    </div>
                `);
                break;

                case '4x_box_section':
                $('#dynamicFieldsContainer').append(`

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box1_image">Box 1 Image</label>
                        <input type="file" name="items[0][image]" class="form-control">
                        <input type="text" name="items[0][title]" class="form-control" placeholder="Title" required>
                        <select name="items[0][tag_id]" id="box1_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[0][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[0][visibility]" value="1" {{ old('items[0][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 2 Image</label>
                        <input type="file" name="items[1][image]" class="form-control">
                        <input type="text" name="items[1][title]" class="form-control" placeholder="Title" required>
                        <select name="items[1][tag_id]" id="box2_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[1][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[1][visibility]" value="1" {{ old('items[1][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 3 Image</label>
                        <input type="file" name="items[2][image]" class="form-control">
                        <input type="text" name="items[2][title]" class="form-control" placeholder="Title" required>
                        <select name="items[2][tag_id]" id="box3_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[2][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[2][visibility]" value="1" {{ old('items[2][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>

                 <div class="col-md-4">
                    <div class="form-group">
                        <label for="box2_image">Box 4 Image</label>
                        <input type="file" name="items[3][image]" class="form-control">
                        <input type="text" name="items[3][title]" class="form-control" placeholder="Title" required>
                        <select name="items[3][tag_id]" id="box4_tag" class="form-control" required>
                            <option value="">Select Tag</option>
                        </select>
                        <input type="number" name="items[3][index]" class="form-control" placeholder="Index" required>
                        <input type="checkbox" name="items[3][visibility]" value="1" {{ old('items[3][visibility]', isset($item['visibility']) ? $item['visibility'] : 1) ? 'checked' : '' }}> Visibility
                    </div>
                </div>
                `);
                break;


        }

        }

        handleSectionTypeChange($('#section_type').val());
    });
</script>
@endsection
