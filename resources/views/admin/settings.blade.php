@extends('admin.admin_dashboard')
@section('main')
<div class="page-inner">
    <div class="page-header">
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="icon-home"></i>
                    Dashbard
                </a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="javascript:void(0)">Settings</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Settings</h4>
                    </div>
                </div>
                <div class="card-body">
                    <hr />
                    <form id="myForm" action="{{ route('admin.settings.update') }}" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        {{-- Start logo --}}
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Logo</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <input type="file" name="logo" class="form-control"
                                    onchange="document.getElementById('logo').src = window.URL.createObjectURL(this.files[0])" />
                                @error('logo')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <img id="logo" src="" alt="logo"
                                    style="width: 100px;">
                            </div>
                        </div>
                        {{-- End logo --}}

                        {{-- Start Footer logo --}}
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Footer Logo</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <input type="file" name="footer_logo" class="form-control"
                                    onchange="document.getElementById('footer_logo').src = window.URL.createObjectURL(this.files[0])" />
                                @error('footer_logo')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <img id="footer_logo" src="" alt="footer_logo"
                                    style="width: 100px;">
                            </div>
                        </div>
                        {{-- End Footer logo --}}

                        {{-- Start favicon --}}
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Favicon</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <input type="file" name="favicon" class="form-control"
                                    onchange="document.getElementById('favicon').src = window.URL.createObjectURL(this.files[0])" />
                                @error('favicon')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <img id="favicon" src="" alt="favicon"
                                    style="width: 100px;">
                            </div>
                        </div>
                        {{-- End favicon --}}

                        {{-- Start Footer Bg --}}
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Footer Bg</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <input type="file" name="footer_bg_image" class="form-control"
                                    onchange="document.getElementById('footer_bg_image').src = window.URL.createObjectURL(this.files[0])" />
                                @error('footer_bg_image')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <img id="footer_bg_image" src="{{ asset($setting->footer_bg_image) }}" alt="footer_bg_image"
                                    style="width: 100px;">
                            </div>
                        </div>
                        {{-- End footer_bg_image --}}


                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Title</h6>
                            </div>
                            <div class=" form-group col-sm-9 text-secondary">
                                <input type="text" name="title"
                                    class="form-control @error('title') is-invalid @enderror"
                                    value="{{ old('title', $setting->title ?? '') }}" />
                                @error('title')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Address</h6>
                            </div>
                            <div class=" form-group col-sm-9 text-secondary">
                                <input type="text" name="address"
                                    class="form-control @error('address') is-invalid @enderror"
                                    value="{{ old('address', $setting->address ?? '') }}" />
                                @error('address')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Phone</h6>
                            </div>
                            <div class=" form-group col-sm-9 text-secondary">
                                <input type="number" name="phone"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    value="{{ old('phone', $setting->phone ?? '') }}" placeholder="Ex.01912395149" />
                                @error('phone')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Email</h6>
                            </div>
                            <div class=" form-group col-sm-9 text-secondary">
                                <input type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $setting->email ?? '') }}" />
                                @error('email')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Meta Keywards</h6>
                            </div>
                            <div class=" form-group col-sm-9 text-secondary">
                                <input type="text" name="meta_keyword" class="form-control"
                                    value="{{ old('meta_keyword', $setting->meta_keyword ?? '') }}" />
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Meta Description</h6>
                            </div>
                            <div class=" form-group col-sm-9 text-secondary">
                                <input type="text" name="meta_description" class="form-control"
                                    value="{{ old('meta_description', $setting->meta_description ?? '') }}" />
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Footer Text</h6>
                            </div>
                            <div class=" form-group col-sm-9 text-secondary">
                                <textarea class="form-control @error('footer_text') is-invalid @enderror" name="footer_text" id="">{{ old('footer_text', $setting->footer_text ?? '') }}</textarea>
                                @error('footer_text')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Shipping Charge</h6>
                            </div>
                            <div class=" form-group col-sm-9 text-secondary">
                                <input type="text" name="shipping_charge" class="form-control"
                                    value="{{ old('shipping_charge', $setting->shipping_charge ?? '') }}" />
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Footer Copyright By</h6>
                            </div>
                            <div class=" form-group col-sm-9 text-secondary">
                                <input type="text" name="footer_copyright_by"
                                    class="form-control @error('footer_copyright_by') is-invalid @enderror"
                                    value="{{ old('footer_copyright_by', $setting->footer_copyright_by ?? '') }}" />
                                @error('footer_copyright_by')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Footer Copyright Url</h6>
                            </div>
                            <div class=" form-group col-sm-9 text-secondary">
                                <input type="url" name="footer_copyright_url"
                                    class="form-control @error('footer_copyright_url') is-invalid @enderror"
                                    value="{{ old('footer_copyright_url', $setting->footer_copyright_url ?? '') }}" />
                                @error('footer_copyright_url')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Term & Condition</h6>
                            </div>
                            <div class=" form-group col-sm-9 text-secondary">
                                <textarea name="TC" id="TC"
                                    class="form-control mytextarea @error('TC') is-invalid @enderror">{{ old('TC', $setting->TC ?? '') }}</textarea>
                                @error('TC')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">About Us</h6>
                            </div>
                            <div class=" form-group col-sm-9 text-secondary">
                                <textarea name="about_us" id="about_us"
                                    class="form-control mytextarea @error('about_us') is-invalid @enderror">{{ old('about_us', $setting->about_us ?? '') }}</textarea>
                                @error('about_us')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-9 text-secondary">
                                <input type="submit" class="btn btn-primary px-4" value="Save Changes" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
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
@endpush
