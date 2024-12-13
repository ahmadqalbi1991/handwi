@extends('admin.template.layout')
@section('header')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@stop
@section('content')
    <div class="card mb-5">
        <div class="card-body">


            <form method="post" id="admin-form" action="{{ url('admin/save_coupons_voucher') }}" enctype="multipart/form-data"
                data-parsley-validate="true">
                <div class="row">
                    <input type="hidden" name="id"
                        value="{{ $id }}">
                    @csrf()
                    <div class="col-md-6 form-group">
                        <label>Title<b class="text-danger">*</b></label>
                        <input type="text" name="name" class="form-control" required
                            data-parsley-required-message="Enter Coupon Title"
                            value="{{ $name }}"
                            data-parsley-type="text">
                    </div>
                    <div class="col-md-6 form-group" style="display:none;">
                        <label>Title Ar<b class="text-danger">*</b></label>
                        <input type="text" name="name_ar" class="form-control" 
                            data-parsley-required-message="Enter Coupon Amount"
                            value="{{ $name_ar }}"
                            data-parsley-type="text">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Coupon Code<b class="text-danger">*</b></label>
                        <input type="text" name="coupon_code" class="form-control" required
                            data-parsley-required-message="Enter Coupon Code"
                            value="{{ $coupon_code }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Categories<b class="text-danger">*</b></label>
                        <select name="category_id" class="form-control" required
                            data-parsley-required-message="Select Coupon Type">
                            @foreach ($categories as $data)
                                <option value="{{ $data->id }}" {{ $category_id == $data->id ? 'selected' : null }}>
                                    {{ $data->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <input type="hidden" name="brand_id" value="{{$brand_id}}">
                    <div class="col-md-6 form-group" style="display:none;">
                        <label>Countries</label>
                        <select multiple name="countries[]" class="form-control select2">
                            @foreach($countries as $item)
                                <option value="{{$item->id}}" {{ in_array($item->id,$selected_countries) ? 'selected' : '' }}>{{$item->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Status</label>
                        <select name="active" class="form-control">
                            <option
                                @if (!empty($datamain->coupon_status)) {{ $datamain->coupon_status == 1 ? 'selected' : null }} @endif
                                value="1">Active</option>
                            <option
                                @if (!empty($datamain->coupon_status)) {{ $datamain->coupon_status == 0 ? 'selected' : null }} @endif
                                value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-12 mt-4">
                        <label for="description_ar">Short Description</label>
                        <textarea name="description_ar" class="form-control jqv-input" required data-jqv-required="true"
                            data-parsley-required-message="description">{{$description_ar}}</textarea>
                    </div>
                    <div class="col-md-12 mt-4">
                        <label for="description">Description</label>
                        <textarea name="description" class="form-control jqv-input" data-jqv-required="true" required
                            data-parsley-required-message="description">{{$description}}</textarea><br>
                    </div>
                    
                </div>
                <div class="row mt-4" style="display:none;">
                    <div class="col-md-2 form-group">
                        <label for="trending">
                            <input style="width:auto" type="checkbox" name="trending" {{$trending == 1 ? 'checked' : ''}} /> Trending Products
                        </label>
                    </div>
                    <div class="col-md-8 form-group" style="display:none;">
                        <label for="hot_deal">
                            <input style="width:auto" type="checkbox" name="hot_deal" {{$hot_deal == 1 ? 'checked' : ''}} /> Top Deals
                        </label>
                    </div>
                </div>
                <div class="row">
                  <div class="col-md-6 form-group">
                                        
                                            <label>Start date <span style="color:red;">*<span></span></span></label>
                                            <input type="text" class="form-control basicDate" data-parsley-daterangevalidation data-parsley-trigger="change focusout" data-parsley-daterangevalidation-requirement="#date_input_2" name="startdate" value="{{empty($datamain->start_date) ? '': date('Y-m-d', strtotime($datamain->start_date))}}" required
                            data-parsley-required-message="Select Start date" id="statrdate">
                                       
                                    </div> 


                    <div class="col-md-6 form-group">
                                        
                                            <label>Expiry date <span style="color:red;">*<span></span></span></label>
                                            <input type="text" class="form-control basicDate" id="date_input_2" data-parsley-daterangevalidation2 data-parsley-daterangevalidation2-requirement="#statrdate" data-parsley-trigger="change focusout" name="expirydate" value="{{empty($datamain->coupon_end_date) ? '': date('Y-m-d', strtotime($datamain->coupon_end_date))}}" required
                            data-parsley-required-message="Select Expiry date">
                                       
                                    </div>   </div> 
                <div class="row">
                <div class="col-md-12 form-group  imgs-wrap">
                            <div class="top-bar">
                            <label class="badge bg-dark text-white text-white d-flex justify-content-between align-items-center">Images<button class="btn btn-button-7 pull-right" type="button" data-role="add-imgs" style="width: 40px;   height: 40px;   border-radius: 0;"><i class="flaticon-plus-1"></i></button> </label>
                            </div>
                            <input type="hidden" id="imgs_counter" value="0">
                            @if(!empty($datamain->id))
                            <div class="row">
                                @foreach ($datamain->images as $img)
                                <div class="col-md-3 img-wrap">
                                    <span class="close" title="Delete" data-role="unlink"
                                        data-message="Do you want to remove this image?"
                                        href="{{ url('admin/coupons_voucher/delete_image/' . $img->id) }}">&times;</span>
                                    <img style="width:205px; height:109px;" class="img-responsive" src="{{$img->image}}">
                                </div>
                                @endforeach

                            </div>
                            @endif
                            <div id="imgs-holder" class="row mt-3"></div>
                        </div>
</div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>

            <div class="col-xs-12 col-sm-6">

            </div>
        </div>
    </div>
@stop

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        App.initFormView();
        $(document).ready(function() {
            $('.select2').select2();

        });
        $('body').off('submit', '#admin-form');
        $('body').on('submit', '#admin-form', function(e) {
            e.preventDefault();
            var $form = $(this);
            var formData = new FormData(this);
            $(".invalid-feedback").remove();

         
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
                            window.location.href = App.siteUrl('/admin/coupons_voucher?brand=' + {{request()->brand}});
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
        $(".datepicker").datepicker({
            minDate: 0
        });
        $(document).delegate("#applies_to", "change", function() {
            $(".applies_to_select").css("display", "none");
            var show = $('option:selected', this).attr('data-show');
            $(show).css("display", "block");
        });

        $('#applies_to').trigger('change');
        $('body').on("click", '[data-role="remove-imgs"]', function() {
            $(this).parent().parent().remove();
        });
let img_counter = $("#imgs_counter").val();
      $('[data-role="add-imgs"]').click(function() {
        img_counter++;
            var html = '<div class="form-group col-lg-4">\
                          <div class="remove_btn_imgs">\
                            <button type="button" class="btn btn-danger btn_remove_img" data-role="remove-imgs"><i class="flaticon-delete"></i></button>\
                          </div>\
                            <label>Banner Image<b class="text-danger">*</b></label><br>\
                            <img id="image-preview-bnr_'+img_counter+'" style="width:100%; height:160px; object-fit: cover" class="img-responsive" >\
                            <br><br>\
                            <input type="file" name="banners[]" required class="form-control" data-role="file-image" data-preview="image-preview-bnr_'+img_counter+'" data-parsley-trigger="change" data-parsley-fileextension="jpg,png,gif,jpeg" data-parsley-fileextension-message="Only files with type jpg,png,gif,jpeg are supported" data-parsley-max-file-size="5120" data-parsley-max-file-size-message="Max file size should be 5MB" data-parsley-imagedimensions="1024x547" required data-parsley-required-message="Select Image" data-parsley-imagedimensions="1024x547">\
                                <span class="text-info">Upload image with dimension 1024x547</span>\
                        </div>\
                        ';
                        $('#imgs-holder').append(html);

        });
        $(".basicDate").flatpickr({
     minDate: "today"
});
window.Parsley.addValidator('daterangevalidation', {
  validateString: function (value, requirement) {
    var date1 = new Date(value);
    var date2 = new Date($('#date_input_2').val());

    return date1 < date2;
  },
  messages: {
    en: 'Start date should not be greater than End date.'
  }
});

window.Parsley.addValidator('daterangevalidation2', {
  validateString: function (value, requirement) {
    var date1 = new Date(value);
    var date2 = new Date($('#statrdate').val());

    return date1 > date2;
  },
  messages: {
    en: 'Start date should not be greater than End date.'
  }
});
@if(empty($id))
$(function(){
    $('[data-role="add-imgs"]').trigger("click");
});
@endif

    </script>
@stop
