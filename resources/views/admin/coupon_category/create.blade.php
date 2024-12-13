@extends('admin.template.layout')
@section('header')

@stop
@section('content')
    <div class="card mb-5">
        <div class="card-body">
            <div class="">
                <form method="post" id="admin-form" action="{{ url('admin/save_coupon_category') }}" enctype="multipart/form-data"
                    data-parsley-validate="true">
                    <input type="hidden" name="id" id="cid" value="{{ $id }}">
                    @csrf()

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Category Name<b class="text-danger">*</b></label>
                                <input type="text" name="name" class="form-control" required
                                    data-parsley-required-message="Enter Category Name" value="{{ $name }}">
                            </div>
                        </div>
						<div class="col-md-6" style="display:none;">
                            <div class="form-group">
                                <label>Category Name Ar<b class="text-danger">*</b></label>
                                <input type="text" name="name_ar" class="form-control" 
                                    data-parsley-required-message="Enter Category Name" value="{{ $name_ar }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Sort Order<b class="text-danger">*</b></label>
                                <input type="number" min="0" name="sort_order" class="form-control" required
                                    data-parsley-required-message="Enter Sort Order" value="{{ $sort_order ?? 0 }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="active" class="form-control">
                                    <option <?= $active == 1 ? 'selected' : '' ?> value="1">Active</option>
                                    <option <?= $active == 0 ? 'selected' : '' ?> value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6" style="display:none;">

                            <div class="form-group">
                                <label>Parent Category</label>
                                <select name="parent_id" class="form-control parent_cat">
                                    <option value="">None</option>
                                    @foreach ($categories as $cat)
                                        <option {{ $cat->id == $parent_id ? 'selected' : '' }} value="{{ $cat->id }}">
                                            {{ $cat->name }}</option>
                                    @endforeach;
                                </select>
                            </div>

                        </div>
                        <!-- <input type="hidden" name="parent_id" value="0"> -->
                        {{-- <div class="col-md-6">
                            <div class="form-group">
                                <label>Image</label><br>
                                <img id="image-preview" style="width:100px; height:90px;" class="img-responsive"
                                    @if ($image) src="{{ asset($image) }}" @else src="http://localhost/discounto/public/storage/users/placeholder.png" @endif>
                                <br><br>
                                <input type="file" name="image" class="form-control" data-role="file-image"
                                    data-preview="image-preview" data-parsley-trigger="change"
                                    data-parsley-fileextension="jpg,png,gif,jpeg"
                                    data-parsley-fileextension-message="Only files with type jpg,png,gif,jpeg are supported"
                                    data-parsley-max-file-size="5120"
                                    data-parsley-max-file-size-message="Max file size should be 5MB"
                                    data-parsley-imagedimensionss="300x300" accept="image/*">
                                <span class="text-info">Upload image with dimension 300x300</span>
                            </div>
                        </div> --}}

                        <div class="col-md-6">
                            <div class="form-group d-flex align-items-center">
                                <div class="col-md-10">
                                    <label>Image (gif,jpg,png,jpeg) </label>
                                    <input type="file" name="image" class="form-control" data-role="file-image"
                                    data-preview="image-preview" data-parsley-trigger="change"
                                    data-parsley-fileextension="jpg,png,gif,jpeg"
                                    data-parsley-fileextension-message="Only files with type jpg,png,gif,jpeg are supported"
                                    data-parsley-max-file-size="5120"
                                    data-parsley-max-file-size-message="Max file size should be 5MB"
                                    data-parsley-imagedimensionss="300x300" accept="image/*">
                                    {{-- <span class="text-info">Upload image with dimension 300x300</span> --}}
                                </div>
                                <img id="image-preview" class="img-thumbnail w-50"
                                    style="margin-left: 5px; height:75px; width:75px !important;"
                                    @if ($image) src="{{ asset($image) }}" @else src="{{asset('storage/users/placeholder.png')}}" @endif>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="form-group b_img_div">
                                <label>Banner Image</label><br>
                                <img id="image-preview-b" style="width:300px; height:93px;" class="img-responsive"
                                    @if ($banner_image) src="{{ asset($banner_image) }}" @endif>

                                <br><br>
                                <input type="file" name="banner_image" class="form-control" onclick="this.value=null;"
                                    data-role="file-image" data-preview="image-preview-b" data-parsley-trigger="change"
                                    data-parsley-fileextension="jpg,png,gif,jpeg"
                                    data-parsley-fileextension-message="Only files with type jpg,png,gif,jpeg are supported"
                                    data-parsley-max-file-size="5120"
                                    data-parsley-max-file-size-message="Max file size should be 5MB">
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
                                'Unable to save category. Please try again later.';
                            App.alert(m, 'Oops!');
                        }
                    } else {
                        App.alert(res['message'], 'Success!');
                        setTimeout(function() {
                            window.location.href = App.siteUrl('/admin/coupon_category');
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
