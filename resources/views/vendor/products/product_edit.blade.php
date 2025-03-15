@extends('vendor.vendor_dashboard')
@section('main')
<div class="page-inner">
    <div class="page-header">
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="{{ route('vendor.dashboard') }}">
                    <i class="icon-home"></i>
                    Dashboard
                </a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="javascript:void(0)">Products</a>
            </li>
        </ul>
    </div>
    <div class="row">


        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Products</h4>
                        <a class="btn btn-primary btn-round ms-auto" href="{{ route('vendor.products.index') }}">
                            <i class="fa fa-list"></i>
                            List View
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form id="myForm" action="{{ route('vendor.products.update', $product->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="category_id">Category <span class="text-danger">*</span> </label>
                                <select name="category_id" id="category_id"
                                    class="form-control @error('category_id') is-invalid @enderror">
                                    <option selected disabled>Select Category</option>
                                    @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ isset($category) && $product->category_id == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tags Selection (Checkboxes) -->
                            <div class="form-group">
                                <label for="tags">Tags</label>
                                <div id="tags-container">
                                    <!-- Tags will be loaded here dynamically -->
                                </div>
                                @error('tags')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>



                            <div class="form-group">
                                <label for="images">Product Existing Images </label>
                                <div class="row">
                                    @foreach ($product->images as $image)
                                    <div class="col-md-2" id="image-container-{{ $image->id }}">
                                        <div class="form-group mb-3" style="position: relative;">
                                            <img class="w-100 rounded" src="{{ asset($image->path) }}" alt="Product Image">


                                            @if($productImageCount < 2)

                                                <a class="btn btn-danger btn-sm"
                                                id="deleteImage-{{ $image->id }}"
                                                href=""
                                                data-bs-toggle="tooltip"
                                                title="Delete Data"
                                                style="position: absolute; right:0; top:0; display: none;">
                                                <span aria-hidden="true">&times;</span>
                                                </a>

                                                <!-- Select Image Button -->
                                                <a class="btn btn-danger btn-sm"
                                                    onclick="checkImageField({{$image->id}})"
                                                    id="selectImageButton"
                                                    style="position: absolute; right:0; top:0;">
                                                    <span aria-hidden="true">&times;</span>
                                                </a>

                                                @else
                                                {{-- If more than 1 image is present, show the delete button --}}
                                                <a class="btn btn-danger btn-sm" style="position: absolute; right:0; top:0"
                                                    href="{{ route('vendor.product-image-delete', $image->id) }}" id="delete"
                                                    data-bs-toggle="tooltip" title="Delete Data">
                                                    <span aria-hidden="true">&times;</span>
                                                </a>
                                                @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="images">Product Images <span class="text-danger">*</span></label>
                                <input type="file" name="images[]" id="images" class="form-control @error('images') is-invalid @enderror" multiple>
                                @error('images')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>



                            <!-- Modal for adding images -->
                            <div class="modal fade" id="addRowModal" tabindex="-1" aria-labelledby="addRowModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <!-- Modal content here (e.g., a form to add images) -->
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="name">Name <span class="text-danger">*</span> </label>
                                <input type="text" name="name" id="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $product->name ?? '') }}">
                                @error('name')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            @foreach ($product->stocks as $info)
                            <h4 class="mt-4">Variant - {{ $loop->index + 1 }}</h4>
                            <div class="variant-sizes">
                                <div class="row size-row">
                                    <div class="col-2">
                                        <label for="size">Size</label>
                                        <input type="text" class="form-control"
                                            name="variantsUpdate[{{ $loop->index }}][sizes][{{ $loop->index }}][size]"
                                            value="{{ $info->size }}" required>
                                        <input type="hidden" name="variantsUpdate[{{ $loop->index }}][sizes][{{ $loop->index }}][id]"
                                            value="{{ $info->id }}">
                                    </div>
                                    <div class="col-2">
                                        <label for="quantity">Quantity</label>
                                        <input type="number" min="1" class="form-control"
                                            name="variantsUpdate[{{ $loop->index }}][sizes][{{ $loop->index }}][quantity]"
                                            value="{{ $info->quantity }}" required>
                                    </div>
                                    <div class="col-2">
                                        <label for="buying_price">Buying Price</label>
                                        <input type="number" min="0" class="form-control"
                                            name="variantsUpdate[{{ $loop->index }}][sizes][{{ $loop->index }}][buying_price]"
                                            value="{{ $info->buying_price }}" required>
                                    </div>
                                    <div class="col-2">
                                        <label for="selling_price">Selling Price</label>
                                        <input type="number" min="0" class="form-control"
                                            name="variantsUpdate[{{ $loop->index }}][sizes][{{ $loop->index }}][selling_price]"
                                            value="{{ $info->selling_price }}" required>
                                    </div>
                                    <div class="col-2">
                                        <label for="discount_price">Discount Price</label>
                                        <input type="number" min="0" class="form-control"
                                            name="variantsUpdate[{{ $loop->index }}][sizes][{{ $loop->index }}][discount_price]"
                                            value="{{ $info->discount_price }}">
                                    </div>
                                    <div class="col-2 mt-2">
                                        <a href="{{ route('vendor.product-stock-delete', $info->id) }}" type="button"
                                            class="btn btn-danger btn-sm mt-3">
                                            <i class="bx bx-minus"></i> Remove Variant
                                        </a>
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="col-md-2">
                                    <div class="form-group mb-3" style="position: relative;">
                                        <img class="w-100 rounded" src="{{ asset($info->photo) }}" alt="Variant Photo">
                        </div>
                </div>
                <div>
                    <label for="photo">Photo</label>
                    <input type="file" class="form-control"
                        name="variantsUpdate[{{ $loop->index }}][photo]">
                </div> --}}
                @endforeach

                <div id="variants">
                    <div class="variant">

                        <div class="variant-sizes">

                        </div>

                    </div>
                </div>
                <button type="button" id="addVariant" class="btn btn-success btn-sm mt-3">
                    <i class="bx bx-plus"></i> Add New Variant</button>

                {{-- <div class="form-group">
                                    <label for="selling_price">Selling Price <span class="text-danger">*</span> </label>
                                    <input type="text" name="selling_price" id="selling_price"
                                        class="form-control @error('selling_price') is-invalid @enderror"
                                        value="{{ old('selling_price', $product->selling_price ?? '') }}">
                @error('selling_price')
                <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="discount_price">Discount Price</label>
                <input type="text" name="discount_price" id="discount_price"
                    class="form-control  @error('discount_price') is-invalid @enderror"
                    value="{{ old('discount_price', $product->discount_price ?? '') }}">
                @error('discount_price')
                <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" name="quantity" id="quantity"
                    class="form-control  @error('quantity') is-invalid @enderror"
                    value="{{ old('quantity', $product->quantity ?? '') }}">
                @error('quantity')
                <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div> --}}

            <div class="form-group">
                <label for="short_description">Short Description <span class="text-danger">*</span>
                </label>
                <textarea name="short_description" id="short_description"
                    class="form-control @error('short_description') is-invalid @enderror">{{ old('short_description', $product->short_description ?? '') }}</textarea>
                @error('short_description')
                <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="long_description">Long Description <span class="text-danger">*</span>
                </label>
                <textarea name="long_description" id="long_description"
                    class="form-control mytextarea @error('long_description') is-invalid @enderror">{{ old('long_description', $product->long_description ?? '') }}</textarea>
                @error('long_description')
                <div class="alert alert-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="long_description">Status <span class="text-danger">*</span></label>

                <input type="radio" class="btn-check" name="status" id="btn-check-outlined" value="active"
                    @if($product->status == 'active') checked @endif autocomplete="off">
                <label class="btn btn-outline-primary" for="btn-check-outlined">Active</label>

                <input type="radio" class="btn-check" name="status" id="btn-check-2-outlined" value="inactive"
                    @if($product->status == 'inactive') checked @endif autocomplete="off">
                <label class="btn btn-outline-danger" for="btn-check-2-outlined">Inactive</label>
            </div>

        </div>

        <button type="submit" class="btn btn-success">Update Product</button>
        </form>


    </div>
</div>
</div>
</div>
</div>

<div class="modal fade" id="addRowModal" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">

                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <span style="color: red; font-seze:20px"> Please first Select at least One Image</span>


                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Close</button>

            </div>

        </div>
    </div>
</div>

@endsection

@push('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Load tags based on selected category
        $('#category_id').change(function() {
            let categoryId = $(this).val();
            $('#tags-container').empty(); // Clear previous tags

            if (categoryId) {
                $.ajax({
                    url: "{{ url('/vendor/get-tags') }}",
                    type: 'GET',
                    data: {
                        category_id: categoryId
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log("Tags received:", response);

                        let tags = response.data || response;
                        $('#tags-container').empty();

                        if (Array.isArray(tags) && tags.length > 0) {
                            tags.forEach(tag => {
                                // Get existing tags from the product and check if the current tag is selected
                                let existingTags = @json($product->tags ?? []); // Decode the JSON tags column
                                let isChecked = existingTags.includes(tag.id) ? 'checked' : '';

                                // Create checkboxes for each tag
                                $('#tags-container').append(`
                                    <div class="form-check">
                                        <input type="checkbox" name="tags[]" class="form-check-input" value="${tag.id}" id="tag_${tag.id}" ${isChecked}>
                                        <label class="form-check-label" for="tag_${tag.id}">${tag.name}</label>
                                    </div>
                                `);
                            });
                        } else {
                            $('#tags-container').html('<div>No tags available</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching tags:", xhr.responseText);
                        alert("Failed to load tags. Please try again.");
                    }
                });
            } else {
                $('#tags-container').html('<div>Select a category first</div>');
            }
        });

        // Trigger category change on page load to load tags if the product is being edited
        $('#category_id').trigger('change');
    });
</script>
<script>
    $(document).ready(function() {
        $('#myForm').validate({
            rules: {
                category_id: {
                    required: true,
                },
                name: {
                    required: true,
                },
                long_description: {
                    required: true,
                },
            },
            messages: {
                category_id: {
                    required: 'Please Select Category',
                },
                name: {
                    required: 'Please Enter Name',
                },
                long_description: {
                    required: 'Please Enter long description',
                },
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            },
        });
    });
</script>


<script>
    $(document).ready(function() {
        $('#myForm').validate({
            rules: {
                // 'photos[]': {
                //     required: true,
                // },
                product_name: {
                    required: true,
                },
                product_code: {
                    required: true,
                },
                product_category_id: {
                    required: true,
                },
                'selling_price[]': {
                    required: true,
                },
                product_quantity: {
                    required: true,
                },

            },
            messages: {
                // 'photos[]': {
                //     required: 'Please Choose Product Photos',
                // },
                product_name: {
                    required: 'Please Enter Product Name',
                },

                product_category_id: {
                    required: 'Please Select Product Category',
                },
                'selling_price[]': {
                    required: 'Please Enter Selling Price',
                },
                product_quantity: {
                    required: 'Please Enter quantity',
                },

            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            },
        });
    });
</script>


<script>
    let variantCount = 0;

    document.getElementById('addVariant').addEventListener('click', function() {
        const variantsDiv = document.getElementById('variants');
        const newVariant = document.createElement('div');
        newVariant.classList.add('variant');
        newVariant.innerHTML = `
    <h4 class="mt-4">
        <span class="text-danger remove-variant" onclick="removeVariant(this)" style="cursor: pointer;">
       <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="m16.192 6.344l-4.243 4.242l-4.242-4.242l-1.414 1.414L10.535 12l-4.242 4.242l1.414 1.414l4.242-4.242l4.243 4.242l1.414-1.414L13.364 12l4.242-4.242z"/></svg>
        </span>
       New Variant - ${variantCount + 1}
    </h4>
    <div class="variant-sizes">
        <div class="row size-row">
            <div class="col-2">
                <label for="size">Size</label>
                <input type="text" class="form-control" name="variants[${variantCount}][sizes][0][size]" required>
            </div>
            <div class="col-2">
                <label for="quantity">Quantity</label>
                <input type="number" min="1" class="form-control" name="variants[${variantCount}][sizes][0][quantity]" required>
            </div>
            <div class="col-2">
                <label for="buying_price">Buying Price</label>
                <input type="number" min="1" class="form-control" name="variants[${variantCount}][sizes][0][buying_price]" required>
            </div>
            <div class="col-2">
                <label for="selling_price">Selling Price</label>
                <input type="number" min="1" class="form-control" name="variants[${variantCount}][sizes][0][selling_price]" required>
            </div>
            <div class="col-2">
                <label for="discount_price">Discount Price</label>
                <input type="number" min="1" class="form-control" name="variants[${variantCount}][sizes][0][discount_price]">
            </div>
            <div class="col-2 fa-2x mt-3">
                <span class="text-danger remove-size" onclick="removeSize(this)" style="cursor: pointer;">
                    <i class="bx bx-x"></i>
                </span>
            </div>
        </div>
    </div>



`;
        variantsDiv.appendChild(newVariant);

        // Hide the first "remove-size" button
        const firstRemoveSizeBtn = newVariant.querySelector('.remove-size');
        firstRemoveSizeBtn.style.display = 'none';

        variantCount++;
    });

    // Function to remove a variant
    function removeVariant(button) {
        const variantDiv = button.closest('.variant');
        variantDiv.remove();
    }

    // Function to remove a size
    function removeSize(button) {
        const sizeRow = button.closest('.size-row');
        sizeRow.remove();
    }

    // Event delegation for dynamically added "Add More Size" buttons
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('add-size-btn')) {
            const variantDiv = event.target.closest('.variant');
            const sizeRows = variantDiv.querySelectorAll('.size-row');
            const newSizeRow = sizeRows[0].cloneNode(true);

            // Reset input values in the cloned size row
            newSizeRow.querySelectorAll('input').forEach(input => input.value = '');

            const lastSizeIndex = sizeRows.length;
            newSizeRow.querySelectorAll('input').forEach((input, index) => {
                const name = input.name.replace(/\[sizes\]\[\d+\]/, `[sizes][${lastSizeIndex}]`);
                input.name = name;
            });

            variantDiv.querySelector('.variant-sizes').appendChild(newSizeRow);

            // Show the "remove-size" button for the new size row
            newSizeRow.querySelector('.remove-size').style.display = 'inline';
        }
    });

    function checkImageField(imageId) {
        const imageInput = document.getElementById('images');
        const deleteButton = document.getElementById('deleteImage-' + imageId);
        const selectImageButton = document.getElementById('selectImageButton');


        if (imageInput.files.length === 0) {
            alert('Please select at least one image.');

            selectImageButton.style.display = 'inline-block';

            deleteButton.style.display = 'none';
            return false;
        } else {


            selectImageButton.style.display = 'none';
            deleteButton.style.display = 'inline-block';
            return true;
        }
    }

    document.getElementById('images').addEventListener('change', function() {
        checkImageField({
            {
                $image->id;
            }
        });
    });
</script>

<style>
    #tags-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
    }

    .tag {
        display: flex;
        align-items: center;
        cursor: pointer;
    }

    .tag input {
        margin: 0;
    }
</style>

@endpush
