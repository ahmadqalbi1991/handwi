@extends('admin.layouts.master')
@section('headerFiles')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-assets/plugins/table/datatable/datatables.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-assets/plugins/table/datatable/custom_dt_zero_config.css') }}">
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

                <div style="float:right" class="dropdown ml-2">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Export
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item" onclick="exportReport('xlsx')" href="#">Excel</a>
                        <a class="dropdown-item" onclick="exportReport('pdf')" href="#">Pdf</a>
                        <a class="dropdown-item" onclick="exportReport('csv')" href="#">CSV</a>
                    </div>
                </div>
            </div>
            
        </div>

        <div class="row layout-spacing ">

            <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
                @if (session('message'))
                    <div class="alert alert-success alert-dismissable custom-success-box" style="margin: 15px;">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        <strong> {{ session('message') }} </strong>
                    </div>
                @endif
                <div class="statbox widget box box-shadow">
                    <div class="widget-content widget-content-area">
                        <div class="table-responsive mb-4">
                            <form class="order_table_data mb-4 mt-4" id="search_form">
                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <label for="container_in" class="form-label">Name</label>
                                        <input type="text" class="form-control" id="restaurant_name" name="restaurant_name"
                                            placeholder="">
                                    </div>
                                   
                                   

                                    <div class="col-md-6 mt-4">
                                        <div class="d-flex flex-wrap gap-2 mt-1">
                                           <!--  <button type="button"
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
                                        <th>Chef Info</th>
                                        {{-- <th>Email</th>
                                        <th>Phone</th> --}}
                                        <!-- <th>Address</th> -->
                                        <th>Documents</th>
                                        <!-- <th>Email</th> -->
                                        <!-- <th>Phone number</th> -->
                                        <th>Registration Date</th>
                                        {{--<th>License Expiry Date</th>--}}
                                        <th>Avg Food Preperation Time</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="ApproveModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form method="post" class="form-validate form-delete" method="post">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Approve Confirmation</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to proceed?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger approve">Confirm</button>
                        </div>
                        <input type="hidden" name="id" id="id" value="">
                        <input type="hidden" name="req_status" id="req_status" value="1">
                </form>
            </div>
        </div>
    </div>
@endsection

@section('footerJs')

    <script>
       

        $('#restaurant_name').on('keyup',function(){
            if($(this).val().length > 2 || $(this).val().length == 0)
                applyParams();
        });
         $('#search_form').on('submit',function(e){
            applyParams();
            e.preventDefault();
        })
        var data = null;
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            });
            var restaurant_name = $('#restaurant_name');
            data = $('#datatable-buttons').DataTable({
                dom: 'Brtip',
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                "stateSave": false,
                stateSaveParams: function (s, data) {
                    data.restaurant_name = restaurant_name.val();
                   
                },
                stateLoadParams: function (s, data) {
                    restaurant_name.val(data.restaurant_name);
                   
                },
                buttons: [
                    // {
                    //     extend: 'csvHtml5',
                    //     text: 'Export',
                    //     title: '{{ $page_title }}',
                    //     exportOptions: {
                    //         columns: [0,1,2,3,4,5,6]
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
                "processing": true,
                "serverSide": true,
                "order": [[0, 'desc']],
                "ajax": {
                    "url": "{{ url('admin/pending_chef_data') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function(d) {
                        if ($('#restaurant_name').val() != '') {
                            d['restaurant_name'] = $('#restaurant_name').val();
                        }
                        if ($('#owner_name').val() != '') {
                            d['owner_name'] = $('#owner_name').val();
                        }
                        if ($('#city_id').val() != '') {
                            d['city_id'] = $('#city_id').val();
                        }
                        d['registration_status'] = 0;

                    }
                },
                "columns": [{
                        "data": "id"
                    },
                   
                    {
                        "data": "details"
                    },
                    // {
                    //     "data": "email"
                    // },{
                    //     "data": "mobile"
                    // },
                    // {
                    //     "data": "region"
                    // },
                     {
                        "data": "documents"
                    },
                    
                    {
                        "data": "created_at"
                    },
                   
                    {
                        "data": "avg_time"
                    },
                    {
                        "data": "action" ,"orderable": false
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

        function showApproveModal(id,status) {
            $("#id").val(id);
            $("#req_status").val(status);
            if(status ==1) {
                $('#exampleModalLabel').html('Approve Confirmation');
                $('.modal-body').html('Are you sure want to approve ? ');
            } else {
                $('#exampleModalLabel').html('Reject Confirmation');
                $('.modal-body').html('Are you sure want to reject ? ');
            }
            $("#ApproveModal").modal("show");
        }

        $(".approve").click(function() {
            $.ajax({
                type: 'POST',
                url: "{{ url('admin/request/change_to_user') }}",
                data: $('.form-delete').serialize(),
                dataType:'json',
                success: function(response) {
                  
                    $("#ApproveModal").modal("hide");
                    if (response.status === "1") {
                        toastr["success"](response.message);
                        setTimeout(function() {
                            window.location.href="{{url('admin/chef/edit')}}/"+response.uiddata;
                        }, 1500);
                        
                        
                        data.ajax.reload();
                    } else {
                        toastr["error"](response.message);
                        data.ajax.reload();
                    }
                },
                error: function() {
                    toastr["error"]("Something went wrong");
                }
            });
        });

        function applyParams() {
            data.ajax.reload();
        }

        function formReset() {
            $('#city_id').val('').trigger("change");
            $('#restaurant_name').val('');
            $('#owner_name').val('');
            data.ajax.reload();
        }
        function exportReport(reportType) {
            reportType = typeof reportType === "undefined" ? xlsx : reportType 

            let exportUrl = '{{url('admin/chef_approval_export')}}' + '?registration_status=0'

            if(reportType === "pdf") {
                exportUrl = '{{url('admin/chef_approval_pdf')}}' + '?registration_status=0'
            }
            else if( reportType === "csv" ) {
                exportUrl = '{{url('admin/chef_approval_csv_export')}}'+'?registration_status=0'
            }


            if ($('#restaurant_name').val() != '') {
                exportUrl += '&restaurant_name=' +$('#restaurant_name').val();
            }
              
                        
            window.location = exportUrl
            //alert(reportType);

        }
    </script>
@endsection
