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
                        <a class="btn btn-primary btn-round ms-auto" href="{{ route('admin.products.index') }}">
                            <i class="fa fa-list"></i>
                            List View
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form id="myForm" action="{{ route('admin.products.update', $product->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="category_id">Category:</label>
                                <p class="form-control-static">{{ $product->category->name }}</p>
                            </div>

                            @php
                            $tagIds = json_decode($product->tags, true); // Decode stored JSON (IDs)
                            $tags = \App\Models\Category::whereIn('id', $tagIds)->pluck('name')->toArray(); // Fetch names
                            @endphp

                            <div class="form-group">
                                <label for="tags">Tags:</label>
                                <p class="form-control-static">
                                    @if(!empty($tags))
                                    {{ implode(', ', $tags) }}
                                    @else
                                    No tags available
                                    @endif
                                </p>
                            </div>

                            <div class="form-group">
                                <label>Product Existing Images:</label>
                                <div class="row">
                                    @foreach ($product->images as $image)
                                    <div class="col-md-2">
                                        <img class="w-100 rounded" src="{{ asset($image->path) }}" alt="Product Image">
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="name">Product Name:</label>
                                <input type="text" class="form-control" value="{{ $product->name }}" readonly>
                            </div>

                            @foreach ($product->stocks as $info)
                            <h4 class="mt-4">Variant - {{ $loop->index + 1 }}</h4>
                            <div class="variant-sizes">
                                <div class="row size-row">
                                    <div class="col-2">
                                        <label for="size">Size</label>
                                        <input type="text" class="form-control" value="{{ $info->size }}" readonly>
                                    </div>
                                    <div class="col-2">
                                        <label for="quantity">Quantity</label>
                                        <input type="number" class="form-control" value="{{ $info->quantity }}" readonly>
                                    </div>
                                    <div class="col-2">
                                        <label for="selling_price">Selling Price</label>
                                        <input type="number" class="form-control" value="{{ $info->selling_price }}" readonly>
                                    </div>
                                    <div class="col-3">
                                        <label for="discount_price">Discount Price</label>
                                        <input type="number" class="form-control" value="{{ $info->discount_price }}" readonly>
                                    </div>
                                </div>
                            </div>
                            @endforeach

                            <div class="form-group">
                                <label for="short_description">Short Description:</label>
                                <textarea class="form-control" readonly>{{ $product->short_description }}</textarea>
                            </div>

                            <div class="form-group">
                                <label for="long_description">Long Description:</label>
                                <textarea class="form-control" readonly>{{ $product->long_description }}</textarea>
                            </div>

                            <div class="form-group">
                                <label for="long_description">Admin approval <span class="text-danger">*</span></label>

                                <input type="radio" class="btn-check" name="admin_approval" id="btn-check-outlined" value="approved"
                                    @if($product->admin_approval == 'approved') checked @endif autocomplete="off">
                                <label class="btn btn-outline-primary" for="btn-check-outlined">Approved</label>

                                <input type="radio" class="btn-check" name="admin_approval" id="btn-check-2-outlined" value="suspended"
                                    @if($product->admin_approval == 'suspended') checked @endif autocomplete="off">
                                <label class="btn btn-outline-danger" for="btn-check-2-outlined">Suspended</label>
                            </div>

                        </div>

                        <button type="submit" class="btn btn-success">Update Admin Approval</button>
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
