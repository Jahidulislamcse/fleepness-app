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
                        <button class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal"
                            data-bs-target="#addRowModal">
                            <i class="fa fa-plus"></i>
                            Add New
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Modal -->
                    <div class="modal fade" id="addRowModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">Add New</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form id="myForm" action="{{ route('vendor.products.store') }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="category_id">Category <span class="text-danger">*</span>
                                                    </label>
                                                    <select name="category_id" id="category_id"
                                                        class="form-control @error('category_id') is-invalid @enderror">
                                                        <option selected disabled>Select Category</option>
                                                        @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}"
                                                            {{ isset($product) && $product->category_id == $category->id ? 'selected' : '' }}>
                                                            {{ $category->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    @error('category_id')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">

                                                <div class="form-group">
                                                    <label for="tags">Tags</label>
                                                    <div id="tags-container">
                                                        <!-- Tags will be loaded here dynamically -->
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="name">Name <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" name="name" id="name"
                                                        class="form-control @error('name') is-invalid @enderror"
                                                        value="{{ old('name', $product->name ?? '') }}" required>
                                                    @error('name')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>


                                                <div class="form-group">
                                                    <label for="images">Product Main Images <span
                                                            class="text-danger">*</span> </label>
                                                    <input type="file" name="images[]" id="images"
                                                        class="form-control @error('images') is-invalid @enderror"
                                                        multiple required>
                                                    @error('images')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                            <div id="variants">
                                                <div class="variant">
                                                    <h4 class="mt-4">Variant - 1</h4>
                                                    <div class="variant-sizes">
                                                        <div class="row size-row">
                                                            <div class="col-2">
                                                                <label for="size">Size</label>
                                                                <input type="text" class="form-control" name="variants[0][sizes][0][size]"
                                                                    required>
                                                            </div>
                                                            <div class="col-2">
                                                                <label for="quantity">quantity</label>
                                                                <input type="number" min="1" class="form-control"
                                                                    name="variants[0][sizes][0][quantity]" required>
                                                            </div>
                                                            <div class="col-2">
                                                                <label for="buying_price">Buying Price</label>
                                                                <input type="number" min="1" class="form-control"
                                                                    name="variants[0][sizes][0][buying_price]" required>
                                                            </div>
                                                            <div class="col-2">
                                                                <label for="selling_price">Selling Price</label>
                                                                <input type="number" min="1" class="form-control"
                                                                    name="variants[0][sizes][0][selling_price]" required>
                                                            </div>
                                                            <div class="col-2">
                                                                <label for="discount_price">Discount Price</label>
                                                                <input type="number" min="1" class="form-control"
                                                                    name="variants[0][sizes][0][discount_price]">
                                                            </div>
                                                            <div class="col-2 fa-2x mt-3">
                                                                <span class="text-danger remove-size" onclick="removeSize(this)"
                                                                    style="cursor: pointer; display: none;"> <i class="bx bx-x"></i></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" id="addVariant" class="btn btn-success btn-sm mt-3">
                                                <i class="bx bx-plus"></i> Add Variant</button>

                                            <div class="form-group">
                                                <label for="short_description">Short Description <span
                                                        class="text-danger">*</span> </label>
                                                <textarea name="short_description" id="short_description"
                                                    class="form-control @error('short_description') is-invalid @enderror" required>{{ old('short_description', $product->short_description ?? '') }}</textarea>
                                                @error('short_description')
                                                <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="long_description">Long Description </label>
                                                <textarea name="long_description" id="long_description"
                                                    class="form-control mytextarea @error('long_description') is-invalid @enderror">{{ old('long_description', $product->long_description ?? '') }}</textarea>
                                                @error('long_description')
                                                <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div>

                                        </div>
                                    </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save changes</button>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="basic-datatables" class="display table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>SN</th>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Status</th>

                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $key => $product)
                            @php
                            $countQty = App\Models\Stock::where('product_id',$product->id)->sum('quantity');
                            @endphp
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    @if ($product->images->first())
                                    <img src="{{ asset($product->images->first()->path) }}"
                                        alt="{{ $product->images->first()->alt_text }}" width="50">
                                    @else
                                    <img src="{{ asset('default-image.png') }}" alt="No Image"
                                        width="50">
                                    @endif
                                </td>

                                <td>{{ $product->name }}</td>
                                <td>{{ optional($product->category)->name ?? 'No Category' }}</td>
                                <td>{{ $product->selling_price }}</td>
                                <td>{{ $countQty }}</td>
                                <td>{{ $product->status }}</td>
                                <td style="display: flex;gap:5px ">
                                    <a href="{{ route('vendor.products.edit', $product->id) }}"
                                        class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('vendor.products.destroy', $product->id) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>

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
                                // Create checkboxes for each tag
                                $('#tags-container').append(`
                                    <div class="form-check">
                                        <input type="checkbox" name="tags[]" class="form-check-input" value="${tag.id}" id="tag_${tag.id}">
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
    let variantCount = 1;

    document.getElementById('addVariant').addEventListener('click', function() {
        const variantsDiv = document.getElementById('variants');
        const newVariant = document.createElement('div');
        newVariant.classList.add('variant');
        newVariant.innerHTML = `
    <h4 class="mt-4">
        <span class="text-danger remove-variant" onclick="removeVariant(this)" style="cursor: pointer;">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="m16.192 6.344l-4.243 4.242l-4.242-4.242l-1.414 1.414L10.535 12l-4.242 4.242l1.414 1.414l4.242-4.242l4.243 4.242l1.414-1.414L13.364 12l4.242-4.242z"/></svg>
        </span>
        Variant - ${variantCount + 1}
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
