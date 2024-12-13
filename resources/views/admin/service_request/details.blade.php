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
                            <td>: {{$list->customer_name}}</td>
                            <th>Order ID</th>
                            <td>: {{$list->order_id}}</td>
                        </tr>
                        <tr>
                            <th>Vendor</th>
                            <td>: {{$list->company_name}} </td>
                            <th>Customer Address</th>
                            <td>:{{$list->address}}, 
                                {{$list->building_name}},
                                {{$list->land_mark}}
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>: {{$list->email}}</td>
                            <th>Phone</th>
                            <td>: {{$list->dial_code}} {{$list->phone}}</td>
                        </tr>
                        <tr>
                            <th>Created Date</th>
                            <td>: {{ fetch_booking_created_at_date($list->order_id) }}</td>
                            <th>Total Amount</th>
                            <td>: {{ number_format($list->grand_total, 2, '.', '') }}</td>
                        </tr>
                        <tr>
                            <th>Sub Total </th>
                            <td>: {{ number_format($list->total, 2, '.', '') }}</td>
                            <th></th>
                            <td>
                                                            </td>
                        </tr>

                        <tr>
                            <th>VAT </th>
                            <td>: {{ number_format($list->vat, 2, '.', '') }}</td>
                            <th>Discount</th>
                            <td>: {{ number_format($list->discount, 2, '.', '') }}
                                                            </td>
                        </tr>
                         
                        <tr>
                            <th>Service charge</th>
                            <td>: {{ number_format($list->service_charge, 2, '.', '') }}
                            <th>Payment Mode</th>
                            <td>:        {{ payment_mode($list->payment_mode) }}
                                
                            </td>
                             
                        </tr>
                        <tr>
                           <th>Invoice</th>
                            <td>: <a href="{{get_service_pdf_url($list->order_no)}}" target="_blank"><button type="button" class="btn btn-primary">View Invoice</button></a>
                                                            </td>
                             
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
                       

                        <div class="product-order-details-div">
                                                        <div class="row">
                                                             
                                                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-3">
                                                                @foreach($list->service_details as $key_it =>$dataservice)
                                                                
                                                                 @php
                                    
                                      $accepted = \App\Models\ServiceOrderStatusHistory::where(['order_id'=>$dataservice->order_id,'status_id'=>config('global.order_status_accepted')])->first()??'';
                                    $ready = \App\Models\ServiceOrderStatusHistory::where(['order_id'=>$dataservice->order_id,'status_id'=>config('global.order_status_ready_for_delivery')])->first()??'';
                                    $dispatched = \App\Models\ServiceOrderStatusHistory::where(['order_id'=>$dataservice->order_id,'status_id'=>config('global.order_status_dispatched')])->first()??'';
                                    $deliverd = \App\Models\ServiceOrderStatusHistory::where(['order_id'=>$dataservice->order_id,'status_id'=>config('global.order_status_delivered')])->first()??'';
                                    $cancelled = \App\Models\ServiceOrderStatusHistory::where(['order_id'=>$dataservice->order_id,'status_id'=>config('global.order_status_cancelled')])->first()??'';
                                     $rejected = \App\Models\ServiceOrderStatusHistory::where(['order_id'=>$dataservice->order_id,'status_id'=>config('global.order_status_rejected')])->first()??'';
                                    @endphp
                                                                    @if($key_it == 0)
                                                                    <div class="product-headeing-title">
                                                                        <h4>Service</h4>
                                                                    </div>
                                                                    @endif
                                                                    <div class="product_details-flex d-flex">
                                                                        <div class="producT_img">
                                                                                                                        <img src="{{get_uploaded_image_url($dataservice->image,'service_image_upload_dir')}}" style="width:100px;height:100px;object-fit:cover;">
                                                                                                                    </div>
                                                                        <div class="product_content">
                                                                            <h4 class="product-name">{{$dataservice->name}}</h4>
                                                                            <p>{{$dataservice->description}}</p>
                                                                            <p><strong>Admin share: </strong> {{number_format($dataservice->admin_commission,2, '.', '')}}</p>
                                                                            <p><strong>Vendor share: </strong> {{number_format($dataservice->vendor_commission,2, '.', '')}}</p>
                                                                            
                                                                            <p><strong>Service rate: </strong>{{$dataservice->qty}} X {{number_format($dataservice->price, 2, '.', '')}} - {{$dataservice->text}}</p>
                                                                            
                                                                            
                                                                            
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
                                                                @endforeach
                                                                <div>
                                                                    <div class="w-100">
                                                                        @foreach($list->service_details as $key_it =>$dataservice)
                                                                            @if($key_it == 0)
                                                                            <p><strong>Scheduled date: </strong> {{ date('d-M-y h:i A', strtotime($dataservice->booking_date))}}</p>
                                                                            @if(!empty($dataservice->doc)) 
                                                                            <p><strong>Task Attachment: </strong> <a href="{{asset($dataservice->doc)}}" style="border: none;background-color: #2C93FA;color: #fff;padding: 4px 10px;font-weight: 500;border-radius: 6px;text-transform: capitalize;font-size:11px;"> View Attachment</a></p>
                                                                            @endif
                                                                            <p><strong>Task Description: </strong> {{$dataservice->task_description??'-'}}</p>
                                                                            @endif
                                                                            
                                                                            @endforeach
                                                                        </div>
                                                                </div>
                                                            </div>
                                                            @foreach($list->service_details as $key_it =>$dataservice)
                                                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 mb-3">
                                                                 @if($key_it == 0)
                                                                <div class="product-headeing-title">
                                                                    <h4>Service Status</h4>
                                                                </div>
                                                                <div class="delivery-status-block">
                                                                @if($dataservice->order_status != config('global.order_status_cancelled') &&  $dataservice->order_status != config('global.order_status_rejected') &&  $dataservice->order_status != config('global.order_status_delivered'))
                                                                    <div class="col-md-12">
                                                                    <button class="btn " href="{{url('admin/service/change_status')}}" style="color: #fff !important;background-color: #000;" data-role="status-change" data-message="Are you sure you want to cancel this request" value="{{config('global.order_status_cancelled')}}" data-detailsid="{{$dataservice->id}}">Cancel</button>
                                                                        </div>
                                                                @endif
                                                                <br>
                                                                     <ul class="list-unstyled">
                                                                        <li class="pending @if($dataservice->order_status >= config('global.order_status_pending')) active @endif"><button class="btn-design">Pending</button>
                                                                        <br>{{get_date_in_timezone($dataservice->created_at,'d-M-Y h:i A')}}</li>
                                                                        @if($dataservice->order_status != config('global.order_status_cancelled') &&  $dataservice->order_status != config('global.order_status_rejected'))
                                                                        <li class="accepted @if($dataservice->order_status >= config('global.order_status_accepted')) active @endif"><button class="btn-design">Accepted</button>
                                                                        @if(!empty($accepted))<br>{{get_date_in_timezone($accepted->created_at,'d-M-Y h:i A')}}@endif</li>
                                                                        <li class="accepted @if($dataservice->order_status >= config('global.order_status_dispatched')) active @endif"><button class="btn-design">Ongoing</button>
                                                                        @if(!empty($dispatched))<br>{{get_date_in_timezone($dispatched->created_at,'d-M-Y h:i A')}}@endif</li>
                                                                        <li class="delivered @if($dataservice->order_status >= config('global.order_status_delivered')) active @endif"><button class="btn-design">Completed</button>
                                                                        @if(!empty($deliverd))<br>{{get_date_in_timezone($deliverd->created_at,'d-M-Y h:i A')}}@endif</li>
                                                                       
                                                                        @else
                                                                            @if($dataservice->order_status == config('global.order_status_cancelled') )
                                                                            <li class="delivered @if($dataservice->order_status >= config('global.order_status_cancelled')) active @endif"><button class="btn-design">Cancelled</button>
                                                                            @if(!empty($cancelled))<br>{{get_date_in_timezone($cancelled->created_at,'d-M-Y h:i A')}}@endif</li>
                                                                            @else
                                                                            <li class="delivered @if($dataservice->order_status >= config('global.order_status_rejected')) active @endif"><button class="btn-design">Rejected</button>
                                                                            @if(!empty($rejected))<br>{{get_date_in_timezone($rejected->created_at,'d-M-Y h:i A')}}@endif</li>
                                                                            @endif
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
                                                        @endif
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
@stop
@section('script')
<script>
$('body').off('click', '[data-role="status-change"]');
        $('body').on('click', '[data-role="status-change"]', function(e) {
            e.preventDefault();
            var msg = $(this).data('message') || 'Are you sure that you want to change status?';
            var href = $(this).attr('href');
            var detailsid = $(this).attr('data-detailsid');
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
</script>
@stop