@extends('admin.layouts.master')
@section('headerFiles')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin-assets/plugins/table/datatable/datatables.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('admin-assets/plugins/table/datatable/custom_dt_zero_config.css') }}">

@endsection

@section('content')
    <div class="container">
        <div class="page-header page-header_custom">
            <div class="page-title">
                <h3>{{ $page_title ?? '' }}</h3>
            </div>
            <div class="page_header_btns">
                <a href="javascript:history.back()"><button class="btn btn-secondary gobackbtn"><i class='bx bx-chevron-left'></i> Back</button></a>
                @if (check_permission('module_food_item_add'))
                    <a href="{{ url('admin/food_item/create/'.$chef_id) }}" class="mt-4 btn-custom" style="float: right;"> <i class='bx bx-plus mr-1' ></i> Create Food
                        Items</a>
                    <a href="{{ url('admin/import') }}" class="mt-4 btn-custom mr-3" style="float: right;"> <i class='bx bxs-file-import mr-1'></i> Import
                        Items</a>
                @endif
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
                        <div class="card-body">
                            <div class="table-responsive">

                                <form class="order_table_data mb-4 mt-2" id="search_form">
                                    <div class="row align-items-center">


                                        <div class="col-md-4 mb-3">
                                            <label for="container_in" class="form-label">Dish name</label>
                                            <input type="text" class="form-control" id="name" name="name"
                                                placeholder="Enter Name">
                                        </div>
                                        <input type="hidden" name="chef_id" value="{{$chef_id}}" id="chef_id">
                                        <div class="col-md-4 ">
                                            <div class="d-flex flex-wrap gap-2 mt-2">
                                                <button type="button"
                                                    onclick="applyParams()"class="btn btn-secondary  waves-effect waves-light mr-2">
                                                    Apply Filter
                                                </button>
                                                <button type="reset" onclick="formReset()"
                                                    class="btn btn-secondary  waves-effect ml-3">
                                                    Clear
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <table id="datatable-buttons"
                                    class="table table-bordered dt-responsive  wrap w-100">

                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Price</th>
                                            <th>Sale Price</th>
                                            <th>Description</th>
                                            <th>Sufficient For</th>
                                            <th>Quantity</th>
                                            <th>Out Of Stock</th>
                                            <th>Publish</th>
                                            <th>Active</th>
                                            <th>Last Updated</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
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
            <form method="post" id="dish_delete" class="form-validate form-delete" method="post">
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
                    <input type="hidden" name="id" id="del_id" value="">
            </form>
        </div>
    </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="changepublishStatusModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post" class="form-validate form-delete" method="post">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Publish Confirmation</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to publish this food?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger publishrecord">Publish</button>
                    </div>
                    <input type="hidden" name="id" id="id" value="">
            </form>
        </div>
    </div>
    </div>

@endsection

@section('footerJs')
    <script>
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
                        title: '{{ $page_title }}',

                    },
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
                    "url": "{{ url('admin/dish_data') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function(d) {
                        if ($('#name').val() != '') {
                            d['name'] = $('#name').val();
                        }
                        if ($('#chef_id').val() != '') {
                            d['chef_id'] = $('#chef_id').val();
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
                        "data": "regular_price"
                    },
                    {
                        "data": "sale_price"
                    },
                    {
                        "data": "description"
                    },
                    {
                        "data": "sufficient_for"
                    },
                    {
                        "data": "quantity"
                    },
                    {
                        "data": "out_of_stock"
                    },
                    {
                        "data": "publish"
                    },
                    {
                        "data": "active"
                    },
                    {
                        "data": "updated_at"
                    },
                    {
                        "data": "action"
                    },

                ],
                createdRow: function( row, data, dataIndex ) {
                    $( row ).addClass(data.publish_menu);

                },
                "order": [[5,'desc']]

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

        function showDeleteModal(id) {
            $("#del_id").val(id);
            $("#deleteModal").modal("show");
        }

        $(".deleteRecord").click(function() {
            $.ajax({
                type: 'POST',
                url: "{{ url('admin/food_item/delete') }}",
                data: $('#dish_delete').serialize(),
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

        function applyParams() {
            data.ajax.reload();
        }

        function formReset() {
            $('#state_id').val('').trigger("change");
            $('#ownership_id').val('').trigger("change");
            $('#name').val('');
            data.ajax.reload();
        }


    </script>
@endsection
