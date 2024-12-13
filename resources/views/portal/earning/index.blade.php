@extends("portal.template.layout")
@section('header')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}admin_assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('') }}admin_assets/plugins/table/datatable/custom_dt_customer.css">
@stop

@section('content')
<style>
    .widget-content-area{
        border-radius: 10px !important;
        color: #141414 !important;
        background: linear-gradient(180deg, #FFC087 0%, #FFC087 100%) !important;
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
            <div class="row layout-spacing">
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
                                        <p class="widget-numeric-value">JOD {{amount_currency(($total_vendor_commission ))}}</p>
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
                                        <p class="widget-numeric-value">JOD {{amount_currency($vendor_commission_approved + $cod_commission)}}</p>
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
                                        <p class="widget-numeric-value">JOD {{amount_currency(($total_vendor_commission - $cod_commission) - $vendor_commission_approved)}}</p>
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
                                        <p class="widget-numeric-value">JOD {{amount_currency($cod_amount)}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                @php $allStatus = config('global.withdraw_status') @endphp
                @foreach($allStatus as $key => $val)
                @if($key !=2)
                <div class="col-xl-4 col-lg-6 col-md-6 col-sm-6 mb-4 d-none">
                    <a href="javascript:void(0)">
                        <div class="widget-content-area  data-widgets">
                            <div class="widget  t-2-widget">

                                <div class="media w-100">
                                    {{-- <div class="icon ml-2">
                                        <i class='bx bx-dollar-circle' ></i>
                                    </div> --}}
                                    <div class="media-body text-center">
                                        <p class="widget-text mb-0">{{$val}}</p>
                                        <p class="widget-numeric-value">@if(array_key_exists($key,$payStatus)) {{amount_currency($payStatus[$key])}} @else 0 @endif</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                @endif
                @endforeach
               
            </div>
            <!-- <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing"> -->
                <!-- <div class="statbox widget box box-shadow"> -->
                    <!-- <div class="widget-content widget-content-area"> -->
                        <div class="table-responsive mb-4">
                            <form class="order_table_data mb-4">
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
                                        <option value="{{$key}}">{{$val}}</option>
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
                                    <div class="col-md-4 mb-3 mt-1">
                                        <div class="d-flex flex-wrap gap-2 mt-4">
                                            <button type="button"
                                                onclick="applyParams()"class="btn btn-primary float-right ml-2">
                                                Apply Filter
                                            </button>
                                            <button type="reset" onclick="formReset()"
                                                class="btn btn-primary float-right ml-2">
                                                Clear
                                            </button>
                                            <button type="submit" name="export" value="1"
                                                class="btn btn-primary float-right ml-2">
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
                                        <th>Admin Share </th>
                                        <th>Vendor Share </th>
                                        <th>Payment Method</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th data-orderable="false">Action</th>
                                    </tr>
                                </thead>
                            </table>
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
                    <input type="hidden" name="order_id" id="earning_o_id" value="">
                    <input type="hidden" name="o_id" id="earning_o_id" value="">
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
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            });
            data = $('#datatable-buttons').DataTable({
                "processing": true,
                "serverSide": true,
                "order": [[0, 'desc']],
                dom: 'Brtip',
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                buttons: [{
                        extend: 'csvHtml5',
                        text: 'Export',
                        title: '{{ $page_heading }}',

                    },
                    {
                        extend: 'pageLength'
                    }
                ],
                "ajax": {
                    "url": "{{ url('portal/get_earning_data') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function(d) {
                        if ($('#request_status').val() != '') {
                            d['request_status'] = $('#request_status').val();
                        }
                        if ($('#order_number').val() != '') {
                            d['order_number'] = $('#order_number').val();
                        }
                        if ($('#from_date').val() != '') {
                            d['from_date'] = $('#from_date').val();
                        }
                        if ($('#to_date').val() != '') {
                            d['to_date'] = $('#to_date').val();
                        }
                        if ($('#payment_mode').val() != '') {
                            d['payment_mode'] = $('#payment_mode').val();
                        }
                    }
                },
                "columns": [
                    // {
                    //     "data": "st",
                    //     "orderable":false
                    // },
                    {
                        "data": "id"
                    },
                    {
                        "data": "order_id"
                    },
                    {
                        "data": "customer"
                    },
                    {
                        "data": "grand_total"
                    },
                    {
                        "data": "admin_commission_per"
                    },
                    {
                        "data": "commission"
                    },
                    {
                        "data": "payment_mode"
                    },
                    {
                        "data": "status"
                    },
                    
                    {
                        "data": "created_at"
                    },
                    {
                        "data": "action"
                    }

                ]

            });
        });

        function applyParams() {
            data.ajax.reload();
        }

        function formReset() {
            $('#outlet_rating').val('');
            $('#request_status').val('').trigger("change");
            setTimeout(function(){
                data.ajax.reload();
            },2000);
        }
        function sendRequest(id) {
            $("#earning_o_id").val(id);
            $("#sendRequestModal").modal("show");
        }
        $(".earning_sendrequest").click(function(){
            $.ajax({
                type:'POST',
                url: '{{ route("portal.earning.request") }}',
                // data: $('.earning-form-request').serialize(),
                data: {
                        "_token": "{{ csrf_token() }}",
                        "order_id": $("#earning_o_id").val(),
                    },
                success: function(response){
                    response = jQuery.parseJSON(response);
                    if(response.status){
                        $("#sendRequestModal").modal("hide");
                        App.alert(response.message, 'Success!');
                        // toastr["success"](response.message);
                        data.ajax.reload();                      
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

        $('.select_all').on('click',function(){
            if($(this).is(":checked")) {
                $('.changestatus_check').prop('checked',true);
            } else {
                $('.changestatus_check').prop('checked',false);
            }
        })

        function bulkStatusChange(type, status) {
            var values = [];
            $('input[name="status_ids[]"]:checked').each(function () {
                values[values.length] = (this.checked ? $(this).val() : "");
            });
            if (values.length > 0) {
                $.ajax({
                    type: 'POST',
                    url: "{{ url('portal/earning/change_status') }}",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "order_ids": values,
                        "type": type,
                        "status": status
                    },

                    success: function (response) {
                        App.loading(false);
                        response = jQuery.parseJSON(response);
                        if (response.status === 1) {
                            $("#approveModal").modal("hide");
                            App.alert(response.message, 'Success!');
                            window.location.reload();
                            data.ajax.reload();
                        } else {
                            App.alert(response.message, 'error');
                            // toastr["error"](response.message);
                        }
                    },
                    error: function () {
                        App.alert("Something went wrong", 'error');
                    }
                });
            } else {
                App.alert("Please select at least one order", 'error');
            }
        }
    </script>
@endsection
