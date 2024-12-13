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

@stop

@section('footer')
    <!-- <script src='https://cdnjs.cloudflare.com/ajax/libs/Chart.js/1.0.2/Chart.min.js'></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js" integrity="sha512-ElRFoEQdI5Ht6kZvyzXhYG9NqjtkmlkfYk0wr6wHxU9JEHakS7UJZNeml5ALk+8IKlU6jDgMabC3vkumRokgJA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0-rc"></script>
@stop
