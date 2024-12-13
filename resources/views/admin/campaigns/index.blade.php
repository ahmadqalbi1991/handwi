@extends('admin.template.layout')

@section('header')
    <link href="{{ asset('') }}admin-assets/assets/css/support-chat.css" rel="stylesheet" type="text/css"/>
    <link href="{{ asset('') }}admin-assets/plugins/maps/vector/jvector/jquery-jvectormap-2.0.3.css" rel="stylesheet"
          type="text/css"/>
    <link href="{{ asset('') }}admin-assets/plugins/charts/chartist/chartist.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('') }}admin-assets/assets/css/default-dashboard/style.css" rel="stylesheet" type="text/css"/>
@stop

@section('buttons')
    <a href="{{ route('admin.campaigns.create') }}" class="btn btn-sm btn-primary mt-4">Add Campaign</a>
@endsection

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
        <form action="" style="width: 100%">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="">Select Country</label>
                        <select name="country_id" id="country_id" class="form-control">
                            <option value="">Select Country</option>
                            @foreach($countries as $country)
                                <option {{ request()->country_id == $country->countries_id ? 'selected' : '' }} value="{{ $country->country_id }}">{{ $country->countries_nice_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="">Price From</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white" id="basic-addon1">JOD</span>
                            </div>
                            <input type="number" min="0" value="{{ request()->price_from }}" placeholder="Price from" name="price_from" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="">Price To</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white" id="basic-addon1">JOD</span>
                            </div>
                            <input type="number" min="0" value="{{ request()->price_to }}" placeholder="Price to" name="price_to" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="col-md-3" style="text-align: right">
                    <button class="btn btn-success btn-rounded">Search</button>
                    <a href="{{ route('admin.campaigns.index') }}" class="btn btn-default">Clear</a>
                </div>
            </div>
        </form>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <table class="table table-bordered" id="products-table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Campaign Image</th>
                                <th>Campaign name</th>
                                <th>Start Date</th>
                                <th>Product Name</th>
                                <th>Product Type</th>
                                <th>Product Price</th>
                                <th>Product Image</th>
                                <th>Draw Date</th>
                                <th>Active</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    @php
                                        $campaign_image = asset('images/dummy.jpg');
                                    @endphp
                                    {{--                            <td>{{ $product->campaign_image }}</td>--}}
                                    <td class="text-center">
                                        <img width="40" src="{{ !empty($product) ? $product->campaigns_image : $campaign_image }}" alt="">
                                    </td>
                                    <td>{{ $product->campaigns_title }}</td>
                                    <td>{{ \Carbon\Carbon::parse($product->campaigns_date_start)->format('d M, Y') }}</td>
                                    <td>{{ $product->product_name }}</td>
                                    <td>{{ $product->product_type == 1 ? 'Simple' : 'Variable' }}</td>
                                    <td>JOD {{ $product->sale_price }}</td>
                                    <td class="text-center">
                                        <img width="40" src="{{ $product->product_image }}" alt="">
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($product->campaigns_draw_date)->format('d M, Y') }}</td>

                                    <td>
                                        <label class="switch">
                                            <input {{ $product->campaigns_status ? 'checked' : '' }} type="checkbox" class="campaign_status" data-id="{{ $product->campaigns_id }}">
                                            <span class="slider round"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <a class="text-success" href="{{ route('admin.campaigns.edit', ['product_id' => $product->product_id]) }}"><i class="bx bx-pen"></i></a>
                                        <a class="text-danger" href="javascript:void(0);" onclick="deleteProduct('{{ $product->product_id }}')"><i class="bx bx-trash"></i></a>
                                        <a class="text-primary" href="javascript:void(0)" onclick="openDetails('{{ json_encode($product) }}')"><i class="bx bx-show"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Product Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="product-details">

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@stop
@section('script')
    <script>
        $('.campaign_status').on('change', function () {
            let status = 0;
            if ($(this).is(':checked')) {
                status = 1;
            }

            $.ajax({
                url: "{{ route('admin.campaigns.change-status') }}",
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

        function openDetails(product) {
            product = JSON.parse(product)
            let campaignsDateStart = product.campaigns_date_start;
            let campaignsTimeStart = product.campaigns_time_start;
            let combinedDateTime = campaignsDateStart + ' ' + campaignsTimeStart;
            let parsedDate = new Date(combinedDateTime);
            let formattedDate = parsedDate.toLocaleString('en-US', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });

            let html = '';

            html += '<table class="table table-bordered">';
            html += '<tr>';
            html += '<td><strong>Campaign Title</strong></td>';
            html += '<td>' + product.campaigns_title + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td><strong>Campaign Description</strong></td>';
            html += '<td>' + product.campaigns_desc + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td><strong>Campaign Date</strong></td>';
            html += '<td>' + formattedDate + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td><strong>Product Name</strong></td>';
            html += '<td>' + product.product_name + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td><strong>Product Type</strong></td>';
            html += '<td>' + (product.product_type == 1 ? 'Simple' : 'Variable') + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td><strong>Stock Quantity</strong></td>';
            html += '<td>' + product.stock_quantity + '</td>';
            html += '</tr>';
            html += '</table>';

            html += '<hr>';

            html += '<table class="table table-bordered">';
            html += '<thead>';
            html += '<tr>';
            html += '<th colspan="2">Inventory Details</th>';
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';
            html += '<tr>';
            html += '<td><strong>Price</strong></td>';
            html += '<td>JOD ' + product.sale_price + '</td>';
            html += '</tr>';
            html += '<tr>';
            html += '<td colspan="2"><img width="250" src="' + product.product_image + '"></td>';
            html += '</tr>';
            html += '</tbody>';
            html += '</table>';

            $('#product-details').html(html)
            $('#detailsModal').modal('show')
        }

        function deleteProduct(id) {
            let url = '{{ route('admin.campaigns.delete', ['product_id' => ':id']) }}';
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
    </script>
@endsection