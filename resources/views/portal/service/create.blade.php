@extends('portal.template.layout')
@section('header')

@stop
@section('content')
    <div class="card mb-5">
        <div class="card-body">
            <div class="">
                <form method="post" id="admin-form" action="{{ url('portal/save_services') }}" enctype="multipart/form-data"
                    data-parsley-validate="true">
                    <input type="hidden" name="id" id="cid" value="{{ $id }}">
                    @csrf()

                    <div class="row">
                         <div class="col-md-6">
                                <div class="form-group">
                                    <label>Service Name<b class="text-danger">*</b></label>
                                    <input type="text" name="name" class="form-control" required
                                        data-parsley-required-message="Enter Service Category Name" value="{{ $name }}">
                                </div>
                         </div>

                         <div class="col-md-6">
                            <div class="form-group">
                                <label>Service Category<b class="text-danger">*</b></label>
                                <select name="category" class="form-control" required id="servicecat" onchange="serviceType(this)"
                                        data-parsley-required-message="Selcect Service Category">
                                    <option value="">Select...</option>
                                    @foreach($categories as $value)
                                    <option <?= $category == $value->id ? 'selected' : '' ?> value="{{$value->id}}">{{$value->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div id="servicetypeshow" class="col-md-6" @if($service_type != null) style="display: block;" @else style="display: none"  @endif>
                            <div class="form-group">
                                <label>Service Type</label>
                                <select id='stypeshow' name='service_type' class="form-control">
                                    <option value=''>Select Service Type</option>
                                    <option <?= $service_type == 3 ? 'selected' : '' ?> value='3' label="45min Service"></option>
                                    <option <?= $service_type == 0 ? 'selected' : '' ?> value='0' label="One Day Service"></option>
                                    <option <?= $service_type == 1 ? 'selected' : '' ?> value='1' label="Weekly Service"></option>
                                    <option <?= $service_type == 2 ? 'selected' : '' ?> value='2' label="Monthly Service"></option>

                                </select>
                            </div>
                        </div>

                        {{--                        Contract Type Menu--}}
                        <div id="displaycontract" class="col-md-6" @if($contract_type != null) style="display: block;" @else style="display: none"  @endif>
                            <div class="form-group">
                                <label>Contract Type</label>
                                <select id='contracttype' name='contract_type' class="form-control">
                                    <option value=''>Select Contract Type</option>
                                    <option <?= $contract_type == 0 ? 'selected' : '' ?> value='0'>Fresh</option>
                                    <option <?= $contract_type == 1 ? 'selected' : '' ?> value='1'>Extention</option>
                                </select>
                            </div>
                        </div>


                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Building Type</label>
                                <select name="building_type" class="form-control">
                                    <option value="">Select</option>
                                    <option value="Villa" <?= $building_type =='Villa' ?'selected' : '' ?>>Villa</option>
                                    <option value="Office" <?= $building_type =='Office' ?'selected' : '' ?>>Office</option>
                                    <option  value="Apartment" <?= $building_type =='Apartment' ?'selected' : '' ?> >Apartment</option>
                                </select>
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

                        <div class="col-md-6">
                                <div class="form-group">
                                    <label>Service Price<b class="text-danger">*</b></label>
                                    <input type="text" name="price" class="form-control" required
                                        data-parsley-required-message="Enter Service Price" oninput="validateNumber(this);" value="{{ $serviceprice }}">
                                </div>
                         </div>


                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" rows="5" class="form-control">{{ $description }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>What's Included?<b class="text-danger">*</b></label>
                                <textarea id="summernote" name="included_text"  class="form-control" required
                                          data-parsley-required-message="Enter Details">{{$included}}</textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Image</label><br>
                                <img id="image-preview" style="width:100px; height:90px;" class="img-responsive"
                                    @if ($image) src="{{$image}}" @endif>
                                <br><br>
                                <input type="file" name="image" class="form-control" data-role="file-image" data-preview="image-preview" data-parsley-trigger="change"
                                    data-parsley-fileextension="jpg,png,gif,jpeg"
                                    data-parsley-fileextension-message="Only files with type jpg,png,gif,jpeg are supported" data-parsley-max-file-size="5120" data-parsley-max-file-size-message="Max file size should be 5MB" data-parsley-imagedimensions="300x300">
                                <span class="text-info">Upload image with dimension 300x300</span>
                            </div>
                        </div>

                        <div class="col-md-6" style="display: none;">
                            <div class="form-group b_img_div">
                                <label>Banner Image</label><br>
                                <img id="image-preview-b" style="width:300px; height:93px;" class="img-responsive"
                                    @if ($banner_image) src="{{ $banner_image }}" @endif>

                                <br><br>
                                <input type="file" name="banner_image" class="form-control" onclick="this.value=null;"
                                    data-role="file-image" data-preview="image-preview-b" data-parsley-trigger="change"
                                    data-parsley-fileextension="jpg,png,gif,jpeg"
                                    data-parsley-fileextension-message="Only files with type jpg,png,gif,jpeg are supported" data-parsley-max-file-size="5120" data-parsley-max-file-size-message="Max file size should be 5MB">
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#summernote').summernote({
                tabsize: 2,
                height: 100
            });
        });
        function serviceType(servicecat) {
            let selectedText = servicecat.options[servicecat.selectedIndex].innerText;
            if(selectedText.trim() === "Cleaning Services".trim()) {
                $('#displaycontract').find('option').not(':first').remove();
                $('#displaycontract').css('display', 'none')
                $('#servicetypeshow').css('display', 'block').animate(2000)
            }
            if(selectedText.trim() === "Contracts".trim()) {
                $('#servicetypeshow').find('option').not(':first').remove();
                $('#servicetypeshow').css('display', 'none')
                $('#displaycontract').css('display', 'block').animate(2000)
            }
            if(selectedText.trim() !== "Cleaning Services".trim() && selectedText.trim() !== "Contracts".trim()){
                $('#servicetypeshow').css('display', 'none')
                $('#displaycontract').css('display', 'none')
            }
        }
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
                            'Unable to save service. Please try again later.';
                            App.alert(m, 'Oops!');
                        }
                    } else {
                        App.alert(res['message'], 'Success!');
                                setTimeout(function(){
                                    window.location.href = App.siteUrl('/portal/services');
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
        var validNumber = new RegExp(/^\d*\.?\d*$/);
var lastValid = 0;
function validateNumber(elem) {
  if (validNumber.test(elem.value)) {
    lastValid = elem.value;
  } else {
    elem.value = lastValid;
  }
}
    </script>
@stop
