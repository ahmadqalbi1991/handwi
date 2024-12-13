@extends("portal.template.layout")

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
                            <th>: {{$list[0]->name??$list[0]->customer_name}}</th>
                        </tr>
                        <tr>
                        <th>Created on.</th>
                            <th>: {{get_date_in_timezone($list[0]->created_at,'d-M-Y h:i A')}}</th>
                            {{--<th>Invoice No.</th>
                            <th>: {{$list[0]->invoice_id}}</th>--}}
                            <th> @if($list[0]->order_type == 1) Order Type @else Delivery Address @endif</th>
                            <th>: 
                                @if(!empty($list[0]->shipping_address)) <?php $shipdata = $list[0]->shipping_address;
                             ?>
                                {{$shipdata->address}}, <br>
                                {{$shipdata->building_name}},<br>
                                {{$shipdata->land_mark}}<br>
                            @elseif($list[0]->order_type == 1)
                             Pick Up
                            @else
                                                                    
                            
                              @endif
                        </th>
                        </tr>
                        @if($list[0]->order_type == 1)
                        <tr>
                            <th >Pick Up Date</th>
                            <th >{{ $list[0]->pick_up_date ? date('d-M-y',strtotime($list[0]->pick_up_date)) : '-' }}</th>
                        
                            <th >Pick Up Time</th>
                            <th >{{ $list[0]->pick_up_time ? $list[0]->pick_up_time : '-' }}</th>
                        </tr>
                        @endif
                        <tr>
                            
                            <th>Sale Amount</th>
                            <th>: {{number_format($list[0]->total, 2, '.', '')}}</th>
                            <th>VAT</th>
                            <th>: {{number_format($list[0]->vat, 2, '.', '')}}</th>
                        </tr>
                        <tr>
                            
                           <th>Coupon Discount</th>
                            <th>: {{number_format($list[0]->discount, 2, '.', '')}}</th>
                            <th>Sub Total </th>
                            <th>: {{number_format(($list[0]->total - $list[0]->discount) + $list[0]->vat, 2, '.', '')}}</th>
                        </tr>
                        <tr>
                            
                            <th>Service charge</th>
                            <th>:{{number_format($list[0]->service_charge, 2, '.', '')}}
                            
                            </th>
                            <th>Shipping Charge</th>
                            <th>: {{number_format($list[0]->shipping_charge, 2, '.', '')}} </th>
                        </tr>
                        <tr>
                            
                            <th>Payment Mode</th>
                            <th>:{{payment_mode($list[0]->payment_mode)}}
                            
                            </th>
                            <th>Grand Total</th>
                            <th>: {{number_format($list[0]->grand_total, 2, '.', '')}} </th>
                        </tr>
                        <tr>
                            <th>Admin Earning</th>
                            <th>: {{number_format($list[0]->admin_commission, 2, '.', '')}}</th>
                            <th>Vendor Earning</th>
                            <th>: {{number_format($list[0]->vendor_commission, 2, '.', '')}} </th>
                        </tr>
                        <tr>
                           
                            <th>Invoice</th>
                            <th>: <a href="{{get_pdf_url($ordernumber)}}" target="_blank"><button type="button" class="btn btn-primary">View Invoice</button></a> </th>
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
                            <td></td>
                            <td>{{order_status($datavalue->order_status)}}</td>
                           </tr>
                    
                    <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
             
               </div> -->
                <div class="order-page-infomatics">

                     <div class="row">
                                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-3">
                                    <div class="product-headeing-title">
                                        <h4>Delivery Status</h4>
                                    </div>
                                    
                                    @php
                                    
                                      $accepted = \App\Models\OrderStatusHistory::where(['order_id'=>$list[0]->order_id,'status_id'=>config('global.order_status_accepted')])->first()??'';
                                    $ready = \App\Models\OrderStatusHistory::where(['order_id'=>$list[0]->order_id,'status_id'=>config('global.order_status_ready_for_delivery')])->first()??'';
                                    $dispatched = \App\Models\OrderStatusHistory::where(['order_id'=>$list[0]->order_id,'status_id'=>config('global.order_status_dispatched')])->first()??'';
                                    $deliverd = \App\Models\OrderStatusHistory::where(['order_id'=>$list[0]->order_id,'status_id'=>config('global.order_status_delivered')])->first()??'';
                                    $cancelled = \App\Models\OrderStatusHistory::where(['order_id'=>$list[0]->order_id,'status_id'=>config('global.order_status_cancelled')])->first()??'';
                                    $rejected = \App\Models\OrderStatusHistory::where(['order_id'=>$list[0]->order_id,'status_id'=>config('global.order_status_rejected')])->first()??'';
                                    @endphp

                                    <div class="delivery-status-block">
                                        <ul class="list-unstyled">
                                            <li class="pending @if($datavalue->order_status >= config('global.order_status_pending')) active @endif"><button class="btn-design">Pending</button>
                                            <br>{{get_date_in_timezone($list[0]->created_at,'d-M-Y h:i A')}}</li>
                                            @if($datavalue->order_status != config('global.order_status_cancelled') && $list[0]->status != config('global.order_status_rejected'))
                                            <li class="accepted @if($datavalue->order_status >= config('global.order_status_accepted')) active @endif"><button class="btn-design">Accepted</button>
                                            @if(!empty($accepted))<br>{{get_date_in_timezone($accepted->created_at,'d-M-Y h:i A')}}@endif</li>
                                            <li class="ready-for-delivery @if($datavalue->order_status >= config('global.order_status_ready_for_delivery')) active @endif"><button class="btn-design">Ready For Delivery</button>
                                            @if(!empty($ready))<br>{{get_date_in_timezone($ready->created_at,'d-M-Y h:i A')}}@endif</li>
                                            <li class="dispatched @if($datavalue->order_status >= config('global.order_status_dispatched')) active @endif"><button class="btn-design">Dispatched</button>
                                            @if(!empty($dispatched))<br>{{get_date_in_timezone($dispatched->created_at,'d-M-Y h:i A')}}@endif</li>
                                            <li class="delivered @if($datavalue->order_status >= config('global.order_status_delivered')) active @endif"><button class="btn-design">Delivered</button>
                                            @if(!empty($deliverd))<br>{{get_date_in_timezone($deliverd->created_at,'d-M-Y h:i A')}}@endif</li>
                                           
                                            @else
                                            @if($datavalue->order_status == config('global.order_status_cancelled'))
                                            <li class="delivered @if($datavalue->order_status >= config('global.order_status_cancelled')) active @endif"><button class="btn-design">Cancelled</button>
                                            @if(!empty($cancelled))<br>{{get_date_in_timezone($cancelled->created_at,'d-M-Y h:i A')}}@endif</li>
                                            @else
                                            <li class="delivered @if($datavalue->order_status >= config('global.order_status_rejected')) active @endif"><button class="btn-design">Rejected</button>
                                             @if(!empty($rejected))<br>{{get_date_in_timezone($rejected->created_at,'d-M-Y h:i A')}}@endif</li>
                                            @endif
                                            @endif

                                        </ul>
                                   </div>



                                   <select class="form-control" data-role="status-change" href="{{url('portal/order/change_status')}}" detailsid="{{$datavalue->id}}" style="display: none;">
                                <option value="{{config('global.order_status_pending')}}" @if(!empty($datavalue->order_status)) {{$datavalue->order_status==config('global.order_status_pending') ? "selected" : null}} @endif>Pending</option>
                                <option value="{{config('global.order_status_accepted')}}"  @if(!empty($datavalue->order_status)) {{$datavalue->order_status==config('global.order_status_accepted') ? "selected" : null}} @endif >Accepted</option>
                                <option value="{{config('global.order_status_ready_for_delivery')}}" @if(!empty($datavalue->order_status)) {{$datavalue->order_status==config('global.order_status_ready_for_delivery') ? "selected" : null}} @endif>Ready for Delivery</option>
                                <option value="{{config('global.order_status_dispatched')}}" @if(!empty($datavalue->order_status)) {{$datavalue->order_status==config('global.order_status_dispatched') ? "selected" : null}} @endif>Dispatched</option>
                                <option value="{{config('global.order_status_delivered')}}" @if(!empty($datavalue->order_status)) {{$datavalue->order_status==config('global.order_status_delivered') ? "selected" : null}} @endif>Delivered</option>
                                <option value="{{config('global.order_status_cancelled')}}" @if(!empty($datavalue->order_status)) {{$datavalue->order_status==config('global.order_status_cancelled') ? "selected" : null}} @endif>Cancelled</option>
                            </select>
                                </div>
                                  <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-3">
                                <div class="action-divs d-flex align-items-center">
                                                @if($list[0]->status != config('global.order_status_cancelled'))
                                                {{-- <div class="checkbox-dsign-order-select">
                                                    <input type="checkbox">
                                                    <label>Select</label>
                                                </div> --}}
                                                <div class="cancel_btn">
                                                    {{-- <button type="button" class="btn btn-danger">Cancel</button> --}}
                                                     @if($list[0]->status != config('global.order_status_cancelled') || $list[0]->status != config('global.order_status_delivered'))
                                                    @if($list[0]->status == config('global.order_status_pending'))
                                                        <span class="btn btn-warning" data-role="status-change" href="{{url('portal/order/change_status')}}" detailsid="{{$list[0]->order_id}}" value="{{config('global.order_status_accepted')}}" >Accept</span>
                                                        <span class="btn btn-danger" data-role="status-change" href="{{url('admin/order/change_status')}}" detailsid="{{$list[0]->order_id}}" value="{{config('global.order_status_cancelled')}}" >cancel</span>
                                                    @endif

                                                    @if($list[0]->status == config('global.order_status_accepted'))
                                                        <span class="btn btn-design" data-role="status-change" href="{{url('portal/order/change_status')}}" detailsid="{{$list[0]->order_id}}" value="{{config('global.order_status_ready_for_delivery')}}" >Ready for Delivery</span>
                                                        <!-- <span class="btn btn-danger" data-role="status-change" href="{{url('admin/order/change_status')}}" detailsid="{{$list[0]->order_id}}" value="{{config('global.order_status_cancelled')}}" >cancel</span> -->
                                                    @endif

                                                    @if($list[0]->status == config('global.order_status_ready_for_delivery'))
                                                        <span class="btn btn-info" data-role="status-change" href="{{url('portal/order/change_status')}}" detailsid="{{$list[0]->order_id}}" value="{{config('global.order_status_dispatched')}}" >Dispatched</span>
                                                        <!-- <span class="btn btn-danger" data-role="status-change" href="{{url('admin/order/change_status')}}" detailsid="{{$list[0]->order_id}}" value="{{config('global.order_status_cancelled')}}" >cancel</span> -->
                                                    @endif

                                                    @if($list[0]->status == config('global.order_status_dispatched'))
                                                        <span class="btn btn-success" data-role="status-change" href="{{url('portal/order/change_status')}}" detailsid="{{$list[0]->order_id}}" value="{{config('global.order_status_delivered')}}" >Delivered</span>
                                                        <!-- <span class="btn btn-danger" data-role="status-change" href="{{url('admin/order/change_status')}}" detailsid="{{$list[0]->order_id}}" value="{{config('global.order_status_cancelled')}}" >cancel</span> -->
                                                    @endif
                                                     @endif
                                                </div>
                                                @endif

                                            </div>
                                        </div></div>
                    
                    <?php if(sizeof($list[0]->order_products)) { ?>
                    <form>
                        <div class="action-divs d-flex align-items-center">
                            
                            {{-- <div class="edit-order_btn">
                                <a href="{{ url('vendor/order_edit/1')}}" class="edit-btn">edit</a>
                            </div> --}}
                        </div>
                        <div class="product-order-details-div">
                            <?php foreach($list[0]->order_products as $datavalue) { ?>
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 col-md-6 col-sm-12 mb-3">
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
                                             @if(isset($datavalue->attribute_name) && $datavalue->attribute_name)
                                            
                                            @endif
                                            @if(isset($datavalue->selected_attribute_list) && !empty($datavalue->selected_attribute_list))
                                                @foreach($datavalue->selected_attribute_list as $variation)
                                                        <p><b>{{$variation->attribute_name}}</b>: {{$variation->attribute_values}}</p>
                                                @endforeach
                                            @endif
                                            <p><strong>Quantity: </strong> {{$datavalue->order_qty}}</p>
                                            <!-- <p><strong>Shipping Charge: </strong> {{ number_format($datavalue->shipping_charge,2) }}</p> -->
                                            <p><strong>Total: </strong> {{$datavalue->order_total}}</p>
                                            
                                                
                                                
                                            
                                              

                                          

                                           
                                        </div>
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
        $('body').off('click', '[data-role="status-change"]');
        $('body').on('click', '[data-role="status-change"]', function(e) {
            e.preventDefault();
            var msg = $(this).data('message') || 'Are you sure that you want to change status?';
            var href = $(this).attr('href');
            var detailsid = $(this).attr('detailsid');
            var statusid = $(this).attr('value');
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
        

        $('body').off('click', '[data-role="cancel-order"]');
        $('body').on('click', '[data-role="cancel-order"]', function(e) {
            e.preventDefault();
            var msg = $(this).data('message') || 'Are you sure that you want to cancel this order?';
            var href = $(this).attr('href');
            var order_id = $(this).attr('order_id');
            var title = 'Confirm Cancel Order';

            App.confirm(title, msg, function() {
                var ajxReq = $.ajax({
                    url: href,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "order_id": order_id,
                    },
                    success: function(res) {
                        if (res['status'] == 1) {
                            App.alert(res['message'], 'Success!');
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);

                        } else {
                            App.alert(res['message'],
                                'Failed!');
                        }
                    },
                    error: function(jqXhr, textStatus, errorMessage) {

                    }
                });
            });

        });


        $('body').off('click', '[data-role="return-status-change"]');
        $('body').on('click', '[data-role="return-status-change"]', function(e) {
            e.preventDefault();
            var msg = $(this).data('message') || 'Are you sure that you want to change return status?';
            var href = $(this).attr('href');
            var detailsid = $(this).attr('detailsid');
            var statusid = $(this).attr('value');
            var title = $(this).data('title') || 'Confirm Return Status Change';

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
                            App.alert(res['message'] || 'Successfully updated', 'Success!');
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);

                        } else {
                            App.alert(res['message'] || 'Unable to change the status.',
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
