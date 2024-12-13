@extends('admin.template.layout')
@section('header')
   <style type="text/css">
       .flexy-row{
            gap: 15px;
            margin-bottom: 15px;
            align-items: center;
       }
       .flexy-row label{
            font-weight: 700;
       }
       .flexy-row p{
            font-weight: 400;
       }
   </style>
@stop
@section('content')
    <div class="card mb-5">
        <div class="card-body">
            <div class="">
                    <div class="row">
                         <div class="col-md-6">
                                <div class="d-flex flexy-row">
                                    <label> Name: </label>
                                    <p>{{ $name }} </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex flexy-row">
                                    <label>Description:</label>
                                    <p>{{ $description }} </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                             <div class="d-flex flexy-row" style="padding-top:25px;">
                                <label>File:</label>
                                
                                <ul class="d-flex align-items-center flex-wrap" style="gap: 10px 20px;">
                                    @foreach($file as $fl)
                                    <li style="list-style: none; display: flex; flex-direction: column; align-items: center;">
                                        <img id="image-preview" style="width:60px; height:60px;border-radius: 10px; padding:5px;border: 1px solid #f0f0f0;" class="img-responsive"
                                            @if ($fl) src="{{$fl}}" @endif> 
                                        <a class="btn btn-primary" href="{{$fl}}" style="display: flex; align-items: center;justify-content: center;width: 40px; height: 40px;margin-top: 10px;" download > <i class="fa fa-download" aria-hidden="true"></i> </a>
                                     </li>
                                    @endforeach
                                </ul>
                                  
                            </div>

                         </div>
                        <div class="col-md-6">

                            <div class="d-flex flexy-row">
                                <label>Building Type:</label>
                                <p>{{$building_list->name ?? ''}}</p>
                            </div>
                             </div>
                        <div class="col-md-6">
                            <div class="d-flex flexy-row">
                                <label>User:</label>
                                <p>{{$user->name}}</p>
                            </div>
                             </div>
                        <div class="col-md-6">
                            <div class="d-flex flexy-row">
                                <label>Created at:</label>
                                <p>{{ get_date_in_timezone($created_at, config('global.datetime_format')) }}</p>
                            </div>
                             </div>
                        <div class="col-md-6">
                            <div class="d-flex flexy-row">
                                <label>Status:</label>
                                <p style="color:#ffc107;">
                                    {{ quote_status($status) }}
                                </p>
                            </div>
                             </div>
                        <div class="col-md-6">
                            <div class="d-flex flexy-row">
                                <label>Contract Type:</label>
                                <p>
                                    @if($contract_type != 0)
                                    @if($contract_type == 1)  Fresh    @endif
                                    @if($contract_type == 2)  Extension    @endif
                                    @else
                                    N/A
                                    @endif
                                   </p>
                            </div>
                        </div>
                       
                        
                <form method="post" id="admin-form" class="w-100" action="{{ url('admin/save_contract_maintenance') }}" enctype="multipart/form-data"
                    data-parsley-validate="true">
                    <input type="hidden" name="id" id="cid" value="{{ $id }}">
                    <input type="hidden" name="name" id="name" value="{{ $name }}">
                    @csrf() 
                    <div class="card-body">
                        <div class="row">
                         <div class="col-md-6">
                                <div class="form-group">
                                    <label> Price <b class="text-danger">*</b></label>
                                    <input type="text" name="price" class="form-control" required @if($status > 0) readonly @endif
                                        data-parsley-required-message="Enter Price" value="{{$price}}">
                                </div>
                         </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                 @if($status <= 0)
                                <label>Quotation File</label><br>

                               

                                <input type="file" name="qoutation_file" class="form-control" 
                                    data-role="file" 
                                    data-preview="image-preview" 
                                    data-parsley-trigger="change"
                                    data-parsley-fileextension="docx,xlsx,pdf,zip,jpg,png,gif,jpeg"
                                    data-parsley-fileextension-message="Only files with type jpg,png,gif,jpeg are supported" 
                                    data-parsley-max-file-size-message="Max file size should be 5MB" 
                                    >
                                @endif
                                    
                                    
                            </div>
                            <!--<a class="btn btn-primary" href="{{asset('/storage/uploads/'.config('global.contracts_image_upload_dir'). $qoutation_file)}}" download > Download File </a>-->
                        </div>
                        </div>

                        <div class="col-md-12 mt-2">

                            @if ( $status <= 0 )
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                            @endif
                        </div>

                    </div>
                </form>
            </div>
            <div class="col-xs-12 col-sm-6">
            </div>
        </div>
    </div>
@stop
@section('script')
    <script>
        App.initFormView();
       
        $('body').on("click", '[data-role="remove-spec"]', function() {
            $(this).parent().parent().remove();
        });
        var form_uploaded_images = {};
        $('[data-role="add-spec"]').click(function() {
            let counter = $("#spec_counter").val();
            counter++;
            var html = '<div class="row">'+
                       '<div class="form-group col-md-4">' +
                        '<input type="text" name="include_title[]" class="form-control" required data-parsley-required-message="Enter title" placeholder=" title">'
                            +
                        '</div>' + 
                       '<div class="form-group col-md-4">'+ 
                         '  <input type="text" name="include_description[]" class="form-control"  required ssdata-parsley-required-message="Enter description" placeholder=" description"></div>'+
                       '<div class="form-group col-md-2">'+ 
                            '<input type="file" name="include_icon[]" class="form-control" data-role="file-image" data-preview="image-preview-b" data-parsley-trigger="change" data-parsley-fileextension="jpg,png,gif,jpeg"                                    data-parsley-fileextension-message="Only files with type jpg,png,gif,jpeg are supported" data-parsley-max-file-size="5120" data-parsley-max-file-size-message="Max file size should be 5MB"></div>' +
                       '<div class="col-md-2">'+
                            '<button type="button" class="btn btn-danger" data-role="remove-spec"><i class="flaticon-minus-2"></i></button>'+
                        '</div></div>'
            $("#spec_counter").val(counter);
            $('#spec-holder').append(html);
        });

        $('body').off('submit', '#admin-form');
        $('body').on('submit', '#admin-form', function(e) {
            e.preventDefault();
            var $form = $(this);
            var formData = new FormData(this);

            App.loading(true);
            $form.find('button[type="submit"]')
                .text('Saving')
                .attr('disabled', true);

            var parent_tree = $('option:selected', "#parent_id").attr('data-tree');
            formData.append("parent_tree", parent_tree);

            $.ajax({
                type: "POST",
                enctype: 'multipart/form-data',
                url: $form.attr('action'),
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                timeout: 600000,
                dataType: 'json',
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
                            var m = res['message'] ||
                            'Unable to save service category. Please try again later.';
                            App.alert(m, 'Oops!');
                        }
                    } else {
                        App.alert(res['message'], 'Success!');
                            setTimeout(function(){
                                window.location.href = App.siteUrl('/admin/contract_maintenance');
                        },1500);
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
