@extends('store_manager.template.layout')
@php use Illuminate\Support\Facades\DB; @endphp
@section('header')
    <link href="{{ asset('') }}admin-assets/assets/css/support-chat.css" rel="stylesheet" type="text/css" />
    <link href="{{ asset('') }}admin-assets/plugins/maps/vector/jvector/jquery-jvectormap-2.0.3.css" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('') }}admin-assets/plugins/charts/chartist/chartist.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('') }}admin-assets/assets/css/default-dashboard/style.css" rel="stylesheet" type="text/css" />
@stop


@section('content')
    <div class="row layout-spacing ">

        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 mb-sm-0 mb-4">
            <a href="{{ url('store/store') }}">
                <div class="widget-content-area  data-widgets br-4">
                    <div class="widget  t-customer-widget">

                        <div class="media">
                            <div class="icon ml-2">
                                <i class="flaticon-users"></i>
                            </div>
                            <div class="media-body text-right">
                                <p class="widget-text mb-0">Stores</p>
                                <p class="widget-numeric-value">
                                    {{ DB::table('stores')->where('vendor_id', auth()->user()->id)->count() }}
                                </p>
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
    <script src='https://cdnjs.cloudflare.com/ajax/libs/Chart.js/1.0.2/Chart.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0-rc"></script>
@stop

@section('script')
    <script>
       
    </script>
@stop
