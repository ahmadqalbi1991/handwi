@extends('admin.template.layout')
@section('header')
 <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

@stop
@section('content')

 <style>
                    .text-muted {
                        color: #181722 !important;
                        font-size: 12px;
                    }
                    .uploaded-prev-imd{
                            display: flex;
                            flex-direction: row-reverse;
                            justify-content: flex-end;
                            align-items: center;
                            margin: 10px 0px;
                    }
                    .del-product-img{
                        margin-left: 5px;
                            color: #007bff;
                            font-size: 14px;
                            font-weight: 600;
                    }
                    .del-product-img:hover{
                        color: #ff3743;
                    }
                    .select2-container .select2-selection--multiple{
                        min-height: 44px;
                    }
                    #product-single-variant legend{
                        font-size: 15px;
                        color: #000;
                        font-weight: 600;
                        margin-bottom: 5px;
                    }
                    #product-single-variant hr{
                        display: none;
                    }
                    .select-category-form-group .parsley-required{
                        position: absolute;
                        bottom: -20px
                    }

                    .default_attribute_id{
                        width: auto;
                        margin-right: 5px;
                    }

                </style>
    <div class="card mb-5">
        <div class="card-body">
            <div class="">
                <form method="post" id="admin-form" action="{{ url('admin/save_services') }}" enctype="multipart/form-data"
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


                         <div class="col-md-6 form-group select-category-form-group">
                            <label>Service Category<b class="text-danger">*</b></label>
                            <select data-url="{{url('admin/sellers_by_categories')}}" class="form-control jqv-input product_catd select2 servicecat" data-jqv-required="true"
                                name="category_ids[]" data-role="select2" data-placeholder="Select Categories"
                                data-allow-clear="true" {{--multiple="multiple"--}} required
                                data-parsley-required-message="Select Category" id="servicecat" onchange="serviceType(this)">
                                <option value=''>Select Categories</option>

                                @if(isset($category_list) && count($category_list) > 0)

                                @foreach($category_list as $parent_cat_id => $parent_cat_name)


                                <?php if ( isset($sub_category_list[$parent_cat_id]) && !empty($sub_category_list[$parent_cat_id]) ) { ?>
                                <optgroup label="<?php echo $parent_cat_name; ?>" <?php echo in_array($parent_cat_id, $category_ids) ? 'selected' : ''; ?>>
                                    <?php foreach ($sub_category_list[$parent_cat_id] as $sub_cat_id => $sub_cat_name): ?>
                                    <?php if ($id > 0 && $id == $sub_cat_id) {
                                        continue;
                                    } ?>
                                    <?php if ( isset($sub_category_list[$sub_cat_id]) && !empty($sub_category_list[$sub_cat_id]) ){ ?>
                                <optgroup label="<?php echo str_repeat('&nbsp;', 4) . $sub_cat_name; ?>" <?php echo in_array($sub_cat_id, $category_ids) ? 'selected' : ''; ?>>
                                    <?php foreach ($sub_category_list[$sub_cat_id] as $sub_cat_id2 => $sub_cat_name2): ?>
                                    <?php if ($id > 0 && $id == $sub_cat_id2) {
                                        continue;
                                    } ?>
                                    <?php if ( isset($sub_category_list[$sub_cat_id2]) && !empty($sub_category_list[$sub_cat_id2]) ){ ?>
                                <optgroup label="<?php echo str_repeat('&nbsp;', 6) . $sub_cat_name2; ?>" <?php echo in_array($sub_cat_id2, $category_ids) ? 'selected' : ''; ?>>
                                    <?php foreach ($sub_category_list[$sub_cat_id2] as $sub_cat_id3 => $sub_cat_name3): ?>
                                    <?php if ($id > 0 && $id == $sub_cat_id3) {
                                        continue;
                                    } ?>
                                    <?php if ( isset($sub_category_list[$sub_cat_id3]) && !empty($sub_category_list[$sub_cat_id3]) ){ ?>
                                    <?php foreach ($sub_category_list[$sub_cat_id3] as $sub_cat_id4 => $sub_cat_name4): ?>
                                    <?php if ($id > 0 && $id == $sub_cat_id4) {
                                        continue;
                                    } ?>
                                    <option data-style="background-color: #ff0000;" value="<?php echo $sub_cat_id4; ?>"
                                        <?php echo in_array($sub_cat_id4, $category_ids) ? 'selected' : ''; ?>>
                                        <?php echo str_repeat('&nbsp;', 10) . $sub_cat_name4; ?>
                                    </option>
                                    <?php endforeach; ?>
                                    <?php }else{ ?>
                                    <option data-style="background-color: #ff0000;" value="<?php echo $sub_cat_id3; ?>"
                                        <?php echo in_array($sub_cat_id3, $category_ids) ? 'selected' : ''; ?>>
                                        <?php echo str_repeat('&nbsp;', 8) . $sub_cat_name3; ?>
                                    </option>
                                    <?php } ?>
                                    <?php endforeach; ?>
                                </optgroup>
                                <?php }else{ ?>
                                <option value="<?php echo $sub_cat_id2; ?>" <?php echo in_array($sub_cat_id2, $category_ids) ? 'selected' : ''; ?>>
                                    <?php echo str_repeat('&nbsp;', 6) . $sub_cat_name2; ?>
                                </option>
                                <?php } ?>
                                <?php endforeach; ?>
                                </optgroup>
                                <?php }else{ ?>
                                <option value="<?php echo $sub_cat_id; ?>" <?php echo in_array($sub_cat_id, $category_ids) ? 'selected' : ''; ?>>
                                    <?php echo str_repeat('&nbsp;', 4) . $sub_cat_name; ?>
                                </option>
                                <?php } ?>
                                <?php endforeach; ?>
                                </optgroup>
                                <?php }else{ ?>
                                <option value="<?php echo $parent_cat_id; ?>" <?php echo in_array($parent_cat_id, $category_ids) ? 'selected' : ''; ?>>
                                    <?php echo $parent_cat_name; ?>
                                </option>
                                <?php } ?>


                                @endforeach
                                @endif
                            </select>
                        </div>
                        <br><br>



                        <div id="displaycontract" class="col-md-6" @if($contract_type != null) style="display: block;" @else style="display: none"  @endif>
                            <div class="form-group">
                                <label>Contract Type</label>
                                <select id='contracttype' name='contract_type' class="form-control">
                                    <option value=''>Select Contract Type</option>
                                    <option <?= $contract_type == 'Fresh' ? 'selected' : '' ?> value='Fresh'>Fresh</option>
                                    <option <?= $contract_type == 'Extension' ? 'selected' : '' ?> value='Extension'>Extension</option>
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
                                <label>Image</label><br>
                                <img id="image-preview" style="width:100px; height:90px;" class="img-responsive"
                                    @if ($image) src="{{$image}}" @endif>
                                <br><br>
                                <input type="file" name="image" class="form-control" data-role="file-image" data-preview="image-preview" data-parsley-trigger="change"
                                    data-parsley-fileextension="jpg,png,gif,jpeg"
                                    data-parsley-fileextension-message="Only files with type jpg,png,gif,jpeg are supported" data-parsley-max-file-size="5120" data-parsley-max-file-size-message="Max file size should be 5MB" data-parsley-imagedimensions="700x700">
                                <span class="text-info">Upload image with dimension 700x700</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Upload Document</label><br>
                                <input type="file" name="document" class="form-control">
{{--                                       data-parsley-fileextension="jpg,png,gif,jpeg"--}}
{{--                                       data-parsley-fileextension-message="Only files with type jpg,png,gif,jpeg are supported" data-parsley-max-file-size="5120" data-parsley-max-file-size-message="Max file size should be 5MB" data-parsley-imagedimensions="700x700">--}}
{{--                                <span class="text-info">Upload image with dimension 700x700</span>--}}
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

                        <div class="col-md-6">
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description" rows="5" class="form-control">{{ $description }}</textarea>
                                </div>
                         </div>


                        <div class="col-md-12">
                            <div class="form-group">
                                <label>What's Included?<b class="text-danger">*</b></label>
                                <textarea id="summernote" name="included_text"  class="form-control" required
                                          data-parsley-required-message="Enter Details">{{$included}}</textarea>
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

<script type="text/javascript">

    function serviceType(servicecat) {
        let selectedText = servicecat.options[servicecat.selectedIndex].innerText;
        if(selectedText.trim() === "Cleaning Services".trim()) {
            $('#displaycontract').hide();
            $('#servicetypeshow').show();
            $('#time').show();
            // $('#displaycontract').find('option').not(':first').remove();
            // $('#displaycontract').css('display', 'none')
            // $('#servicetypeshow').css('display', 'block').animate(2000)
        }
        if(selectedText.trim() === "Contracts".trim()) {
            $('#servicetypeshow').hide();
            $('#displaycontract').show();
            $('#time').hide();
            // $('#servicetypeshow').find('option').not(':first').remove();
            // $('#servicetypeshow').css('display', 'none')
            // $('#displaycontract').css('display', 'block').animate(2000)
        }
        if(selectedText.trim() !== "Cleaning Services".trim() && selectedText.trim() !== "Contracts".trim()){
            $('#servicetypeshow').css('display', 'none')
            $('#displaycontract').css('display', 'none')
            $('#time').hide();
        }
    }
</script>


{{--<script>--}}
{{--    $('#category_ids').change(function() {--}}
{{--        var item = $(this).val();--}}
{{--        console.log(item);--}}
{{--    })--}}


{{--</script>--}}

{{--<script type='text/javascript'>--}}

{{--    $(document).ready(function(){--}}

{{--        // Department Change--}}
{{--        $('#category_ids').change(function(){--}}

{{--            // Department id--}}
{{--            var id = $(this).val();--}}


{{--            // Empty the dropdown--}}
{{--            $('#sel_emp').find('option').not(':first').remove();--}}
{{--            let url = '{{route('admin.subcat')}}';--}}
{{--            url = url +'/'+id;--}}
{{--            // AJAX request--}}
{{--            $.ajax({--}}
{{--                url: url,--}}
{{--                type: 'get',--}}
{{--                dataType: 'json',--}}
{{--                success: function(response){--}}

{{--                    let len = 0;--}}
{{--                    if(response['data'] != null){--}}
{{--                        $('#displayshow').css('display', 'block').animate(2000)--}}

{{--                        len = response['data'].length;--}}

{{--                    }--}}
{{--                    if(response['message'] == 'error'){--}}
{{--                        $('#displayshow').css('display', 'none').animate(2000)--}}

{{--                    }--}}

{{--                    if(len > 0){--}}
{{--                        // Read data and create <option >--}}
{{--                        for(var i=0; i<len; i++){--}}

{{--                            var id = response['data'][i].id;--}}
{{--                            var name = response['data'][i].title;--}}

{{--                            var option = "<option value='"+id+"'>"+name+"</option>";--}}

{{--                            $("#sel_emp").append(option);--}}
{{--                        }--}}
{{--                    }--}}

{{--                }--}}
{{--            });--}}
{{--        });--}}

{{--    });--}}

{{--</script>--}}

<script>
        $(document).ready(function () {
            $('#summernote').summernote({
                tabsize: 2,
                height: 100
            });
        });

        $(document).ready(function() {
            $('.select2').select2();
            $('#summernote').summernote();
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
                                    window.location.href = App.siteUrl('/admin/services');
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
