<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Vendor Dashboard</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="{{ asset('backend/upload/favicon.ico') }}" type="image/x-icon" />

    <style>
        .bootstrap-select .dropdown-toggle {
            display: block !important;
            visibility: visible !important;
        }
    </style>
    <!-- Fonts and icons -->
    <script src="{{ asset('backend/js/plugin/webfont/webfont.min.js') }}"></script>


    <script>
        WebFont.load({
            google: {
                families: ["Public Sans:300,400,500,600,700"]
            },
            custom: {
                families: [
                    "Font Awesome 5 Solid",
                    "Font Awesome 5 Regular",
                    "Font Awesome 5 Brands",
                    "simple-line-icons",
                ],
                urls: ["{{ asset('backend/css/fonts.min.css') }}"],
            },
            active: function() {
                sessionStorage.fonts = true;
            },
        });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ asset('backend/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/css/plugins.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('backend/css/template.css') }}" />

    <style>
        /* Chrome, Safari, Edge, Opera */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Firefox */
        input[type=number] {
            -moz-appearance: textfield;
        }

        .vendor-dashboard-heading {
            margin-left: 20px;
            margin-top: 20px;
            /* Adjust as needed */
            color: black;
            /* Text color */
            font-size: 28px;
            /* Font size */
            font-weight: bold;
            /* Font weight */
        }
    </style>

</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        @include('vendor.body.sidebar')
        <!-- End Sidebar -->

        <div class="main-panel">
            @include('vendor.body.header')

            <div class="container">
                <div class="vendor-dashboard-heading">
                </div>
                @yield('main')
            </div>

            @include('vendor.body.footer')
        </div>


    </div>
    <!--   Core JS Files   -->
    <script src="{{ asset('backend/js/core/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('backend/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('backend/js/core/bootstrap.min.js') }}"></script>

    <!-- jQuery Scrollbar -->
    <script src="{{ asset('backend/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>

    <!-- Chart JS -->
    <script src="{{ asset('backend/js/plugin/chart.js/chart.min.js') }}"></script>

    <!-- jQuery Sparkline -->
    <script src="{{ asset('backend/js/plugin/jquery.sparkline/jquery.sparkline.min.js') }}"></script>

    <!-- Chart Circle -->
    <script src="{{ asset('backend/js/plugin/chart-circle/circles.min.js') }}"></script>

    <!-- Datatables -->
    <script src="{{ asset('backend/js/plugin/datatables/datatables.min.js') }}"></script>

    <!-- Bootstrap Notify -->
    <script src="{{ asset('backend/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

    <!-- jQuery Vector Maps -->
    <script src="{{ asset('backend/js/plugin/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('backend/js/plugin/jsvectormap/world.js') }}"></script>

    <!-- Sweet Alert -->
    <script src="{{ asset('backend/js/plugin/sweetalert/sweetalert.min.js') }}"></script>

    <!-- template JS -->
    <script src="{{ asset('backend/js/template.js') }}"></script>
    {{-- Validate js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.21.0/jquery.validate.min.js"
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    {{-- Summernote --}}
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

    <script>
        $('.mytextarea').summernote({
            placeholder: 'Enter Description',
            tabsize: 2,
            height: 120,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    </script>

    @if (session('success'))
    <script>
        $(document).ready(function() {
            $.notify({
                icon: 'icon-bell',
                message: '{{ session('
                success ') }}',
            }, {
                type: 'success',
                placement: {
                    from: "bottom",
                    align: "right"
                },
                time: 1000,
            });
        });
    </script>
    @endif


    <script>
        $(document).ready(function() {
            $("#basic-datatables").DataTable({});

        });
    </script>

    {{-- Sweet Alert --}}
    <script>
        $(document).ready(function() {
            $(document).on('click', '#delete', function(e) {
                e.preventDefault();
                var link = $(this).attr("href");

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = link
                        Swal.fire(
                            'Deleted!',
                            'Your file has been deleted.',
                            'success'
                        )
                    }
                })
            });
        });

        $(document).ready(function() {
            $(document).on('click', '[id^="deleteImage-"]', function(e) {
                e.preventDefault();

                var link = $(this).attr("href");
                var imageId = $(this).attr('id').split('-')[1];
                var containerId = 'image-container-' + imageId;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: link,
                            type: 'GET',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire(
                                        'Deleted!',
                                        'Your image has been deleted.',
                                        'success'
                                    );


                                    $('#' + containerId).fadeOut(function() {
                                        $(this).remove();
                                    });
                                } else {
                                    Swal.fire(
                                        'Error!',
                                        response.error || 'There was an error deleting your image.',
                                        'error'
                                    );
                                }
                            },
                            error: function(xhr) {
                                Swal.fire(
                                    'Error!',
                                    'There was an error processing your request.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>

    @stack('script')
</body>

</html>