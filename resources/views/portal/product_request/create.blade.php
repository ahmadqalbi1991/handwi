@extends('portal.template.layout')
@section('header')
   
@stop
@section('content')
    <div class="card mb-5">
        <div class="card-body">
            <div class="">
                <form method="post" id="admin-form" action="{{ url('portal/product_request') }}" enctype="multipart/form-data"
                    data-parsley-validate="true">
                    <input type="hidden" name="id" id="cid" value="{{ $id }}">
                    @csrf()
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Product Type <b class="text-danger">*</b></label>
                                <select class="form-control text-filed" id="txt_product_type"  name="product_type">
                                <option value="1" <?php echo (($product_type == 1) ? 'selected="selected"' : '')?>>Simple</option>
                                <option value="2" <?php echo (($product_type == '2') ? 'selected="selected"' : '')?>>Variable</option>
                            </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                                <div class="form-group">
                                    <label>Product Name<b class="text-danger">*</b></label>
                                    <input type="text" name="name" class="form-control" required
                                        data-parsley-required-message="Enter Product Name" value="{{ $name }}">
                                </div>
                        </div>

                        <div class="col-md-6">
                                <div class="form-group">
                                    <label>Product Category<b class="text-danger">*</b></label>
                                    <input type="text" name="category" class="form-control" required
                                        data-parsley-required-message="Enter category" value="{{ $category }}">
                                </div>
                        </div>

                        <div class="col-md-6">
                                <div class="form-group">
                                    <label>Description<b class="text-danger">*</b></label>
                                    <textarea name="description" class="form-control" required
                                        data-parsley-required-message="Enter Description" >{{ $description }}</textarea> 
                                </div>
                        </div>





                    </div>

                    <div class="card mb-2" style="display:none;" id="variable">
                    <div class="card-body">
                        <div class="row  d-flex justify-content-between align-items-center">
                            <div class="col-md-12 form-group other-specs-wrap">
                                <div class="top-bar">
                                <label class="badge bg-light d-flex justify-content-between align-items-center">Attribute and Values <button class="btn btn-primary pull-right" type="button"
                                        data-role="add-spec"><i class="flaticon-plus-1"></i></button> </label>
                                </div>
                                <input type="hidden" id="spec_counter" value="{{ count($specs) }}">
                                <div id="spec-holder">
                                    @if (!empty($specs))
                                        <?php $i = 0; ?>
                                        @foreach ($specs as $spec)
                                            <div class="row">
                                                <div class="col-md-5 form-group">
                                                    <input type="text" name="attribute[]" placeholder="Attribute"
                                                        value="{{ $spec->attribute }}" class="form-control jqv-input"
                                                        data-jqv-required="true">
                                                </div>
                                                <div class="col-md-5 form-group">
                                                    <textarea name="value[]"
                                                        placeholder="Values" class="form-control jqv-input"
                                                        data-jqv-required="true">{{ $spec->value }}</textarea>
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-danger" data-role="remove-spec"><i class="flaticon-minus-2"></i></button>
                                                </div>
                                            </div>
                                            <?php $i++; ?>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                        <div class="row">

                        <div class="col-md-6" style="display: none;">
                            <div class="form-group b_img_div">
                                <label>Banner Image</label><br>
                                <img id="image-preview-b" style="width:300px; height:93px;" class="img-responsive"
                                    @if ($banner_image) src="{{public_url()}}{{ $banner_image }}" @endif>

                                <br><br>
                                <input type="file" name="banner_image" class="form-control" onclick="this.value=null;"
                                    data-role="file-image" data-preview="image-preview-b" data-parsley-trigger="change"
                                    data-parsley-fileextension="jpg,png,gif,jpeg"
                                    data-parsley-fileextension-message="Only files with type jpg,png,gif,jpeg are supported" data-parsley-max-file-size="5120" data-parsley-max-file-size-message="Max file size should be 5MB">
                            </div>
                        </div>
                        </div>
                        <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" style="display:none;">
                                <label>Status</label>
                                <select name="active" class="form-control">
                                    <option <?= $active == 1 ? 'selected' : '' ?> value="1">Active</option>
                                    <option <?= $active == 0 ? 'selected' : '' ?> value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12 mt-2">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
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
        $(function(){
        $('[name="product_type"]').trigger('change');
        });
        App.initFormView();
        // $(document).ready(function() {
        //     if (!$("#cid").val()) {
        //         $(".b_img_div").removeClass("d-none");
        //     }
        // });
        // $(".parent_cat").change(function() {
        //     if (!$(this).val()) {
        //         $(".b_img_div").removeClass("d-none");
        //     } else {
        //         $(".b_img_div").addClass("d-none");
        //     }
        // });
        $('body').off('change', '[name="product_type"]');
        $('body').on('change', '[name="product_type"]', function () {
            var type = $('#txt_product_type').val();
            $('#variable').hide();  
            if(type == 2)
            {
             $('#variable').show();   
            }
            
        });
        $('body').on("click", '[data-role="remove-spec"]', function() {
            $(this).parent().parent().remove();
        });
        $('[data-role="add-spec"]').click(function() {
            let counter = $("#spec_counter").val();
            counter++;
            var html = '<div class="row">' +
                '<div class="col-md-5 form-group">' +
                '<input type="text" name="attribute[]" placeholder="Attribute" class="form-control jqv-input" data-jqv-required="true">' +
                '</div>' +
                '<div class="col-md-5 form-group">' +
                '<textarea name="value[]" placeholder="Values" class="form-control jqv-input" data-jqv-required="true"></textarea>' +
                '</div>' +
                '<div class="col-md-2">' +
                '<button type="button" class="btn btn-danger" data-role="remove-spec"><i class="flaticon-minus-2"></i></button>' +
                '</div>' +
                '</div>'
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
                            'Unable to save store type. Please try again later.';
                            App.alert(m, 'Oops!');
                        }
                    } else {
                        App.alert(res['message'], 'Success!');
                                setTimeout(function(){
                                    window.location.href = App.siteUrl('/portal/product_request');
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
