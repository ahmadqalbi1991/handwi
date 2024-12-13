@extends("admin.template.layout")

@section('header')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}admin_assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('') }}admin_assets/plugins/table/datatable/custom_dt_customer.css">
@stop


@section('content')
    <div class="order-detail-page">
            <div class="dataTables_wrapper container-fluid dt-bootstrap4">
            {{-- <form action="" method="get">
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Date From</label>
                            <input type="text" name="from" class="form-control datepicker" autocomplete="off" value="">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Date To</label>
                            <input type="text" name="to" class="form-control datepicker" autocomplete="off" value="">
                        </div> 
                        <div class="col-md-4 form-group">
                            <label>Search Order ID</label>
                            <input type="text" name="search_key" class="form-control" autocomplete="off"
                                value="{{ $search_key }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Customer Name</label>
                            <input type="text" name="search_key" class="form-control" autocomplete="off"
                                value="{{ $search_key }}">
                        </div>
                        <button type="submit" class="btn btn-warning mb-4 mr-2 btn-rounded">Search</button>
                    </div>
                </form>--}}




                <!-- <div class="row mt-3">
                    <div class="col-sm-12 col-md-6">
                        <div class="dataTables_length" id="column-filter_length">
                        </div>
                    </div>


                </div> -->
                
                <div class="order-totel-details">
                    <div class="card">
                    <div class="card-body">
                    <div class="col-sm-12">
                        <?php $ordernumber = config('global.sale_order_prefix').date(date('Ymd', strtotime($list[0]->created_at))).$list[0]->order_id; ?>
                <h4>Order NO: {{$ordernumber}} </h4>
                <div class="table-responsive">
                <table width="100%">
                    <thead>
                        <tr>
                            <th>Order No.</th>
                            <th>: {{$ordernumber}}</th>
                            <th>Customer</th>
                            <th>: {{$list[0]->customer_name}}</th>
                        </tr>
                        <tr>
                            <th>Bill No.</th>
                            <th>: {{$list[0]->invoice_id}}</th>
                            <th>Delivery Address</th>
                            <th>: @if(!empty($list[0]->shipping_address)) <?php $shipdata = $list[0]->shipping_address;
                             ?>
                                {{$shipdata->address}}, <br>
                                {{$shipdata->city_name}},<br>
                                {{$shipdata->country_name}},<br>
                                {{$shipdata->email}},<br>
                                {{$shipdata->dial_code}} {{$shipdata->phone}}<br>

                            
                              @endif
                        </th>
                        </tr>
                        <tr>
                            <th>Created on.</th>
                            <th>: {{ get_date_in_timezone($list[0]->created_at, 'd-M-y H:i A') }}</th>
                            <th>Sale Amount</th>
                            <th>: {{number_format($list[0]->total, 2, '.', '')}}</th>
                        </tr>
                      {{--  <tr>
                            <th>VAT</th>
                            <th>: {{number_format($list[0]->vat, 2, '.', '')}}</th>
                            <th></th>
                            <th></th>
                        </tr>
                        <tr>
                            <th>Coupon Discount</th>
                            <th>: {{number_format('0', 2, '.', '')}}</th>
                            <th></th>
                            <th></th>
                        </tr>--}}

                        <tr>
                            <th>Sub Total </th>
                            <th>: {{number_format($list[0]->total + $list[0]->vat, 2, '.', '')}}</th>
                            <th>Mode of Delivery</th>
                            <th>: @if($list[0]->payment_mode==1)
        {{'WALLET'}}
    @else
        {{'CARD'}}
    @endif
                            
                            </th>
                        </tr>
                         {{--<tr>
                            <th>Shipping Charge</th>
                            <th>: {{number_format('0', 2, '.', '')}}</th>
                            <th>Service Charge</th>
                            <th>: {{number_format('0', 2, '.', '')}}</th>
                        </tr>--}}
                        <tr>
                            <th>Grand Total</th>
                            <th>: {{number_format($list[0]->grand_total, 2, '.', '')}} </th>
                           {{-- <th>Amount Paid</th>
                            <th>: {{number_format('0', 2, '.', '')}}</th>--}}
                        </tr>
                    </thead>

                </table>
                </div>
                    </div>
                    </div>
                    </div>
                </div>
                <div class="order-totel-details">
                <div class="card">
                <div class="card-body">
                <!-- <div class="col-sm-12" style="padding-top: 20px;">
                <?php if(sizeof($list[0]->order_products)) { ?>
                <h4>Order Items <a href="{{ url('admin/order_edit/1')}}">edit</a></h4>
                <table class="table table-condensed table-striped">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Vendor</th>
                            <th>Item</th>
                            <th>Quantity</th>
                           {{-- <th>Discount</th>--}}
                           <th> Shipping Charge </th>
                            <th>Total</th>
                            <th>Change Status</th>
                            <th>Status</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($list[0]->order_products as $datavalue) { ?>
                           <tr>
                            <td>
                                @if($datavalue->product_images)
                                <img src="{{$datavalue->product_images[0]}}" style="width:100px;height:100px;object-fit:cover;">
                                @endif</td>
                            <td>{{$datavalue->name}}</td>
                            <td>{{$datavalue->product_name}}</td>
                            <td>{{$datavalue->order_qty}}</td>
                            {{--<td>{{$datavalue->order_discount}}</td>--}}
                            <td>{{ number_format($datavalue->shipping_charge,2) }}</td>
                            <td>{{$datavalue->order_total}}{{config('global.site_name')}}</td> 
                            <td><select class="form-control" data-role="status-change" href="{{url('admin/order/change_status')}}" detailsid="{{$datavalue->id}}">
                                <option value="{{config('global.order_status_pending')}}" @if(!empty($datavalue->order_status)) {{$datavalue->order_status==config('global.order_status_pending') ? "selected" : null}} @endif>Pending</option>
                                <option value="{{config('global.order_status_accepted')}}"  @if(!empty($datavalue->order_status)) {{$datavalue->order_status==config('global.order_status_accepted') ? "selected" : null}} @endif >Accepted</option>
                                <option value="{{config('global.order_status_ready_for_delivery')}}" @if(!empty($datavalue->order_status)) {{$datavalue->order_status==config('global.order_status_ready_for_delivery') ? "selected" : null}} @endif>Ready for Delivery</option>
                                <option value="{{config('global.order_status_dispatched')}}" @if(!empty($datavalue->order_status)) {{$datavalue->order_status==config('global.order_status_dispatched') ? "selected" : null}} @endif>Dispatched</option>
                                <option value="{{config('global.order_status_delivered')}}" @if(!empty($datavalue->order_status)) {{$datavalue->order_status==config('global.order_status_delivered') ? "selected" : null}} @endif>Delivered</option>
                                <option value="{{config('global.order_status_cancelled')}}" @if(!empty($datavalue->order_status)) {{$datavalue->order_status==config('global.order_status_cancelled') ? "selected" : null}} @endif>Cancelled</option>
                            </select></td>
                            <td>{{order_status($datavalue->order_status)}}</td>
                           </tr>
                    
                    <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
             
               </div> -->
                <div class="order-page-infomatics">
                    <?php if(sizeof($list[0]->order_products)) { ?>
                    <form>
                        <div class="action-divs d-flex align-items-center">
                            <div class="checkbox-dsign-order-select">
                                <input type="checkbox">
                                <label>Select All</label>
                            </div>
                            <div class="cancel_btn">
                                <button class="cancel-selection">Cancel</button>
                            </div>
                            <div class="edit-order_btn">
                                <a href="{{ url('vendor/order_edit/1')}}" class="edit-btn">edit</a>
                            </div>
                        </div>
                        <div class="product-order-details-div">
                            <?php foreach($list[0]->order_products as $datavalue) { ?>
                            <div class="row">
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-3">
                                    <div class="product-headeing-title">
                                        <h4>Products</h4>
                                    </div>
                                    <div class="product_details-flex d-flex">
                                        <div class="producT_img">
                                            @if($datavalue->product_images)
                                            <img src="{{$datavalue->product_images[0]}}" style="width:100px;height:100px;object-fit:cover;">
                                            @endif
                                        </div>
                                        <div class="product_content">
                                            <h4 class="product-name">{{$datavalue->product_name}}</h4>
                                            <p><strong>Vendor: </strong> {{$datavalue->name}}</p>
                                            <p><strong>Quantity: </strong> {{$datavalue->order_qty}}</p>
                                            <p><strong>Shipping Charge: </strong> {{ number_format($datavalue->shipping_charge,2) }}</p>
                                            <p><strong>Total: </strong> {{$datavalue->order_total}}{{config('global.site_name')}}</p>
                                            <div class="action-divs d-flex align-items-center">
                                                <div class="checkbox-dsign-order-select">
                                                    <input type="checkbox">
                                                    <label>Select</label>
                                                </div>
                                                <div class="cancel_btn">
                                                    <button class="cancel-selection">Cancel</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-3">
                                    <div class="product-headeing-title">
                                        <h4>Delivery Status</h4>
                                    </div>
                                    <div class="delivery-status-block">
                                        <ul class="list-unstyled">
                                            <li class="pending active"><button class="btn-design">Pending</button></li>
                                            <li class="accepted"><button class="btn-design">Accepted</button></li>
                                            <li class="ready-for-delivery"><button class="btn-design">Ready For Delivery</button></li>
                                            <li class="dispatched"><button class="btn-design">Dispatched</button></li>
                                            <li class="delivered"><button class="btn-design">Delivered</button></li>
                                        </ul>
                                   </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </form>
                    <?php } ?>
                </div>
            </div>
            </div>
             </div>
            </div>
    </div>
@stop

@section('script')
    <script src="{{ asset('') }}admin_assets/plugins/table/datatable/datatables.js"></script>
    <script>
        $('body').off('change', '[data-role="status-change"]');
        $('body').on('change', '[data-role="status-change"]', function(e) {
            e.preventDefault();
            var msg = $(this).data('message') || 'Are you sure that you want to change status?';
            var href = $(this).attr('href');
            var detailsid = $(this).attr('detailsid');
            var statusid = $(this).val();
            var title = $(this).data('title') || 'Confirm Status Change';

            App.confirm(title, msg, function() {
                var ajxReq = $.ajax({
                    url: href,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "detailsid": detailsid,
                        "statusid": statusid,
                    },
                    success: function(res) {
                        if (res['status'] == 1) {
                            App.alert(res['message'] || 'Status changed successfully', 'Success!');
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);

                        } else {
                            App.alert(res['message'] || 'Unable to change the record.',
                                'Failed!');
                        }
                    },
                    error: function(jqXhr, textStatus, errorMessage) {

                    }
                });
            });

        });
    </script>
@stop
