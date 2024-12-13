@extends("portal.template.layout")

@section('header')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}admin_assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('') }}admin_assets/plugins/table/datatable/custom_dt_customer.css">
          <style>
            .btn-warning{
                    background: #000;
    color: #fff !important;
    border-color: #000 !important;
    padding: 6px 16px !important;
            }
        </style>
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
                        <?php
                        $ordernumber =  config('global.sale_order_prefix')."-SER".date(date('Ymd', strtotime($list->created_at))).$list->order_id; ?>
                                        <h4>Service Request No: {{$ordernumber}} </h4>
                <div class="table-responsive">
                <table width="100%">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <td>: {{$list->customer_name}}</td>
                            <th>Order ID</th>
                            <td>: {{$list->order_id}}</td>
                        </tr>
                        <tr>
                            <th>Vendor</th>
                            <td>: {{$list->service_details[0]->first_name??''}} {{$list->service_details[0]->last_name??'-'}}</td>
                            <th>Customer Address</th>
                            <td>:{{$list->address}},  
                                {{$list->building_name}},
                                {{$list->land_mark}}
                                <!-- {{$list->location}}<br> -->
                                 <!-- <button class="btn btn-warning" href="#!" data-toggle="modal" data-target="#viewlocation">View On Map</button> -->
                                 <a target="_blank" href="http://www.google.com/maps/place/{{$list->latitude??25.193437221217184}},{{$list->longitude??55.29526081705425}}">
                                    <button class="btn mt-2" style="border: none;background-color: #2C93FA;color: #fff;padding: 4px 10px;font-weight: 500;border-radius: 6px;text-transform: capitalize;font-size:11px;"> View On Map</button>
                                </a>
                                
                                                      </td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>: {{$list->email}}</td>
                            <th>Phone</th>
                            <td>: {{$list->dial_code}} {{$list->phone}}</td>
                        </tr>
                        <tr>
                            <th>Created Date</th>
                            <td>: {{ fetch_booking_created_at_date($list->order_id) }} </td>
                            <th>Total Amount</th>
                            <td>: {{ number_format($list->grand_total, 2, '.', '') }}</td>
                        </tr>
                      

                        <tr>
                            <th>VAT </th>
                            <td>: {{ number_format($list->vat, 2, '.', '') }}</td>
                            <th>Discount</th>
                            <td>: {{ number_format($list->discount, 2, '.', '') }}
                                                            </td>
                        </tr>

                        <tr>
                            <th>Service Charge </th>
                            <td>: {{ number_format($list->service_charge, 2, '.', '') }}</td>
                            <th>Invoice</th>
                            <td><a href="{{get_service_pdf_url($list->order_no)}}" target="_blank"><button type="button" class="btn btn-primary">View Invoice</button></a>
                                                            </td>
                        </tr>
                         
                        <!-- <tr>
                            <th>Admin Commission</th>
                            <th>: {{number_format($list->service_details[0]->admin_commission??0, 2, '.', '')}} </th>
                            <th>Payment Mode</th>
                            <th>:        {{ payment_mode($list->payment_mode) }}
                                
                            </th>
                        </tr> -->

                        
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
                        <div class="action-divs d-flex align-items-center">

                                       
                            
                        </div>
                        
                        <div class="product-order-details-div">

                                                        <div class="row">
                                                        
                                                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-4">
                                                            @foreach($list->service_details as $key_item =>$dataservice)
                                                            @if($key_item == 0)
                                                            <div class="product-headeing-title">
                                                                <h4>Service</h4>
                                                            </div>
                                                            @endif
                                                            <div class="product_details-flex d-flex mb-4">
                                                                <div class="producT_img">
                                                                                                                <img src="{{get_uploaded_image_url($dataservice->image,'service_image_upload_dir')}}" style="width:100px;height:100px;object-fit:cover;">
                                                                                                            </div>
                                                                <div class="product_content">
                                                                    <h4 class="product-name">{{$dataservice->name}}</h4>
                                                                    <p>{{$dataservice->description}}</p>
                                                                    <p><strong>Service rate: </strong>{{$dataservice->qty}} X {{number_format($dataservice->price, 2, '.', '')}} - {{$dataservice->text}}</p>
                                                                    
                                                                    
                                                                    <div class="action-divs d-flex align-items-center">
                                                                                                                       <!--  <div class="checkbox-dsign-order-select">
                                                                            <input type="checkbox">
                                                                            <label>Select</label>
                                                                        </div> -->
                                                                         
                                                                        
                                                                    </div>
                                                                   
                                                                </div>
                                                            </div>
                                                            @endforeach
                                                            
                                                            @foreach($list->service_details as $key_item =>$dataservice)
                                                            <div class="w-100 mt-3">
                                                                 @if($key_item == 0)
                                                                <p><strong>Scheduled date: </strong>{{ date('d-M-y h:i A', strtotime($dataservice->booking_date))}}</p>
                                                                    <p><strong>Task Description: </strong> {{$dataservice->task_description??'-'}}</p>
                                                                    
                                                                    @if(!empty($dataservice->doc))
                                                                    <p><strong>Task Attachment: </strong> <a href="{{asset($dataservice->doc)}}" style="border: none;background-color: #2C93FA;color: #fff;padding: 4px 10px;font-weight: 500;border-radius: 6px;text-transform: capitalize;font-size:11px;"> View Attachment</a></p>
                        
                                                                    @endif
                                                                    
                                                                    @endif
                                                            </div>
                                                            @endforeach
                                                        </div>
                                
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-4">
                                    @foreach($list->service_details as $key_item =>$dataservice)
                                @if($key_item == 0)
                                    <div class="product-headeing-title">
                                        <h4>Service Status</h4>
                                    </div>
                                    @endif
                                    @if($key_item == 0)
                                            <div class="action-divs d-flex align-items-center">
                                                <br> <br><br>
                                                @if($dataservice->order_status != config('global.order_status_cancelled'))
                                                {{-- <div class="checkbox-dsign-order-select">
                                                    <input type="checkbox">
                                                    <label>Select</label>
                                                </div> --}}
                                                <div class="cancel_btn">
                                                    {{-- <button type="button" class="btn btn-danger">Cancel</button> --}}
                                                     @if($dataservice->order_status != config('global.order_status_cancelled') || $datavalue->order_status != config('global.order_status_delivered'))
                                                    @if($dataservice->order_status == config('global.order_status_pending'))
                                                    <span class="btn btn-warning" style="border: none;padding: 6px 15px;font-weight: 600;border-radius: 6px;text-transform: capitalize;" data-role="status-change" href="{{url('portal/service/change_status')}}" orderid="{{$dataservice->order_id}}" detailsid="{{$dataservice->id}}" value="{{config('global.order_status_accepted')}}" >Accept</span>
                                                    @if($list->status < 1)
                                                <button class="cancel-selection" data-role="status-change" href="{{url('portal/service/change_status_rejected')}}" detailsid="{{$list->service_details[0]->id}}" orderid="{{$list->order_id}}" value="{{config('global.order_status_cancelled')}}">Reject order</button>
                                               @endif
                                                    @endif
                                                    @if($dataservice->order_status == config('global.order_status_accepted'))
                                                    <span class="btn btn-warning" style="border: none;padding: 6px 15px;font-weight: 600;border-radius: 6px;text-transform: capitalize;"data-role="status-change" href="{{url('portal/service/change_status')}}" orderid="{{$dataservice->order_id}}" detailsid="{{$dataservice->id}}" value="{{config('global.order_status_dispatched')}}" >Ongoing</span>
                                                    @endif
                                                    @if($dataservice->order_status == config('global.order_status_dispatched'))
                                                    <span class="btn btn-success" style="border: none;padding: 6px 15px;font-weight: 600;border-radius: 6px;text-transform: capitalize;" data-role="status-change" href="{{url('portal/service/change_status')}}" orderid="{{$dataservice->order_id}}" detailsid="{{$dataservice->id}}" value="{{config('global.order_status_delivered')}}" >Completed</span>
                                                    @endif
                                                     @endif
                                                </div>
                                                @endif

                                            </div> 
                                            @endif
                                    @if($key_item == 0)
                                    <div class="delivery-status-block">
                                         <ul class="list-unstyled">
                                            <li class="pending @if($dataservice->order_status >= config('global.order_status_pending')) active @endif"><button class="btn-design">Pending</button></li>
                                            @if($dataservice->order_status != config('global.order_status_cancelled'))
                                            <li class="accepted @if($dataservice->order_status >= config('global.order_status_accepted')) active @endif"><button class="btn-design">Accepted</button></li>
                                            <li class="accepted @if($dataservice->order_status >= config('global.order_status_dispatched')) active @endif"><button class="btn-design">Ongoing</button></li>
                                            <li class="delivered @if($dataservice->order_status >= config('global.order_status_delivered')) active @endif"><button class="btn-design">Completed</button></li>
                                           
                                            @else
                                            <li class="delivered @if($dataservice->order_status >= config('global.order_status_cancelled')) active @endif"><button class="btn-design">Cancelled</button></li>
                                            @endif
                                        </ul>
                                   </div>
                                   @endif

                                   <select class="form-control" data-role="status-change" href="https://jarsite.com/moda/public/admin/order/change_status" detailsid="147" style="display: none;">
                                <option value="0">Pending</option>
                                <option value="1" selected="">Accepted</option>
                                <option value="2">Ready for Delivery</option>
                                <option value="3">Dispatched</option>
                                <option value="4">Delivered</option>
                                <option value="10">Cancelled</option>
                            </select>
                                </div>
                                @endforeach
                            </div>
                                                    </div>
                                                   
                    

                                    </div>
            </div>
            </div>
             </div>
            </div>
    </div>
      </div>
      <!-- Modal -->
<div class="modal fade" id="viewlocation" tabindex="-1" role="dialog" aria-labelledby="viewlocationLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content overflow-hidden">
      <div class="modal-header">
        <h5 class="modal-title" id="viewlocationLabel">View Location</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-0 overflow-hidden">
     <div id="my_map_add" style="border:0; width:100%; height: 450px;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
            var orderid = $(this).attr('orderid');
            var statusid = $(this).attr('value');
            var title = $(this).data('title') || 'Confirm Status Change';

            App.confirm(title, msg, function() {
                var ajxReq = $.ajax({
                    url: href,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "order_id": orderid,
                        "id": detailsid,
                        "order_status": statusid,
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
    <script type="text/javascript">
function my_map_add() {
var myMapCenter = new google.maps.LatLng({{$list->latitude??25.193437221217184}}, {{$list->longitude??55.29526081705425}});
var myMapProp = {center:myMapCenter, zoom:12, scrollwheel:false, draggable:false, mapTypeId:google.maps.MapTypeId.ROADMAP};
var map = new google.maps.Map(document.getElementById("my_map_add"),myMapProp);
var marker = new google.maps.Marker({position:myMapCenter});
marker.setMap(map);

}

</script>

<script src="https://maps.googleapis.com/maps/api/js?key={{config('global.google_map_key')}}&callback=my_map_add"></script>

@stop
