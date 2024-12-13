@extends('admin.template.layout')

@section('header')
    <link href="{{ asset('') }}admin-assets/assets/css/support-chat.css" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('') }}admin-assets/plugins/maps/vector/jvector/jquery-jvectormap-2.0.3.css" rel="stylesheet"
          type="text/css"/>
    <link href="{{ asset('') }}admin-assets/plugins/charts/chartist/chartist.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('') }}admin-assets/assets/css/default-dashboard/style.css" rel="stylesheet" type="text/css"/>
@stop

@section('content')

    <style>

        .home-section footer {
            bottom: auto !important;
        }

        .custom-container {
            min-height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }

        /*body.dark{*/
        /*    background: url('



        {{ asset('') }}    admin-assets/assets/img/laconcierge-bg.jpg');*/
        /*    background-size: 100% 100%;*/
        /*    background-position: center;*/
        /*    background-repeat: no-repeat;*/
        /*}*/
        .custom-wl {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;

        }

        .custom-wl li {
            width: 250px;
            list-style-type: none;
        }

        .custom-wl li .icon-card {
            /*max-width: 320px;*/
            margin: auto;
            min-height: 250px;
        }
    </style>

    <div class="custom">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <table class="table table-bordered ma7zouz-tables">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Winner Name</th>
                                <th>Ticket Number</th>
                                <th>Campaign Name</th>
                                <th>Campaign Image</th>
                                <th>Product Name</th>
                                <th>Product Name</th>
                                <th>Purchased On</th>
                                <th>Draw Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($user_list_won as $winner)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $winner['user_first_name'] }} {{ $winner['user_last_name'] }}</td>
                                    <td>{{ $winner['won_ticket_number'] }}</td>
                                    <td>{{ $winner['campaigns_title'] }}</td>
                                    <td class="text-center">
                                        <img width="100" src="{{ $winner['campaigns_home_image'] }}" alt="">
                                    </td>
                                    <td>{{ $winner['product_name'] }}</td>
                                    <td class="text-center">
                                        <img width="100" src="{{ $winner['m_product_image'] }}" alt="">
                                    </td>
                                    <td>{{ $winner['purchased_on'] }}</td>
                                    <td>{{ $winner['draw_date'] }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop