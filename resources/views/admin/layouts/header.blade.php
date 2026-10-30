<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{$title}} | {{ \App\Support\Store::brand()['name'] }} Admin</title>
    <meta charset="utf-8"/>
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta content="{{ \App\Support\Store::brand()['name'] }}" name="description"/>
<meta content="{{ \App\Support\Store::brand()['name'] }}" name="author"/>



<!-- App favicon -->
<link rel="icon" href="{{ asset('assets/drape/favicon-32.png') }}">
<link href="{{asset('assets/admin/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css')}}" rel="stylesheet" type="text/css" />
<!-- preloader css -->
<link rel="stylesheet" href="{{ asset('assets/admin/css/preloader.min.css')}}" type="text/css" />
<link href="{{asset('assets/admin/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
<script src="{{asset('assets/admin/libs/sweetalert2/sweetalert2.min.js') }}"></script>


<!-- Bootstrap Css -->
<link href="{{ asset('assets/admin/css/bootstrap.min.css')}}" id="bootstrap-style" rel="stylesheet" type="text/css" />
<!-- Icons Css -->
<link href="{{ asset('assets/admin/css/icons.min.css')}}" rel="stylesheet" type="text/css" />
<!-- choices css -->
<link href="{{asset('assets/admin/libs/choices.js/public/assets/styles/choices.min.css')}}" rel="stylesheet" type="text/css" />

<!-- color picker css -->
<link rel="stylesheet" href="{{asset('assets/admin/libs/@simonwep/pickr/themes/classic.min.css')}}" /> <!-- 'classic' theme -->
<link rel="stylesheet" href="{{asset('assets/admin/libs/@simonwep/pickr/themes/monolith.min.css')}}" /> <!-- 'monolith' theme -->
<link rel="stylesheet" href="{{asset('assets/admin/libs/@simonwep/pickr/themes/nano.min.css')}}" /> <!-- 'nano' theme -->
 <!-- glightbox css -->
 <link rel="stylesheet" href="{{asset('assets/admin/libs/glightbox/css/glightbox.min.css')}}">
<!-- datepicker css -->
<link rel="stylesheet" href="{{asset('assets/admin/libs/flatpickr/flatpickr.min.css')}}">
<!-- App Css-->
<link href="{{ asset('assets/admin/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/admin/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/admin/css/app.min.css')}}" id="app-style" rel="stylesheet" type="text/css" />
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600&display=swap" rel="stylesheet">
<link href="{{ asset_ver('assets/admin/css/drape-admin.css') }}" rel="stylesheet" type="text/css" />
<script src="{{ asset('assets/admin/libs/jquery/jquery.min.js')}}"></script>



</head>
<!--All Vertical Pages-->
<body>
    <!-- Begin page -->
    @if ($message = Session::get('fail'))
        <script>
            // Store the Swal.fire() instance in a variable
            var toast = Swal.fire({
                icon: 'error',
                title: '{{$message}}',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                // Pause while hovered.
                didOpen: (popup) => {
                    popup.addEventListener('mouseenter', Swal.stopTimer);
                    popup.addEventListener('mouseleave', Swal.resumeTimer);
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                hideClass: {
                    popup: '',
                    backdrop: ''
                }
            });
        </script>
    @endif


@if ($message = Session::get('success'))
    <script>
        // Store the Swal.fire() instance in a variable
        var toast = Swal.fire({
            icon: 'success',
            title: '{{$message}}',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
                // Pause while hovered.
                didOpen: (popup) => {
                    popup.addEventListener('mouseenter', Swal.stopTimer);
                    popup.addEventListener('mouseleave', Swal.resumeTimer);
                },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
            hideClass: {
                popup: '',
                backdrop: ''
            }
        });
    </script>
@endif

@if ($errors->any())
    <script>
        var errorMessages = '<ul style="list-style: none;font-size: 14px;">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>';

        Swal.fire({
            icon: 'error',
            html: errorMessages,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
                // Pause while hovered.
                didOpen: (popup) => {
                    popup.addEventListener('mouseenter', Swal.stopTimer);
                    popup.addEventListener('mouseleave', Swal.resumeTimer);
                },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
            hideClass: {
                popup: '',
                backdrop: ''
            }
        });
    </script>
@endif
<div id="layout-wrapper">
