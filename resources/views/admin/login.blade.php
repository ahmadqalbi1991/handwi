<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <title>{{env('APP_NAME')}} | Admin Login </title>
    <link rel="shortcut icon" href="{{ asset('') }}admin-assets/assets/img/favicon/favicon.ico" type="image/x-icon" />
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
        .form-login .btn{
            margin-top: 30px;
        }
        #email-error, #password-error{
            position: absolute;
            top: 5px;
            right: 0;
            font-size: 10px;
            margin: 0;
            color: red;
        }
    </style>

</head>
<body class="login" style="background-image: linear-gradient(to right top, #00D3FF, #41DDBE, #A6EC59, #E6F619, #FFFA00); background-size: 100% 100%; background-position: center; background-repeat: no-repeat;">
<!--<body class="login" >-->

    <form method="POST" class="form-login" action="{{ route('admin.check_login') }}">
        @csrf
        <input type="hidden" name="admin" value="1">
        <div class="row">
            <div class="col-md-12 text-center mb-4">
                <img alt="logo" src="{{ asset('') }}admin-assets/admin-assets/assets/img/logo.png" class="theme-logo img-fluid">
            </div>
            <div class="col-md-12">
                <h5 class="card-header"> LOGIN TO CONTINUE</h5>
                <div class="card-container">
                    <div class="position-relative">
                        <label for="inputEmail" class="text-black">Email</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ is_null(old('email')) ? 'admin@admin.com': old('email') }}" required autocomplete="email" autofocus>
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="position-relative"> 
                        <label for="inputPassword" class="text-black">Password</label>
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror        
                    </div>
                    
                    <button type="submit" class="btn btn-gradient-dark btn-rounded btn-block">Sign in</button>
                </div>
            </div>

        </div>
    </form>

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
                    url: "{{ route("admin.check_login")}}",
                    data:{
                        '_token': $('input[name=_token]').val(),
                        'email': $("#email").val(),
                        'password': $("#password").val(),
                    },
                    success: function(response) {
                        if(response.success){
                            toastr["success"](response.message);
                            setTimeout(function(){
                                window.location.href = "{{ route("admin.dashboard")}}";
                            }, 1000);

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
