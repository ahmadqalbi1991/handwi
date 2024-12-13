@extends('admin.template.layout')
@section('header')
<style>
    .img-wrap {
        position: relative;
        display: inline-block;
        font-size: 0;
    }
    .img-wrap .close {
        position: absolute;
        top: 2px;
        right: 2px;
        z-index: 100;
        background-color: #FFF;
        padding: 5px 2px 2px;
        color: #000;
        font-weight: bold;
        cursor: pointer;
        opacity: .2;
        text-align: center;
        font-size: 22px;
        line-height: 10px;
        border-radius: 50%;
    }
    .close:hover {
        opacity: 1;
    }
    #parsley-id-35, #parsley-id-37{
        bottom: -15px !important;
        position: absolute;
    }
    #parsley-id-27{
        position: absolute;
        bottom: -20px;
    }
</style>

@stop
@section('content')
    <div class="card mb-5">
        <div class="card-body">
            <div class="col-xs-12 col-sm-12">
                <form method="post" id="admin-form" action="{{ url('admin/featuredstore') }}" enctype="multipart/form-data"
                    data-parsley-validate="true">
                    <input type="hidden" name="id" id="cid" value="{{ $id }}">
                    @csrf()
                    <div class="row">
                        <div class="col-md-6 form-group applies_to_select" id="browse_product">
                                                <label class="control-label">Browse Product</label>
                                                <select class="form-control product_search select2" id="product_search"  name="product_master" style="width:100%" required>
                                                    <option value=""></option>
                                                 @foreach($products as $prod)
                                                 <option value="<?=$prod->id?>"@if($id)  @if($prod->id == $datamain->master_product) selected @endif @endif><?=$prod->name?></option>
                                                 @endforeach
                                                </select>
                                                <div class="error"></div>
                                            </div>
                       
                       
                        
                       
                        <div class="form-group col-md-6">
                            <label>Description<b class="text-danger">*</b></label>
                            <textarea type="text" name="description" class="form-control" required
                                data-parsley-required-message="Enter Description ">@if($id) {{$datamain->description}} @endif</textarea>
                        </div>

                        

                        </div>
                        
                        <div class="row">
                       

                        <div class="col-md-12 form-group  imgs-wrap">
                            <div class="top-bar">
                            <label class="badge bg-light d-flex justify-content-between align-items-center">Images<button class="btn btn-button-7 pull-right" type="button" data-role="add-imgs"><i class="flaticon-plus-1"></i></button> </label>
                            </div>
                            <input type="hidden" id="imgs_counter" value="0">
                            @if($id)
                            <div class="row">
                                @foreach ($images as $img)
                                <div class="col-md-3 img-wrap">
                                    <span class="close" title="Delete" data-role="unlink"
                                        data-message="Do you want to remove this image?"
                                        href="{{ url('admin/delete_ftimage/' . $img->id) }}">&times;</span>
                                    <img style="width:155px; height:155px;" class="img-responsive" src="{{ asset($img->image) }}">
                                </div>
                                @endforeach
                                
                            </div>
                            @endif
                            <div id="imgs-holder" class="row mt-3"></div>
                        </div>
                    </div>
                  
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Submit</button>
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

        $(document).ready(function() {
            $('select').select2();
        });

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
                            'Unable to save store. Please try again later.';
                            App.alert(m, 'Oops!');
                        }
                    } else {
                        App.alert(res['message'], 'Success!');
                                setTimeout(function(){
                                    window.location.href = App.siteUrl('/admin/featured_products');
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

        $('body').on("click", '[data-role="remove-imgs"]', function() {
            $(this).parent().parent().remove();
        });
        let img_counter = $("#imgs_counter").val();
      $('[data-role="add-imgs"]').click(function() {
        img_counter++;
            var html = '<div class="form-group col-md-5">\
                          <div class="col-md-1">\
                            <button type="button" class="btn btn-danger" data-role="remove-imgs"><i class="flaticon-minus-2"></i></button>\
                          </div>\
                            <label>Cover Image<b class="text-danger">*</b></label><br>\
                            <img id="image-preview-bnr_'+img_counter+'" style="width:155px; height:155px;" class="img-responsive" >\
                            <br><br>\
                            <input type="file" name="banners[]" class="form-control" data-role="file-image" data-preview="image-preview-bnr_'+img_counter+'" data-parsley-trigger="change" data-parsley-fileextension="jpg,png,gif,jpeg" data-parsley-fileextension-message="Only files with type jpg,png,gif,jpeg are supported" data-parsley-max-file-size="5120" data-parsley-max-file-size-message="Max file size should be 5MB" data-parsley-imagedimensions="1024x1024" required data-parsley-required-message="Select Banner Image">\
                                <span class="text-info">Upload image with dimension 1024x1024</span>\
                        </div>\
                        ';
                        $('#imgs-holder').append(html);
           
        });
    </script>
@stop
