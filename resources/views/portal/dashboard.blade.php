@extends('portal.template.layout')
@php use Illuminate\Support\Facades\DB; @endphp
@section('header')
    <link href="{{ asset('') }}admin-assets/assets/css/support-chat.css" rel="stylesheet" type="text/css" />
    <link href="{{ asset('') }}admin-assets/plugins/maps/vector/jvector/jquery-jvectormap-2.0.3.css" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('') }}admin-assets/plugins/charts/chartist/chartist.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('') }}admin-assets/assets/css/default-dashboard/style.css" rel="stylesheet" type="text/css" />
@stop


@section('content')
@if(Auth::user()->activity_id == 7 || (Auth::user()->activity_id == 5 && Auth::user()->is_delivery) || Auth::user()->activity_id == 3)
<div class="row mb-4">
       <div class="col-lg-3-auto col-md-6 mb-4">
          <a href="{{url('portal/orders')}}"><div class="icon-card height-100 text-center orange h-100">
             <div class="icon">
                <i class="fas fa-shopping-cart"></i>
             </div>
             <div class="content m-0 text-center">
                <h6 class="mb-1 text-center">All Orders</h6>
                <h3>{{$all}}</h3>
             </div>
          </div></a>
       </div>
       <div class="col-lg-3-auto col-md-6 mb-4">
          <a href="{{url('portal/orders')}}?status=0"><div class="icon-card height-100 text-center green h-100">
             <div class="icon">
                <i class="fas fa-cart-arrow-down"></i>
             </div>
             <div class="content m-0 text-center">
                <h6 class="mb-1 text-center">Pending Orders</h6>
                <h3>{{$pending}}</h3>
             </div>
          </div></a>
       </div>

       <div class="col-lg-3-auto col-md-6 mb-4">
          <a href="{{url('portal/orders')}}?status=1"><div class="icon-card height-100 text-center blue h-100">
             <div class="icon">
                
                <i class="fas fa-check-circle"></i>
             </div>
             <div class="content m-0 text-center">
                <h6 class="mb-1 text-center">Accepted Orders</h6>
                <h3>{{$accepted}}</h3>
             </div>
          </div></a>
       </div>
       <div class="col-lg-3-auto col-md-6 mb-4">
          <a href="{{url('portal/orders')}}?status=2"><div class="icon-card height-100 text-center blue h-100">
             <div class="icon">
                
                <i class="fas fa-check-circle"></i>
             </div>
             <div class="content m-0 text-center">
                <h6 class="mb-1 text-center">Ready For Delivery</h6>
                <h3>{{$ready_for_delivery}}</h3>
             </div>
          </div></a>
       </div>
       <div class="col-lg-3-auto col-md-6 mb-4">
          <a href="{{url('portal/orders')}}?status=3"><div class="icon-card height-100 text-center blue h-100">
             <div class="icon">
                
                <i class="fas fa-check-circle"></i>
             </div>
             <div class="content m-0 text-center">
                <h6 class="mb-1 text-center">Dispatched</h6>
                <h3>{{$dispatched}}</h3>
             </div>
          </div></a>
       </div>
       <div class="col-lg-3-auto col-md-6 mb-4">
          <a href="{{url('portal/orders')}}?status=4"><div class="icon-card height-100 text-center green h-100">
             <div class="icon">
                <i class="fas fa-check-circle"></i>
             </div>
             <div class="content m-0 text-center">
                <h6 class="mb-1 text-center">Delivered Orders</h6>
                <h3>{{$delivered}}</h3>
             </div>
          </div></a>
       </div>
       <div class="col-lg-3-auto col-md-6 mb-4">
          <a href="{{url('portal/orders')}}?status=10"><div class="icon-card height-100 text-center red h-100">
             <div class="icon">
                <i class="fas fa-times"></i>
             </div>
             <div class="content m-0 text-center">
                <h6 class="mb-1 text-center">Cancelled Orders</h6>
                <h3>{{$rejected}}</h3>
             </div>
          </div></a>
       </div>
       
    </div>
    
<div class="row mb-4" style="display:none;">
    <div class="col-lg-12">
    <div class="row">
                <div class="col-12 mb-4">
                    <div class="card custom-card">
                      <div class="card-header "> <b style="color: black;">Latest Orders</b></div>
                    <table class="table table-responsive recent-orders-table mb-0" width="100%">
                        <thead>
                           <tr>
                              <th scope="col" width="22%">Order ID</th>
                              <th scope="col" width="27%">Customer</th>
                              <th scope="col" width="22%">Order Date</th>
                              <th scope="col" width="22%">Total</th>
                              <th scope="col" width="17%">Status</th>
                              
                           </tr>
                        </thead>
                        <tbody id="newdata">
                           @if($latest_orders->isEmpty())
                           <tr>
                              <td colspan="6"> No Data</td>
                           </tr>
                           @endif
                           @foreach ($latest_orders as $item)
                           <tr>
                              <td><?php echo config('global.sale_order_prefix').date(date('Ymd', strtotime($item->created_at))).$item->order_id; ?></td>
                              <td>{{ $item->name ?? $item->customer_name }}</td>
                              <td>{{ web_date_in_timezone($item->booking_date, 'M d h:i A') }}</td>
                              <td>{{config('global.default_currency_code')}} {{ $item->grand_total }}</td>
                              <td><span class="badge badge-info"> {{ $item->status_text }}</span></td>
                              
                           </tr>
                           @endforeach
                        </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
        @endif
@if(Auth::user()->activity_id == 6 || Auth::user()->activity_id == 4 || Auth::user()->activity_id == 1) 
<div class="row mb-4">
       <div class="col-lg-3-auto col-md-6 mb-4">
          <a href="{{url('portal/service_request')}}"><div class="icon-card height-100 text-center orange h-100">
             <div class="icon">
                <i class="fas fa-shopping-cart"></i>
             </div>
             <div class="content m-0 text-center">
                <h6 class="mb-0 text-center">All Services</h6>
                <h3>{{$all}}</h3>
             </div>
          </div></a>
       </div>
       <div class="col-lg-3-auto col-md-6 mb-4">
          <a href="{{url('portal/service_request?status=0')}}"><div class="icon-card height-100 text-center green h-100">
             <div class="icon">
                <i class="fas fa-cart-arrow-down"></i>
             </div>
             <div class="content m-0 text-center">
                <h6 class="mb-0 text-center">Pending</h6>
                <h3>{{$pending}}</h3>
             </div>
          </div></a>
       </div>
       <div class="col-lg-3-auto col-md-6 mb-4">
          <a href="{{url('portal/service_request?status=1')}}"><div class="icon-card height-100 text-center blue h-100">
             <div class="icon">
                
                <i class="fas fa-check-circle"></i>
             </div>
             <div class="content m-0 text-center">
                <h6 class="mb-0 text-center">Accepted</h6>
                <h3>{{$accepted}}</h3>
             </div>
          </div></a>
       </div>
       <div class="col-lg-3-auto col-md-6 mb-4">
          <a href="{{url('portal/service_request?status=3')}}"><div class="icon-card height-100 text-center blue h-100">
             <div class="icon">
                
                <i class="fas fa-check-circle"></i>
             </div>
             <div class="content m-0 text-center">
                <h6 class="mb-0 text-center">Ongoing</h6>
                <h3>{{$ongoing}}</h3>
             </div>
          </div></a>
       </div>
       <div class="col-lg-3-auto col-md-6 mb-4">
          <a href="{{url('portal/service_request?status=10')}}"><div class="icon-card height-100 text-center red h-100">
             <div class="icon">
                <i class="fas fa-times"></i>
             </div>
             <div class="content m-0 text-center">
                <h6 class="mb-0 text-center">Cancelled</h6>
                <h3>{{$rejected}}</h3>
             </div>
          </div></a>
       </div>
       
    </div>
    
<div class="row mb-4" style="display:none;">
    <div class="col-lg-12">
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card custom-card">
                    <table class="table table-responsive recent-orders-table mb-0" width="100%">
                        <thead>
                            <tr>
                            <th scope="col" width="10%">Request Id</th>
                            <th scope="col" width="10%">Customer</th>
                            <th scope="col" width="25%">Service name</th>
                            <th scope="col" width="12%">Date</th>
                            <th scope="col" width="13%">Grand total</th>
                            <th scope="col" width="10%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
        </div></div>
        @endif

<div class="row d-none">
        <div class="col-lg-12 mb-4">
            <div class="row">
                <div class="col-lg-3 mb-3">
                    <a href="{{url('portal/orders')}}">
                        <div class="card custom-card">
                            <div class="widget">
                                <div class="widget-icon widget-bg-yellow">
                                    <i class='bx bx-user' ></i>
                                </div>
                                <div class="widget-info">
                                    <span>{{$order}}</span>
                                    <h6>Orders</h6>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 mb-3">
                    <a href="{{url('portal/products')}}">
                        <div class="card custom-card">
                            <div class="widget">
                                <div class="widget-icon widget-bg-red-light text-white">
                                    <i class='bx bx-user-circle' ></i>
                                </div>
                                <div class="widget-info">
                                    <span>10</span>
                                    <h6>Products</h6>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 mb-3">
                    <a href="{{url('portal/service_request')}}">
                        <div class="card custom-card">
                            <div class="widget">
                                <div class="widget-icon widget-bg-yellow-light">
                                    <i class='bx bx-list-check'></i>
                                </div>
                                <div class="widget-info">
                                    <span>60</span>
                                    <h6>Services</h6>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 mb-3">
                    <a href="{{url('portal/orders')}}">
                        <div class="card custom-card">
                            <div class="widget">
                                <div class="widget-icon widget-bg-red text-white">
                                    <i class='bx bx-store'></i>
                                </div>
                                <div class="widget-info">
                                    <span>80</span>
                                    <h6>Earnings</h6>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <!-- <ul class="nav nav-pills mb-3 custom-pills justify-content-around" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="pills-user-tab" href="{{url('admin/customers')}}" role="tab" aria-controls="pills-user" aria-selected="true">
                        <i class='bx bx-user' ></i> <span>Users</span>
                        <span>10</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pills-vendors-tab" href="{{url('admin/vendors')}}" role="tab" aria-controls="pills-vendors" aria-selected="false">
                        <i class='bx bx-user-circle' ></i> <span>Vendors</span>
                        <span>10</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pills-product-tab" href="{{url('admin/products')}}" role="tab" aria-controls="pills-product" aria-selected="false">
                    <i class='bx bx-list-check'></i> <span>Service provider</span>
                    <span>15</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pills-stores-tab" href="{{url('admin/store')}}" role="tab" aria-controls="pills-stores" aria-selected="false">
                        <i class='bx bx-store' ></i> <span>Sales</span>
                        <span>30</span>
                    </a>
                </li>

                 <li class="nav-item">
                    <a class="nav-link" id="pills-stores-tab" href="{{url('admin/store')}}" role="tab" aria-controls="pills-stores" aria-selected="false">
                        <i class='bx bx-store' ></i> <span>Commission</span>
                        <span>300</span>
                    </a>
                </li>
            </ul> -->
        </div>
        <div class="col-lg-12 mb-4" style="display:none;">
            <div class="card custom-card">
                <div class="progress-bars">
                    <!-- <span>
                        <div class="d-flex justify-content-between mb-2">
                                <h6 class="text-xsl">Daily sells of medicine vrs service</h6>
                                <p class="text-xxs mb-0">Lorem Ipsum is simply dummy text</p>
                        </div>
                        
                    </span> -->
                 
                    <div class="chart" style="position: relative; height: 40vh; overflow: hidden;">
                        <canvas id="myChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-6" style="display:none;">
            <div class="card custom-card mb-4" >
                <div class="row align-items-center">
                    <div class="col-lg-12 mb-4">
                        <div class="custom-card-flex">
                            <div class="mb-2">
                                <h6 class="mb-2 text-xl">User registration</h6>
                                <p class="text-xsl">Monthly user registration. </p>
                            </div>
                            <div class="d-flex align-items-center border-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                <input type="text" class="form-control dashboard-form-control flat-picker border-0 bg-transparent pr-0 flatpickr-input" placeholder="YYYY-MM-DD">
                            </div>
                        </div>
                        <div class="text-right">
                            
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="chart" style="position: relative; height: 30vh; overflow: hidden;">
                            <canvas id="mybarlineChart1"></canvas>
                        </div>
                        
                    </div>
                    
                </div>
            </div>
        </div>
        <div class="col-lg-6" style="display:none;">
            <div class="card custom-card mb-4">
                <div class="row align-items-center">

                    <div class="col-lg-12 mb-4">
                        <div class="custom-card-flex">
                            <div class="mb-2">
                                <h6 class="mb-2 text-xl">Vendor registration</h6>
                                <p class="text-xsl">Monthly Vendor registration. </p>
                            </div>
                            <div class="d-flex align-items-center border-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                <input type="text" class="form-control dashboard-form-control flat-picker border-0 bg-transparent pr-0 flatpickr-input" placeholder="YYYY-MM-DD">
                            </div>
                        </div>
                        <div class="text-right">
                            
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="chart" style="position: relative; height: 30vh; overflow: hidden;">
                            <canvas id="mybarlineChart2"></canvas>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6" style="display:none;">
            <div class="card custom-card">
                <div class="chart" style="position: relative; height: 30vh; overflow: hidden;">
                    <canvas id="orderschart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6" style="display:none;">
            <div class="card custom-card">
                <div class="chart" style="position: relative; height: 30vh; overflow: hidden;">
                <canvas id="userschat" width="600" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="row">
                <div class="col-12 mb-4" style="display: none;">
                    <div class="card custom-card">
                    <table class="table table-responsive recent-orders-table mb-0">
                        <thead>
                            <tr>
                            <th scope="col" width="10%">Order Id</th>
                            <th scope="col" width="35%">Product Name</th>
                            <th scope="col" width="10%">Unit</th>
                            <th scope="col" width="12%">Order Date</th>
                            <th scope="col" width="13%">Order Cost</th>
                            <th scope="col" width="10%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                            <td>24551</td>
                            <td>Toddler Shoes, Gucci Watch</td>
                            <td>1 Unit</td>
                            <td>Nov 18, 2022</td>
                            <td>JOD 158</td>
                            <td><span class="badge badge-success">Delivered</span></td>
                            </tr>
                            <tr>
                            <td>24552</td>
                            <td> Coach Swagger</td>
                            <td>2 Unit</td>
                            <td>Nov 18, 2022</td>
                            <td>JOD 53</td>
                            <td><span class="badge badge-warning">Delayed</span></td>
                            </tr>
                            <tr>
                            <td>24553</td>
                            <td>Hat Black Suits</td>
                            <td>3 Unit</td>
                            <td>Nov 18, 2022</td>
                            <td>JOD 79</td>
                            <td><span class="badge badge-secondary">On Hold</span></td>
                            </tr>
                            <tr>
                            <td>24553</td>
                            <td>Hat Black Suits</td>
                            <td>3 Unit</td>
                            <td>Nov 18, 2022</td>
                            <td>JOD 54</td>
                            <td><span class="badge badge-danger">Cancelled</span></td>
                            </tr>
                        </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
        </div>
    </div>

    <div class="row layout-spacing d-none">

        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 mb-sm-0 mb-4">
            <a href="#">
                <div class="widget-content-area  data-widgets br-4">
                    <div class="widget  t-customer-widget">

                        <div class="media">
                            <div class="icon ml-2">
                                <i class="flaticon-users"></i>
                            </div>
                            <div class="media-body text-right">
                                <p class="widget-text mb-0">Users</p>
                                <p class="widget-numeric-value">0</p>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 mb-sm-0 mb-4">
            <a href="#">
                <div class="widget-content-area  data-widgets br-4">
                    <div class="widget  t-customer-widget">

                        <div class="media">
                            <div class="icon ml-2">
                                <i class="flaticon-users"></i>
                            </div>
                            <div class="media-body text-right">
                                <p class="widget-text mb-0">Vendors</p>
                                <p class="widget-numeric-value">10</p>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 mb-sm-0 mb-4">
            <a href="#">
                <div class="widget-content-area  data-widgets br-4">
                    <div class="widget  t-customer-widget">

                        <div class="media">
                            <div class="icon ml-2">
                                <i class="flaticon-menu-list"></i>
                            </div>
                            <div class="media-body text-right">
                                <p class="widget-text mb-0">Products</p>
                                <p class="widget-numeric-value">10</p>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>




    </div>








    </div>
@stop

@section('footer')
    <!-- <script src='https://cdnjs.cloudflare.com/ajax/libs/Chart.js/1.0.2/Chart.min.js'></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js" integrity="sha512-ElRFoEQdI5Ht6kZvyzXhYG9NqjtkmlkfYk0wr6wHxU9JEHakS7UJZNeml5ALk+8IKlU6jDgMabC3vkumRokgJA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0-rc"></script>
@stop

@section('script')
    <script>

    var myChartctx = document.getElementById("myChart").getContext('2d');
    var myChart = new Chart(myChartctx, {
    type: 'bar',
    data: {
        labels: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
        datasets: [{
        label: 'data-1',
        data: [12, 19, 3, 17, 28, 24, 7],
        backgroundColor: "rgba(250, 183, 0, 0.549)"
        }, {
        label: 'data-2',
        data: [30, 29, 5, 5, 20, 3, 10],
        backgroundColor: "rgb(255,69,0)"
        }]
    },
    options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                },
                scales: {
                    x: {
                        grid: {
                            color: '#f5f5f5'
                        }
                    },
                    y: {
                        grid: {
                            color: '#f5f5f5'
                        }
                    },
                },
            }
    });



    //    var ctx = document.getElementById('myChart').getContext('2d');
    //     var chart = new Chart(ctx, {
    //         type: 'line',
    //         data: {
    //             labels: ["100", "200", "300", "400", "500", "600", "700", "800", "900", "1000", "1100","1200", "1300", "1400", "1500", "1600", "1700"],
    //             datasets: [{
    //                 label: "",
    //                 fill: true,
    //                 backgroundColor: 'rgb(204 155 68)',
    //                 borderColor: 'rgb(235 192 94)',
    //                 data: [10, 200, 150, 50, 180, 80, 150, 60, 130, 90, 135, 85, 165, 78, 138, 48, 158],
    //             }]
    //         },

    //         options: {
    //             maintainAspectRatio: false,
    //             responsive: true,
    //             scales: {
    //                 x: {
    //                     position: 'top',
    //                     grid: {
    //                         color: '#FAF0E6'
    //                     },
    //                     ticks: {
    //                         maxRotation: 0,
    //                         minRotation: 0,
    //                         font: {
    //                             size: 5
    //                         }
    //                     }
    //                 },
    //                 y: {
    //                     grid: {
    //                         color: '#FAF0E6'
    //                     },
    //                     ticks: {
    //                         display: false
    //                     }
                        
    //                 },
    //             },
    //             plugins: {
    //                 legend: {
    //                     display: false
    //                 },
    //             }
    //         }
    //     });


        var ctx = document.getElementById("mybarlineChart1");
        var chart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: ["", "", "", "", "", "","", "", ""],
            datasets: [
            {
                type: "bar",
                backgroundColor: "rgb(255,69,0)",
                borderWidth: 1,
                label: "",
                data: [60, 49, 72, 85, 80, 60, 50, 45, 46]
            },
            {
                type: "line",
                
                backgroundColor: "rgba(250, 183, 0, 0.549)",
                borderColor: "rgba(250, 183, 0, 0.549)",
                label: "",
                data: [60, 49, 72, 85, 80, 60, 50, 45, 46],
                lineTension: 0, 
            }
            ]
        },
        options: {
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    x: {
                        grid: {
                            color: 'white'
                        },
                        ticks: {
                            display: false
                        }
                    },
                    y: {
                        

                        position: 'top',
                        grid: {
                            borderDash: [2, 3],
                            color: '#FAF0E6'
                        },
                        ticks: {
                            maxRotation: 0,
                            minRotation: 0,
                            font: {
                                size: 5
                            }
                        }
                        
                    },
                },
                plugins: {
                    legend: {
                        display: false
                    },
                }
            }
        });


        var ctx = document.getElementById("mybarlineChart2");
        var chart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: ["", "", "", "", "", "","", "", ""],
            datasets: [
                {
                type: "bar",
                backgroundColor: "rgb(255,69,0)",
                borderWidth: 1,
                label: "",
                data: [60, 49, 72, 85, 80, 60, 50, 45, 46]
            },
            {
                type: "line",
                backgroundColor: "rgba(250, 183, 0, 0.549)",
                borderColor: "rgba(250, 183, 0, 0.549)",
                label: "",
                data: [60, 49, 72, 85, 80, 60, 50, 45, 46],
                lineTension: 0, 
            }
            ]
        },
        options: {
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    x: {
                        grid: {
                            color: 'white'
                        },
                        ticks: {
                            display: false
                        }
                    },
                    y: {
                        

                        position: 'top',
                        grid: {
                            borderDash: [2, 3],
                            color: '#FAF0E6'
                        },
                        ticks: {
                            maxRotation: 0,
                            minRotation: 0,
                            font: {
                                size: 5
                            }
                        }
                        
                    },
                },
                plugins: {
                    legend: {
                        display: false
                    },
                }
            }
        });

        var ctx = document.getElementById("mybarlineChart3");
        var chart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: ["", "", "", "", "", "", "", "", ""],
            datasets: [
                {
                type: "bar",
                backgroundColor: "rgba(0,0,0,0.3)",
                borderWidth: 1,
                label: "",
                data: [60, 49, 72, 85, 80, 60, 50, 45, 46]
            },
            {
                type: "line",
                backgroundColor: "rgb(0,0,0)",
                borderColor: "rgb(0,0,0)",
                label: "",
                data: [60, 49, 72, 85, 80, 60, 50, 45, 46],
                lineTension: 0,
            }
            ]
        },
        options: {
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    x: {
                        grid: {
                            color: 'white'
                        },
                        ticks: {
                            display: false
                        }
                    },
                    y: {
                        

                        position: 'top',
                        grid: {
                            borderDash: [2, 3],
                            color: '#FAF0E6'
                        },
                        ticks: {
                            maxRotation: 0,
                            minRotation: 0,
                            font: {
                                size: 5
                            }
                        }
                        
                    },
                },
                plugins: {
                    legend: {
                        display: false
                    },
                }
            }
        });

        
        var orderschartctx = document.getElementById("orderschart");
        var myChart = new Chart(orderschartctx, {
        type: 'doughnut',
        data: {
            labels: ['User', 'Pharmacy vendor', 'Service provider'],
            datasets: [{
            label: '',
            data: [12, 19, 3],
            backgroundColor: [
                'rgb(255,69,0)',
                'rgba(250, 183, 0, 0.549)',
                'rgba(64, 69, 83, 0.712)'
            ],
            borderColor: [
                'rgb(255,69,0)',
                'rgba(250, 183, 0, 0.549)',
                'rgba(64, 69, 83, 0.712)'
            ],
            borderWidth: 2
            }]
        },
        options:{
            cutout: 60,
            centerPercentage: 80,
            responsive: true,
            maintainAspectRatio: false,
            tooltips: {
                enabled: true
            },
            interaction: {
            intersect: false
            },
            plugins: {
            legend: {
                display: true,
                position: 'bottom',
                
                labels: {
                    font: {
                        size: 10
                    },
                    boxWidth: 10
                }
            }
            },
        }
        });


document.addEventListener('DOMContentLoaded', function () {
  var ctxs = document.getElementById('userschat').getContext('2d');
  var gradient = ctxs.createLinearGradient(0, 0, 0, 225);
  gradient.addColorStop(0, 'rgba(250, 183, 0, 0.8)');
  gradient.addColorStop(1, 'rgb(255,69,0)');
  // Line chart
  new Chart(document.getElementById('userschat'), {
    type: 'line',
    data: {
      labels: [
        'Jan',
        'Feb',
        'Mar',
        'Apr',
        'May',
        'Jun',
        'Jul',
        'Aug',
        'Sep',
        'Oct',
        'Nov',
        'Dec',
      ],
      datasets: [
        {
          label: 'Active Users',
          fill: true,
          backgroundColor: gradient,
          borderColor: '#404553',
          tension: 0.5,
          data: [
            2115, 1562, 1584, 1892, 1587, 1923, 2566, 2448, 2805, 3438, 2917,
            3327,
          ],
        },
      ],
    },
    options: {
        maintainAspectRatio: false,
        responsive: true,
      tooltips: {
        intersect: false,
      },
      hover: {
        intersect: true,
      },
      plugins: {
        filler: {
          propagate: false,
        },
        legend: {
            display: false
        },
      },
      scales: {
                    x: {
                        grid: {
                            color: 'white'
                        },
                        ticks: {
                            display: false
                        }
                    },
                    y: {
                        

                        position: 'top',
                        grid: {
                            borderDash: [2, 3],
                            color: '#FAF0E6'
                        },
                        ticks: {
                            maxRotation: 0,
                            minRotation: 0,
                            font: {
                                size: 5
                            }
                        }
                        
                    },
                },
    },
  });
});

    </script>
@stop
