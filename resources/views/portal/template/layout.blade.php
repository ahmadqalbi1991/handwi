<?php
$_current_user = \Request::get('_current_user');
$role = Auth::user()->role;
if($role == 4) //store manager
{
    $privileges = \App\Models\UserPrivileges::join('users', 'users.id', 'user_privileges.user_id')
    ->join('designations', 'designations.id', '=', 'users.designation_id')->where(['users.id' => Auth::user()->id, 'user_privileges.designation_id' => \App\Models\User::where('id', Auth::user()->id)->pluck('designation_id')->first()])->pluck('privileges')->first();
    $privileges = json_decode($privileges, true);

}
$vendordata = \App\Models\VendorDetailsModel::select('*')
            ->where(['user_id'=>Auth::user()->id])->first();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <title>{{ config('global.site_name') }} | Vendor </title>
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('') }}admin-assets/assets/img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('') }}admin-assets/assets/img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('') }}admin-assets/assets/img/favicon/favicon-16x16.png">
    <link rel="manifest" href="{{ asset('') }}admin-assets/assets/img/favicon/site.webmanifest">
    <link rel="mask-icon" href="{{ asset('') }}admin-assets/assets/img/favicon/safari-pinned-tab.svg" color="#ac772b">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700' rel='stylesheet' type='text/css'>
    <link href="{{ asset('') }}admin-assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker3.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link href="{{ asset('') }}admin-assets/assets/css/sidebar.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css" integrity="sha512-1sCRPdkRXhBV2PBLUdRb4tMg1w2YPf37qatUFeS7zlBy7jJI8Lf4VHwWfZZfpXtYSLy85pkm9GaYVYMfw5BC1A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/solid.min.css" integrity="sha512-EvFBzDO3WBynQTyPJI+wLCiV0DFXzOazn6aoy/bGjuIhGCZFh8ObhV/nVgDgL0HZYC34D89REh6DOfeJEWMwgg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css" integrity="sha512-MQXduO8IQnJVq1qmySpN87QQkiR1bZHtorbJBD0tzy7/0U9+YIC93QWHeGTEoojMVHWWNkoCp8V6OzVSYrX0oQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/1.1.2/css/bootstrap-multiselect.min.css" integrity="sha512-fZNmykQ6RlCyzGl9he+ScLrlU0LWeaR6MO/Kq9lelfXOw54O63gizFMSD5fVgZvU1YfDIc6mxom5n60qJ1nCrQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="{{ asset('') }}admin-assets/assets/css/custom.css" rel="stylesheet" type="text/css" />
    <link href="{{ asset('') }}admin-assets/assets/css/plugins.css" rel="stylesheet" type="text/css" />
    <link href="{{ asset('') }}admin-assets/assets/css/parsley.css" rel="stylesheet" type="text/css" />
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

        
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- END GLOBAL MANDATORY STYLES -->

    <!-- BEGIN PAGE LEVEL PLUGINS/CUSTOM STYLES -->
    @yield('header')
    <!-- END PAGE LEVEL PLUGINS/CUSTOM STYLES -->

</head>

<body class="dark">


    <!-- New Layout Section Starts Here -->


    <div class="sidebar">
        <div class="logo-details mt-2">
            <a href="#">
                <i class='bx bx-menu' ></i>
            </a>
            <!-- <i class='bx bxl-c-plus-plus'></i> -->
            <!-- <img src="{{ asset('') }}admin-assets/assets/img/moda-icon.png" class="img-fluid" alt="">
            <span class="logo_name">MODA</span> -->
        </div>
        <ul class="nav-links">
            <li>
            <a href="{{ url('portal/dashboard') }}">
                <i class='bx bx-grid-alt'></i>
                <span class="link_name">Dashboard</span>
            </a>
            <ul class="sub-menu blank">
                <li>
                <a class="link_name" href="{{ url('portal/dashboard') }}">Dashboard</a>
                </li>
            </ul>
            </li>

            @if(Auth::user()->activity_id == 7 || Auth::user()->activity_id == 3 || (Auth::user()->activity_id == 5 ))
            <li>
                <a href="{{ url('portal/products') }}">
                    <i class='bx bx-cart-alt'></i>
                    <span class="link_name"> Inventory </span>
                </a>
                <ul class="sub-menu blank">
                    <li>
                        <a class="link_name" href="{{ url('portal/products') }}"> Inventory </a>
                    </li>
                </ul>
            </li>
            @endif
            @if(Auth::user()->activity_id == 7 || Auth::user()->activity_id == 3 || (Auth::user()->activity_id == 5 ))
            <li>
            <a href="{{url('portal/orders')}}">
                <i class='bx bx-cart-alt'></i>
                <span class="link_name">Orders</span>
            </a>
            <ul class="sub-menu blank">
                <li>
                <a class="link_name" href="{{url('portal/orders')}}">Orders</a>
                </li>
            </ul>
            </li>
            @endif

            @if(Auth::user()->activity_id == 6 || Auth::user()->activity_id == 4 || Auth::user()->activity_id == 1)
              <li>
                <div class="iocn-link">
                    <a href="#">
                        <i class='bx bx-box'></i>
                        <span class="link_name">My Services </span>
                    </a>
                    <i class='bx bxs-chevron-down arrow'></i>
                </div>
                <ul class="sub-menu">
                    <li>
                    <a class="link_name" href="#">My Services</a>
                    </li>
                    <li>
                    <a href="{{ url('portal/vendor/view_services') }}">My Services</a>
                    </li>

                </ul>
            </li>
            @endif
           

           
            @if(Auth::user()->activity_id == 6 || Auth::user()->activity_id == 4 || Auth::user()->activity_id == 1)

            <li>
                <div class="iocn-link">
                    <a href="{{url('portal/service_request')}}">
                        <i class='bx bx-file'></i>
                        <span class="link_name">Service Request</span>
                    </a>
                    <i class='bx bxs-chevron-down arrow'></i>
                </div>
                <ul class="sub-menu">
                    <li>
                    <a class="link_name" href="#">Service Request</a>
                    </li>
                    <li>
                    <a href="{{url('portal/service_request')}}">Service Request</a>
                    </li>
                   
                </ul>
            </li>
            @endif

            @if(Auth::user()->activity_id == 7 || Auth::user()->activity_id == 3 || (Auth::user()->activity_id == 5 && Auth::user()->is_delivery) || Auth::user()->activity_id == 6 || Auth::user()->activity_id == 4 || Auth::user()->activity_id == 1)

            <li class="menu-children">
            <a href="{{url('portal/rating')}}">
                <i class='bx bxs-report'></i>
                <span class="link_name">Rating</span>
            </a>
            <ul class="sub-menu blank">
                <li>
                <a class="link_name" href="{{url('portal/rating')}}">Rating and Reviews</a>
                </li>
            </ul>

            </li>
            
            @endif
            @if(Auth::user()->activity_id == 7 || Auth::user()->activity_id == 3 || (Auth::user()->activity_id == 5 && Auth::user()->is_delivery))
            <li class="menu-children">
                <a href="{{url('portal/earnings')}}">
                    <i class='bx bxs-report'></i>
                    <span class="link_name">Earnings</span>
                </a>
                <ul class="sub-menu blank">
                    <li>
                    <a class="link_name" href="{{url('portal/earnings')}}">Earnings</a>
                    </li>
                </ul>
            </li>
            <li class="menu-children">
                <a href="{{url('portal/order_report')}}">
                    <i class='bx bxs-report'></i>
                    <span class="link_name">Report</span>
                </a>
                <ul class="sub-menu blank">
                    <li>
                    <a class="link_name" href="{{url('portal/order_report')}}">Order Report</a>
                    </li>
                </ul>
            </li>
            @endif

            @if(Auth::user()->activity_id == 6 || Auth::user()->activity_id == 1 || Auth::user()->activity_id == 4)
            <li class="menu-children">
            <a href="{{url('portal/service_earnings')}}">
                <i class='bx bxs-report'></i>
                <span class="link_name">Earnings</span>
            </a>
            <ul class="sub-menu blank">
                <li>
                <a class="link_name" href="{{url('portal/service_earnings')}}">Earnings</a>
                </li>
            </ul>

            </li>
            <li class="menu-children">
                <a href="{{url('portal/report/service')}}">
                    <i class='bx bxs-report'></i>
                    <span class="link_name">Report</span>
                </a>
                <ul class="sub-menu blank">
                    <li>
                    <a class="link_name" href="{{url('portal/report/service')}}">Service Request Report</a>
                    </li>
                </ul>
            </li>
            @endif



            <li>
            <a href="{{url('portal/my_profile')}}">
                <i class='bx bx-user-circle' ></i>
                <span class="link_name">My Profile</span>
            </a>
            <ul class="sub-menu blank">
                <li>
                <a class="link_name" href="{{url('portal/my_profile')}}">My Profile</a>
                </li>

            </ul>

            </li>
            <li>
            <a href="{{url('portal/locations')}}">
                <i class='bx bx-user-circle' ></i>
                <span class="link_name">My Locations</span>
            </a>
            <ul class="sub-menu blank">
                <li>
                <a class="link_name" href="{{url('portal/locations')}}">My Locations</a>
                </li>

            </ul>

            </li>

            <li class="mode d-none">
            <div class="sun-moon">
                <i class='bx bx-moon icon moon'></i>
                <i class='bx bx-sun icon sun'></i>
            </div>
            <!-- <span class="mode-text text">Dark mode</span> -->
            <div class="toggle-switch">
                <span class="switch"></span>
            </div>
            </li>
        </ul>
    </div>


<section class="home-section">
    <!-- <div class="home-content"> -->
      <!-- <div class="container-fluid">
        <i class='bx bx-menu' ></i>

      </div> -->
        <div class="p-3 mb-2 container custom-header mt-4">
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center">
                <!-- <div class="home-content">
                    <i class='bx bx-menu'></i>
                  </div> -->
                  <a href="{{ url('portal/dashboard') }}" class="d-flex align-items-center mb-2 mb-lg-0 text-dark text-decoration-none">
                    <img src="{{ asset('') }}admin-assets/assets/img/admin_logo_new.svg" class="img-fluid brand-logo" alt="">
                  </a>
              </div>

              <div class="text-end d-flex align-items-center header-end">
                <!-- <div class="dropdown">
                    <a href="#" class="d-flex align-items-center link-dark text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="profile-name">Hi, Adam</span>
                    <img src="assets/img/profile-icon.png" alt="mdo" width="32" height="32" class="rounded-circle">
                    </a>
                    <ul class="dropdown-menu text-small">
                    <li><a class="dropdown-item" href="#">Dashboard</a></li>
                    <li><a class="dropdown-item" href="#">Settings</a></li>
                    <li><a class="dropdown-item" href="#">Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#">Log out</a></li>
                    </ul>
                </div> -->

                <div class="dropdown">
                    <button class="dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="profile-name">Hi, {{Auth::user()->first_name}}</span>
                        <img src="{{ asset('') }}admin-assets/assets/img/profile-icon.svg" alt="mdo" width="32" height="32" class="rounded-circle">
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item" href="{{url('portal/my_profile')}}"><i class='bx bx-user-circle' ></i> My Profile</a>
                        <a class="dropdown-item" href="{{ url('portal/change_password') }}"><i class='bx bxs-key' ></i> Change Password</a>
                        <a class="logout-link dropdown-item" href="{{ url('portal/logout') }}"><i class='bx bx-log-out' ></i> Log Out</a>
                    </div>
                </div>
              </div>
            </div>
        </div>
      </div>

      <div class="container">
            <div class="page-header">
                    <div class="page-title">
                        <h3>{{ $page_heading ?? '' }}</h3>
                        <div class="crumbs">
                            <ul id="breadcrumbs" class="breadcrumb">
                                <li><a href="{{ url('portal/dashboard') }}"><i class="flaticon-home-fill"></i></a>
                                </li>
                                <li><a onclick="window.history.back()" href="#">{{ $page_heading ?? '' }}</a>
                                </li>
                                <?php if(isset($mode)) { ?>
                                <li class="active"><a href="#">{{ $mode ?? '' }}</a></li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
            </div>
      </div>

      <div class="container">
      @yield('content')
      </div>

      <footer class="container-fluid text-center">
        <!-- Copyright -->
        <div class="text-center p-3">
            <p class="bottom-footer mb-0 text-white">&#xA9; {{ date('Y') }} <a target="_blank" class="text-white" href="#">{{ config('global.site_name') }}</a></p>
        </div>

        <!-- Copyright -->
        </footer>

        @yield('footer')
</section>



    <!-- New Layout Section Ends Here -->









    <!-- Tab Mobile View Header -->
    <header class="tabMobileView header navbar fixed-top d-lg-none d-none">
        <div class="nav-toggle">
            <a href="javascript:void(0);" class="nav-link sidebarCollapse" data-placement="bottom">
                <i class="flaticon-menu-line-2"></i>
            </a>
            <a href="{{ url('/') }}" class=""> <img src="{{ asset('') }}admin-assets/assets/img/admin_logo_new.svg"
                    class="img-fluid" alt="logo"></a>
        </div>
        <ul class="nav navbar-nav">
            <li class="nav-item d-lg-none">
                <form class="form-inline justify-content-end" role="search">
                    <input type="text" class="form-control search-form-control mr-3">
                </form>
            </li>
        </ul>
    </header>
    <!-- Tab Mobile View Header -->

    <!--  BEGIN NAVBAR  -->
    <header class="header navbar fixed-top navbar-expand-sm d-none">

        <ul class="navbar-nav flex-row ml-lg-auto">






            <li class="nav-item dropdown user-profile-dropdown ml-lg-0 mr-lg-2 ml-3 order-lg-0 order-1">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle user" id="userProfileDropdown"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="flaticon-user-12"></span>
                </a>
                <div class="dropdown-menu  position-absolute" aria-labelledby="userProfileDropdown">
                    {{-- <a class="dropdown-item" href="#">
                        <i class="mr-1 flaticon-user-6"></i> <span>My Profile</span>
                    </a> --}}
                    <a class="dropdown-item" href="{{ url('vendor/change_password') }}">
                        <i class="mr-1 flaticon-key-2"></i> <span>Change Password</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="logout-link dropdown-item" href="{{ url('vendor/logout') }}">
                        <i class="mr-1 flaticon-power-button"></i> <span>Log Out</span>
                    </a>
                </div>
            </li>

            <li class="nav-item dropdown cs-toggle order-lg-0 order-3" style="display:none;">
                <a href="#" class="nav-link toggle-control-sidebar suffle">
                    <span class="flaticon-menu-dot-fill d-lg-inline-block d-none"></span>
                    <span class="flaticon-dots d-lg-none"></span>
                </a>
            </li>
        </ul>
    </header>
    <!--  END NAVBAR  -->

    <!--  BEGIN MAIN CONTAINER  -->
    <div class="main-container d-none" id="container">

        <div class="overlay"></div>
        <div class="cs-overlay"></div>

        <!--  BEGIN SIDEBAR  -->

        <div class="sidebar-wrapper sidebar-theme">

            <div id="dismiss" class="d-lg-none"><i class="flaticon-cancel-12"></i></div>

            <nav id="sidebar">

                <ul class="navbar-nav theme-brand flex-row  d-none d-lg-flex">
                    <li class="nav-item d-flex">
                        <a href="{{ url('vendor/dashboard') }}" class="navbar-brand">
                            <img src="{{ asset('') }}admin-assets/assets/img/admin-assets/assets/img/admin_logo_new.svg" class="img-fluid"
                                alt="logo" style="width:70px">
                        </a>
                        <p class="border-underline"></p>
                    </li>
                    <li class="nav-item theme-text">
                        <a href="{{ url('vendor/dashboard') }}" class="nav-link" style="color: black!important"> {{ config('global.site_name') }} </a>
                    </li>
                </ul>


                <ul class="list-unstyled menu-categories" id="accordionExample">
                    <li class="menu">
                        <a href="{{ url('vendor/dashboard') }}" class="dropdown-toggle">
                            <div class="">
                                <i class="flaticon-computer-6 ml-3"></i>
                                <span>Dashboard</span>
                            </div>

                        </a>
                    </li>



                    <li class="menu">
                        <a href="#vendors" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                            <div class="">
                                <i class="flaticon-3d-cube"></i>
                                <span>Stores</span>
                            </div>
                            <div>
                                <i class="flaticon-right-arrow"></i>
                            </div>
                        </a>
                        <ul class="collapse submenu list-unstyled" id="vendors" data-parent="#accordionExample">
                            <li>
                                <a href="{{ url('vendor/store') }}">Stores </a>
                            </li>

                            <li>
                                <a href="{{ url('vendor/store_managers') }}"> Store Manager </a>
                            </li>

                            <li>
                                <a href="{{ url('vendor/designation') }}"> Designations </a>
                            </li>

                        </ul>

                    </li>

                     <li class="menu">
                        <a href="#Products" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                            <div class="">
                                <i class="flaticon-3d-cube"></i>
                                <span>My Products</span>
                            </div>
                            <div>
                                <i class="flaticon-right-arrow"></i>
                            </div>
                        </a>
                        <ul class="collapse submenu list-unstyled" id="Products" data-parent="#accordionExample">
                            <li>
                                <a href="{{ url('vendor/store') }}">Product list </a>
                            </li>

                            <li>
                                <a href="{{ url('vendor/import_export') }}"> Product Import/Export </a>
                            </li>


                        </ul>

                    </li>

                 
              
             
        

                    <li class="menu">
                        <a href="{{url('portal/my_profile')}}" class="dropdown-toggle">
                            <div>
                                <i class="flaticon-3d-cube"></i>
                                My Profile
                            </div>
                        </a>
                    </li>
                </ul>
            </nav>

        </div>

        <!--  END SIDEBAR  -->

        <!--  BEGIN CONTENT PART  -->
        <div id="content" class="main-content">
            <div class="container">
                <div class="page-header">
                    <div class="page-title">
                        <h3>{{ $page_heading ?? '' }}</h3>
                        <div class="crumbs">
                            <ul id="breadcrumbs" class="breadcrumb">
                                <li><a href="{{ url('vendor/dashboard') }}"><i class="flaticon-home-fill"></i></a>
                                </li>
                                <li><a onclick="window.history.back()" href="#">{{ $page_heading ?? '' }}</a>
                                </li>
                                <?php if(isset($mode)) { ?>
                                <li class="active"><a href="#">{{ $mode ?? '' }}</a></li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                </div>


                <!-- CONTENT AREA -->


                @yield('content')


                <!-- CONTENT AREA -->

            </div>
        </div>
        <!--  END CONTENT PART  -->
    </div>
    <!-- END MAIN CONTAINER -->

    <!--  BEGIN FOOTER  -->
    <footer class="footer-section theme-footer d-none">

        <div class="footer-section-1  sidebar-theme">

        </div>

        <div class="footer-section-2 container-fluid">
            <div class="row">
                <div id="toggle-grid" class="col-xl-7 col-md-6 col-sm-6 col-12 text-sm-left text-center">
                    <ul class="list-inline links ml-sm-5">

                    </ul>
                </div>
                <div class="col-xl-5 col-md-6 col-sm-6 col-12">
                    <ul
                        class="list-inline mb-0 d-flex justify-content-sm-end justify-content-center mr-sm-3 ml-sm-0 mx-3">
                        <li class="list-inline-item  mr-3">
                            <p class="bottom-footer">&#xA9; {{ date('Y') }} <a target="_blank"
                                    href="#">{{ config('global.site_name') }}</a></p>
                        </li>
                        <li class="list-inline-item align-self-center">
                            <div class="scrollTop"><i class="flaticon-up-arrow-fill-1"></i></div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    @yield('footer')
    <!--  END FOOTER  -->

    <!--  BEGIN CONTROL SIDEBAR  -->

    <!--  END CONTROL SIDEBAR  -->
    <div class="modal_loader">
        <!-- Place at bottom of page -->
    </div>
    <style>
        .modal_loader {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
            background: rgba(255, 255, 255, .8) url('https://i.stack.imgur.com/FhHRx.gif') 50% 50% no-repeat;
        }

        /* When the body has the loading class, we turn
    the scrollbar off with overflow:hidden */
        body.my-loading .modal_loader {
            overflow: hidden;
        }

        /* Anytime the body has the loading class, our
    modal element will be visible */
        body.my-loading .modal_loader {
            display: block;
        }

        .custom-file-label {
            overflow: hidden;
            white-space: nowrap;
            padding-right: 6em;
            text-overflow: ellipsis;
        }

        #image_crop_section {
            max-width: 100% !important;
        }
    </style>
    <!-- BEGIN GLOBAL MANDATORY SCRIPTS -->
    <script src="{{ asset('') }}admin-assets/assets/js/libs/jquery-3.1.1.min.js"></script>
    <script src="{{ asset('') }}admin-assets/bootstrap/js/popper.min.js"></script>
    <script src="{{ asset('') }}admin-assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="{{ asset('') }}admin-assets/plugins/scrollbar/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js"></script>
    {{-- <script src="{{asset('custom_js/')}}/jquery-validation/jquery.validate.min.js"></script> --}}
    <script src="{{ asset('admin-assets/assets/js/app.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/js/all.min.js" integrity="sha512-8pHNiqTlsrRjVD4A/3va++W1sMbUHwWxxRPWNyVlql3T+Hgfd81Qc6FC5WMXDC+tSauxxzp1tgiAvSKFu1qIlA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/js/solid.min.js" integrity="sha512-LKdDHe5ZhpmiH6Kd6crBCESKkS6ryNpGRoBjGeh5mM/BW3NRN4WH8pyd7lHgQTTHQm5nhu0M+UQclYQalQzJnw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"
        integrity="sha512-eyHL1atYNycXNXZMDndxrDhNAegH2BDWt1TmkXJPoGf1WLlNYt08CSjkqF5lnCRmdm3IrkHid8s2jOUY4NIZVQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/1.1.2/js/bootstrap-multiselect.min.js" integrity="sha512-lxQ4VnKKW7foGFV6L9zlSe+6QppP9B2t+tMMaV4s4iqAv4iHIyXED7O+fke1VeLNaRdoVkVt8Hw/jmZ+XocsXQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js" integrity="sha512-K/oyQtMXpxI4+K0W7H25UopjM8pzq0yrVdFdG21Fh5dBe91I40pDd9A4lzNlHPHBIP2cwZuoxaUSX0GJSObvGA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="text/javascript">
        jQuery(function() {
            App2.init();
            App.init({
                site_url: '{{ url('/') }}',
                base_url: '{{ url('/') }}',
                site_name: '{{ config('global.site_name') }}',
            });
            App.toast([]);

            App.initTreeView();
        });
        $('.date').datepicker({
            orientation: "bottom auto",
            todayHighlight: true,
            format: "yyyy-mm-dd",
            autoclose: true,
        });

        window.Parsley.addValidator('fileextension', {
            validateString: function(value, requirement) {
                var fileExtension = value.split('.').pop();
                extns = requirement.split(',');
                if (extns.indexOf(fileExtension.toLowerCase()) == -1) {
                    return fileExtension === requirement;
                }
            },
        });
        window.Parsley.addValidator('maxFileSize', {
            validateString: function(_value, maxSize, parsleyInstance) {
                var files = parsleyInstance.$element[0].files;
                return files.length != 1 || files[0].size <= maxSize * 1024;
            },
            requirementType: 'integer',
        });
        window.Parsley.addValidator('imagedimensions', {
            requirementType: 'string',
            validateString: function (value, requirement, parsleyInstance) {
                let file = parsleyInstance.$element[0].files[0];
                let [width, height] = requirement.split('x');
                let image = new Image();
                let deferred = $.Deferred();

                image.src = window.URL.createObjectURL(file);
                image.onload = function() {
                    if (image.width == width && image.height == height) {
                        deferred.resolve();
                    }
                    else {
                        deferred.reject();
                    }
                };

                return deferred.promise();
            },
            messages: {
                en: 'Image dimensions should be  %spx'
            }
        });


        // Handle record delete
        $('body').off('click', '[data-role="unlink"]');
        $('body').on('click', '[data-role="unlink"]', function(e) {
            e.preventDefault();
            var msg = $(this).data('message') || 'Are you sure that you want to delete this record?';
            var href = $(this).attr('href');

            App.confirm('Confirm Delete', msg, function() {
                var ajxReq = $.ajax({
                    url: href,
                    type: 'DELETE',
                    dataType: 'json',
                    data: {
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(res) {
                        if (res['status'] == 1) {
                            App.alert(res['message'] || 'Deleted successfully', 'Success!');
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);

                        } else {
                            App.alert(res['message'] || 'Unable to delete the record.',
                                'Failed!');
                        }
                    },
                    error: function(jqXhr, textStatus, errorMessage) {

                    }
                });
            });

        });

        $('body').off('click', '[data-role="approve"]');
        $('body').on('click', '[data-role="approve"]', function(e) {
            e.preventDefault();
            var msg = $(this).data('message') || 'Are you sure that you want to approve this record?';
            var href = $(this).attr('href');
            var title = $(this).data('title') || 'Confirm Approve';

            App.confirm(title, msg, function() {
                var ajxReq = $.ajax({
                    url: href,
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(res) {
                        if (res['status'] == 1) {
                            App.alert(res['message'] || 'Approved successfully', 'Success!');
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);

                        } else {
                            App.alert(res['message'] || 'Unable to approve the record.',
                                'Failed!');
                        }
                    },
                    error: function(jqXhr, textStatus, errorMessage) {

                    }
                });
            });

        });
        $('body').off('click', '[data-role="reject"]');
        $('body').on('click', '[data-role="reject"]', function(e) {
            e.preventDefault();
            var msg = $(this).data('message') || 'Are you sure that you want to reject this record?';
            var href = $(this).attr('href');
            var title = $(this).data('title') || 'Confirm Reject';

            App.confirm(title, msg, function() {
                var ajxReq = $.ajax({
                    url: href,
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(res) {
                        if (res['status'] == 1) {
                            App.alert(res['message'] || 'Rejected successfully', 'Success!');
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);

                        } else {
                            App.alert(res['message'] || 'Unable to reject the record.',
                                'Failed!');
                        }
                    },
                    error: function(jqXhr, textStatus, errorMessage) {

                    }
                });
            });

        });

        $(document).on('change', '.custom-file-input', function() {
            var file = $(this)[0].files[0].name;
            $(this).next('.custom-file-label').html(file);
        });

        $('body').off('click', '[data-role="verify"]');
        $('body').on('click', '[data-role="verify"]', function(e) {
            e.preventDefault();
            var href = $(this).attr('url');

            $.ajax({
                url: href,
                type: 'POST',
                dataType: 'json',
                data: {
                    "_token": "{{ csrf_token() }}",
                },
                success: function(res) {
                    if (res['status'] == 1) {
                        App.alert(res['message'] || 'Verified successfully', 'Success!');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);

                    } else {
                        App.alert(res['message'] || 'Unable to verify the record.', 'Failed!');
                    }
                },
                error: function(jqXhr, textStatus, errorMessage) {

                }
            });

        });
        $(".change_status").change(function() {
            status = 0;
            if (this.checked) {
                status = 1;
            }

            $.ajax({
                type: "POST",
                enctype: 'multipart/form-data',
                url: $(this).data('url'),
                data: {
                    "id": $(this).data('id'),
                    'status': status,
                    "_token": "{{ csrf_token() }}"
                },
                timeout: 600000,
                dataType: 'json',
                success: function(res) {
                    App.loading(false);

                    if (res['status'] == 0) {
                        var m = res['message']
                        App.alert(m, 'Oops!');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    } else {
                        App.alert(res['message']);
                    }
                },
                error: function(e) {
                    App.alert(e.responseText, 'Oops!');
                }
            });
        });
        $(document).on('keyup', 'input[type="text"],textarea', function() {
            _name = $(this).attr("name");
            _type = $(this).attr("type");
            if (_name == "email" || _name == "r_email" || _name == "password" || _type == "email" || _type ==
                "password" || $(this).hasClass("no-caps")) {
                return false;
            }
            txt = $(this).val();
            //$(this).val(txt.substr(0,1).toUpperCase()+txt.substr(1));
        });
        // Load Provinces on Country Change
        $('body').off('change', '[data-role="country-change"]');
        $('body').on('change', '[data-role="country-change"]', function() {
            var $t = $(this);
            var $dialcode = $('#'+ $t.data('input-dial-code'));
            var $state = $('#'+ $t.data('input-state'));

            if ( $dialcode.length > 0 ) {
                var code = $t.find('option:selected').data('phone-code');
                console.log(code)
                if ( code == '' ) {
                    $dialcode.val('');
                } else {
                    $dialcode.val(code);
                }
            }

            if ( $state.length > 0 ) {

                var id   = $t.val();
                var html = '<option value="">Select</option>';
                $state.html(html);
                $state.trigger('change');

                if ( id != '' ) {
                    $.ajax({
                        type: "POST",
                        enctype: 'multipart/form-data',
                        url: "{{url('vendor/states/get_by_country')}}",
                        data: {
                            "id": id,
                            "_token": "{{ csrf_token() }}"
                        },
                        timeout: 600000,
                        dataType: 'json',
                        success: function(res) {
                            for (var i=0; i < res['states'].length; i++) {
                                html += '<option value="'+ res['states'][i]['id'] +'">'+ res['states'][i]['name'] +'</option>';
                                if ( i == res['states'].length-1 ) {
                                    $state.html(html);
                                // $('.selectpicker').selectpicker('refresh')
                                }
                            }
                        }
                    });
                }
            }
        });
        $('body').off('change', '[data-role="state-change"]');
        $('body').on('change', '[data-role="state-change"]', function() {
            var $t = $(this);
            var $city = $('#'+ $t.data('input-city'));

            if ( $city.length > 0 ) {
                var id   = $t.val();
                var html = '<option value="">Select</option>';

                $city.html(html);
                if ( id != '' ) {
                    $.ajax({
                        type: "POST",
                        enctype: 'multipart/form-data',
                        url: "{{url('vendor/cities/get_by_state')}}",
                        data: {
                            "id": id,
                            "_token": "{{ csrf_token() }}"
                        },
                        timeout: 600000,
                        dataType: 'json',
                        success: function(res) {
                            for (var i=0; i < res['cities'].length; i++) {
                            html += '<option value="'+ res['cities'][i]['id'] +'">'+ res['cities'][i]['name'] +'</option>';
                            if ( i == res['cities'].length-1 ) {
                                $city.html(html);
    	                       // $('.selectpicker').selectpicker('refresh')
                            }
                        }
                        }
                    });
                }

            }
        });

        $('body').off('change', '[data-role="vendor-change"]');
        $('body').on('change', '[data-role="vendor-change"]', function() {
            var $t = $(this);
            var $city = $('#'+ $t.data('input-store'));

            if ( $city.length > 0 ) {
                var id   = $t.val();
                var html = '<option value="">Select</option>';

                $city.html(html);
                if ( id != '' ) {
                    $.ajax({
                        type: "POST",
                        enctype: 'multipart/form-data',
                        url: "{{url('vendor/store/get_by_vendor')}}",
                        data: {
                            "id": id,
                            "_token": "{{ csrf_token() }}"
                        },
                        timeout: 600000,
                        dataType: 'json',
                        success: function(res) {
                            for (var i=0; i < res['stores'].length; i++) {
                                html += '<option value="'+ res['stores'][i]['id'] +'">'+ res['stores'][i]['store_name'] +'</option>';
                                if ( i == res['stores'].length-1 ) {
                                    $city.html(html);
                                    // $('.selectpicker').selectpicker('refresh')
                                }
                            }
                        }
                    });
                }

            }
        });



        $(".flatpickr-input").flatpickr({enableTime: false,dateFormat: "Y-m-d"});

        let arrow = document.querySelectorAll(".arrow");
            for (var i = 0; i < arrow.length; i++) {
            arrow[i].addEventListener("click", (e)=>{
            let arrowParent = e.target.parentElement.parentElement;//selecting main parent of arrow
            arrowParent.classList.toggle("showMenu");
            });
            }

            let sidebar = document.querySelector(".sidebar");
            let sidebarBtn = document.querySelector(".bx-menu");
            console.log(sidebarBtn);
            sidebarBtn.addEventListener("click", ()=>{
            sidebar.classList.toggle("close");
            });

            $(".progress-bar-1").css('width', '30%');
            $(".progress-bar-2").css('width', '70%');


            const body = document.querySelector("body"),
            modeSwitch = body.querySelector(".toggle-switch"),
            modeText = body.querySelector(".mode-text");
            modeSwitch.addEventListener("click" , () =>{
                body.classList.toggle("dark");

                if(body.classList.contains("dark")){
                    modeText.innerText = "Light mode";
                }else{
                    modeText.innerText = "Dark mode";

                }
            });

    </script>
     <script>
            document.addEventListener("DOMContentLoaded", function() {

                var logoutLinks = document.querySelectorAll('.logout-link');


                logoutLinks.forEach(function(logoutLink) {
                    logoutLink.addEventListener('click', function(event) {

                        event.preventDefault();


                        Swal.fire({
                            title: 'Are you sure?',
                            text: "You are about to log out.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, log me out!'
                        }).then((result) => {

                            if (result.isConfirmed) {
                                window.location.href = logoutLink.getAttribute('href');
                            }
                        });
                    });
                });
            });
        </script>

    <!-- END GLOBAL MANDATORY SCRIPTS -->

    <!-- BEGIN PAGE LEVEL PLUGINS/CUSTOM SCRIPTS -->
    @yield('script')
    <!-- BEGIN PAGE LEVEL PLUGINS/CUSTOM SCRIPTS -->
</body>

</html>
