@extends("admin.template.layout")

@section('header')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}admin_assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('') }}admin_assets/plugins/table/datatable/custom_dt_customer.css">
@stop


@section('content')
<div class="card mb-3">

    <div class="card-header"><span id="subtitle">Create Attribute Values</span> - {{ $datamain['attribute_name'] }}</div>

    <div class="card-body">
        <form action="{{ url('/admin/attribute_value_save') }}" method="post" id="admin-form">
                    @csrf
                    <div class="row">
                        <div class="col-sm-6 col-md-6">
                           <select name="txt_attr_name" class="form-control" id="txt_attr_name">
                               <option value="{{ $datamain['attribute_id'] }}">{{ $datamain['attribute_name'] }}</option>
                               
                           </select>
                        </div>
                        <div class="col-sm-6 col-md-6 d-none">
                           <select name="txt_attr_value_in" class="form-control" id="txt_attr_value_in">
                               <option value="1">Value in Text</option>
                               <option value="2">Value in Color</option>
                           </select>
                        </div>                       
                    </div><br/>
                    <div class="row">
                        <div class="col-sm-6 col-md-6">
                            <input type="text" name="txt_attr_val_name"  id="txt_attr_val_name" class="form-control" placeholder="Attribute Value">
                        </div>  
                       <!--  <div class="col-sm-6 col-md-6">
                            <input type="text" name="txt_attr_val_name_arabic"  id="txt_attr_val_name_arabic" class="form-control" placeholder="Attribute Value (arabic)" dir="rtl">
                        </div> -->
                        </div> 
                        <br/>
                        <div class="row" id="cnt-color-picker" style="display: none;">
                            <div class="col-sm-6 col-md-6">
                            <input type="color" name="txt_attr_color" value="#ffffff" id="txt_attr_color" style="height:38px;" class="form-control" placeholder="Pick Color" dir="rtl">
                        </div>
                            </div> 
                            <br/>
                        <div class="row">
                        <input type="hidden" name="attribute_values_id" value="0" id="attribute_values_id">
                        <div class="col-sm-6 col-md-6">
                            
                            <input type="submit" name="Save" value="Save" class="btn btn-warning mb-2 mr-2 btn-rounded">
                        </div>
                    </div>
            </form>
    </div>
</div>

<div class="card mb-5">
    <div class="card-body">
    <table class="table table-condensed table-striped" id="example2" >
                        <thead>
                            <tr>
                                <th>Attribute Name</th>                                
                                <th>Attribute value</th> 
                                <!-- <th>Attribute value Arabic</th>  -->
                                <th>Action</th>
                            </tr>
                        </thead>
                        @if (count($list) > 0)
                        <tbody>
                            <?php $i = 0; ?>
                            @foreach ($list as $item)   
                                <?php $i++;  ?>
                                <tr>
                                    <td>{{ $item->attribute_name }}</td>
                                    
                                    <td>{{ $item->attribute_values }}</td>
                                    <!-- <td>{{ $item->attribute_values_arabic }}</td> -->
                                    <td class="text-center">
                                        <div class="dropdown custom-dropdown">
                                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                <i class="flaticon-dot-three"></i>
                                            </a>

                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                                <a class="dropdown-item edit_attribute"
                                                    href="#" attid ="{{$item->attribute_values_id}}"><i
                                                        class="flaticon-pencil-1"></i> Edit</a>
                                                <a class="dropdown-item" data-role="unlink"
                                                    data-message="Do you want to remove this attribute value?"
                                                    href="{{ url('admin/attribute_values/delete/' . $item->attribute_values_id) }}"><i
                                                        class="flaticon-delete-1"></i> Delete</a>

                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                         @else
                         <!-- <tr><td colspan="8" align="center"><div class="alert alert-warning">
                        <p>No result found</p>
                    </div></td></tr> -->

                        <tr>
                            <td colspan="8" align="center" class="p-0">
                                <div class="alert alert-warning mt-2">
                                    <p class="mb-0">No products found</p>
                                </div>
                            </td>
                        </tr>
                           @endif
                    </table>
                    </div>

                    <div class="col-sm-12 col-md-12 pull-right">
                        <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">
                           {!! $list->links('admin.template.pagination') !!}
                        </div>
                    </div>

    </div>
</div>

    
@stop

@section('script')
    <script src="{{ asset('') }}admin_assets/plugins/table/datatable/datatables.js"></script>
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
            $('.invalid-feedback').remove();
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
                            window.location.href = App.siteUrl('/admin/product_attribute');
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

$('.edit_attribute').on('click',function(){
    var id = $(this).attr('attid');
    $.ajax({
        type: "get",
        enctype: 'multipart/form-data',
        url: "{{ url('admin/attribute_values_edit/')}}/"+id,
        data: {},
        dataType: 'json',
        success: function(res) {
              $.each(res, function(index) {
                $('#cnt-color-picker').hide(); 
                $('#txt_attr_val_name').val(res[index].attribute_values);
                $('#txt_attr_val_name_arabic').val(res[index].attribute_values_arabic);
                $('select#txt_attr_value_in').val(res[index].attribute_value_in);
                if(res[index].attribute_value_in == 2)
                {
                $('#cnt-color-picker').show();   
                $('#txt_attr_color').val(res[index].attribute_color); 
                }
                $('#attribute_values_id').val(res[index].attribute_values_id); 
                $('#subtitle').html('Edit attribute value'); 
              });
        }
    })
})
$("#txt_attr_value_in").on("change", function(e){
           
            if(e.target.value == "2")
                $("#cnt-color-picker").show();
            else
                $("#cnt-color-picker").hide();

        });
    </script>
@stop
