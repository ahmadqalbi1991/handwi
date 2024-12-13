@extends("admin.template.layout")

@section('header')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}admin_assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('') }}admin_assets/plugins/table/datatable/custom_dt_customer.css">
@stop


@section('content')
   <div class="container">
          <div class="order-detail-page">
            <div class="dataTables_wrapper container-fluid dt-bootstrap4">
            




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
                                        <h4>Service Request No: <?php echo config('global.sale_order_prefix')."-SER".date(date('Ymd', strtotime($list->created_at))).$list->order_id; ?> </h4>
                <div class="table-responsive">
                <table width="100%">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>: {{$list->customer_name}}</th>
                            <th>Order ID</th>
                            <th>: {{$list->order_id}}</th>
                        </tr>
                        <tr>
                            <th>Invoice ID</th>
                            <th>: {{$list->invoice_id}}</th>
                            <th>Customer Address</th>
                            <th>:                                  {{$list->address}},  <br>
                                {{$list->building_name}},<br>
                                {{$list->land_mark}}<br>
                                
                                                      </th>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <th>: {{ get_date_in_timezone($list->created_at, 'd-M-y H:i A') }}</th>
                            <th>Total Amount</th>
                            <th>: {{ number_format($list->grand_total, 2, '.', '') }}</th>
                        </tr>
                      

                        <tr>
                            <th>VAT </th>
                            <th>: {{ number_format($list->vat, 2, '.', '') }}</th>
                            <th>Discount</th>
                            <th>: {{ number_format($list->discount, 2, '.', '') }}
                                                            </th>
                        </tr>
                         
                        <tr>
                            <!-- <th>Admin Commission</th>
                            <th>: {{ number_format($list->admin_commission, 2, '.', '') }} </th> -->
                            <th>Payment Mode</th>
                            <th>:        {{ payment_mode($list->payment_mode) }}
                                
                            </th>
                             <th>Booking Date</th>
                            <th>: {{ get_date_in_timezone($list->booking_date, 'd-M-y H:i A') }}</th>
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
              
                <div class="order-page-infomatics">
                                        <form>
                        <!-- <div class="action-divs d-flex align-items-center">
                            <div class="checkbox-dsign-order-select">
                                <input type="checkbox">
                                <label>Select All</label>
                            </div>
                            <div class="cancel_btn">
                                <button class="cancel-selection">Cancel</button>
                            </div>
                            
                        </div> -->
                        @foreach($list->service_details as $dataservice)
                        <div class="product-order-details-div">
                                                        <div class="row">
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-3">
                                    <div class="product-headeing-title">
                                        <h4>Service</h4>
                                    </div>
                                    <div class="product_details-flex d-flex">
                                        <div class="producT_img">
                                                                                        <img src="{{get_uploaded_image_url($dataservice->image,'service_image_upload_dir')}}" style="width:100px;height:100px;object-fit:cover;">
                                                                                    </div>
                                        <div class="product_content">
                                            <h4 class="product-name">{{$dataservice->name}}</h4>
                                            <p>{{$dataservice->description}}</p>
                                            <p><strong>Admin commission: </strong> {{number_format($dataservice->admin_commission,2, '.', '')}}</p>
                                            <p><strong>Vendor commission: </strong> {{number_format($dataservice->vendor_commission,2, '.', '')}}</p>
                                            <p><strong>Service rate: </strong> {{number_format($dataservice->price, 2, '.', '')}}</p>
                                            <p><strong>Booking Date: </strong> {{ get_date_in_timezone($dataservice->booking_date, 'd-M-y H:i A') }}</p>
                                            <div class="action-divs d-flex align-items-center">
                                                                                               <!--  <div class="checkbox-dsign-order-select">
                                                    <input type="checkbox">
                                                    <label>Select</label>
                                                </div> -->
                                                <!-- <div class="cancel_btn">
                                                    <button class="cancel-selection" data-role="status-change" href="https://jarsite.com/moda/public/admin/order/change_status" detailsid="147" value="10">Cancel</button>

                                                </div> -->
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-3">
                                    <div class="product-headeing-title">
                                        <h4>Service Status</h4>
                                    </div>
                                    <div class="delivery-status-block">
                                         <ul class="list-unstyled">
                                            <li class="pending @if($dataservice->order_status >= config('global.order_status_pending')) active @endif"><button class="btn-design">Pending</button></li>
                                            @if($dataservice->order_status != config('global.order_status_cancelled'))
                                            <li class="accepted @if($dataservice->order_status >= config('global.order_status_accepted')) active @endif"><button class="btn-design">Accepted</button></li>
                                            <li class="delivered @if($dataservice->order_status >= config('global.order_status_delivered')) active @endif"><button class="btn-design">Completed</button></li>
                                           
                                            @else
                                            <li class="delivered @if($dataservice->order_status >= config('global.order_status_cancelled')) active @endif"><button class="btn-design">Cancelled</button></li>
                                            @endif

                                        </ul>
                                   </div>

                                   <select class="form-control" data-role="status-change" href="https://jarsite.com/moda/public/admin/order/change_status" detailsid="147" style="display: none;">
                                <option value="0">Pending</option>
                                <option value="1" selected="">Accepted</option>
                                <option value="2">Ready for Delivery</option>
                                <option value="3">Dispatched</option>
                                <option value="4">Delivered</option>
                                <option value="10">Cancelled</option>
                            </select>
                                </div>
                            </div>
                                                    </div>
                                                    @endforeach
                    

                                    </div>
            </div>
            </div>
             </div>
            </div>
    </div>
      </div>
@stop
