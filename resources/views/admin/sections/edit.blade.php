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
                <a href="javascript:void(0)">Edit Section</a>
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
                <div class="card-header"><h4 class="card-title">Edit Section</h4></div>
                <div class="card-body col-md-12">
                    <form action="{{ route('admin.sections.update', $section->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Left Column -->
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
                                            <option value="{{ $type }}" {{ $section->section_type == $type ? 'selected' : '' }}>
                                                {{ ucwords(str_replace('_', ' ', $type)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="section_name">Section Name</label>
                                    <input type="text" name="section_name" class="form-control" value="{{ old('section_name', $section->section_name) }}" required>
                                </div>

                                <div class="form-group">
                                    <label for="section_title">Section Title</label>
                                    <input type="text" name="section_title" class="form-control" value="{{ old('section_title', $section->section_title) }}" required>
                                </div>

                                <div class="form-group">
                                    <label for="category">Category</label>
                                    <select name="category_id" id="category" class="form-control" required>
                                        <option value="">Select Category</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ $section->category_id == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="section_index">Section Index</label>
                                    <input type="number" name="index" class="form-control" value="{{ old('index', $section->index) }}" required>
                                </div>

                                <div class="form-group">
                                    <label for="visibility">Visibility</label><br>
                                    <input type="checkbox" name="visibility" value="1" {{ $section->visibility ? 'checked' : '' }}> Show Section
                                </div>
                                
                               
                                <button type="submit" class="btn btn-success mt-3">Update Section</button>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-8">
                                <div class="form-group">
                                     @if ($section->section_type == "multiproduct_banner" || $section->section_type == "lighting_deals" || $section->section_type == "smaller_4x_box_grid")
                                    <div class="form-group mt-3">
                                        @if ($section->background_image)
                                            <img src="{{ asset($section->background_image) }}" class="img-thumbnail mb-2" width="100">
                                        @endif
                                        <div>
                                            <label for="background_image">Update Background Image</label><br>
                                            <input type="file" name="background_image" class="form-control">
                                        </div>
                                    </div>
                                    @endif

                                    @if ($section->section_type == "single_banner")
                                        <div class="form-group mt-3">
                                            @if ($section->banner_image)
                                                <img src="{{ asset($section->banner_image) }}" class="img-thumbnail mb-2" width="100">
                                            @endif
                                        <div>
                                            <label for="banner_image">Update Banner Image</label><br>
                                            <input type="file" name="banner_image" class="form-control">
                                        </div>
                                        </div>
                                    @endif
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

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Dynamic Field Loader -->
<script>
    const existingItems = @json($section->items ?? []);
    const existingSectionType = '{{ $section->section_type }}';
    const selectedCategoryId = '{{ $section->category_id }}';

    $(document).ready(function () {
        // Load section type fields on load
        handleSectionTypeChange(existingSectionType);

        // Load tags after loading section type
        setTimeout(() => {
            if (selectedCategoryId) {
                $('#category').val(selectedCategoryId).trigger('change');
            }
        }, 300);

        $('#section_type').change(function () {
            handleSectionTypeChange($(this).val());
        });

        $('#category').change(function () {
            const categoryId = $(this).val();
            if (!categoryId) return;

            $.get(`/categories/${categoryId}/tags`, function (data) {
                const tagOptions = data.status ? data.tags.map(tag =>
                    `<option value="${tag.id}">${tag.name}</option>`).join('') :
                    `<option value="">No tags found</option>`;

                ['tag_id', 'box1_tag', 'box2_tag', 'box3_tag', 'box4_tag', 'box5_tag', 'box6_tag', 'box7_tag', 'box8_tag'].forEach(id => {
                    if ($('#' + id).length) {
                        $('#' + id).html('<option value="">Select Tag</option>' + tagOptions);
                    }
                });

                // After tags loaded, set selected tags
                existingItems.forEach((item, i) => {
                    $(`[name="items[${i}][tag_id]"]`).val(item.tag_id);
                });
            });
        });
    });

    function handleSectionTypeChange(type) {
        $('#dynamicFieldsContainer').empty();

        switch (type) {
            case 'multiproduct_banner':
                for (let i = 0; i < existingItems.length; i++) {
                    const item = existingItems[i] || {};
                    const imagePath = item.image ? `/${item.image}` : '';
                    const imagePreview = item.image
                        ? `<img src="${imagePath}" class="img-thumbnail mb-2" width="50">`
                        : '';

                    $('#dynamicFieldsContainer').append(`
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Box ${i + 1} Image</label>
                                ${imagePreview}
                                <input type="file" name="items[${i}][image]" class="form-control">
                                <select name="items[${i}][tag_id]" id="box${i + 1}_tag" class="form-control" required>
                                    <option value="">Select Tag</option>
                                </select>
                                <input type="number" name="items[${i}][index]" class="form-control mt-1" placeholder="Index" value="${item.index || ''}" required>
                                <input type="checkbox" name="items[${i}][visibility]" value="1" ${item.visibility ? 'checked' : ''}> Visibility
                            </div>
                        </div>
                    `);
                }
                break;

            case 'single_banner':
                $('#dynamicFieldsContainer').append(`
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tag</label>
                            <select name="items[0][tag_id]" id="tag_id" class="form-control mt-1" required>
                                <option value="">Select Tag</option>
                            </select>
                        </div>
                    </div>
                `);
                break;

            case 'scrollable_product':
                $('#dynamicFieldsContainer').append(`
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Bio</label>
                            <textarea name="items[0][bio]" class="form-control" rows="4"></textarea>
                            <label>Tag</label>
                            <select name="items[0][tag_id]" id="tag_id" class="form-control mt-1" required>
                                <option value="">Select Tag</option>
                            </select>
                        </div>
                    </div>
                `);
                break;

            case 'lighting_deals':
                $('#dynamicFieldsContainer').append(`
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tag ID</label>
                            <select name="items[0][tag_id]" id="tag_id" class="form-control" required>
                                <option value="">Select Tag</option>
                            </select>
                        </div>
                    </div>
                `);
                break;

            case 'tag_box':
                for (let i = 0; i < existingItems.length; i++) {
                    const item = existingItems[i] || {};
                    const imagePath = item.image ? `/${item.image}` : '';
                    const imagePreview = item.image
                        ? `<img src="${imagePath}" class="img-thumbnail mb-2" width="50">`
                        : '';
                    $('#dynamicFieldsContainer').append(`
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Box ${i + 1} Image</label>
                                ${imagePreview}
                                <input type="file" name="items[${i}][image]" class="form-control">
                                <textarea name="items[${i}][bio]" class="form-control" rows="5" placeholder="Bio"  required></textarea>
                                <select name="items[${i}][tag_id]" id="box${i + 1}_tag" class="form-control" required>
                                    <option value="">Select Tag</option>
                                </select>
                                <input type="number" name="items[${i}][index]" class="form-control mt-1" placeholder="Index" required>
                                <input type="checkbox" name="items[${i}][visibility]" value="1" checked> Visibility
                            </div>
                        </div>
                    `);
                }
                break;

            case 'fancy_3x_box_grid':
                for (let i = 0; i < existingItems.length; i++) {
                    const item = existingItems[i] || {};
                    const imagePath = item.image ? `/${item.image}` : '';
                    const imagePreview = item.image
                        ? `<img src="${imagePath}" class="img-thumbnail mb-2" width="50">`
                        : '';
                    $('#dynamicFieldsContainer').append(`
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Box ${i + 1} Image</label>
                                ${imagePreview}
                                <input type="file" name="items[${i}][image]" class="form-control">
                                <select name="items[${i}][tag_id]" id="box${i + 1}_tag" class="form-control" required>
                                    <option value="">Select Tag</option>
                                </select>
                                <input type="number" name="items[${i}][index]" class="form-control mt-1" placeholder="Index" required>
                                <input type="checkbox" name="items[${i}][visibility]" value="1" checked> Visibility
                            </div>
                        </div>
                    `);
                }
                break;

            case 'poster_section':
                for (let i = 0; i < existingItems.length; i++) {
                    const item = existingItems[i] || {};
                    const imagePath = item.image ? `/${item.image}` : '';
                    const imagePreview = item.image
                        ? `<img src="${imagePath}" class="img-thumbnail mb-2" width="50">`
                        : '';
                    $('#dynamicFieldsContainer').append(`
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Box ${i + 1} Image</label>
                                ${imagePreview}
                                <input type="file" name="items[${i}][image]" class="form-control">
                                <select name="items[${i}][tag_id]" id="box${i + 1}_tag" class="form-control" required>
                                    <option value="">Select Tag</option>
                                </select>
                                <input type="number" name="items[${i}][index]" class="form-control mt-1" placeholder="Index" required>
                                <input type="checkbox" name="items[${i}][visibility]" value="1" checked> Visibility
                            </div>
                        </div>
                    `);
                }
                break;

            case 'scrollable_banners':
                for (let i = 0; i < existingItems.length; i++) {
                    const item = existingItems[i] || {};
                    const imagePath = item.image ? `/${item.image}` : '';
                    const imagePreview = item.image
                        ? `<img src="${imagePath}" class="img-thumbnail mb-2" width="50">`
                        : '';
                    $('#dynamicFieldsContainer').append(`
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Box ${i + 1} Image</label>
                                ${imagePreview}
                                <input type="file" name="items[${i}][image]" class="form-control">
                                <select name="items[${i}][tag_id]" id="box${i + 1}_tag" class="form-control" required>
                                    <option value="">Select Tag</option>
                                </select>
                                <input type="number" name="items[${i}][index]" class="form-control mt-1" placeholder="Index" required>
                                <input type="checkbox" name="items[${i}][visibility]" value="1" checked> Visibility
                            </div>
                        </div>
                    `);
                }
                break;

            case 'smaller_4x_box_grid':
                for (let i = 0; i < existingItems.length; i++) {
                    const item = existingItems[i] || {};
                    const imagePath = item.image ? `/${item.image}` : '';
                    const imagePreview = item.image
                        ? `<img src="${imagePath}" class="img-thumbnail mb-2" width="50">`
                        : '';
                    $('#dynamicFieldsContainer').append(`
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Box ${i + 1} Image</label>
                                ${imagePreview}
                                <input type="file" name="items[${i}][image]" class="form-control">
                                <select name="items[${i}][tag_id]" id="box${i + 1}_tag" class="form-control" required>
                                    <option value="">Select Tag</option>
                                </select>
                                <input type="number" name="items[${i}][index]" class="form-control mt-1" placeholder="Index" required>
                                <input type="checkbox" name="items[${i}][visibility]" value="1" checked> Visibility
                            </div>
                        </div>
                    `);
                }
                break;

            case 'best_brands':
                for (let i = 0; i < existingItems.length; i++) {
                    const item = existingItems[i] || {};
                    const imagePath = item.image ? `/${item.image}` : '';
                    const imagePreview = item.image
                        ? `<img src="${imagePath}" class="img-thumbnail mb-2" width="50">`
                        : '';
                    $('#dynamicFieldsContainer').append(`
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Box ${i + 1} Image</label>
                                ${imagePreview}
                                <input type="file" name="items[${i}][image]" class="form-control">
                                <select name="items[${i}][tag_id]" id="box${i + 1}_tag" class="form-control" required>
                                    <option value="">Select Tag</option>
                                </select>
                                <input type="number" name="items[${i}][index]" class="form-control mt-1" placeholder="Index" required>
                                <input type="checkbox" name="items[${i}][visibility]" value="1" checked> Visibility
                            </div>
                        </div>
                    `);
                }
                break;

            case '6x_box_grid':
                for (let i = 0; i < existingItems.length; i++) {
                    const item = existingItems[i] || {};
                    const imagePath = item.image ? `/${item.image}` : '';
                    const imagePreview = item.image
                        ? `<img src="${imagePath}" class="img-thumbnail mb-2" width="50">`
                        : '';
                    $('#dynamicFieldsContainer').append(`
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Box ${i + 1} Image</label>
                                ${imagePreview}
                                <input type="file" name="items[${i}][image]" class="form-control">
                                <select name="items[${i}][tag_id]" id="box${i + 1}_tag" class="form-control" required>
                                    <option value="">Select Tag</option>
                                </select>
                                <input type="number" name="items[${i}][index]" class="form-control mt-1" placeholder="Index" required>
                                <input type="checkbox" name="items[${i}][visibility]" value="1" checked> Visibility
                            </div>
                        </div>
                    `);
                }
                break;

            case '2x_box_grid':
                for (let i = 0; i < existingItems.length; i++) {
                    const item = existingItems[i] || {};
                    const imagePath = item.image ? `/${item.image}` : '';
                    const imagePreview = item.image
                        ? `<img src="${imagePath}" class="img-thumbnail mb-2" width="50">`
                        : '';
                    $('#dynamicFieldsContainer').append(`
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Box ${i + 1} Image</label>
                                ${imagePreview}
                                <input type="file" name="items[${i}][image]" class="form-control">
                                <select name="items[${i}][tag_id]" id="box${i + 1}_tag" class="form-control" required>
                                    <option value="">Select Tag</option>
                                </select>
                                <input type="number" name="items[${i}][index]" class="form-control mt-1" placeholder="Index" required>
                                <input type="checkbox" name="items[${i}][visibility]" value="1" checked> Visibility
                            </div>
                        </div>
                    `);
                }
                break;

            case 'u_shape_section':
                for (let i = 0; i < existingItems.length; i++) {
                    const item = existingItems[i] || {};
                    const imagePath = item.image ? `/${item.image}` : '';
                    const imagePreview = item.image
                        ? `<img src="${imagePath}" class="img-thumbnail mb-2" width="50">`
                        : '';
                    $('#dynamicFieldsContainer').append(`
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Box ${i + 1} Image</label>
                                ${imagePreview}
                                <input type="file" name="items[${i}][image]" class="form-control">
                                <select name="items[${i}][tag_id]" id="box${i + 1}_tag" class="form-control" required>
                                    <option value="">Select Tag</option>
                                </select>
                                <input type="number" name="items[${i}][index]" class="form-control mt-1" placeholder="Index" required>
                                <input type="checkbox" name="items[${i}][visibility]" value="1" checked> Visibility
                            </div>
                        </div>
                    `);
                }
                break;

            case 'fancy_6x_product_grid':
                for (let i = 0; i < existingItems.length; i++) {
                    const item = existingItems[i] || {};
                    const imagePath = item.image ? `/${item.image}` : '';
                    const imagePreview = item.image
                        ? `<img src="${imagePath}" class="img-thumbnail mb-2" width="50">`
                        : '';
                    $('#dynamicFieldsContainer').append(`
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Box ${i + 1} Image</label>
                                ${imagePreview}
                                <input type="file" name="items[${i}][image]" class="form-control">
                                <select name="items[${i}][tag_id]" id="box${i + 1}_tag" class="form-control" required>
                                    <option value="">Select Tag</option>
                                </select>
                                <input type="number" name="items[${i}][index]" class="form-control mt-1" placeholder="Index" required>
                                <input type="checkbox" name="items[${i}][visibility]" value="1" checked> Visibility
                            </div>
                        </div>
                    `);
                }
                break;

            case '8x_box_grid':
                for (let i = 0; i < existingItems.length; i++) {
                    const item = existingItems[i] || {};
                    const imagePath = item.image ? `/${item.image}` : '';
                    const imagePreview = item.image
                        ? `<img src="${imagePath}" class="img-thumbnail mb-2" width="50">`
                        : '';
                    $('#dynamicFieldsContainer').append(`
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Box ${i + 1} Image</label>
                                ${imagePreview}
                                <input type="file" name="items[${i}][image]" class="form-control">
                                <textarea name="items[${i}][bio]" class="form-control" rows="4"></textarea>
                                <select name="items[${i}][tag_id]" id="box${i + 1}_tag" class="form-control" required>
                                    <option value="">Select Tag</option>
                                </select>
                                <input type="number" name="items[${i}][index]" class="form-control mt-1" placeholder="Index" required>
                                <input type="checkbox" name="items[${i}][visibility]" value="1" checked> Visibility
                            </div>
                        </div>
                    `);
                }
                break;

            case 'problem_specific':
                for (let i = 0; i < existingItems.length; i++) {
                    const item = existingItems[i] || {};
                    const imagePath = item.image ? `/${item.image}` : '';
                    const imagePreview = item.image
                        ? `<img src="${imagePath}" class="img-thumbnail mb-2" width="50">`
                        : '';
                    $('#dynamicFieldsContainer').append(`
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Box ${i + 1} Image</label>
                                ${imagePreview}
                                <input type="file" name="items[${i}][image]" class="form-control">
                                <input type="text" name="items[${i}][title]" class="form-control" required>
                                <textarea name="items[${i}][bio]" class="form-control" rows="4"></textarea>
                                <select name="items[${i}][tag_id]" id="box${i + 1}_tag" class="form-control" required>
                                    <option value="">Select Tag</option>
                                </select>
                                <input type="number" name="items[${i}][index]" class="form-control mt-1" placeholder="Index" required>
                                <input type="checkbox" name="items[${i}][visibility]" value="1" checked> Visibility
                            </div>
                        </div>
                    `);
                }
                break;

            case '4x_box_section':
                for (let i = 0; i < existingItems.length; i++) {
                    const item = existingItems[i] || {};
                    const imagePath = item.image ? `/${item.image}` : '';
                    const imagePreview = item.image
                        ? `<img src="${imagePath}" class="img-thumbnail mb-2" width="50">`
                        : '';
                    $('#dynamicFieldsContainer').append(`
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Box ${i + 1} Image</label>
                                ${imagePreview}
                                <input type="file" name="items[${i}][image]" class="form-control">
                                <input type="text" name="items[${i}][title]" class="form-control" required>
                                <select name="items[${i}][tag_id]" id="box${i + 1}_tag" class="form-control" required>
                                    <option value="">Select Tag</option>
                                </select>
                                <input type="number" name="items[${i}][index]" class="form-control mt-1" placeholder="Index" required>
                                <input type="checkbox" name="items[${i}][visibility]" value="1" checked> Visibility
                            </div>
                        </div>
                    `);
                }
                break;

            case 'spotlight_deals':
                $('#dynamicFieldsContainer').append(`
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Bio</label>
                            <textarea name="items[0][bio]" class="form-control" rows="4"></textarea>
                            <label>Tag</label>
                            <select name="items[0][tag_id]" id="tag_id" class="form-control mt-1" required>
                                <option value="">Select Tag</option>
                            </select>
                        </div>
                    </div>
                `);
                break;



        }

        // Fill existing values
        existingItems.forEach((item, i) => {
            if (item.index) $(`[name="items[${i}][index]"]`).val(item.index);
            if (item.bio) $(`[name="items[${i}][bio]"]`).val(item.bio);
            if (item.title) $(`[name="items[${i}][title]"]`).val(item.title);
            if (item.visibility != null) $(`[name="items[${i}][visibility]"]`).prop('checked', item.visibility == 1);
        });
    }
</script>
@endsection
