@extends('admin.template.layout')
@section('header')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-assets/plugins/table/datatable/datatables.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('admin-assets/plugins/table/datatable/custom_dt_zero_config.css') }}">
@endsection

@section('content')
    <div class="container">
        <!-- <div class="page-header page-header_custom">
            <div class="page-title">
                <h3>{{ $page_heading }}</h3>
            </div>
            <a href="javascript:history.back()"><button class="btn btn-secondary gobackbtn"><i class='bx bx-chevron-left'></i> Back</button></a>
        </div> -->

       <div class="card">
        <div class="card-body">
                @if (session('message'))
                    <div class="alert alert-success alert-dismissable custom-success-box" style="margin: 15px;">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        <strong> {{ session('message') }} </strong>
                    </div>
                @endif
                <!-- <div class="statbox widget box box-shadow"> -->
                    <!-- <div class="widget-content widget-content-area"> -->
                        <div class="table-responsive mb-4">
                            <form class="order_table_data mb-4 mt-4" id="search_form">
                                <div class="row align-items-center">

                                    <div class="col-md-4 mb-4 d-none ">
                                        <label for="container_in" class="form-label">Order ID</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                            placeholder="">
                                    </div>
                                    <div class="col-md-4 mb-4 ">
                                        <label for="container_in" class="form-label">Vendors</label>
                                        <select name="vendor_id" id="vendor_id" class="form-select form-control">
                                            <option value="">Select</option>
                                            @foreach($vendors as $each)
                                            <option value="{{$each->id}}">{{$each->first_name .' '. $each->last_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-4 d-none">
                                        <label for="container_in" class="form-label">Rider</label>
                                        <select name="driver_id" id="driver_id" class="form-select form-control">
                                            <option value="">Select</option>
                                            @foreach($driver as $each)
                                            <option value="{{$each->id}}">{{$each->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-4 ">
                                        <label for="container_in" class="form-label">Payment Status</label>
                                        @php $withdraw_status =  config('global.withdraw_status'); @endphp
                                        <select name="withdraw_status" id="withdraw_status" class="form-select form-control">
                                            <option value="">Select</option>
                                            @foreach($withdraw_status as $key => $each)
                                            <option value="{{$key}}">{{$each}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <label for="" class="form-label">Filters</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="button"
                                                onclick="applyParams()"class="btn btn-primary float-right ml-2">
                                                Apply Filter
                                            </button>
                                            <button type="reset" onclick="formReset()"
                                                class="btn btn-primary float-right ml-2">
                                                Clear
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="row d-none">
                                <div class="col-md-6 mb-3">
                                    <div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups">
                                        <div class="btn-group mr-2" role="group" aria-label="First group">
                                            <div class="custom-control custom-checkbox header-checkbox">
                                                <input type="checkbox" class="custom-control-input mt-2" id="all_record">
                                                <label class="custom-control-label" for="all_record"></label>
                                            </div>

                                            <span>
                                                <select class="form-control" id="bulk-action">
                                                    <option value="">Select Action</option>
                                                    <option value="3">Delete</option>
                                                </select>
                                            </span>
                                            &nbsp;
                                            <div>
                                                <button type="button"
                                                    class="btn btn-secondary btn-sm waves-effect waves-light ml-3"
                                                    id="bulk-apply">Apply</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <table id="datatable-buttons"
                                class="table table-bordered dt-responsive table-striped wrap w-100">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Order ID</th>
                                        <th>Customer Name</th>
                                        <th>Grand Total</th>
                                        <th>Vendor</th>
                                        <th style="display: none;">Vendor Payment</th>
                                        <th style="display: none;">Vendor Pay Status</th>
                                        <th>Vendor Payment Status</th>
                                        <!-- <th>Rider</th> -->
                                        <!-- <th style="display: none;">Rider Payment</th> -->
                                        <!-- <th style="display: none;">Rider Pay Status</th> -->
                                        <!-- <th>Rider Payment Status</th> -->
                                        <th>Last Updated</th>

                                    </tr>
                                </thead>
                                <tbody>

                            </table>
                        </div>
                    <!-- </div> -->
                <!-- </div> -->
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form method="post" class="form-validate form-delete" method="post">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Update <span id="utype"></span> payment status</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            @php $withdraw_status = config('global.withdraw_status'); @endphp
                            <select name="status" class="form-control" id="status">

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
    <div class="modal fade" id="viewBankModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Bank Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="view_bank_details">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

                </div>

            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('admin-assets/plugins/table/datatable/datatables.js') }}"></script>

    <script>
        // $('#vendor_id').on('change',function(){
          
        //     applyParams();
        // });
        
        // $('#driver_id').on('change',function(){
           
        //     applyParams();
        // });
        // $('#withdraw_status').on('change',function(){
           
        //     applyParams();
        // });
        // $('#name').on('keyup',function(){
          
        //     applyParams();
        // });
        $('.form-select').select2();
        // $('#search_form').on('submit',function(e){
        //     applyParams();
        //     e.preventDefault();
        // })
        var data = null;
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            });
            data = $('#datatable-buttons').DataTable({
                "processing": true,
                "serverSide": true,
                dom: 'Brtip',
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                buttons: [{
                        extend: 'csvHtml5',
                        text: 'Export',
                        title: '{{ $page_heading }}',
                        exportOptions: {
                            columns: [0,1,2,3,4,5,6,8,9,10,12]
                        }

                    },

                    {
                        extend: 'pageLength'
                    }
                ],
                "ajax": {
                    "url": "{{ url('admin/earning_data') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function(d) {
                        if ($('#name').val() != '') {
                            d['name'] = $('#name').val();
                        }
                        if ($('#restaurant_id').val() != '') {
                            d['restaurant_id'] = $('#restaurant_id').val();
                        }
                        if ($('#vendor_id').val() != '') {
                            d['vendor_id'] = $('#vendor_id').val();
                        }
                        if ($('#driver_id').val() != '') {
                            d['driver_id'] = $('#driver_id').val();
                        }
                        if ($('#withdraw_status').val() != '') {
                            d['request_status'] = $('#withdraw_status').val();
                        }

                    }
                },
                "columns": [{
                        "data": "id"
                    },
                    {
                        "data": "order_id"
                    },
                    {
                        "data": "name"
                    },
                    {
                        "data": "amount"
                    },
                    {
                        "data": "chef",'orderable':false
                    },
                    {
                        "data": "chef_payment",'orderable':false,visible: false,
                    },
                    {
                        "data": "chef_status",'orderable':false,visible: false,
                    },
                    {
                        "data": "chef_payment_status",'orderable':false
                    },
                    // {
                    //     "data": "driver",'orderable':false
                    // },
                    // {
                    //     "data": "driver_payment",'orderable':false,visible: false,
                    // },
                    // {
                    //     "data": "driver_status",'orderable':false,visible: false,
                    // },
                    // {
                    //     "data": "driver_payment_status",'orderable':false
                    // },


                    {
                        "data": "updated_at",'orderable':false
                    },


                ]

            });
        });

        $(function() {
            $('#all_record').change(function() {
                if ($(this).is(':checked')) {
                    $('.record_row').prop('checked', true).trigger('change');
                } else {
                    $('.record_row').prop('checked', false).trigger('change');
                }
            });
        });

        function showApproveModal(id) {
            $("#id").val(id);
            $("#approveModal").modal("show");
        }

        $(".saveStatus").click(function() {
            $.ajax({
                type: 'POST',
                url: "{{ url('admin/earning/change_status') }}",
                data: $('.form-delete').serialize(),
                data: {
                        "_token": "{{ csrf_token() }}",
                        "order_id": $("#order_id").val(),
                        "status": $("#status").val(),
                        "type": $("#type").val(),
                    },

                success: function(response) {
                    App.loading(false);
                    response = jQuery.parseJSON(response);
                    if (response.status === 1) {
                         $("#approveModal").modal("hide");
                       App.alert(response.message, 'Success!');
                        data.ajax.reload(null,false);
                    } else {
                        App.alert(response.message, 'error');
                    }
                },
                error: function() {
                   App.alert("Something went wrong", 'error');
                }
            });
        });

        function applyParams() {
            data.ajax.reload();
        }

        function formReset() {
             $('#driver_id').val('').trigger("change");
            $('#vendor_id').val('').trigger("change");
            $('#name').val('');
            $('#withdraw_status').val('').trigger("change");;
            data.ajax.reload();
        }
        function showChangeStatusModal(id){
            $("#status_id").val(id);
            $("#changeStatusModal").modal("show");
        }


        function showBankModal(id) {
            //$("#id").val(id);
            $.ajax({
                type:'POST',
                url: "{{ route("admin.bank_details") }}",
                data: {id:id},

                success: function(response){
                    response = jQuery.parseJSON(response);
                    $('#view_bank_details').html(response.details);
                    $("#viewBankModal").modal("show");
                },
                error: function(){
                    App.alert("Something went wrong", 'error');
                }
            });

        }

        function changeStatus(type,orderid){
            $('#type').val(type);
            $('#order_id').val(orderid);
            $('#utype').html(type);
            $('#approveModal').modal('show');
        }

    </script>
@endsection
