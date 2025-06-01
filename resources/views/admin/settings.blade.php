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
                <a href="javascript:void(0)">Settings</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Settings</h4>
                </div>
                <div class="card-body">
                    <hr />

                    {{-- Show global success message, if any --}}
                    @if(session('message'))
                        <div class="alert alert-success">
                            {{ session('message') }}
                        </div>
                    @endif

                    <form action="{{ route('admin.settings.update') }}" method="post" enctype="multipart/form-data">
                        @csrf

                        {{-- Logo --}}
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Logo</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <input
                                    type="file"
                                    name="logo"
                                    class="form-control @error('logo') is-invalid @enderror"
                                    onchange="document.getElementById('logo-preview').src = window.URL.createObjectURL(this.files[0]); document.getElementById('logo-preview').style.display='block';"
                                />
                                @error('logo')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-9 text-secondary">
                                {{-- If $setting->logo is truthy, show the existing image, else hide --}}
                                @if(isset($setting->logo) && $setting->logo !== '')
                                    <img id="logo-preview" src="{{ asset($setting->logo) }}" style="width:100px;">
                                @else
                                    <img id="logo-preview" src="" style="width:100px; display:none;">
                                @endif

                            </div>
                        </div>

                        {{-- Footer Logo --}}
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Footer Logo</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <input
                                    type="file"
                                    name="footer_logo"
                                    class="form-control @error('footer_logo') is-invalid @enderror"
                                    onchange="document.getElementById('footer-logo-preview').src = window.URL.createObjectURL(this.files[0]); document.getElementById('footer-logo-preview').style.display='block';"
                                />
                                @error('footer_logo')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-9 text-secondary">
                                @if(isset($setting->footer_logo) && $setting->footer_logo !== '')
                                    <img
                                        id="footer-logo-preview"
                                        src="{{ asset($setting->footer_logo) }}"
                                        alt="footer_logo"
                                        style="width: 100px;"
                                    >
                                @else
                                    <img
                                        id="footer-logo-preview"
                                        src=""
                                        alt="footer_logo"
                                        style="width: 100px; display: none;"
                                    >
                                @endif
                            </div>
                        </div>

                        {{-- Favicon --}}
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Favicon</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <input
                                    type="file"
                                    name="favicon"
                                    class="form-control @error('favicon') is-invalid @enderror"
                                    onchange="document.getElementById('favicon-preview').src = window.URL.createObjectURL(this.files[0]); document.getElementById('favicon-preview').style.display='block';"
                                />
                                @error('favicon')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-9 text-secondary">
                                @if(isset($setting->favicon) && $setting->favicon !== '')
                                    <img
                                        id="favicon-preview"
                                        src="{{ asset($setting->favicon) }}"
                                        alt="favicon"
                                        style="width: 100px;"
                                    >
                                @else
                                    <img
                                        id="favicon-preview"
                                        src=""
                                        alt="favicon"
                                        style="width: 100px; display: none;"
                                    >
                                @endif
                            </div>
                        </div>

                        {{-- Footer Background --}}
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Footer Background</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <input
                                    type="file"
                                    name="footer_bg_image"
                                    class="form-control @error('footer_bg_image') is-invalid @enderror"
                                    onchange="document.getElementById('footer-bg-preview').src = window.URL.createObjectURL(this.files[0]); document.getElementById('footer-bg-preview').style.display='block';"
                                />
                                @error('footer_bg_image')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-9 text-secondary">
                                @if(isset($setting->footer_bg_image) && $setting->footer_bg_image !== '')
                                    <img
                                        id="footer-bg-preview"
                                        src="{{ asset($setting->footer_bg_image) }}"
                                        alt="footer_bg_image"
                                        style="width: 100px;"
                                    >
                                @else
                                    <img
                                        id="footer-bg-preview"
                                        src=""
                                        alt="footer_bg_image"
                                        style="width: 100px; display: none;"
                                    >
                                @endif
                            </div>
                        </div>

                        {{-- Title --}}
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Title</h6>
                            </div>
                            <div class="form-group col-sm-9 text-secondary">
                                <input
                                    type="text"
                                    name="title"
                                    class="form-control @error('title') is-invalid @enderror"
                                    value="{{ old('title', $setting->title ?? '') }}"
                                />
                                @error('title')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Address --}}
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Address</h6>
                            </div>
                            <div class="form-group col-sm-9 text-secondary">
                                <input
                                    type="text"
                                    name="address"
                                    class="form-control @error('address') is-invalid @enderror"
                                    value="{{ old('address', $setting->address ?? '') }}"
                                />
                                @error('address')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Phone --}}
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Phone</h6>
                            </div>
                            <div class="form-group col-sm-9 text-secondary">
                                <input
                                    type="text"
                                    name="phone"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    value="{{ old('phone', $setting->phone ?? '') }}"
                                    placeholder="Ex. 01912345678"
                                />
                                @error('phone')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Email --}}
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Email</h6>
                            </div>
                            <div class="form-group col-sm-9 text-secondary">
                                <input
                                    type="email"
                                    name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $setting->email ?? '') }}"
                                />
                                @error('email')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Number of Tags --}}
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Number of Tags to be Shown</h6>
                            </div>
                            <div class="form-group col-sm-9 text-secondary">
                                <input
                                    type="number"
                                    name="num_of_tag"
                                    class="form-control @error('num_of_tag') is-invalid @enderror"
                                    value="{{ old('num_of_tag', $setting->num_of_tag ?? 0) }}"
                                    min="0"
                                />
                                @error('num_of_tag')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>


                        {{-- Meta Keywords --}}
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Meta Keywords</h6>
                            </div>
                            <div class="form-group col-sm-9 text-secondary">
                                <input
                                    type="text"
                                    name="meta_keyword"
                                    class="form-control"
                                    value="{{ old('meta_keyword', $setting->meta_keyword ?? '') }}"
                                />
                            </div>
                        </div>

                        {{-- Meta Description --}}
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Meta Description</h6>
                            </div>
                            <div class="form-group col-sm-9 text-secondary">
                                <input
                                    type="text"
                                    name="meta_description"
                                    class="form-control"
                                    value="{{ old('meta_description', $setting->meta_description ?? '') }}"
                                />
                            </div>
                        </div>

                        {{-- Footer Text --}}
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Footer Text</h6>
                            </div>
                            <div class="form-group col-sm-9 text-secondary">
                                <textarea
                                    name="footer_text"
                                    class="form-control @error('footer_text') is-invalid @enderror"
                                    rows="3"
                                >{{ old('footer_text', $setting->footer_text ?? '') }}</textarea>
                                @error('footer_text')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Footer Copyright By --}}
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Footer Copyright By</h6>
                            </div>
                            <div class="form-group col-sm-9 text-secondary">
                                <input
                                    type="text"
                                    name="footer_copyright_by"
                                    class="form-control @error('footer_copyright_by') is-invalid @enderror"
                                    value="{{ old('footer_copyright_by', $setting->footer_copyright_by ?? '') }}"
                                />
                                @error('footer_copyright_by')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Footer Copyright URL --}}
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">Footer Copyright URL</h6>
                            </div>
                            <div class="form-group col-sm-9 text-secondary">
                                <input
                                    type="url"
                                    name="footer_copyright_url"
                                    class="form-control @error('footer_copyright_url') is-invalid @enderror"
                                    value="{{ old('footer_copyright_url', $setting->footer_copyright_url ?? '') }}"
                                />
                                @error('footer_copyright_url')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <div class="row">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-9 text-secondary">
                                <input
                                    type="submit"
                                    class="btn btn-primary px-4"
                                    value="Save Changes"
                                />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
