@extends("admin.template.layout")

@section("header")
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/custom_dt_customer.css">
@stop


@section("content")

<style>
    #example2_info{
        display: none
    }
</style>
<div class="card mb-5">
    <div class="card-header">

        <b> Vendor : {{ $vendor_name  }} </b>
        
        <div class="status d-flex justify-content-end ">
               

                <a class="btn-custom btn mr-2 mt-2 mb-2"  data-role="add services" data-toggle="modal" data-target="#exampleModal" ><i class="fa-solid fa-plus"></i> Add Service </a>
                
            </div>

    <div class="card-body">
        <div class="table-responsive">
        <table class="table table-condensed table-striped" id="example2">
            <thead>
                <tr>
                <th>#</th>
                <th>Service Name</th>
                <th>Category name</th>
                <th>Created</th>
                <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=0; ?>
                @foreach($category_with_services as $datarow)
                    <?php $i++ ?>
                    <tr>
                        <td>{{$i}}</td>
                        <td>
                            <a href="#" class="yellow-color">{{$datarow->service_name}} </a>
                            
                        </td>
                        <td>{{$datarow->category_name}}</td>

                        <td>{{ get_date_in_timezone($datarow->created_at, config('global.datetime_format')) }}</td>

                        <td class="text-center">
                            <div class="dropdown custom-dropdown">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    <i class="flaticon-dot-three"></i>
                                </a>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">

                                    <a class="dropdown-item" data-role="unlink"
                                    data-message="Do you want to remove this Vendor service?"
                                    href="{{ url('admin/vendor/delete_services?vendor_id='.$datarow->vendor_id.'&service_id=' . $datarow->vendor_service_id) }}"><i
                                        class="flaticon-delete-1"></i> Delete</a>
                                    
                                </div>
                            </div>
                        </td>
                        
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
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

                    <input type="hidden" id="vendor_id" name="vendor_id" value="{{$vendor_id??''}}">

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
                            window.location.href = App.siteUrl('/admin/vendor/view_services?vendor={{$vendor_id}}');
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
    $(document).ready(function() {
        $('#ven_services').multiselect();
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
                        window.location.href = App.siteUrl('/admin/vendor/view_services?vendor={{$vendor_id}}');
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