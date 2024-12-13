<!DOCTYPE html>
<html lang="en">


<head>
    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <title>{{config('app.name')}} | Vendor Login</title>
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('') }}admin-assets/assets/img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('') }}admin-assets/assets/img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('') }}admin-assets/assets/img/favicon/favicon-16x16.png">
    <link rel="manifest" href="{{ asset('') }}admin-assets/assets/img/favicon/site.webmanifest">
    <link rel="mask-icon" href="{{ asset('') }}admin-assets/assets/img/favicon/safari-pinned-tab.svg" color="#ac772b">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="{{ asset('') }}front_end/vendor-assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('') }}front_end/vendor-assets/css/app.css">
    <link href="{{ asset('admin-assets/plugins/notification/toastr/toastr.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="login" style="background: #121212;">
<!--<body class="login" style="background: url('{{ asset('') }}admin-assets/assets/img/Admin-background.jpg'); background-size: 100% 100%; background-position: center; background-repeat: no-repeat;">-->

<style>
    label{
        color: #fff;
    }
    .parsley-errors-list.filled li, label.error {
        color: yellow;
        font-size: 8px !important;
        position: absolute;
        top: 10px;
        right: 20px;
    }
    .password-append #password-error{
        top: -15px;
        right: 0;
    }
    .password-toggle{
                position: absolute;
                right: 0;
                border: 0;
                z-index: 9;
                top: 0;
            }
            .password-toggle button{
                border-radius: 0 !important;
                background: #000 !important;
                color: #fff !important;
                border: 0 !important;
                height: 50px;
                width: 50px;
                border-top-right-radius: 5px !important;
                border-bottom-right-radius: 5px !important;

            }
</style>
    <div class="login-box h-100">
        <form method="POST" class="form-login" action="{{ route('portal.check_login') }}">
         @csrf
            <div class="row">
                <div class="col-md-12 text-center mb-4">
                    <img alt="logo" src="{{ asset('') }}admin-assets/assets/img/admin_logo_new.svg" class="img-fluid">
                </div>
                <div class="col-md-12">
    
                    <label for="inputEmail" class="">Email</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="" required autocomplete="email" autofocus>
                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                </div>
                <div class="col-md-12 mb-2">
                    <label for="inputPassword" class="">Password</label>
                    <div class="position-relative password-append">
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password"> 
                        <div class="input-group-append password-toggle">
                                                <button class="btn btn-outline-secondary" type="button" id="togglePassword"><i class="bi bi-eye"></i></button>
                                            </div>
                    </div>
                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror             
                </div>
                <div class="col-md-12 mb-3">
                    <button type="submit" class="btn btn-gradient-dark btn-rounded btn-block">Sign in</button>
                </div>
                <div class="col-md-12">
                    <div class="password-reset">
                       <!--  <a href="{{url('/forgot-password')}}">Forgot Password</a> -->
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="create-account-section h-100">
        <span>New Vendor</span>
        <h3>Create an Account</h3>
        <p class="mb-4">Welcome to our platform! Join our esteemed network of vendors and expand your reach. Showcase your offerings to a discerning audience and embark on a journey of mutual success. Register now and be part of something extraordinary!</p>
        <a href="{{url('/register')}}" class="btn btn-gradient-dark btn-rounded btn-block ml-0">Create an Accoount</a>
    </div>




    <script src="{{ asset('') }}front_end/vendor-assets/js/jquery.min.js"></script>
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
                password: "Password field is required"
            },
            submitHandler: function(form) {
                $.ajax({
                    type:'POST',
                    url: "{{ route("portal.check_login")}}",
                    data:{
                        '_token': $('input[name=_token]').val(),
                        'email': $("#email").val(),
                        'password': $("#password").val(),
                    },
                    success: function(response) {
                        if(response.success){
                            toastr["success"](response.message);
                            setTimeout(function(){
                                window.location.href = "{{ route("portal.dashboard")}}";
                            }, 1000);

                        } else {
                            toastr["error"](response.message);
                        }
                    }
                });
            }
        });
        
    </script>
    <script>
    $(document).ready(function() {
        $('#togglePassword').click(function() {
            var passwordInput = $('#password');
            var fieldType = passwordInput.attr('type');
            passwordInput.attr('type', fieldType === 'password' ? 'text' : 'password');
            $(this).find('i').toggleClass('bi-eye bi-eye-slash');
        });
    });
</script>
</body>

</html>