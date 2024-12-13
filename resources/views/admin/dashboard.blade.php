@extends('admin.template.layout')

@section('header')
    <link href="{{ asset('') }}admin-assets/assets/css/support-chat.css" rel="stylesheet" type="text/css" />
    <link href="{{ asset('') }}admin-assets/plugins/maps/vector/jvector/jquery-jvectormap-2.0.3.css" rel="stylesheet"
          type="text/css" />
    <link href="{{ asset('') }}admin-assets/plugins/charts/chartist/chartist.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('') }}admin-assets/assets/css/default-dashboard/style.css" rel="stylesheet" type="text/css" />
@stop


@section('content')

    <style>

        .home-section footer{
            bottom: auto !important;
        }
        .custom-container{
            min-height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }
        /*body.dark{*/
        /*    background: url('{{ asset('') }}admin-assets/assets/img/laconcierge-bg.jpg');*/
        /*    background-size: 100% 100%;*/
        /*    background-position: center;*/
        /*    background-repeat: no-repeat;*/
        /*}*/
        .custom-wl{
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;

        }
        .custom-wl li{
            width: 250px;
            list-style-type: none;
        }
        .custom-wl li .icon-card{
            /*max-width: 320px;*/
            margin: auto;
            min-height: 250px;
        }
    </style>

    <div class="row">
        <div class="col-sm-12 col-md-4">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('admin.customers.index') }}">
                        <div class="row">
                            <div class="col-3 text-center">
                                <h1 class="mt-3">
                                    <i class="fa fa-users"></i>
                                </h1>
                            </div>
                            <div class="col-9">
                                <h4 class="m-0">Customers</h4>
                                <h1 class="m-0">{{ $total_customers }}</h1>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-4">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('admin.campaigns.index') }}">
                        <div class="row">
                            <div class="col-3 text-center">
                                <h1 class="mt-3">
                                    <i class="fa fa-gift"></i>
                                </h1>
                            </div>
                            <div class="col-9">
                                <h4 class="m-0">Active Campaigns</h4>
                                <h1 class="m-0">{{ count($active_campaigns) }}</h1>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-md-4">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('admin.winners') }}">
                        <div class="row">
                            <div class="col-3 text-center">
                                <h1 class="mt-3">
                                    <i class="fa fa-trophy"></i>
                                </h1>
                            </div>
                            <div class="col-9">
                                <h4 class="m-0">Winners</h4>
                                <h1 class="m-0">{{ $total_winners }}</h1>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-12">
            <h3 class="text-dark">Daily purchase of campaigns</h3>
        </div>
        <div class="col-12">
            <div>
                <canvas id="myChart"></canvas>
            </div>
        </div>
    </div>
@stop

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const campaigns_labels = @json($campaigns_list);
        const total_orders = @json($total_orders);
        const ctx = document.getElementById('myChart');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: campaigns_labels,
                datasets: [{
                    label: 'Campaigns',
                    data: total_orders,
                    borderWidth: 1,
                    borderColor: '#36A2EB',
                    backgroundColor: '#9BD0F5',
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMin: 50,
                        suggestedMax: 100,
                        title: {
                            display: true,
                            text: 'Total number of orders',
                        }
                    },
                }
            }
        });
    </script>
@stop
