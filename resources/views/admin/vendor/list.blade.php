@extends("admin.template.layout")

@section("header")
<?php header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0 "); // Proxies.
?>
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/custom_dt_customer.css">
    <link href="http://localhost/healthy_wealthy/public/admin-assets/bootstrap/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" />
@stop


@section("content")

<style>
    #example2_info{
        display: none
    }
</style>
<div class="card mb-5">

    <div class="card-header d-flex justify-content-end">
        <a href="{{ url('admin/vendors').'?deleted=1' }}" class="btn-custom btn mr-2 mt-2 mb-2"><i class="fa-solid fa-trash"></i> Deleted Vendor</a>
        @if(empty($type))<a href="{{url('admin/vendors/create')}}" class="btn-custom btn mr-2 mt-2 mb-2"><i class="fa-solid fa-plus"></i> Create Vendors</a>@endif
    </div>

    <div class="card-body">
    <form action="" method="get">
                    <div class="row">
                        <div class="col-md-3 mb-3 ">
                            <label for="container_in" class="form-label">Sort by</label>
                            <select type="text" class="form-control select-nosearch" name="users">
                            <option value="">All</option>                            
                            <option value="new" {{$users_ty == 'new' ? 'selected' : null;}}>New</option>
                            </select>
                        </div>

                        <div class="col-md-3 form-group mt-4">
                            <button type="submit" class="btn btn-warning mb-4 ml-2 btn-rounded">Search</button>
                            @if(isset($_GET['customer']))
                            <a href="{{url('admin/orders')}}?customer={{$_GET['customer']}}" class="btn btn-primary mb-4 ml-2 btn-rounded">Clear</a>
                            @else
                            <a href="{{url('admin/vendors')}}" class="btn btn-primary mb-4 ml-2 btn-rounded">Clear</a>
                            @endif
                        </div>

                    </div>
                </form>
                
        <div class="table-responsive">
        <table class="table table-condensed table-striped" id="example2">
            <thead>
                <tr>
                <th>#</th>
                <th style="min-width:100px;">Details</th>
                <th>Company name</th>
                <th>Activity Type</th>
                <!--<th>E-mail</th>-->
                <!--<th>Phone Number</th>-->

                @if(!request()->deleted)
                    @if ( ! isset ( $type ) || $type <= 0 )
                        <th>Is verified</th>
                    @endif
                    @if ( isset ( $type ) && $type > 0 )
                        <th>Active</th>
                    @endif
                @endif
                <th>Created</th>
                @if(!request()->deleted)
                <th>Action</th>
                @endif
                </tr>
            </thead>
            <tbody>
                <?php $i=0;
              /*  echo "<pre>";
                    print_r($datamain);
                exit(" || ");*/
                ?>
                @foreach($datamain as $datarow)
                    <?php $i++ ?>
                    <tr>
                        <td>{{$i}}</td>
                        <td>
                            <p class="mb-1" style="display: flex; align-items: center; gap: 10px;"><a href="#" class="yellow-color">{{$datarow->first_name}} {{$datarow->last_name}}</a>
                         @if($datarow->featured_flag)<br><span class="badge badge-warning mt-2">Featured</span>@endif 
                         @if($datarow->admin_viewed == 0)<span class="badge badge-danger" style="border-radius: 5px; padding: 4px 6px; width: auto; font-size: 11px;">New</span> @endif </p>
                         <p class="mb-1">{{$datarow->email}}</p>
                         <p class="mb-0">+{{$datarow->dial_code}} {{$datarow->phone}}</p>
                         
                        </td>
                        <td>{{$datarow->company_name}}
                            </td>
                            <td>{{$datarow->activity->name??''}}
                            </td>
                        <!--<td>-->
                        <!--    @if(!empty($datarow->logo)) <span>-->
                        <!--            <img src="{{asset($datarow->logo)}}" style="width:60px;height:60px;object-fit:cover;" class="rounded-circle" alt="User">-->
                        <!--        </span>@endif-->
                        <!--        </td>-->
                        <!--<td> {{$datarow->email}}</td>-->
                        <!--<td> +{{$datarow->dial_code}} {{$datarow->phone}}</td>-->
                        @if(!request()->deleted)
                            @if ( ! isset ( $type ) || $type <= 0 )
                                <td>
                                    <label class="switch s-icons s-outline  s-outline-warning  mb-4 mr-2">
                                        <input type="checkbox" class="change_status" data-id="{{ $datarow->id }}"
                                               data-url="{{ url('admin/vendors/verify') }}"
                                               @if ($datarow->verified) checked @endif>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                            @endif



                            @if ( isset ( $type ) && $type > 0 )
                                <td>
                                    <label class="switch s-icons s-outline  s-outline-warning  mb-4 mr-2">
                                        <input type="checkbox" class="change_status" data-id="{{ $datarow->id }}"
                                               data-url="{{ url('admin/vendors/change_status') }}"
                                               @if ($datarow->active) checked @endif>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                            @endif
                        @endif


                        <td>{{ get_date_in_timezone($datarow->created_at, config('global.datetime_format')) }}</td>
                        @if(!request()->deleted)
                        <td class="text-center">
                            <div class="dropdown custom-dropdown">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    <i class="flaticon-dot-three"></i>
                                </a>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                    @if($datarow->activity_id == 7  || $datarow->activity_id == 3)
                                    <a class="dropdown-item" href="{{url('/admin/product/create?vendor='.$datarow->id)}}"><i class="flaticon-plus"></i> Add Products</a>
                                    <a class="dropdown-item" href="{{url('/admin/products?vendor='.$datarow->id)}}"><i class="flaticon-plus"></i> View Products</a>
                                     <a class="dropdown-item" href="{{url('/admin/earnings',$datarow->id)}}"><i class="flaticon-plus"></i> View Earnings</a>
                                     <a class="dropdown-item" href="{{url('admin/vendor/locations/'.$datarow->id)}}"><i class="flaticon-plus"></i> Locations</a>
                                     
                                    @endif

                                    @if($datarow->activity_id == 7  || $datarow->activity_id == 3 || $datarow->activity_id == 5)
                                    <a class="dropdown-item" href="{{url('/admin/orders?vendor_id='.$datarow->id)}}"><i class="flaticon-plus"></i> View Orders</a>
                                    
                                    @endif

                                    @if($datarow->activity_id == 5 )
                                    <a class="dropdown-item" href="{{url('/admin/product/create?vendor='.$datarow->id.'&activity_id='.$datarow->activity_id)}}"><i class="flaticon-plus"></i> Add Dish</a>
                                    <a class="dropdown-item" href="{{url('/admin/products?vendor='.$datarow->id.'&activity_id='.$datarow->activity_id)}}"><i class="flaticon-plus"></i> View Dishes</a>
                                    <a class="dropdown-item" href="{{url('/admin/earnings',$datarow->id)}}"><i class="flaticon-plus"></i> View Earnings</a>
                                    <a class="dropdown-item" href="{{url('admin/vendor/locations/'.$datarow->id)}}"><i class="flaticon-plus"></i> Locations</a>
                                    @endif

                                    <a class="dropdown-item" href="{{url('admin/vendors/'.$datarow->id.'/edit')}}"><i class="flaticon-pencil-1"></i> Edit</a>
                                    <!--<a class="dropdown-item" href="{{url('admin/vendors/'.$datarow->id.'/view')}}"><i class="flaticon-plus"></i> View</a>-->
                                    <!-- <a class="dropdown-item" userid="{{$datarow->id}}" data-role="change_password" ><i class="flaticon-plus"></i> Change Password</a> -->
                                    @if($datarow->activity_id == 6)
                                    <a class="dropdown-item" href="{{url('/admin/service_request?vendor_id='.$datarow->id)}}"><i class="flaticon-plus"></i> Service Jobs</a>
                                    <a class="dropdown-item" href="{{url('/admin/vendor/view_services?vendor='.$datarow->id)}}"><i class="flaticon-plus"></i> View Services </a>
                                    <a class="dropdown-item" href="{{url('/admin/service_earnings',$datarow->id)}}"><i class="flaticon-plus"></i> View Earnings</a>
                                    
                                    @endif
                                    
                                    @if($datarow->activity_id == 1)
                                    <a class="dropdown-item" href="{{url('/admin/service_request?vendor_id='.$datarow->id)}}"><i class="flaticon-plus"></i> Transport Jobs</a>
                                    <a class="dropdown-item" href="{{url('/admin/vendor/view_services?vendor='.$datarow->id)}}"><i class="flaticon-plus"></i> View Services </a>
                                    <a class="dropdown-item" href="{{url('/admin/service_earnings',$datarow->id)}}"><i class="flaticon-plus"></i> View Earnings</a>
                                    
                                    @endif
                                    @if($datarow->activity_id == 4)
                                    <a class="dropdown-item" href="{{url('/admin/service_request?vendor_id='.$datarow->id)}}"><i class="flaticon-plus"></i> Medical & Beauty Jobs</a>
                                    <a class="dropdown-item" href="{{url('/admin/vendor/view_services?vendor='.$datarow->id)}}"><i class="flaticon-plus"></i> View Services </a>
                                    <a class="dropdown-item" href="{{url('/admin/service_earnings',$datarow->id)}}"><i class="flaticon-plus"></i> View Earnings</a>
                                    
                                    @endif

                                    @if($datarow->activity_id == 6 || $datarow->activity_id == 4 || $datarow->activity_id == 1)
                                    <a class="dropdown-item" href="{{url('/admin/service_request?vendor_id='.$datarow->id)}}"><i class="flaticon-plus"></i>  Requests</a>
                                    <a class="dropdown-item" href="{{url('admin/vendor/locations/'.$datarow->id)}}"><i class="flaticon-plus"></i> Locations</a>
                                    @endif

                                    <!--<a class="dropdown-item" href="javascript:void(0)"><i class="flaticon-plus"></i> Contract Jobs</a>-->

                                    <!--  <a class="dropdown-item" href="{{url('/admin/store/create?vendor='.$datarow->id)}}"><i class="flaticon-plus"></i> Add store</a>
                                    <a class="dropdown-item" href="{{url('/admin/store?vendor='.$datarow->id)}}"><i class="flaticon-plus"></i> View Store</a> -->
                                   
                                    
                                  
                                    
                                    <a class="dropdown-item" data-role="unlink"
                                   data-message="Do you want to remove this Vendor?"
                                   href="{{ url('admin/vendors/' . $datarow->id) }}"><i
                                       class="flaticon-delete-1"></i> Delete</a>
                                    

                                </div>
                            </div>
                        </td>
                        @endif

                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"> Add Services </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form name="add_vendor_services" id="add_vendor_services" method="post" action="{{ url('admin/vendor/add_services') }}">
                <div class="modal-body">

                    <input type="hidden" id="vendor_id" name="vendor_id">

                    <select id="ven_services" name="ven_services" multiple="multiple">

                        @if ( isset ( $aService_data ) && count ( $aService_data ) > 0 )

                            @foreach ( $aService_data as $index => $services )

                                @php
                                    $category = explode('_',$index);
                                    $category_id = $category['0'];
                                    $category_name = $category['1'];
                                @endphp

                                <optgroup label="{{ $category_name  }}">
                                    @foreach ( $services as $key => $service )
                                        <option value="{{$service['service_id']}}">
                                            {{ $service['service_name'] }}
                                        </option>
                                    @endforeach
                                </optgroup>

                            @endforeach
                        @endif

                    </select>

                    @csrf
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                    {{--<button type="button" class="btn btn-primary" onclick="add_wallet_balance()">Add</button>--}}
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@section("script")

<script src="{{asset('')}}admin-assets/plugins/table/datatable/datatables.js"></script>
<script>
$('#example2').DataTable({
      "paging": true,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": true,
      "responsive": true,
    });
    </script>
    <script>
        App.initFormView();
        $('body').off('submit', '#admin-form');
        $('body').on('submit', '#admin-form', function(e) {
            e.preventDefault();
            var $form = $(this);
            var formData = new FormData(this);

            App.loading(true);
            $form.find('button[type="submit"]')
                .text('Saving')
                .attr('disabled', true);

            $.ajax({
                type: "POST",
                enctype: 'multipart/form-data',
                url: $form.attr('action'),
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                dataType: 'json',
                timeout: 600000,
                success: function(res) {
                    App.loading(false);

                    if (res['status'] == 0) {
                        if (typeof res['errors'] !== 'undefined') {
                            var error_def = $.Deferred();
                            var error_index = 0;
                            jQuery.each(res['errors'], function(e_field, e_message) {
                                if (e_message != '') {
                                    $('[name="' + e_field + '"]').eq(0).addClass('is-invalid');
                                    $('<div class="invalid-feedback">' + e_message + '</div>')
                                        .insertAfter($('[name="' + e_field + '"]').eq(0));
                                    if (error_index == 0) {
                                        error_def.resolve();
                                    }
                                    error_index++;
                                }
                            });
                            error_def.done(function() {
                                var error = $form.find('.is-invalid').eq(0);
                                $('html, body').animate({
                                    scrollTop: (error.offset().top - 100),
                                }, 500);
                            });
                        } else {
                            var m = res['message'];
                            App.alert(m, 'Oops!');
                        }
                    } else {
                        App.alert(res['message']);
                        setTimeout(function() {
                            window.location.href = App.siteUrl('/admin/vendors');
                        }, 1500);
                    }

                    $form.find('button[type="submit"]')
                        .text('Save')
                        .attr('disabled', false);
                },
                error: function(e) {
                    App.loading(false);
                    $form.find('button[type="submit"]')
                        .text('Save')
                        .attr('disabled', false);
                    App.alert(e.responseText, 'Oops!');
                }
            });
        });


    </script>

<script type="text/javascript">

    function set_id( id ) {
        $("#vendor_id").val(id);
    }

</script>

<script type="text/javascript">
   /* $(document).ready(function() {
        $('#ven_services').multiselect();
    });*/
</script>

<script type="text/javascript">
    $(document).ready(function() {
        $('#ven_services').multiselect({
            enableFiltering: true,
            includeSelectAllOption: true,
            maxHeight: 400,
            enableClickableOptGroups: true,
            onChange: function(option, checked) {
                //alert(option.length + ' options ' + (checked ? 'selected' : 'deselected'));
            }
        });
    });
</script>

<script>
    App.initFormView();
    $('body').off('click', '#add_vendor_services');
    $('body').on('submit', '#add_vendor_services', function(e) {
        e.preventDefault();
        var $form = $(this);
        var formData = new FormData(this);

        var ven_services = $("#ven_services").val();
        formData.append("ven_services", ven_services);

        App.loading(true);
        $form.find('button[type="submit"]')
            .text('Saving')
            .attr('disabled', true);

        $.ajax({
            type: "POST",
            enctype: 'multipart/form-data',
            url: $form.attr('action'),
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            dataType: 'json',
            timeout: 600000,
            success: function(res) {
                App.loading(false);

                if (res['status'] == 0) {
                    if (typeof res['errors'] !== 'undefined') {
                        var error_def = $.Deferred();
                        var error_index = 0;
                        jQuery.each(res['errors'], function(e_field, e_message) {
                            if (e_message != '') {
                                $('[name="' + e_field + '"]').eq(0).addClass('is-invalid');
                                $('<div class="invalid-feedback">' + e_message + '</div>')
                                    .insertAfter($('[name="' + e_field + '"]').eq(0));
                                if (error_index == 0) {
                                    error_def.resolve();
                                }
                                error_index++;
                            }
                        });
                        error_def.done(function() {
                            var error = $form.find('.is-invalid').eq(0);
                            $('html, body').animate({
                                scrollTop: (error.offset().top - 100),
                            }, 500);
                        });
                    } else {
                        var m = res['message'];
                        App.alert(m, 'Oops!');
                    }
                } else {
                    App.alert(res['message']);
                    setTimeout(function() {
                        window.location.href = App.siteUrl('/admin/vendors');
                    }, 1500);
                }

                $form.find('button[type="submit"]')
                    .text('Save')
                    .attr('disabled', false);
            },
            error: function(e) {
                App.loading(false);
                $form.find('button[type="submit"]')
                    .text('Save')
                    .attr('disabled', false);
                App.alert(e.responseText, 'Oops!');
            }
        });
    });


</script>

@stop
