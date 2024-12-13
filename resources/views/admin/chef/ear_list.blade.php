@extends('admin.layouts.master')
@section('headerFiles')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-assets/plugins/table/datatable/datatables.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('admin-assets/plugins/table/datatable/custom_dt_zero_config.css') }}">
    <style type="text/css">
       /* .notiy_row {
                background-color : #EFB6B7;
            }*/
            .dropdown-item{
                margin-left: 0px;

                }
                .available, .unavailable{
                    position: relative;
                    top: 0;
                    margin-bottom: 5px;
                    bottom: 0;
                }
    </style>
    <style>
    .order_table_data button[type = reset] {
        margin-left: 0px !important;
    }
    #datatable-buttons_wrapper{
        padding: 0px 12px !important; 
    }
</style>
@endsection

@section('content')
    <div class="container">
        <div class="page-header page-header_custom">
            <div class="page-title">
                <h3>{{ $page_title }}</h3>
            </div>
            <div class="page_header_btns">
                <a href="javascript:history.back()"><button class="btn btn-secondary gobackbtn"><i class='bx bx-chevron-left'></i> Back</button></a>
                <!-- <a href="#" onclick="exportReport()"><button class="btn btn-secondary gobackbtn"><i class='bx bx-chevron-down'></i> Export</button></a> -->
                <div style="float:right" class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Export
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item" onclick="exportReport('xlsx')" href="#">Excel</a>
                        <a class="dropdown-item" onclick="exportReport('csv')"  href="#">CSV</a>
                        <a class="dropdown-item" onclick="exportReport('pdf')"  href="#">PDF</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row layout-spacing ">

            <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
                <div class="statbox widget box box-shadow">
                    <div class="widget-content widget-content-area">
                        <div class="mb-4">
                            <form class="order_table_data mt-4">
                                <div class="row">

                                    <div class="col-md-4 mb-3 ">
                                        <label for="container_in" class="form-label">Name</label>
                                        <input type="text" class="form-control" id="name" name="name"
                                            placeholder="Enter Name">
                                    </div>
                                    <div class="col-md-4 mb-3 ">
                                        <label for="container_in" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            placeholder="Enter Email">
                                    </div>

                                    <div class="col-md-4 mb-3 ">
                                        <label for="container_in" class="form-label">Phone Number</label>
                                        <input type="text" class="form-control" id="phone_number" name="phone_number"
                                            placeholder="Enter Phone Number">
                                    </div>
                                    <div class="col-md-12 mb-4">
                                        <div class="d-flex flex-wrap gap-2">
                                            <!-- <button type="button"
                                                onclick="applyParams()"class="btn btn-secondary  waves-effect waves-light">
                                                Apply Filter
                                            </button> -->

                                            <button type="reset" onclick="formReset()"
                                                class="btn btn-secondary  waves-effect ml-3">
                                                Clear
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="available" id="available" value="{{$available}}">
                            </form>

                            <table id="datatable-buttons"
                                class="table table-bordered dt-responsive   w-100">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Total Earnings</th>
                                        <th>Requested for Payment</th>
                                        
                                        <th>Payment Received</th>
                                        <th>Payment Sent</th>
                                        <th>Payment Declined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post" class="form-validate form-delete" method="post">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Delete Confirmation</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this record?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger deleteRecord">Delete</button>
                    </div>
                    <input type="hidden" name="id" id="id" value="">
            </form>
        </div>
    </div>
    </div>
@endsection

@section('footerJs')
    <script>

        function exportReport(reportType) {
            // let exportUrl = '{{url('admin/chef_earnings_export')}}' + '?'


            reportType = typeof reportType === "undefined" ? xlsx : reportType 

            let exportUrl = '{{url('admin/chef_earnings_export')}}' + '?'

            if(reportType === "pdf") {
                exportUrl = '{{url('admin/chef_earnings_pdf_export')}}' + '?'
            }
            else if( reportType === "csv" ) {
                exportUrl = '{{url('admin/chef_earnings_csv_export')}}' + '?'
            }

   
            if ($('#name').val() != '') {
                exportUrl += 'name=' + $('#name').val() + '&'
            }
            if ($('#email').val() != '') {
                exportUrl += 'email=' + $('#email').val() + '&'
            }
            if ($('#phone_number').val() != '') {
                exportUrl += 'phone_number=' + $('#phone_number').val() + '&'
            }


            window.location = exportUrl
            
        }

        $('#name').on('keyup',function(){
            if($(this).val().length > 2 || $(this).val().length == 0)
                applyParams();
        });
        $('#email').on('keyup',function(){
            if($(this).val().length > 2 || $(this).val().length == 0)
            applyParams();
        });
        $('#phone_number').on('keyup',function(){
            if($(this).val().length > 2 || $(this).val().length == 0)
            applyParams();
        });
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            });
            data = $('#datatable-buttons').DataTable({
                "processing": true,
                "serverSide": true,
                "order": [[4, 'desc']],
                dom: 'Brtip',
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                buttons: [
                    // {
                    //     extend: 'csvHtml5',
                    //     text: 'Export',
                    //     title: '{{ $page_title }}',
                    //     exportOptions: {
                    //         columns: [0,1,3,4,5,6,7,8,9,10,11,12,13]
                    //     }

                    // },
                    // {
                    //     extend: 'pdfHtml5',
                    //     text: 'PDF',
                    //     title: '{{ $page_title }}'
                    // },
                    {
                        extend: 'pageLength'
                    }
                ],
                "ajax": {
                    "url": "{{ url('admin/get_chef_wise_total_earnings') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function(d) {
                        if ($('#name').val() != '') {
                            d['name'] = $('#name').val();
                        }
                        if ($('#email').val() != '') {
                            d['email'] = $('#email').val();
                        }
                        if ($('#phone_number').val() != '') {
                            d['phone_number'] = $('#phone_number').val();
                        }
                        if ($('#user_id').val() != '') {
                            d['user_id'] = $('#user_id').val();
                        }
                        if ($('#available').val() != '') {
                            d['available'] = $('#available').val();
                        }


                    }
                },
                "columns": [{
                        "data": "id"
                    },
                    {
                        "data": "name"
                    },
                    {
                        "data": "d_earning_total"
                    },
                    {
                        "data": "requested_for_payment"
                    },
                    {
                        "data": "payment_received"
                    },
                    {
                        "data": "payment_approved"
                    },
                    {
                        "data": "payment_rejected"
                    },
                    {
                        "data": "action" ,"orderable": false
                    },

                ],
                createdRow: function( row, data, dataIndex ) {
                    $( row ).addClass(data.publish_menu);

                },

            });
        });

        function applyParams() {
            data.ajax.reload();
        }

        function reload_table(){
            data.ajax.reload( null, false );
        }

        function formReset() {
            $('#name').val('');
            $('#phone_number').val('');
            $('#email').val('');
            data.ajax.reload();
        }

        function showDeleteModal(id) {
            $("#id").val(id);
            $("#deleteModal").modal("show");
        }

        $(".deleteRecord").click(function() {
            $.ajax({
                type: 'POST',
                url: "{{ url('admin/chef/remove') }}",
                data: $('.form-delete').serialize(),
                success: function(response) {
                    console.log(response);
                    response = jQuery.parseJSON(response);
                    if (response.status === "1") {
                        $("#deleteModal").modal("hide");
                        toastr["success"](response.message);
                        data.ajax.reload();
                    } else {
                        toastr["error"](response.message);
                    }
                },
                error: function() {
                    toastr["error"]("Something went wrong");
                }
            });
        });

    </script>
@endsection
