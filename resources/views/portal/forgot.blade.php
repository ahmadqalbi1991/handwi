<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <title>{{config('app.name')}} | Vendor Login </title>
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('') }}admin-assets/assets/img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('') }}admin-assets/assets/img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('') }}admin-assets/assets/img/favicon/favicon-16x16.png">
    <link rel="manifest" href="{{ asset('') }}admin-assets/assets/img/favicon/site.webmanifest">
    <link rel="mask-icon" href="{{ asset('') }}admin-assets/assets/img/favicon/safari-pinned-tab.svg" color="#ac772b">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">

    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700' rel='stylesheet' type='text/css'>
    <link href="{{ asset('admin-assets/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin-assets/assets/css/plugins.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin-assets/assets/css/users/login-3.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-assets/plugins/jqvalidation/custom-jqBootstrapValidation.css') }}">
    <link href="{{ asset('admin-assets/plugins/notification/toastr/toastr.min.css') }}" rel="stylesheet" type="text/css" />

    <style type="text/css">
        .invalid-feedback{
            color: red;
            display: block;
        }
        .form-login{
            width: 100%;
            max-width: 100%;
        }
        .create-account-section{
            width: 100%;
            margin: auto;
            background: rgba( 0, 0, 0, 0.25 );
            /* box-shadow: 0 8px 32px 0 rgb(31 38 135 / 37%); */
            backdrop-filter: blur( 20px );
            -webkit-backdrop-filter: blur( 20px );
            border-radius: 10px;
            border: 1px solid #B27E30;
            min-height: 402px;
            padding: 30px
        }
        .create-account-section span{
            padding: 8px 12px;
            margin-bottom: 20px !important;
            color: #fff !important;
            background: linear-gradient(to right top, #af7a2d, #b58032, #ba8738, #c08d3d, #c59443, #cc9c48, #d2a34e, #d9ab53, #e3b65a, #ecc160, #f6cc67, #ffd76e);
            background-image: linear-gradient(to right top, #af7a2d, #b58032, #ba8738, #c08d3d, #c59443, #cc9c48, #d2a34e, #d9ab53, #e3b65a, #ecc160, #f6cc67, #ffd76e);
            box-shadow: none;
            border-radius: 50rem;
        }
        .create-account-section h3 {
            font-size: 32px;
            color: #fff;
            font-weight: 800;
        }
        .create-account-section p{
            color: #fff;
            line-height: 1.8;
        }
        .create-account-section a{

        }
    </style>

</head>
<body class="login" style="background: url('{{ asset('') }}admin-assets/assets/img/bg-1920x1080.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">

    <div class="container">
        <div class="row">
            <div class="col-lg-7 mb-4">
                <form method="POST" class="form-login" action="{{ route('vendor.check_user') }}" >
                    @csrf
                    <input type="hidden" name="admin" value="1">
                    <div class="row">
                        <div class="col-md-12 text-center mb-4">
                            <img alt="logo" src="{{ asset('') }}admin-assets/assets/img/logo-provider.svg" style="height: 60px;" class="theme-logo">
                        </div>
                        <div class="col-md-12">

                            <div class="mb-4">
                                <label for="inputEmail" class="">Email</label>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                        
                            <button type="submit" class="btn btn-gradient-dark btn-rounded btn-block ml-0">Submit</button>

                           

                            <div style="margin-top: 30px; text-align: right;">
                                <p class="text-muted"> <a href="{{url('/vendor')}}" class="text-white"> Login </a> </b> </p>
                            </div>

                        </div>
                    </div>
                </form>
            </div>

            <div class="col-lg-5 mb-4">
                    <div class="create-account-section">
                        <span>New Vendor</span>
                        <h3  class="mt-3 mb-2">Create an Account</h3>
                        <p class="mb-4">Whether you are a vendor or products supplier, Moda's registration process is more easier than you think. Simply fill out the registration details to register with MODA. Vendors can then provide their company name, products and services to be sold, and other important business details through the vendor panel.The biggest selling point?</p>
                        <a href="{{url('/register')}}" class="btn btn-gradient-dark btn-rounded btn-block ml-0">Create an Accoount</a>
                    </div>
            </div>
        </div>
    </div>

    <!-- BEGIN GLOBAL MANDATORY SCRIPTS -->
    <script src="{{ asset('admin-assets/assets/js/libs/jquery-3.1.1.min.js') }}"></script>
    <script src="{{ asset('admin-assets/bootstrap/js/popper.min.js') }}"></script>
    <script src="{{ asset('admin-assets/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('admin-assets/plugins/jqvalidation/jqBootstrapValidation-1.3.7.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.js"></script>
    <script src="{{ asset('admin-assets/plugins/notification/toastr/toastr.min.js') }}"></script>
    <script>
        // Toaster options
        toastr.options = {
            "closeButton": false,
            "debug": false,
            "newestOnTop": false,
            "progressBar": false,
            "rtl": false,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": 300,
            "hideDuration": 1000,
            "timeOut": 2000,
            "extendedTimeOut": 1000,
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }

        $(document).ready(function() {
        @if (\Session::has('error') && \Session::get('error') != null)
            toastr["error"]("{{\Session::get('error')}}");
        @endif

        })
        $(".form-login").submit(function(e) {
            e.preventDefault();
        }).validate({
            rules: {
                username: {
                    required: true,
                    email: true
                },
                password: "required"
            },
            messages: {
                username: {
                    required: "Password field is required",
                    email: "Please enter valid email address"
                },
                password: "User name field is required"
            },
            submitHandler: function(form) {
                $.ajax({
                    type:'POST',
                    url: "{{ route("vendor.check_user")}}",
                    data:{
                        '_token': $('input[name=_token]').val(),
                        'email': $("#email").val()
                    },
                    success: function(response) {
                        if(response.success){
                            toastr["success"](response.message);

                        } else {
                            toastr["error"](response.message);
                        }
                    }
                });
            }
        });
    </script>

    <!-- END GLOBAL MANDATORY SCRIPTS -->
</body>
</html>
