<?php
$_current_user = \Request::get('_current_user');
$CurrentUrl = url()->current();
?>
        <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no"/>
    <title>{{ config('global.site_name') }} | Admin</title>
    <link rel="shortcut icon" href="{{ asset('') }}admin-assets/assets/img/favicon/favicon.ico" type="image/x-icon"/>
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <!-- <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700' rel='stylesheet' type='text/css'> -->
    <link href="{{ asset('') }}admin-assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('') }}admin-assets/bootstrap/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css"/>
    <link href="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker3.min.css" rel="stylesheet"/>
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet"/>
    <link href="{{ asset('') }}admin-assets/assets/css/sidebar.css" rel="stylesheet" type="text/css"/>
    <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css"
            integrity="sha512-1sCRPdkRXhBV2PBLUdRb4tMg1w2YPf37qatUFeS7zlBy7jJI8Lf4VHwWfZZfpXtYSLy85pkm9GaYVYMfw5BC1A=="
            crossorigin="anonymous"
            referrerpolicy="no-referrer"
    />
    <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/solid.min.css"
            integrity="sha512-EvFBzDO3WBynQTyPJI+wLCiV0DFXzOazn6aoy/bGjuIhGCZFh8ObhV/nVgDgL0HZYC34D89REh6DOfeJEWMwgg=="
            crossorigin="anonymous"
            referrerpolicy="no-referrer"
    />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <link href="{{ asset('') }}admin-assets/assets/css/custom.css" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('') }}admin-assets/assets/css/plugins.css" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('') }}admin-assets/assets/css/parsley.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">
    <!-- include libraries(jQuery, bootstrap) -->

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="{{ asset('admin-assets/plugins/notification/toastr/toastr.min.css') }}" rel="stylesheet"
          type="text/css"/>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet"/>

    <!-- END GLOBAL MANDATORY STYLES -->

    <!-- BEGIN PAGE LEVEL PLUGINS/CUSTOM STYLES -->
    @yield('header')
    <!-- END PAGE LEVEL PLUGINS/CUSTOM STYLES -->

    <style>
        div.dt-container .dt-paging .dt-paging-button.current, div.dt-container .dt-paging .dt-paging-button.current:hover {
            color: #000000 !important;
            border: 1px solid #41DDBE;
            background-color: #41DDBE;
            background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #41DDBE), color-stop(100%, #41DDBE));
            background: -webkit-linear-gradient(top, #41DDBE 0%, #41DDBE 100%);
            background: -moz-linear-gradient(top, #41DDBE 0%, #41DDBE 100%);
            background: -ms-linear-gradient(top, #41DDBE 0%, #41DDBE 100%);
            background: -o-linear-gradient(top, #41DDBE 0%, #41DDBE 100%);
            background: linear-gradient(to bottom, #41DDBE 0%, #41DDBE 100%);
        }

        div.dt-container .dt-paging .dt-paging-button:hover {
            color: #000000 !important;
            border: 1px solid #41DDBE;
            background-color: #41DDBE;
            background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #41DDBE), color-stop(100%, #41DDBE));
            background: -webkit-linear-gradient(top, #41DDBE 0%, #41DDBE 100%);
            background: -moz-linear-gradient(top, #41DDBE 0%, #41DDBE 100%);
            background: -ms-linear-gradient(top, #41DDBE 0%, #41DDBE 100%);
            background: -o-linear-gradient(top, #41DDBE 0%, #41DDBE 100%);
            background: linear-gradient(to bottom, #41DDBE 0%, #41DDBE 100%);
        }
    </style>
</head>

<!-- <body class="default-sidebar"> -->

<body class="dark">
<!-- Tab Mobile View Header -->
<header class="tabMobileView header navbar fixed-top d-lg-none d-none">
    <div class="nav-toggle">
        <a href="javascript:void(0);" class="nav-link sidebarCollapse" data-placement="bottom">
            <i class="flaticon-menu-line-2"></i>
        </a>
        <a href="{{ url('/') }}" class=""> <img src="{{ asset('') }}admin-assets/assets/img/logo.png"
                                                class="img-fluid" alt="logo"/></a>
    </div>
    <ul class="nav navbar-nav">
        <li class="nav-item d-lg-none">
            <form class="form-inline justify-content-end" role="search">
                <input type="text" class="form-control search-form-control mr-3"/>
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
            <div class="dropdown-menu position-absolute" aria-labelledby="userProfileDropdown">
                {{-- <a class="dropdown-item" href="#"> <i class="mr-1 flaticon-user-6"></i> <span>My Profile</span> </a> --}}
                <a class="dropdown-item" href="{{ url('admin/change_password') }}"> <i class="mr-1 flaticon-key-2"></i>
                    <span>Change Password</span> </a>
                <div class="dropdown-divider"></div>
                <a class="logout-link dropdown-item" href="{{ url('admin/logout') }}"> <i
                            class="mr-1 flaticon-power-button"></i> <span>Log Out</span> </a>
            </div>
        </li>

        <li class="nav-item dropdown cs-toggle order-lg-0 order-3" style="display: none;">
            <a href="#" class="nav-link toggle-control-sidebar suffle">
                <span class="flaticon-menu-dot-fill d-lg-inline-block d-none"></span>
                <span class="flaticon-dots d-lg-none"></span>
            </a>
        </li>
    </ul>
</header>
<!--  END NAVBAR  -->

<div class="sidebar">
    <div class="logo-details mt-2">
        <a href="#">
            <i class="bx bx-menu"></i>
        </a>

        <a href="{{ url('admin/dashboard') }}">
            <img src="{{ asset('') }}admin-assets/assets/img/logo.png" class="img-fluid" alt=""/>
        </a>
        <!-- <i class='bx bxl-c-plus-plus'></i> -->
    </div>
    <ul class="nav-links">
        <li>
            <a href="{{ url('admin/dashboard') }}">
                <i class="bx bx-grid-alt"></i>
                <span class="link_name"><span class="name"></span> Dashboard</span>
            </a>
        </li>
        @if ( GetUserPermissions("admin_users_view") )
            <?php $patterns = array('admin\/admin_users', 'admin\/admin_user_designation');
            $patterns_flattened = implode('|', $patterns);?>
            <li class="menu-children {{preg_match('/'.$patterns_flattened.'/', $CurrentUrl) ? 'showMenu' : null}}">
                <div class="iocn-link">
                    <a href="#">
                        <i class="bx bx-user"></i>
                        <span class="link_name">Admin Users</span>
                    </a>
                    <i class="bx bxs-chevron-down arrow"></i>
                </div>
                <ul class="sub-menu">
                    <li>
                        <a class="link_name" href="#">Admin Users</a>
                    </li>
                    @if ( GetUserPermissions("admin_users_view") )
                        <li>
                            <a href="{{ url('admin/admin_users') }}">Admin User</a>
                        </li>
                    @endif

                </ul>
            </li>
        @endif

        <?php $patterns = array('admin\/customers');
        $patterns_flattened = implode('|', $patterns);?>
        <li class="menu-children {{preg_match('/'.$patterns_flattened.'/', $CurrentUrl) ? 'showMenu' : null}}">
            <div class="iocn-link">
                <a href="#">
                    <i class="bx bx-user-circle"></i>
                    <span class="link_name">Customers</span>
                </a>
                <i class="bx bxs-chevron-down arrow"></i>
            </div>
            <ul class="sub-menu">
                <li>
                    <a class="link_name" href="#">Customers</a>
                </li>
                <li>
                    <a href="{{ url('admin/customers') }}">Customers list</a>
                </li>
            </ul>
        </li>
        @if ( GetUserPermissions("vendor_view") || GetUserPermissions("vendor_earning") || GetUserPermissions("vendor_earning"))
            <?php $patterns = array('admin\/vendors', 'admin\/earnings', 'admin\/service_earnings');
            $patterns_flattened = implode('|', $patterns);?>
            <li class="menu-children {{preg_match('/'.$patterns_flattened.'/', $CurrentUrl) ? 'showMenu' : null}}">
                <div class="iocn-link">
                    <a href="#">
                        <i class="bx bx-user-circle"></i>
                        <span class="link_name">Vendors</span>
                    </a>
                    <i class="bx bxs-chevron-down arrow"></i>
                </div>
                <ul class="sub-menu">
                    <li>
                        <a class="link_name" href="#">Vendors</a>
                    </li>

                    @if ( GetUserPermissions("vendor_view") )
                        <li>
                            <a href="{{ url('admin/vendors') }}">Vendor list</a>
                        </li>
                    @endif
                    @if ( GetUserPermissions("vendor_earning") )
                        <li>
                            <a href="{{ url('admin/earnings') }}">E-Commerce Earnings</a>
                        </li>
                    @endif

                </ul>
            </li>
        @endif



        @if ( GetUserPermissions("products_view") )
            <?php $patterns = array('admin\/product');
            $food_ac = get_food_activity();
            $patterns_flattened = implode('|', $patterns);?>
            <li class="menu-children {{preg_match('/'.$patterns_flattened.'/', $CurrentUrl) ? 'showMenu' : null}}">
                <div class="iocn-link">
                    <a href="#">
                        <!--<i class="flaticon-display"></i>-->
                        <i class="bx bx-box"></i>
                        <span class="link_name">Products</span>
                    </a>
                    <i class="bx bxs-chevron-down arrow"></i>
                </div>
                <ul class="sub-menu">
                    @if ( GetUserPermissions("products_view") )
                        <li>
                            <a href="{{url('admin/products')}}"> Products </a>
                        </li>
                    @endif

                    <!-- <li>
                            <a href="{{url('admin/import_export')}}"> Product Import/Export </a>
                        </li> -->
                    <!--  <li>
                            <a href="{{url('admin/coupons')}}"> Coupons </a>
                        </li> -->
                </ul>
            </li>
        @endif


        @if ( GetUserPermissions("orders_view") )
            <?php $patterns = array('admin\/orders');
            $patterns_flattened = implode('|', $patterns);?>
            <li class="{{preg_match('/'.$patterns_flattened.'/', $CurrentUrl) ? 'active' : null}}">
                <a href="{{url('admin/orders')}}">
                    <i class='bx bx-cart-alt'></i>
                    <span class="link_name">Orders</span>
                </a>
                <ul class="sub-menu blank d-none">
                    <li>
                        <a class="link_name" href="{{url('admin/orders')}}">Orders</a>
                    </li>
                </ul>
            </li>
        @endif



        @if ( GetUserPermissions("service_request_view") )
            <?php $patterns = array('admin\/report\/refund_request', 'admin\/report\/refund_request_services');
            $patterns_flattened = implode('|', $patterns);?>
            <li class="menu-children {{preg_match('/'.$patterns_flattened.'/', $CurrentUrl) ? 'showMenu' : null}}"
                style="display:none;">
                <div class="iocn-link">
                    <a href="#">
                        <i class='bx bx-file'></i>
                        <span class="link_name">Refund request</span>
                    </a>
                    <i class='bx bxs-chevron-down arrow'></i>
                </div>
                <ul class="sub-menu">
                    @if ( GetUserPermissions("service_request_view") )

                        <li>
                            <a href="{{url('admin/report/refund_request_services')}}">Refund request Services</a>
                        </li>
                    @endif

                </ul>
            </li>
        @endif




        @if ( GetUserPermissions("transport_view") )
            <?php $patterns = array('admin\/transport');
            $patterns_flattened = implode('|', $patterns);?>
            <li class="menu-children d-none {{preg_match('/'.$patterns_flattened.'/', $CurrentUrl) ? 'showMenu' : null}}">

                <a href="{{url('admin/transport')}}">
                    <i class='bx bx-box'></i>
                    <span class="link_name">Transport</span>
                </a>
                <ul class="sub-menu blank">
                    <li>
                        <a class="link_name" href="{{url('admin/transport')}}">Transport</a>
                    </li>
                </ul>

            </li>
        @endif


        @if ( GetUserPermissions("coupon_view") )
            <?php $patterns = array('admin\/coupons');
            $patterns_flattened = implode('|', $patterns);?>
            <li class="{{preg_match('~\b'.$patterns_flattened.'\b~', $CurrentUrl) ? 'active' : null}}">

                <a href="{{url('admin/coupons')}}">
                    <i class='bx bx-box'></i>
                    <span class="link_name">Promo Code</span>
                </a>
                <ul class="sub-menu blank">
                    <li>
                        <a class="link_name" href="{{url('admin/coupons')}}">Promo Code</a>
                    </li>
                </ul>

            </li>
        @endif

        <li class="">
            <a href="{{ route('admin.campaigns.index') }}">
                <i class='bx bx-box'></i>
                <span class="link_name">Campaigns</span>
            </a>
            <ul class="sub-menu blank">
                <li>
                    <a class="link_name" href="{{ route('admin.campaigns.index') }}">Campaigns</a>
                </li>
            </ul>
        </li>

        <li class="">
            <a href="{{ route('admin.cms.index') }}">
                <i class='bx bx-book'></i>
                <span class="link_name">CMS Pages</span>
            </a>
            <ul class="sub-menu blank">
                <li>
                    <a class="link_name" href="{{ route('admin.cms.index') }}">CMS Pages</a>
                </li>
            </ul>
        </li>

        <li class="">
            <a href="{{ route('admin.faq.index') }}">
                <i class='bx bx-book'></i>
                <span class="link_name">FAQs</span>
            </a>
            <ul class="sub-menu blank">
                <li>
                    <a class="link_name" href="{{ route('admin.faq.index') }}">FAQs</a>
                </li>
            </ul>
        </li>

        <li class="">
            <a href="{{ route('admin.promo-codes.index') }}">
                <i class='bx bx-gift'></i>
                <span class="link_name">Promo codes</span>
            </a>
            <ul class="sub-menu blank">
                <li>
                    <a class="link_name" href="{{ route('admin.promo-codes.index') }}">Promo Codes</a>
                </li>
            </ul>
        </li>

        <li class="">
            <a href="{{ route('admin.winners') }}">
                <i class='bx bx-trophy'></i>
                <span class="link_name">Winners</span>
            </a>
            <ul class="sub-menu blank">
                <li>
                    <a class="link_name" href="{{ route('admin.winners') }}">Winners</a>
                </li>
            </ul>
        </li>


        <?php $patterns = array('admin\/contract_maintenance');
        $patterns_flattened = implode('|', $patterns);?>
                <!--<li class="menu-children {{preg_match('/'.$patterns_flattened.'/', $CurrentUrl) ? 'showMenu' : null}}">-->
        <!--    <div class="iocn-link">-->
        <!--        <a href="#">-->
        <!--            <i class="bx bx-box"></i>-->
        <!--            <span class="link_name">Contract & Maintenance </span>-->
        <!--        </a>-->
        <!--        <i class="bx bxs-chevron-down arrow"></i>-->
        <!--    </div>-->
        <!--    <ul class="sub-menu">-->
        <!--        <li>-->
        <!--            <a class="link_name" href="#">Services</a>-->
        <!--        </li>-->


        <!--        <li>-->
        <!--            <a href="{{url('admin/service_request')}}">Contract job</a>-->
        <!--        </li>-->

        <!--    </ul>-->
        <!--</li>-->
        <?php $patterns = array('admin\/product_master', 'admin\/category', 'admin\/building_type', 'admin\/product_attribute', 'admin\/service_category', 'admin\/store_type', 'admin\/bank', 'admin\/country', 'admin\/states', 'admin\/cities', 'admin\/areas', 'admin\/category', 'admin\/brnad', 'admin\/health_and_beauty_category', 'admin\/cuisines', 'admin\/servicetype');
        $patterns_flattened = implode('|', $patterns);?>
        <li class="menu-children {{preg_match('/'.$patterns_flattened.'/', $CurrentUrl) ? 'showMenu' : null}}">
            <div class="iocn-link">
                <a href="#">
                    <i class="bx bx-cube"></i>
                    <span class="link_name">Masters</span>
                </a>
                <i class="bx bxs-chevron-down arrow"></i>
            </div>
            <ul class="sub-menu">
                <li>
                    <a class="link_name" href="#">Masters</a>
                </li>
                @if ( GetUserPermissions("bank") )
                    <li>
                        <a href="{{ url('admin/bank') }}">Bank </a>
                    </li>
                @endif
                <li>
                    <a href="{{ url('admin/country') }}"> Country </a>
                </li>
                <li>
                    <a href="{{ url('admin/cities') }}"> Cities </a>
                </li>
                @if ( GetUserPermissions("masters_view") )
                    <li>
                        <a href="{{ url('admin/areas') }}"> Areas </a>
                    </li>
                @endif
                @if ( GetUserPermissions("masters_view") )
                    <li>
                        <a href="{{ url('admin/cuisines') }}">Cuisines List </a>
                    </li>
                @endif


                @if ( GetUserPermissions("masters_view") )
                    <li>
                        <a href="{{ url('admin/category') }}">Product Categories </a>
                    </li>
                @endif
                @if ( GetUserPermissions("masters_view") )
                    <li>
                        <a href="{{ url('admin/brand') }}">Product Brands </a>
                    </li>
                @endif
                @if ( GetUserPermissions("masters_view") )
                    <li>
                        <a href="{{ url('admin/product_attribute') }}">Product Attributes </a>
                    </li>
                @endif

                <!--  <li>
                            <a href="{{ url('admin/coupons') }}"> Coupons </a>
                        </li> -->
            </ul>
        </li>

        <?php $patterns = array('admin\/report\/commission_service', 'admin\/report\/commission',
            'admin\/report\/orders', 'admin\/report\/vendors', 'admin\/report\/customers');
        $patterns_flattened = implode('|', $patterns);?>
        <li class="menu-children {{preg_match('/'.$patterns_flattened.'/', $CurrentUrl) ? 'showMenu' : null}}">
            <div class="iocn-link">
                <a href="#">
                    <i class='bx bxs-report'></i>
                    <span class="link_name">Reports</span>
                </a>
                <i class='bx bxs-chevron-down arrow'></i>
            </div>
            <ul class="sub-menu">
                <li>
                    <a class="link_name" href="#">Reports</a>
                </li>
                <li>
                    <a href="{{url('admin/report/customers')}}">Customer</a>
                </li>
                <li>
                    <a href="{{ route('admin.reports.campaign-report') }}">Campaign Report</a>
                </li>

            </ul>
        </li>


        @if ( GetUserPermissions("rating_view") )

            <?php $patterns = array('admin\/rating');
            $patterns_flattened = implode('|', $patterns);?>
            <li class="{{preg_match('/'.$patterns_flattened.'/', $CurrentUrl) ? 'active' : null}}">
                <a href="{{url('admin/rating')}}">
                    <i class='bx bxs-report'></i>
                    <span class="link_name">Rating</span>
                </a>
                <ul class="sub-menu blank">
                    <li>
                        <a class="link_name" href="{{url('admin/rating')}}">Rating and Reviews</a>
                    </li>
                </ul>

            </li>
        @endif


        @if ( GetUserPermissions("banners_view") )
            <?php $patterns = array('admin\/banners', 'banner\/create', 'banner\/edit');
            $patterns_flattened = implode('|', $patterns);?>
            <li class="{{preg_match('/'.$patterns_flattened.'/', $CurrentUrl) ? 'active' : null}}">
                <a href="{{url('admin/banners')}}">
                    <i class='bx bx-images'></i>
                    <span class="link_name">App Banners</span>
                </a>
                <ul class="sub-menu blank">
                    <li>
                        <a class="link_name" href="{{url('admin/banners')}}">App Banners</a>
                    </li>
                </ul>
            </li>
            <?php $patterns = array('admin\/promotion', 'promotion\/create', 'promotion\/edit');
            $patterns_flattened = implode('|', $patterns);?>
            <li class="{{preg_match('/'.$patterns_flattened.'/', $CurrentUrl) ? 'active' : null}}">
                <a href="{{url('admin/promotion')}}">
                    <i class='bx bx-images'></i>
                    <span class="link_name">Promotion Banners</span>
                </a>
                <ul class="sub-menu blank">

                    <li>
                        <a class="link_name" href="{{url('admin/promotion')}}">Promotion Banners</a>
                    </li>
                </ul>
            </li>
        @endif

        @if ( GetUserPermissions("cms_pages_view") || GetUserPermissions("faqs_view") || GetUserPermissions("contact_detail_settings_view") || GetUserPermissions("settings_view"))
            <?php $patterns = array('admin\/cms_pages', 'admin\/faq', 'admin\/contact_details', 'admin\/settings');
            $patterns_flattened = implode('|', $patterns);?>
            <li class="menu-children {{preg_match('/'.$patterns_flattened.'/', $CurrentUrl) ? 'showMenu' : null}}">
                <div class="iocn-link">
                    <a href="#">
                        <i class="bx bx-file"></i>
                        <span class="link_name">Pages</span>
                    </a>
                    <i class="bx bxs-chevron-down arrow"></i>
                </div>
                <ul class="sub-menu">
                    <li>
                        <a class="link_name" href="#">Pages</a>
                    </li>
                    @if ( GetUserPermissions("cms_pages_view") )
                        <li>
                            <a href="{{url('admin/cms_pages')}}"> CMS Pages </a>
                        </li>
                    @endif
                    @if ( GetUserPermissions("faqs_view") )
                        <li>
                            <a href="{{url('admin/faq')}}"> FAQ </a>
                        </li>
                    @endif
                    <!-- <li>
                        <a href="{{url('admin/help')}}"> Help </a>
                        </li> -->
                    @if ( GetUserPermissions("contact_detail_settings_view") )
                        <li>
                            <a href="{{url('admin/contact_details')}}"> Contact Details Settings </a>
                        </li>
                    @endif
                    @if ( GetUserPermissions("contact_detail_settings_view") )
                        <li>
                            <a href="{{url('admin/landing_page_details')}}"> Landing Page Settings </a>
                        </li>
                    @endif
                    @if ( GetUserPermissions("settings_view") )
                        <li>
                            <a href="{{url('admin/settings')}}"> Settings </a>
                        </li>
                    @endif

                    @if ( GetUserPermissions("testimonials") )
                        <li>
                            <a href="{{url('admin/testimonials')}}"> Testimonials </a>
                        </li>
                    @endif
                    @if ( GetUserPermissions("subscriberemails") )
                        <li>
                            <a href="{{url('admin/subscriberemails')}}"> Subscriber Emails </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        <?php $patterns = array('admin\/custom_banners', 'admin\/custom_banner\/create', 'admin\/custom_banner\/edit');
        $patterns_flattened = implode('|', $patterns);?>
        <li class="{{preg_match('/'.$patterns_flattened.'/', $CurrentUrl) ? 'active' : null}}">
            <a href="{{ route('admin.app-banners.index') }}">
                <i class='bx bx-images'></i>
                <span class="link_name">App Custom Banners</span>
            </a>
            <ul class="sub-menu blank">

                <li>
                    <a class="link_name" href="{{ route('admin.app-banners.index') }}">App Custom Banners</a>
                </li>
            </ul>
        </li>

        {{-- <?php $patterns = array('admin\/home_section_list');
          $patterns_flattened = implode('|', $patterns);?>
           <li class="{{preg_match('/'.$patterns_flattened.'/', $CurrentUrl) ? 'active' : null}}">
              <a href="{{url('admin/home_section_list')}}">
                  <i class='bx bx-images'></i>
                  <span class="link_name">App Home Sections</span>
              </a>
              <ul class="sub-menu blank">

                  <li>
                  <a class="link_name" href="{{url('admin/home_section_list')}}">App Home Sections</a>
                  </li>
              </ul>
          </li>--}}




        @if ( GetUserPermissions("notification_view") )
            <?php $patterns = array('admin\/notifications');
            $patterns_flattened = implode('|', $patterns);?>
            <li class="{{preg_match('/'.$patterns_flattened.'/', $CurrentUrl) ? 'active' : null}}">
                <a href="{{url('admin/notifications')}}">
                    <i class='bx bx-bell'></i>
                    <span class="link_name">Notification</span>
                </a>
                <ul class="sub-menu blank">
                    <li>
                        <a class="link_name" href="{{url('admin/notifications')}}">Notification</a>
                    </li>
                </ul>

            </li>
        @endif

        <li class="mode d-none">
            <div class="sun-moon">
                <i class="bx bx-moon icon moon"></i>
                <i class="bx bx-sun icon sun"></i>
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
    <div class="p-3 mb-2 container custom-header p-0">
        <div class="d-flex align-items-center justify-content-between p-2">
            <div class="d-flex align-items-center">
                <!-- <div class="home-content">
            <i class='bx bx-menu'></i>
          </div> -->
                <a href="{{ url('admin/dashboard') }}"
                   class="d-flex align-items-center mb-2 mb-lg-0 text-dark text-decoration-none">
                    <img src="{{ asset('') }}admin-assets/assets/img/logo.png" class="img-fluid brand-logo" alt=""/>
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
                    <button class="dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                        <span class="profile-name">Hi, {{ Auth::user()->name }}</span>
                        <img src="{{ asset('') }}admin-assets/assets/img/profile-icon.svg" alt="mdo" width="32"
                             height="32" class="rounded-circle"/>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <!--  <a class="dropdown-item" href="{{ url('admin/dashboard') }}"><i class="bx bx-grid-alt"></i> Dashboard</a> -->
                        <a class="dropdown-item" href="{{ url('admin/change_password') }}"><i class="bx bxs-key"></i>
                            Change Password</a>
                        <!-- <a class="dropdown-item" href="{{ url('admin/logout') }}"><i class="bx bx-log-out"></i> Log Out</a> -->
                        <a class="logout-link dropdown-item" href="{{ url('admin/logout') }}"><i
                                    class="bx bx-log-out"></i> Log Out</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <div class="row">
                <div class="col-6">
                    <div class="page-title">
                        <h3>{{ $page_heading ?? '' }}</h3>
                        <div class="crumbs">
                            <ul id="breadcrumbs" class="breadcrumb">
                                <li>
                                    <a href="{{ url('admin/dashboard') }}"><i class="flaticon-home-fill"></i></a>
                                </li>
                                <li><a onclick="window.history.back()" href="#">{{ $page_heading ?? '' }}</a></li>
                                <?php if(isset($mode)) { ?>
                                <li class="active"><a href="#">{{ $mode ?? '' }}</a></li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-6 text-right">
                    @yield('buttons')
                </div>
            </div>
            <label for="" id="main-error" class="error d-none"></label>
        </div>
    </div>

    <div class="container">
        @yield('content')
    </div>

    <footer class="container-fluid text-center">
        <!-- Copyright -->
        <div class="text-center p-3">
            <p class="bottom-footer mb-0 text-white">&#xA9; {{ date('Y') }} <a target="_blank" class="text-white"
                                                                               href="#">{{ config('global.site_name') }}</a>
            </p>
        </div>

        <!-- Copyright -->
    </footer>

    @yield('footer')
</section>

<!--  BEGIN MAIN CONTAINER  -->
<div class="container d-none" id="container">
    <div class="overlay"></div>
    <div class="cs-overlay"></div>

    <!--  BEGIN SIDEBAR  -->

    <div class="sidebar-wrapper sidebar-theme d-none">
        <div id="dismiss" class="d-lg-none"><i class="flaticon-cancel-12"></i></div>

        <nav id="sidebar">
            <ul class="navbar-nav theme-brand flex-row d-none d-lg-flex justify-content-center">
                <li class="nav-item d-flex justify-content-center">
                    <a href="{{ url('admin/dashboard') }}" class="navbar-brand">
                        <img src="{{ asset('') }}admin-assets/assets/img/logo.jpeg" class="img-fluid" alt="logo"
                             style="width: 212px;"/>
                    </a>
                    <!--<p class="border-underline"></p>-->
                </li>
                <!--<li class="nav-item theme-text">-->
                <!--    <a href="{{ url('admin/dashboard') }}" class="nav-link" style="color: black!important"> {{ config('global.site_name') }} </a>-->
                <!--</li>-->
            </ul>

            <ul class="list-unstyled menu-categories" id="accordionExample">
                <li class="menu">
                    <a href="{{ url('admin/dashboard') }}" class="dropdown-toggle">
                        <div class="">
                            <i class="flaticon-computer-6 ml-3"></i>
                            <span>Dashboard</span>
                        </div>
                    </a>
                </li>

                <li class="menu">
                    <a href="#adminusers" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <div class="">
                            <!--<i class="flaticon-3d-cube"></i>-->
                            <i class="flaticon-3d-cube fa-store fontawesome-icon"></i>
                            <span>Admin Users</span>
                        </div>
                        <div>
                            <i class="flaticon-right-arrow"></i>
                        </div>
                    </a>
                    <ul class="collapse submenu list-unstyled" id="adminusers" data-parent="#accordionExample">
                        <li>
                            <a href="{{ url('admin/admin_users') }}">Admin Users</a>
                        </li>

                        <li>
                            <a href="{{ url('admin/admin_user_designation') }}">Admin User Designation</a>
                        </li>
                    </ul>
                </li>

                <li class="menu">
                    <a href="#vendors" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <div class="">
                            <!--<i class="flaticon-3d-cube"></i>-->
                            <i class="fa-solid fa-store fontawesome-icon"></i>
                            <span>Users</span>
                        </div>
                        <div>
                            <i class="flaticon-right-arrow"></i>
                        </div>
                    </a>
                    <ul class="collapse submenu list-unstyled" id="vendors" data-parent="#accordionExample">
                        <li>
                            <a href="{{ url('admin/customers') }}">Customers </a>
                        </li>
                        <li>
                            <a href="{{ url('admin/vendors') }}">Vendors </a>
                        </li>
                        <li>
                            <a href="{{ url('admin/store') }}">Stores </a>
                        </li>

                        <li>
                            <a href="{{ url('admin/store_managers_type') }}">Stores managers type</a>
                        </li>

                        <li>
                            <a href="{{ url('admin/store_managers') }}">Store managers </a>
                        </li>
                    </ul>
                </li>

                <li class="menu">
                    <a href="#product" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <div class="">
                            <!--<i class="flaticon-display"></i>-->
                            <i class="fa-solid fa-box fontawesome-icon"></i>
                            <span>Products</span>
                        </div>
                        <div>
                            <i class="flaticon-right-arrow"></i>
                        </div>
                    </a>
                    <ul class="collapse submenu list-unstyled" id="product" data-parent="#accordionExample">
                        <li>
                            <a href="{{url('admin/products')}}"> Products </a>
                        </li>
                        <li>
                            <a href="{{url('admin/import_export')}}"> Product Import/Export </a>
                        </li>
                        <li>
                            <a href="{{url('admin/coupons')}}"> Coupons </a>
                        </li>
                    </ul>
                </li>


                <li class="menu">
                    <a href="#components" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <div class="">
                            <i class="flaticon-3d-cube"></i>
                            <span>Masters</span>
                        </div>
                        <div>
                            <i class="flaticon-right-arrow"></i>
                        </div>
                    </a>
                    <ul class="collapse submenu list-unstyled" id="components" data-parent="#accordionExample">
                        <li>
                            <a href="{{ url('admin/category') }}">Category </a>
                        </li>

                        <li>
                            <a href="{{ url('admin/brand') }}">Brand </a>
                        </li>

                        <li>
                            <a href="{{ url('admin/product_attribute') }}">Product Attribute </a>
                        </li>
                        <li>
                            <a href="{{ url('admin/industry_type') }}">Industry Types </a>
                        </li>
                        <li>
                            <a href="{{ url('admin/store_type') }}">Store Types </a>
                        </li>
                        <li>
                            <a href="{{ url('admin/country') }}"> Country </a>
                        </li>
                        <li>
                            <a href="{{ url('admin/states') }}"> States </a>
                        </li>
                        <li>
                            <a href="{{ url('admin/cities') }}"> Cities </a>
                        </li>

                        <li>
                            <a href="{{ url('admin/bank') }}"> Banks </a>
                        </li>
                        <li>
                            <a href="{{ url('admin/moda_category') }}"> Moda Categories </a>
                        </li>
                        <li>
                            <a href="{{ url('admin/skin_color') }}"> Skin Colors </a>
                        </li>
                        <li>
                            <a href="{{ url('admin/hair_color') }}"> Hair Colors </a>
                        </li>
                    </ul>
                </li>

                <li class="menu">
                    <a href="{{url('admin/orders')}}" class="dropdown-toggle">
                        <div class="">
                            <i class="flaticon-3d-cube"></i>
                            <span>Orders</span>
                        </div>
                    </a>
                </li>

                <li class="menu">
                    <a href="#banner" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <div class="">
                            <!--<i class="flaticon-display"></i>-->
                            <i class="fa-solid fa-images fontawesome-icon"></i>
                            <span>Banners</span>
                        </div>
                        <div>
                            <i class="flaticon-right-arrow"></i>
                        </div>
                    </a>
                    <ul class="collapse submenu list-unstyled" id="banner" data-parent="#accordionExample">
                        <li>
                            <a href="{{url('admin/banners')}}"> App Banners </a>
                        </li>
                        <!-- <li>
                                <a href="{{url('admin/web_banners')}}"> Web Banners </a>
                            </li> -->
                    </ul>
                </li>

{{--                <li class="menu">--}}
{{--                    <a href="#Pages" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">--}}
{{--                        <div class="">--}}
{{--                            <!--<i class="flaticon-display"></i>-->--}}
{{--                            <i class="fa-solid fa-file-lines fontawesome-icon"></i>--}}
{{--                            <span>Pages</span>--}}
{{--                        </div>--}}
{{--                        <div>--}}
{{--                            <i class="flaticon-right-arrow"></i>--}}
{{--                        </div>--}}
{{--                    </a>--}}
{{--                    <ul class="collapse submenu list-unstyled" id="Pages" data-parent="#accordionExample">--}}
{{--                        <li>--}}
{{--                            <a href="{{url('admin/cms_pages')}}"> CMS Pages </a>--}}
{{--                        </li>--}}
{{--                        <li>--}}
{{--                            <a href="{{url('admin/faq')}}" class="dropdown-toggle">--}}
{{--                                FAQ--}}
{{--                            </a>--}}
{{--                        </li>--}}
{{--                        <li>--}}
{{--                            <a href="{{url('admin/contact_details')}}"> Contact Details Settings </a>--}}
{{--                        </li>--}}
{{--                        <li>--}}
{{--                            <a href="{{url('admin/settings')}}"> Settings </a>--}}
{{--                        </li>--}}
{{--                    </ul>--}}
{{--                </li>--}}

{{--                <li class="menu">--}}
{{--                    <a href="{{url('admin/notifications')}}" class="dropdown-toggle">--}}
{{--                        <div class="">--}}
{{--                            <!--<i class="flaticon-gear"></i>-->--}}
{{--                            <i class="fa-solid fa-bell fontawesome-icon"></i>--}}
{{--                            <span>Notification</span>--}}
{{--                        </div>--}}
{{--                    </a>--}}
{{--                </li>--}}

                {{--
                <li class="menu">
                    <a href="{{ url('admin/verified_projects') }}" class="dropdown-toggle">
                        <div class="">
                            <i class="flaticon-email-1"></i>
                            <span>Home Banners</span>
                        </div>
                    </a>
                </li>
                --}}

                <li class="menu">
                    <a href="#reports" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                        <div class="">
                            <!--<i class="flaticon-display"></i>-->
                            <i class="fa-solid fa-images fontawesome-icon"></i>
                            <span>Reports</span>
                        </div>
                        <div>
                            <i class="flaticon-right-arrow"></i>
                        </div>
                    </a>
                    <ul class="collapse submenu list-unstyled" id="reports" data-parent="#accordionExample">
                        <li>
                            <a href="{{url('admin/report/vendors')}}"> Vendors </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>

    <!--  END SIDEBAR  -->

    <!--  BEGIN CONTENT PART  -->
    <div id="content" class="main-content d-none">
        <div class="container">
            <div class="page-header">
                <div class="page-title">
                    <h3>{{ $page_heading ?? '' }}</h3>
                    <div class="crumbs">
                        <ul id="breadcrumbs" class="breadcrumb">
                            <li>
                                <a href="{{ url('admin/dashboard') }}"><i class="flaticon-home-fill"></i></a>
                            </li>
                            <li><a onclick="window.history.back()" href="#">{{ $page_heading ?? '' }}</a></li>
                            <?php if(isset($mode)) { ?>
                            <li class="active"><a href="#">{{ $mode ?? '' }}</a></li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- CONTENT AREA -->

            {{-- @yield('content') --}}

            <!-- CONTENT AREA -->
        </div>
    </div>
    <!--  END CONTENT PART  -->
</div>
<!-- END MAIN CONTAINER -->

<!--  BEGIN FOOTER  -->
<footer class="footer-section theme-footer d-none">
    <div class="footer-section-1 sidebar-theme"></div>

    <div class="footer-section-2 container-fluid">
        <div class="row">
            <div id="toggle-grid" class="col-xl-7 col-md-6 col-sm-6 col-12 text-sm-left text-center">
                <ul class="list-inline links ml-sm-5"></ul>
            </div>
            <div class="col-xl-5 col-md-6 col-sm-6 col-12">
                <ul class="list-inline mb-0 d-flex justify-content-sm-end justify-content-center mr-sm-3 ml-sm-0 mx-3">
                    <li class="list-inline-item mr-3">
                        <p class="bottom-footer">&#xA9; {{ date('Y') }} <a target="_blank"
                                                                           href="#">{{ config('global.site_name') }}</a>
                        </p>
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
<!-- Modal -->
<div class="modal fade" id="changepassword" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Change password</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form method="post" id="admin-form" action="{{url('admin/change_user_password')}}"
                      enctype="multipart/form-data" data-parsley-validate="true">
                    @csrf()
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Password</label>
                                <input type="hidden" name="id" value="" id="userid"/>
                                <input
                                        type="password"
                                        name="password"
                                        id="password"
                                        class="form-control jqv-input"
                                        data-jqv-required="true"
                                        required
                                        data-parsley-required-message="Enter Password"
                                        data-parsley-minlength="8"
                                        autocomplete="off"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Re-enter Password</label>
                                <input
                                        type="password"
                                        name="new_pswd"
                                        class="form-control jqv-input"
                                        data-parsley-minlength="8"
                                        data-parsley-equalto="#password"
                                        autocomplete="off"
                                        required
                                        data-parsley-required-message="Please re-enter password."
                                        data-parsley-required-if="#password"
                                        data-parsley-trigger="keyup"
                                />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Change</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
        background: rgba(255, 255, 255, 0.8) url("https://i.stack.imgur.com/FhHRx.gif") 50% 50% no-repeat;
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
<script src="{{ asset('') }}admin-assets/bootstrap/js/bootstrap-multiselect.js"></script>
<script src="{{ asset('') }}admin-assets/plugins/scrollbar/jquery.mCustomScrollbar.concat.min.js"></script>
<script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
{{--
<script src="{{asset('custom_js/')}}/jquery-validation/jquery.validate.min.js"></script>
--}}
<script src="{{ asset('admin-assets/assets/js/app.js') }}"></script>
<script src="{{ asset('js/app.js') }}"></script>
<script
        src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/js/all.min.js"
        integrity="sha512-8pHNiqTlsrRjVD4A/3va++W1sMbUHwWxxRPWNyVlql3T+Hgfd81Qc6FC5WMXDC+tSauxxzp1tgiAvSKFu1qIlA=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
></script>
<script
        src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/js/solid.min.js"
        integrity="sha512-LKdDHe5ZhpmiH6Kd6crBCESKkS6ryNpGRoBjGeh5mM/BW3NRN4WH8pyd7lHgQTTHQm5nhu0M+UQclYQalQzJnw=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
></script>
<script
        src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"
        integrity="sha512-eyHL1atYNycXNXZMDndxrDhNAegH2BDWt1TmkXJPoGf1WLlNYt08CSjkqF5lnCRmdm3IrkHid8s2jOUY4NIZVQ=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script src="{{ asset('admin-assets/plugins/notification/toastr/toastr.min.js') }}"></script>
<script src="https://cdn.tiny.cloud/1/rvycvj06b5wb6aw8kx5gxthyfx5mb7iql0vnxsorwfhmyj56/tinymce/7/tinymce.min.js"
        referrerpolicy="origin"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

<script type="text/javascript">

    $(function () {
        flatpickr('.datepicker', {enableTime: false, dateFormat: "Y-m-d", minDate: 'today'});
        flatpickr('.datetimepicker', {enableTime: true, dateFormat: "Y-m-d h:i K", time_24hr: false, minDate: 'today'});
    });

    // $(".datepicker").datepicker({
    //     dateFormat: 'yy-mm-dd', // Format for the date, you can change this to your preferred format
    //     changeMonth: true,
    //     changeYear: true,
    //     startDate: new Date()
    // });

    function generateWinnerReport(data) {
        $.ajax({
            url: "{{ route('admin.campaigns.generate-winner') }}",
            data: data,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            success: function (response) {
                console.log(response)
                if (response.status == '1') {
                    $('#winner').text(response.message);
                    $('#winner-popup-success').modal('show');
                    setTimeout(function () {
                        window.location.href = '{{ route("admin.reports.campaign-report") }}'
                    }, 3000)
                } else {
                    toastr["error"](response.message);
                }
            }
        })
    }

    $(document).ready(function () {
        $('.select2').select2();
        $('#ticket_number').on('input', function () {
            const query = $(this).val();
            if (query === '') {
                $('#suggestions .suggestion-item').show();
                return;
            }

            $('#suggestions .suggestion-item').each(function () {
                const slipNumber = $(this).text();
                if (slipNumber.includes(query)) {
                    $('#search_user').prop('disabled', false);
                    $(this).show();
                    $('#suggestions').show();
                } else {
                    $('#search_user').prop('disabled', true);
                    $(this).hide();
                }
            });
        });

        $('#suggestions').on('click', '.suggestion-item', function () {
            $('#search_user').prop('disabled', false);
            const selectedValue = $(this).text();
            $('#ticket_number').val(selectedValue);
            $('#suggestions .suggestion-item').hide();
            $('#suggestions').hide();
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest('#ticket_number, #suggestions').length) {
                $('#suggestions').hide();
            }
        });

        $('#search_user').on('click', function () {
            $.ajax({
                url: "{{ route('admin.reports.search-user-by-ticket-number') }}",
                type: "POST",
                data: {
                    campaign_id: $('#campaign_id').val(),
                    draw_slip_number: $('#ticket_number').val()
                },
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                success: function (response) {
                    if (response.status) {
                        $('#user-info').show();
                        $('#user-name').text(response.oData.user_first_name + ' ' + response.oData.user_last_name);
                        $('#user-email').text(response.oData.user_email_id);
                    }
                }
            })
        })


        $('#generate-winner').on('click', function () {
            let data = {
                campaign_id: $('#campaign_id').val(),
                ticket_number: $('#ticket_number').val(),
                campaign_date: $('#campaign_date').val()
            }

            generateWinnerReport(data);
        })

        tinymce.init({
            selector: 'textarea.html-editor'
        });
    })

    jQuery(function () {
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
        validateString: function (value, requirement) {
            var fileExtension = value.split('.').pop();
            extns = requirement.split(',');
            if (extns.indexOf(fileExtension.toLowerCase()) == -1) {
                return fileExtension === requirement;
            }
        },
    });
    window.Parsley.addValidator('maxFileSize', {
        validateString: function (_value, maxSize, parsleyInstance) {
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
            image.onload = function () {
                if (image.width == width && image.height == height) {
                    deferred.resolve();
                } else {
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
    $('body').on('click', '[data-role="unlink"]', function (e) {
        e.preventDefault();
        var msg = $(this).data('message') || 'Are you sure that you want to delete this record?';
        var href = $(this).attr('href');

        App.confirm('Confirm Delete', msg, function () {
            var ajxReq = $.ajax({
                url: href,
                type: 'DELETE',
                dataType: 'json',
                data: {
                    "_token": "{{ csrf_token() }}",
                },
                success: function (res) {
                    if (res['status'] == 1) {
                        App.alert(res['message'] || 'Deleted successfully', 'Success!');
                        setTimeout(function () {
                            window.location.reload();
                        }, 1500);

                    } else {
                        App.alert(res['message'] || 'Unable to delete the record.',
                            'Failed!');
                    }
                },
                error: function (jqXhr, textStatus, errorMessage) {

                }
            });
        });

    });

    $('body').off('click', '[data-role="approve"]');
    $('body').on('click', '[data-role="approve"]', function (e) {
        e.preventDefault();
        var msg = $(this).data('message') || 'Are you sure that you want to approve this record?';
        var href = $(this).attr('href');
        var title = $(this).data('title') || 'Confirm Approve';

        App.confirm(title, msg, function () {
            var ajxReq = $.ajax({
                url: href,
                type: 'GET',
                dataType: 'json',
                data: {
                    "_token": "{{ csrf_token() }}",
                },
                success: function (res) {
                    if (res['status'] == 1) {
                        App.alert(res['message'] || 'Approved successfully', 'Success!');
                        setTimeout(function () {
                            window.location.reload();
                        }, 1500);

                    } else {
                        App.alert(res['message'] || 'Unable to approve the record.',
                            'Failed!');
                    }
                },
                error: function (jqXhr, textStatus, errorMessage) {

                }
            });
        });

    });
    $('body').off('click', '[data-role="reject"]');
    $('body').on('click', '[data-role="reject"]', function (e) {
        e.preventDefault();
        var msg = $(this).data('message') || 'Are you sure that you want to reject this record?';
        var href = $(this).attr('href');
        var title = $(this).data('title') || 'Confirm Reject';

        App.confirm(title, msg, function () {
            var ajxReq = $.ajax({
                url: href,
                type: 'GET',
                dataType: 'json',
                data: {
                    "_token": "{{ csrf_token() }}",
                },
                success: function (res) {
                    if (res['status'] == 1) {
                        App.alert(res['message'] || 'Rejected successfully', 'Success!');
                        setTimeout(function () {
                            window.location.reload();
                        }, 1500);

                    } else {
                        App.alert(res['message'] || 'Unable to reject the record.',
                            'Failed!');
                    }
                },
                error: function (jqXhr, textStatus, errorMessage) {

                }
            });
        });

    });

    $(document).on('change', '.custom-file-input', function () {
        var file = $(this)[0].files[0].name;
        $(this).next('.custom-file-label').html(file);
    });

    $('body').off('click', '[data-role="verify"]');
    $('body').on('click', '[data-role="verify"]', function (e) {
        e.preventDefault();
        var href = $(this).attr('url');

        $.ajax({
            url: href,
            type: 'POST',
            dataType: 'json',
            data: {
                "_token": "{{ csrf_token() }}",
            },
            success: function (res) {
                if (res['status'] == 1) {
                    App.alert(res['message'] || 'Verified successfully', 'Success!');
                    setTimeout(function () {
                        window.location.reload();
                    }, 1500);

                } else {
                    App.alert(res['message'] || 'Unable to verify the record.', 'Failed!');
                }
            },
            error: function (jqXhr, textStatus, errorMessage) {

            }
        });

    });
    $(".change_status").change(function () {
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
            success: function (res) {
                App.loading(false);

                if (res['status'] == 0) {
                    var m = res['message']
                    App.alert(m, 'Oops!');
                    setTimeout(function () {
                        window.location.reload();
                    }, 1500);
                } else {
                    App.alert(res['message']);
                }
            },
            error: function (e) {
                App.alert(e.responseText, 'Oops!');
            }
        });
    });
    $(document).on('keyup', 'input[type="text"],textarea', function () {
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
    $('body').on('change', '[data-role="country-change"]', function () {
        var $t = $(this);
        var $dialcode = $('#' + $t.data('input-dial-code'));
        var $state = $('#' + $t.data('input-state'));

        if ($dialcode.length > 0) {
            var code = $t.find('option:selected').data('phone-code');
            console.log(code)
            if (code == '') {
                $dialcode.val('');
            } else {
                $dialcode.val(code);
            }
        }

        if ($state.length > 0) {

            var id = $t.val();
            var html = '<option value="">Select</option>';
            $state.html(html);
            $state.trigger('change');

            if (id != '') {
                $.ajax({
                    type: "POST",
                    enctype: 'multipart/form-data',
                    url: "{{url('admin/states/get_by_country')}}",
                    data: {
                        "id": id,
                        "_token": "{{ csrf_token() }}"
                    },
                    timeout: 600000,
                    dataType: 'json',
                    success: function (res) {
                        for (var i = 0; i < res['states'].length; i++) {
                            html += '<option value="' + res['states'][i]['id'] + '">' + res['states'][i]['name'] + '</option>';
                            if (i == res['states'].length - 1) {
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
    $('body').on('change', '[data-role="state-change"]', function () {
        var $t = $(this);
        var $city = $('#' + $t.data('input-city'));

        if ($city.length > 0) {
            var id = $t.val();
            var html = '<option value="">Select</option>';

            $city.html(html);
            if (id != '') {
                $.ajax({
                    type: "POST",
                    enctype: 'multipart/form-data',
                    url: "{{url('admin/cities/get_by_state')}}",
                    data: {
                        "id": id,
                        "_token": "{{ csrf_token() }}"
                    },
                    timeout: 600000,
                    dataType: 'json',
                    success: function (res) {
                        for (var i = 0; i < res['cities'].length; i++) {
                            html += '<option value="' + res['cities'][i]['id'] + '">' + res['cities'][i]['name'] + '</option>';
                            if (i == res['cities'].length - 1) {
                                $city.html(html);
                                // $('.selectpicker').selectpicker('refresh')
                            }
                        }
                    }
                });
            }

        }
    });
    $('body').off('change', '[data-role="country-change"]');
    $('body').on('change', '[data-role="country-change"]', function () {
        var $t = $(this);
        var $city = $('#' + $t.data('input-city'));

        if ($city.length > 0) {
            var id = $t.val();
            var html = '<option value="">Select</option>';

            $city.html(html);
            if (id != '') {
                $.ajax({
                    type: "POST",
                    enctype: 'multipart/form-data',
                    url: "{{url('admin/cities/get_by_country')}}",
                    data: {
                        "id": id,
                        "_token": "{{ csrf_token() }}"
                    },
                    timeout: 600000,
                    dataType: 'json',
                    success: function (res) {
                        for (var i = 0; i < res['cities'].length; i++) {
                            html += '<option value="' + res['cities'][i]['id'] + '">' + res['cities'][i]['name'] + '</option>';
                            if (i == res['cities'].length - 1) {
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
    $('body').on('change', '[data-role="vendor-change"]', function () {
        var $t = $(this);
        var $city = $('#' + $t.data('input-store'));

        if ($city.length > 0) {
            var id = $t.val();
            var html = '<option value="">Select</option>';

            $city.html(html);
            if (id != '') {
                $.ajax({
                    type: "POST",
                    enctype: 'multipart/form-data',
                    url: "{{url('admin/store/get_by_vendor')}}",
                    data: {
                        "id": id,
                        "_token": "{{ csrf_token() }}"
                    },
                    timeout: 600000,
                    dataType: 'json',
                    success: function (res) {
                        for (var i = 0; i < res['stores'].length; i++) {
                            html += '<option value="' + res['stores'][i]['id'] + '">' + res['stores'][i]['store_name'] + '</option>';
                            if (i == res['stores'].length - 1) {
                                $city.html(html);
                                // $('.selectpicker').selectpicker('refresh')
                            }
                        }
                    }
                });
            }

        }
    });
    $('body').off('click', '[data-role="change_password"]');
    $('body').on('click', '[data-role="change_password"]', function (e) {
        var userid = $(this).attr('userid');
        $('#userid').val(userid);
        $('#changepassword').modal('show');
    });


    $(".menu-children").click(function () {
        if ($(this).hasClass("showMenu")) {
            $(".menu-children").removeClass("showMenu");
        } else {
            $(".menu-children").removeClass("showMenu");
            $(this).addClass("showMenu");
        }
    });


    // $(".flatpickr-input").flatpickr({enableTime: false, dateFormat: "Y-m-d"});

    // let arrow = document.querySelectorAll(".arrow");
    //     for (var i = 0; i < arrow.length; i++) {
    //     arrow[i].addEventListener("click", (e)=>{
    //     let arrowParent = e.target.parentElement.parentElement;//selecting main parent of arrow
    //     arrowParent.classList.toggle("showMenu");
    //     });
    //     }

    let sidebar = document.querySelector(".sidebar");
    let sidebarBtn = document.querySelector(".bx-menu");
    console.log(sidebarBtn);
    sidebarBtn.addEventListener("click", () => {
        sidebar.classList.toggle("close");
    });

    $(".progress-bar-1").css('width', '30%');
    $(".progress-bar-2").css('width', '70%');


    const body = document.querySelector("body"),
        modeSwitch = body.querySelector(".toggle-switch"),
        modeText = body.querySelector(".mode-text");
    modeSwitch.addEventListener("click", () => {
        body.classList.toggle("dark");

        if (body.classList.contains("dark")) {
            modeText.innerText = "Light mode";
        } else {
            modeText.innerText = "Dark mode";

        }
    });
</script>
<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>
{{--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>--}}
{{--<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>--}}
<script>
    document.addEventListener("DOMContentLoaded", function () {

        var logoutLinks = document.querySelectorAll('.logout-link');


        logoutLinks.forEach(function (logoutLink) {
            logoutLink.addEventListener('click', function (event) {

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

    let table = new DataTable('.ma7zouz-tables');

    new DataTable('#products-table', {
        language: {
            search: "Search by product: "
        }
    });

    function showWinner(id) {
        let url = '<?php echo e(route('admin.reports.view-winner', ['campagin_id' => ':id'])); ?>';
        url = url.replace(':id', id);
        $.ajax({
            url: url,
            type: 'GET',
            success: function (response) {
                let html = '';

                if (response?.oData?.details) {
                    html += `<tr>`;
                    html += `<td><strong>Name</strong></td>`;
                    html += `<td>` + (response.oData.details.user_first_name || 'N/A') + ' ' + (response.oData.details.user_last_name || 'N/A') + `</td>`;
                    html += `</tr>`;

                    html += `<tr>`;
                    html += `<td><strong>Winner Photo</strong></td>`;
                    html += `<td><img src="` + (response.oData.details.image || 'default.jpg') + `" alt="Winner Photo"></td>`;
                    html += `</tr>`;

                    html += `<tr>`;
                    html += `<td><strong>Ticket Number</strong></td>`;
                    html += `<td>` + (response.oData.details.draw_slip_number || 'N/A') + `</td>`;
                    html += `</tr>`;

                    html += `<tr>`;
                    html += `<td><strong>Campaign Title</strong></td>`;
                    html += `<td>` + (response.oData.details.campaigns_title || 'N/A') + `</td>`;
                    html += `</tr>`;

                    html += `<tr>`;
                    html += `<td><strong>Campaign Description</strong></td>`;
                    html += `<td>` + (response.oData.details.campaigns_desc || 'N/A') + `</td>`;
                    html += `</tr>`;

                    html += `<tr>`;
                    html += `<td><strong>Campaign Date</strong></td>`;
                    html += `<td>` + (response.oData.details.campaigns_draw_date || 'N/A') + `</td>`;
                    html += `</tr>`;

                    // html += `<tr>`;
                    // html += `<td><strong>Winner Address</strong></td>`;
                    // html += `<td>` + (response.oData.details.campaigns_draw_date || 'N/A') + `</td>`;
                    // html += `</tr>`;
                }


                // html += `<tr>`;
                // html += `<td><strong>Winner Address</strong></td>`;
                // html += `<td>` +  + `</td>`;
                // html += `</tr>`;

                $('#winner-body').html(html)
                $('#winner-popup').modal('show')
            }
        })
    }

    let success = "{{ !empty($success) ? $success : '' }}";
    let message = "{{ !empty($message) ? $message : '' }}";
    if (success) {
        $('#winner').text(message);
        $('#winner-popup').modal('show');
    }

    function generateWinner(id) {
        $('#campaign_id').val(id);
        $('#generateWinnerPopup').modal('show')
    }

    function confirmPopup() {
        $('#confirm-popup').modal('show');
    }

    function submitPopup() {
        let data = {
            campaign_id: $('#campaign_id').val(),
            campaign_date: $('#campaign_date').val()
        }

        generateWinnerReport(data)
    }

    function cancelPopup() {
        $('#generateWinnerPopup').modal('hide')
        $('#confirm-popup').modal('hide')
    }

    function deleteBanner(id) {
        let url = '{{ route('admin.app-banners.delete', ['banner_id' => ':id']) }}';
        url = url.replace(':id', id);
        $.ajax({
            url: url,
            type: 'GET',
            success: function (response) {
                if (response.status) {
                    toastr["success"](response.message);
                    setTimeout(function () {
                        window.location.reload()
                    }, 2000);
                } else {
                    toastr["error"](response.message);
                }
            }
        })
    }

    $('.banner_status').on('change', function () {
        let status = 0;
        if ($(this).is(':checked')) {
            status = 1;
        }

        $.ajax({
            url: "{{ route('admin.app-banners.change-status') }}",
            type: "POST",
            data: {
                id: $(this).data('id'),
                status: status
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function (response) {
                if (response.status) {
                    toastr["success"](response.message);
                } else {
                    toastr["error"](response.message);
                }
            }, error: function (response) {
                toastr["error"]('Something went wrong');
            }
        })
    })
</script>

<!-- END GLOBAL MANDATORY SCRIPTS -->

<!-- BEGIN PAGE LEVEL PLUGINS/CUSTOM SCRIPTS -->
@yield('script')
<!-- BEGIN PAGE LEVEL PLUGINS/CUSTOM SCRIPTS -->
</body>
</html>
