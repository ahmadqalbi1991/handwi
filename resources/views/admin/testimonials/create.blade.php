@extends('admin.template.layout')
@section('header')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.css">
@stop
@section('content')
    <div class="card mb-5">
        <div class="card-body">
            <div class="">
                <form method="post" id="admin-form" action="{{ url('admin/testimonials/save') }}" enctype="multipart/form-data"
                    data-parsley-validate="true">
                    <input type="hidden" name="id" id="cid" value="{{ $id }}">
                    @csrf()

                    <div class="row">

                        <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Name<b class="text-danger">*</b></label>
                                    <input type="text" name="name" class="form-control" required
                                        data-parsley-required-message="Enter Name" value="@if($testimonial!=null){{ $testimonial->name }}@endif">
                                </div>
                        </div>

                         <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Designation<b class="text-danger">*</b></label>
                                    <input type="text" name="designation" class="form-control" required
                                        data-parsley-required-message="Enter Designation" value="@if($testimonial!=null){{ $testimonial->designation }}@endif">
                                </div>
                        </div>
                        <div class="col-lg-6">
                               <div class="form-group">
                                   <label>Rating<b class="text-danger">*</b></label>
                                   <input type="hidden" step="0.1" max="5" name="rating" id="rating" class="form-control" required
                                       data-parsley-required-message="Enter Rating" value="@if($testimonial!=null){{ $testimonial->rating }}@endif">
                                       <div id="rateYo"></div>
                               </div>
                       </div>

                        <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Comment<b class="text-danger">*</b></label>
                                    <textarea rows="4" onkeyup="textAreaAdjust(this)" type="text" name="comment" class="form-control" required
                                        data-parsley-required-message="Enter Comment" >@if($testimonial!=null){{ $testimonial->comment }}@endif</textarea>
                                </div>
                        </div>


                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Image</label><br>
                                <input type="file" name="image" class="form-control" @if(empty($id)) required @endif data-role="file-image" data-preview="image-preview" data-parsley-trigger="change"
                                    data-parsley-fileextension="jpg,png,gif,jpeg"
                                    data-parsley-fileextension-message="Only files with type jpg,png,gif,jpeg are supported" data-parsley-max-file-size="5120" data-parsley-max-file-size-message="Max file size should be 5MB" data-parsley-imagedimensions="130x130">
                                    <span class="text-info mt-3 d-block mb-3">Upload image with dimension 130x130</span>

                                    <img id="image-preview" style="width:90px; height:90px; object-fit: cover;" class="img-fluid rounded-circle mt-2"
                                    src="@if($testimonial!=null){{ $testimonial->user_image }}@else{{ asset('admin-assets/assets/img/placeholder.jpg')}}@endif">
                          </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="active" class="form-control">
                                    <option <?= ($testimonial!=null &&  $testimonial->active== 1) ? 'selected' : '' ?> value="1">Active</option>
                                    <option <?= ($testimonial!=null &&  $testimonial->active== 0) ? 'selected' : '' ?> value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-12 mt-2">
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.js"></script>
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
                                setTimeout(function(){
                                    window.location.href = App.siteUrl('/admin/testimonials');
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
    <script>
        $(function () {

            $("#rateYo").rateYo({
                starWidth: "30px",
                rating: $('#rating').val(),
                spacing: "5px",
                ratedFill: "#ed5636",
                halfStar: true,
                onChange: function (rating, rateYoInstance) {

                  $('#rating').val(rating);
                }
            });

        });
    </script>
@stop
