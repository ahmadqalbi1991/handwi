@extends("portal.template.layout")
@section('header')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}admin_assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('') }}admin_assets/plugins/table/datatable/custom_dt_customer.css">
@stop

@section('content')
<style>
    .widget-content-area{
        background: linear-gradient(180deg, #2C93FA 0%, #2C93FA 100%) !important;
        border-radius: 10px !important;
        color: white !important;
        box-shadow: 1px 6px 10px rgb(14 17 20 / 14%) !important;
    }
    .widget-numeric-value{
        margin-bottom: 0 !important;
    }
</style>
    <div class="container">
        <!-- <div class="page-header page-header_custom">
            <div class="page-title">
                <h3>{{ $page_heading }}</h3>
            </div>
            <a href="javascript:history.back()"><button class="btn btn-secondary gobackbtn"><i class='bx bx-chevron-left'></i> Back</button></a>
        </div> -->

        
        <div class="card">
        <div class="card-body">
            <div class="row layout-spacing-1 ">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
                    <a href="javascript:void(0)">
                        <div class="widget-content-area  data-widgets">
                            <div class="widget  t-2-widget">

                                <div class="media w-100">
                                    {{-- <div class="icon ml-2">
                                        <i class='bx bx-dollar'></i>
                                    </div> --}}
                                    <div class="media-body text-center">
                                        <p class="widget-text mb-0"><b>Total Earnings</b></p>
                                        <p class="widget-numeric-value">JOD {{amount_currency($total_vendor_commission)}} </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
                    <a href="javascript:void(0)">
                        <div class="widget-content-area  data-widgets">
                            <div class="widget  t-2-widget">

                                <div class="media w-100">
                                    {{-- <div class="icon ml-2">
                                        <i class='bx bx-dollar'></i> 
                                    </div> --}}
                                    <div class="media-body text-center">
                                        <p class="widget-text mb-0"><b>Payment Received</b></p>
                                        <p class="widget-numeric-value">JOD {{amount_currency($vendor_commission_approved + $payment_recived_cod)}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
                    <a href="javascript:void(0)">
                        <div class="widget-content-area  data-widgets">
                            <div class="widget  t-2-widget">

                                <div class="media w-100">
                                    {{-- <div class="icon ml-2">
                                       <i class='bx bx-dollar'></i> 
                                    </div> --}}
                                    <div class="media-body text-center">
                                        <p class="widget-text mb-0"><b>Balance Amount</b></p>
                                        <p class="widget-numeric-value">JOD {{amount_currency($total_vendor_commission - $vendor_commission_approved - $payment_recived_cod)}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 mb-4">
                    <a href="javascript:void(0)">
                        <div class="widget-content-area  data-widgets">
                            <div class="widget  t-2-widget">
                                <div class="media w-100">
                                    {{-- <div class="icon ml-2">
                                       <i class='bx bx-dollar'></i> 
                                    </div> --}}
                                    <div class="media-body text-center">
                                        <p class="widget-text mb-0"><b>COD Amount</b></p>
                                        <p class="widget-numeric-value">JOD {{amount_currency($payment_recived_cod)}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
               
               
            </div>
            <!-- <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing"> -->
                <!-- <div class="statbox widget box box-shadow"> -->
                    <!-- <div class="widget-content widget-content-area"> -->
                        <div class="table-responsive mb-4">
                            <form class="order_table_data mb-4 mt-0" action="" method="get">
                                <div class="row">
                                    {{--<div class="col-md-6 mb-3 ">
                                        <label for="container_in" class="form-label">Rating</label>
                                        <input type="number" class="form-control" id="outlet_rating" name="outlet_rating"
                                            placeholder="Enter Rating">
                                    </div>--}}

                                    <!-- <div class="col-md-4 mb-3 ">
                                        <label for="container_in" class="form-label">Order Number</label>
                                        <input type="text" class="form-control" id="order_number" name="order_number"
                                            placeholder="Enter Order Number">
                                    </div> -->
                                    <div class="col-md-4 mb-3 ">
                                        <label for="container_in" class="form-label">Request Status</label>
                                        <select type="text" class="form-control select-nosearch" id="request_status" name="request_status">
                                        <option value="">All</option>
                                        @php $status = Config('global.withdraw_status') ;@endphp
                                        @foreach($status as $key=> $val)
                                        @if($key !=2)
                                        <option value="{{$key}}" {{$request_status==$key ? "selected" : null}} >{{$val}}</option>
                                        @endif
                                        @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3 ">
                                        <label for="container_in" class="form-label">Order Number</label>
                                        <input type="text" class="form-control" id="order_number" name="order_number" value="{{$_GET['order_number']??''}}"
                                               placeholder="Enter Order Number">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="from_date" class="form-label">From Date</label>
                                        <input type="date" class="form-control" id="from_date" name="from_date" value="{{$_GET['from_date']??''}}"
                                               placeholder="Select From Date">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="to_date" class="form-label">To Date</label>
                                        <input type="date" class="form-control" id="to_date" name="to_date" value="{{$_GET['to_date']??''}}"
                                               placeholder="Select To Date">
                                    </div>
                                    @php
                                        $pay_mode = $_GET['payment_mode']??'';
                                    @endphp
                                    <div class="col-md-4 mb-3">
                                        <label for="payment_mode" class="form-label">Payment Mode</label>
                                        <select class="form-control" name="payment_mode" id="payment_mode">
                                            <option value="">All</option>
                                            <option {{($pay_mode=="1"?'selected':'')}} value="1">Wallet</option>
                                            <option {{($pay_mode=="2"?'selected':'')}} value="2">Card</option>
                                            <option {{($pay_mode=="3"?'selected':'')}} value="3">Apple Pay</option>
                                            <option {{($pay_mode=="5"?'selected':'')}} value="5">Cash</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-4 mb-3 mt-1">
                                        <div class="d-flex flex-wrap gap-2 mt-4" style="gap: 20px;">
                                            <button type="submit"
                                                class="btn btn-primary float-right">
                                                Apply Filter
                                            </button>
                                            <a href="{{url('portal/service_earnings')}}"
                                                class="btn btn-primary float-right">
                                                Clear</a>
                                            
                                                <button type="submit" name="export" value="1"
                                                class="btn btn-primary float-right">
                                                Export
                                            </button>

                                        </div>
                                    </div>
                                </div>
                            </form>
                            <table id="datatable-buttons" class="table table-bordered dt-responsive table-striped wrap w-100">
                                <thead>
                                    <tr>
                                     <!--    <th><input type="checkbox" name="" class="select_all"></th> -->
                                        <th>#</th>
                                        <th>Order ID</th>
                                        <th>Customer</th>                                        
                                        <th>Grand Total</th>
                                        <th>Service Charge</th>
                                        <th>Admin Share</th>
                                        <th>Vendor Share</th>
                                        <th>Payment Method</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th data-orderable="false" style="display:none;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @if ($datamain->total() > 0)
                       

                       <?php $i = $datamain->perPage() * ($datamain->currentPage() - 1); ?>
                       @foreach ($datamain as $item)
                           <?php   $i++; ?>
                                    <tr>
                                        <td>{{ $i }}</td>
                                        <td>{{ $item->order_no }}</td>
                                        <td>{{ $item->name??$item->customer_name }}</td>
                                        <td>{{ $item->grand_total}}</td>
                                        <td>{{$item->service_charge}}</td>
                                        <td>@if($item->payment_mode == 5 )-{{ $item->admin_commission }} @else {{ $item->admin_commission }} @endif</td>
                                        <td>{{ $item->vendor_commission }}</td>
                                        <td>{{ payment_mode($item->payment_mode) }}</td>
                                        <td>@if($item->withdraw_status == 1)
                                            Request sent
                                            @elseif($item->withdraw_status == 3)
                                            Approved
                                            @elseif($item->withdraw_status == 4)
                                            Declined
                                            @else
                                            Pending
                                            @endif
                                        </td>
                                        <td>{{ fetch_booking_created_at_date($item->order_id) }}</td>
                                        <td style="display:none;">@if($item->withdraw_status == 1)
                                           <a class="btn btn-success" item_id="{{$item->id}}" title="Customer View">Request sent</a>
                                            @elseif($item->withdraw_status == 0)
                                            <a class="btn btn-primary sendmodal btn-sm font-sm" item_id="{{$item->id}}" title="Customer View">Send request</a>
                                            @elseif($item->withdraw_status == 3)
                                            <!-- <a class="btn btn-success"  >Approved</a> -->
                                            @elseif($item->withdraw_status == 4)
                                            <!-- <a class="btn btn-danger"  >Declined</a> -->
                                            @else
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                             @else
                             <tr><td colspan="8" align="center" class="pt-2 p-0">
                        
                        <div class="alert alert-warning">
                            <p>No Data found</p>
                        </div>
                    </td>
                </tr>
                    @endif
                                    </tbody>
                            </table>
                        </div>

                        <div class="col-sm-12 col-md-12 pull-right">
                        <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">
                            {!! $datamain->links('admin.template.pagination') !!}
                        </div>
                    </div>
                    <!-- </div> -->
                <!-- </div> -->
            <!-- </div> -->
        </div>
        </div>
    </div>

    </div>
    <!-- Modal -->
    <div class="modal fade" id="sendRequestModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post" class="form-validate earning-form-request" method="post">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Send Request</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to request for earnig?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger earning_sendrequest">Send Request</button>
                    </div>
                    <input type="hidden" name="item_id" id="item_id_er" value="">
            </form>
        </div>
    </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('') }}admin-assets/plugins/table/datatable/datatables.js"></script>

    <script>
        document.getElementById("from_date").onchange = function () {
            $('#to_date').val(' ');
            var input = document.getElementById("to_date");
            input.setAttribute("min", this.value);
        }
     $('body').off('click', '.sendmodal');
     $('body').on('click', '.sendmodal', function(e) {
        var item_id = $(this).attr("item_id");

       $('#item_id_er').val(item_id);
        
        $("#sendRequestModal").modal("show");
    });
    $(".earning_sendrequest").click(function(){
            $.ajax({
                type:'POST',
                url: '{{ route("portal.earning.request_service") }}',
                // data: $('.earning-form-request').serialize(),
                data: {
                        "_token": "{{ csrf_token() }}",
                        "item_id": $("#item_id_er").val(),
                    },
                success: function(response){
                    response = jQuery.parseJSON(response);
                    if(response.status){
                        $("#sendRequestModal").modal("hide");
                        App.alert(response.message, 'Success!');
                        location.reload();                  
                    } else {
                        App.alert(response.message, 'error');
                        // toastr["error"](response.message);
                    }
                },
                error: function(){
                    App.alert("Something went wrong", 'error');
                    // toastr["error"]("Something went wrong");
                }
            });
        }); 

       
      

      
    </script>
@endsection
