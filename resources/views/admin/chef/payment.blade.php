@extends("admin.template.layout")
@section("header")
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/custom_dt_customer.css">
    <link href="http://localhost/healthy_wealthy/public/admin-assets/bootstrap/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" />
@stop



@section('content')

<style>
    select {
        height: 39.48px !important;
    }
    .table-custom{
        width: 98%;
        margin: auto;
    }
</style>
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
        <div class="card">
        <div class="card-body">

        <!-- <div class="row layout-spacing "> -->
            <!-- <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
                <div class="statbox widget box box-shadow"> -->
                    <!-- <div class="widget-content widget-content-area"> -->
                        <div class="form-label">
                            <h3>@if($chefDetails)
                                     Vendor: {{$chefDetails->first_name.' '.$chefDetails->last_name}}
                                @endif</h3>
                        </div>
                        @if($chefDetails)
                        <div class="row layout-spacing">
                            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 ">
                                <a href="javascript:void(0)">
                                    <div class="widget-content-area  data-widgets">
                                        <div class="widget  t-2-widget">

                                            <div class="media w-100">
                                                {{-- <div class="icon ml-2">
                                                    <i class='bx bx-dollar'></i>
                                                </div> --}}
                                                <div class="media-body text-center">
                                                    <p class="widget-text mb-0"><b>Total Earnings</b></p>
                                                    <p class="widget-numeric-value">JOD {{amount_currency(($total_vendor_commission + $cod_commission))}}</p>
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
                        </div>
                        @endif
                        <div class="table-responsive mb-4">
                            @php $allStatus = config('global.withdraw_status') @endphp
                            <table class="table table-bordered table-custom d-none" >
                                <tr>
                                    <td>Total Delivered Orders</td>
                                    <td>Total Delivered Order Amount</td>
                                    <td>Total Share</td>
                                    @foreach($allStatus as $key => $val)
                                    @if($key !=2)
                                        <td>Total {{$val}}</td>
                                        @endif
                                    @endforeach
                                </tr>
                                <tr>
                                    <td>@if($orders)
                                            {{$orders->count}}
                                        @else
                                            0
                                        @endif </td>
                                    <td>@if($orders)
                                            {{number_format($orders->sum,2)}}
                                        @else
                                            0
                                        @endif</td>
                                    <td>{{number_format($total,2)}} </td>
                                    @foreach($allStatus as $key => $val)
                                    @if($key !=2)
                                        <td>@if(array_key_exists($key,$payStatus))
                                                {{number_format($payStatus[$key],2)}}
                                            @else
                                                0
                                            @endif</td>
                                            @endif
                                    @endforeach
                                </tr>
                            </table>

                            <form class="order_table_data mb-4 ">
                                <div class="row">
                                    {{--<div class="col-md-6 mb-3 ">
                                        <label for="container_in" class="form-label">Rating</label>
                                        <input type="number" class="form-control" id="outlet_rating" name="outlet_rating"
                                            placeholder="Enter Rating">
                                    </div>

                                    <div class="col-md-4 mb-3 ">
                                        <label for="container_in" class="form-label">Order Number</label>
                                        <input type="text" class="form-control" id="order_number" name="order_number"
                                               placeholder="Enter Order Number">
                                    </div>
                                    --}}
                                     @if(!$chef_id)
                                     <div class="col-md-4 form-group">
                                        <label>Vendor</label>
                                        <select class="form-control jqv-input select2" name="vendor" 
                                            data-parsley-required-message="Select a vendor" data-role="vendor-change" data-input-store="store-id" id="chef_id">
                                            <option value="">Select Vendor</option>
                                            @foreach ($sellers as $sel)
                                                <option value="{{$sel->id }}" @if ($sel->id == request()->vendor) selected @endif>{{ $sel->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                    <div class="col-md-4 mb-3 ">
                                        <label for="container_in" class="form-label">Request Status</label>
                                        <select type="text" class="form-control select-nosearch" id="request_status"
                                                name="request_status">
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
                                        <input type="text" class="form-control" id="order_number" name="order_number"
                                               placeholder="Enter Order Number">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="from_date" class="form-label">From Date</label>
                                        <input type="date" class="form-control" id="from_date" name="from_date"
                                               placeholder="Select From Date">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="to_date" class="form-label">To Date</label>
                                        <input type="date" class="form-control" id="to_date" name="to_date"
                                               placeholder="Select To Date">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="payment_mode" class="form-label">Payment Mode</label>
                                        <select class="form-control" name="payment_mode" id="payment_mode">
                                            <option value="">All</option>
                                            <option value="1">Wallet</option>
                                            <option value="2">Card</option>
                                            <option value="3">Apple Pay</option>
                                            <option value="5">Cash</option>
                                        </select>
                                    </div>
                                    @if($chef_id)
                                    <input type="hidden" name="chef_id" value="{{$chef_id}}" id="chef_id">
                                    @endif
                                        
                                    <div class="col-md-4 mb-4">
                                        <label for="" class="form-label">Filters</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="button"
                                                    onclick="applyParams()"
                                                    class="btn btn-primary float-right ml-2">
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
                            <div class="mb-3 ml-3">
                                <button type="button" class="btn btn-primary" onclick="bulkStatusChange('chef', 3)">
                                    Approve
                                </button>
                                <button type="button" class="btn btn-green" onclick="bulkStatusChange('chef', 4)" style="background: red; color: #fff; padding: 12px 22px;margin-left: 5px">
                                    Reject
                                </button>
                            </div>
                            <table id="datatable-buttons"
                                   class="table table-bordered dt-responsive table-striped wrap w-100">
                                <thead>
                                <tr>
                                    <th><input type="checkbox" id="checkAll"></th>
                                    <th>#</th>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Vendor</th>
                                    <th>Grand Total</th>
                                    <th>Admin Share</th>
                                    <th>Vendor Share</th>
                                    <th>Payment Method</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                            </table>

                        </div>
                    <!-- </div> -->
                </div>
            </div>
        <!-- </div> -->
    </div>

    <div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post" class="form-validate form-delete" method="post">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Update <span id="utype"></span> payment status
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @php $withdraw_status = config('global.withdraw_status'); @endphp

                        <select name="status" class="form-control">

                            @foreach($withdraw_status as $key=>$val)
                                @if($key > 1)
                                    <option value="{{$key}}">{{$val}}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger saveStatus">Save</button>
                    </div>
                    <input type="hidden" name="order_id" id="order_id" value="">
                    <input type="hidden" name="type" id="type" value="">
            </form>
        </div>
    </div>
    </div>
    </div>

      <!-- Modal -->
<div class="modal fade" id="viewOrderModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
      <form method="post" class="form-validate form-delete" method="post">
          @csrf
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Order Details</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
          <div class="modal-body view_orderid">

          </div>


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
        function bulkStatusChange(type, status) {
            var values = [];
            $('input[name="order_ids[]"]:checked').each(function () {
                values[values.length] = (this.checked ? $(this).val() : "");
            });
            if (values.length > 0) {
                $.ajax({
                    type: 'POST',
                    url: "{{ url('admin/earning/change_status') }}",
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
                        }
                    },
                    error: function () {
                        App.alert("Something went wrong", 'error');
                    }
                });
            } else {
                App.alert("Please Select Any Record", 'Alert');
            }
        }

        function checkClick(chkBox) {
            if ($('[name="order_ids[]"]:checked').length == $('[name="order_ids[]"]').length) {
                $('#checkAll').prop('checked', true);
            } else {
                $('#checkAll').prop('checked', false);
            }
        }

        $(document).ready(function () {
            $('#checkAll').click(function () {
                if (this.checked)
                    $('[name="order_ids[]"]').prop('checked', this.checked);
                else
                    $('[name="order_ids[]"]').prop('checked', false);
            });

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
                    "url": "{{ url('admin/get_chef_earning_data') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function (d) {
                        if ($('#request_status').val() != '') {
                            d['request_status'] = $('#request_status').val();
                        }
                        if ($('#order_number').val() != '') {
                            d['order_number'] = $('#order_number').val();
                        }
                        if($('#request_date').val() != ''){
                            d['request_date'] = $('#request_date').val();
                        }
                        if($('#from_date').val() != ''){
                            d['from_date'] = $('#from_date').val();
                        }
                        if($('#to_date').val() != ''){
                            d['to_date'] = $('#to_date').val();
                        }
                        if($('#payment_mode').val() != ''){
                            d['payment_mode'] = $('#payment_mode').val();
                        }
                        d['chef_id'] = $('#chef_id').val();
                    }
                },
                "columns": [
                    {
                        "data": "checkbox", 'orderable': false, 'searchable': false
                    },
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
                        "data": "vendor"
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
            @if(!$chef_id)
            $('#chef_id').val('').trigger("change");
            @endif
            $('#outlet_rating').val('');
            $('#request_date').val('');
            $("#request_status").val("").trigger("change");
            setTimeout(function(){
                data.ajax.reload();
            },2000);
        }

        function sendRequest(id) {
            $("#order_id").val(id);
            $("#sendRequestModal").modal("show");
        }

        function changeStatus(type, orderid) {
            $('#type').val(type);
            $('#order_id').val(orderid);
            $('#utype').html(type);
            $('#approveModal').modal('show');
        }

        $(".saveStatus").click(function () {
            $.ajax({
                type: 'POST',
                url: "{{ url('admin/earning/change_status') }}",
                data: $('.form-delete').serialize(),

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
                    }
                },
                error: function () {
                    App.alert("Something went wrong", 'error');
                }
            });
        });

         $(document).on('click','.view_orderdetail',function(){
          var orderid = $(this).attr('orderid');
            $("#id").val(orderid);
          $.ajax({
                type:'POST',
                url: "{{ route("admin.view_order") }}",
                data: {'id':orderid},
                success: function(response){
                   $('.view_orderid').html(response.html);
                    $('#viewOrderModal').modal('show');
                },
                error: function(){

                }
            });
        })
    </script>
@endsection
