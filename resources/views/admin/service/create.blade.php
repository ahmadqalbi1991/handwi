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
        .top-bar{
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
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
                         <div class="col-md-6 form-group">
                            <label>Activity Type<b class="text-danger">*</b></label>
                            <select name="activity_id" class="form-control jqv-input select2"
                                    data-parsley-required-message="Select Activity Type" id="activity-id" required 
                                    data-url="{{url('admin/get_service_categories_by_activity_id')}}"
                                    >
                                <option value="">Select Activity Type</option>
                                @foreach ($activity_types as $activity_type)
                                    @if($activity_type->id!=12)
                                        <option value="{{ $activity_type->id }}" {{$activity_id == $activity_type->id ? 'selected' : ""}}>{{ $activity_type->activity_name }}
                                        </option>
                                    @endif;
                                @endforeach
                            </select>
                        </div>

                     
                         <div class="col-md-6 form-group select-category-form-group">
                            <label>Service Category<b class="text-danger">*</b></label>
                            <select data-url="{{url('admin/sellers_by_categories')}}" class="form-control jqv-input product_catd select2" data-jqv-required="true"
                                name="category_ids[]" data-role="select2" data-placeholder="Select Categories"
                                data-allow-clear="true" required
                                data-parsley-required-message="Select Category" >
                                <option value="">
                                    Select Category
                                </option>
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
                                <label>Building Type</label>
                                <select name="buildingType" class="form-control select2"
                                    data-parsley-required-message="Select Building Type">
                                        @foreach ($buildingTypes as $buildingType)
                                        <option 
                                        @if($building_type==$buildingType->id) selected @endif 
                                            value="{{$buildingType->id }}">
                                            {{$buildingType->name}}
                                        </option>
                                             
                                    @endforeach;
                                </select>



                            </div>
                        </div>
                        <!-- <div class="col-md-2">
                                <div class="form-group">
                                    
                                    <label>Service Type<b class="text-danger">*</b></label>
                                    <select class="form-control" name="service_price_type">
                                        <option {{($service_price_type=="Per Hour")?'selected':''}} value="Per Hour">Per Hour</option>
                                        <option {{($service_price_type=="Per Person")?'selected':''}} value="Per Person">Per Person</option>
                                        <option {{($service_price_type=="Per Unit")?'selected':''}} value="Per Unit">Per Unit</option>
                                        <option {{($service_price_type=="Per Service")?'selected':''}} value="Per Service">Per Service</option>
                                    </select>
                                </div>
                         </div> -->
                         <div class="col-md-2 form-group">
                            <label>Activity Type<b class="text-danger">*</b></label>
                            <select name="service_price_type" class="form-control jqv-input select2"
                                    data-parsley-required-message="Select Activity Type" id="activity-id" required 
                                    data-url="{{url('admin/get_service_categories_by_activity_id')}}"
                                    >
                                <option value="">Select Activity Type</option>
                                @foreach ($service_types as $row)
                                    <option value="{{ $row->name }}" {{$service_price_type == $row->name ? 'selected' : ""}}>{{ $row->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                                <div class="form-group">
                                    
                                    <label>Service Price<b class="text-danger">*</b></label>
                                    <input type="text" name="price" class="form-control" required
                                        data-parsley-required-message="Enter Service Price" oninput="validateNumber(this);" value="{{ $serviceprice }}">
                                </div>
                         </div>
                         <div class="col-md-6">
                                <div class="form-group">
                                    <label>Basic price label<b class="text-danger">*</b></label>
                                    <input type="text" name="price_lable" class="form-control" required
                                        data-parsley-required-message="Enter price lable" value="{{ $price_label }}">
                                </div>
                               
                         </div>
                          <div class="col-md-6">
                                <div class="form-group">
                                    <label>Service Duration (min)<b class="text-danger">*</b></label>
                                    <input type="text" name="duration" class="form-control" required
                                        data-parsley-required-message="Enter Service Price" oninput="validateNumber(this);" value="{{ $duration }}">
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description" rows="5" class="form-control">{{ $description }}</textarea>
                                </div>
                         </div>
                         <div class="col-md-6">
                            <div class="form-group">
                                <label>Image</label><br>
                                <input type="file" name="image" class="form-control" data-role="file-image" data-preview="image-preview" data-parsley-trigger="change"
                                    data-parsley-fileextension="jpg,png,gif,jpeg,webp"
                                    data-parsley-fileextension-message="Only files with type jpg,png,gif,jpeg,webp are supported" data-parsley-max-file-size="5120" data-parsley-max-file-size-message="Max file size should be 5MB"  >
                                <span class="text-info" data-parsley-imagedimensions="1204x664">Upload image with dimension 1204 x 664</span>
                                <br><br>
                                <img id="image-preview" style="width:100px; height:90px;" class="img-responsive"
                                    @if ($image) src="{{$image}}" @endif>
                            </div>
                        </div>
                     
                        <div class="col-md-12">
                     
                         <div class="card mb-2">
                    <div class="card-body">
                        <div class="row  d-flex justify-content-between align-items-center">
                            <div class="col-md-12 form-group other-specs-wrap">
                                <div class="top-bar">
                                <div class="badge bg-light d-flex justify-content-between align-items-center">
                                    <label style="display:none">Hourly Rate  </label>
                                    
                                </div>
                                <button class="btn btn-primary pull-right" type="button"
                                    data-role="add-spec" id="addnew"><i class="flaticon-plus-1"></i></button>
                                </div>
                                <input type="hidden" id="spec_counter" value="{{ count($servic_prcies) }}">
                                <div id="spec-holder">
                                    @foreach($servic_prcies as $key=>$value)
                                    <div class="row">
                                    

                                <div class="form-group col-md-4"> 
                                    <input type="text" name="text[]" value="{{$value->text}}" class="form-control" required
                                    data-parsley-required-message="Select Area" id="city-id-{{$key}}">
                                        
                                </div>
                                <div class="form-group col-md-2"> 
                                    <input type="text" name="hourly_rate[]" id="idedit{{$key}}" oninput="validateNumber(this);" counter="edit{{$key}}" class="form-control checkval" value="{{$value->hourly_rate}}" required
                                    data-parsley-required-message="Enter Hourly rate" placeholder="Hourly rate">
                                </div>
                                
                                <div class="col-md-2">
                                             <button type="button" class="btn btn-danger" data-role="remove-spec"><i class="flaticon-minus-2"></i></button>
                                                </div>
                            </div>

                              @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
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
    <script>

        $("#activity-id").change(function(){
            $(".product_catd").attr('disabled','');
            html = '<option value="">None</option>';
            $(".product_catd").html(html);
            $.ajax({
                type: "POST",
                enctype: 'multipart/form-data',
                url: $(this).data('url'),
                data: {
                    "activity_id" :$(this).val(),
                    "_token": "{{ csrf_token() }}"
                },
                dataType: 'json',
                success: function(res) {
                    if(res['status'] == '1'){
                        $(".product_catd").html(res['cat_view']);
                        $(".product_catd").removeAttr('disabled');
                    }
                },
                error: function(e) {
                    App.alert(e.responseText, 'Oops!');
                }
            });
        })

        @if(empty($id))
        $(function(){
            $( "#addnew" ).trigger( "click" );
        });
        @endif
        $(document).ready(function() {
            $('.select2').select2();

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
        $('body').on("click", '[data-role="remove-spec"]', function() {
            $(this).parent().parent().remove();
        });
        var form_uploaded_images = {};
        $('[data-role="add-spec"]').click(function() {
            let counter = $("#spec_counter").val();
            counter++;
            var html = '<div class="row">'+
                       '<div class="form-group col-md-4">'+ 
                       '<input type="text" name="text[]" placeholder="Enter hour" class="form-control" required data-parsley-required-message="Enter hour" id="city-id'+counter+'">' +
                       '</div>'+
                       '<div class="form-group col-md-2">'+ 
                       '<input type="text" name="hourly_rate[]" oninput="validateNumber(this);" class="form-control" required data-parsley-required-message="Enter Price" placeholder="Hourly rate"></div>' +
                       '<div class="col-md-2">'+
                       '<button type="button" class="btn btn-danger" data-role="remove-spec"><i class="flaticon-minus-2"></i></button>'+
                                                '</div></div>'
            $("#spec_counter").val(counter);
            $('#spec-holder').append(html);
        });
        $('body').off('change', '[data-role="state-change-service"]');
        $('body').on('change', '[data-role="state-change-service"]', function() {
                var $t = $(this);
                var $city = $('#'+ $t.data('input-city'));

                if ( $city.length > 0 ) {
                    var id   = $t.val();
                    var html = '<option value="">Select</option>';

                    $city.html(html);
                    if ( id != '' ) {
                        $.ajax({
                            type: "POST",
                            enctype: 'multipart/form-data',
                            url: "{{url('admin/cities/get_by_state')}}",
                            data: {
                                "id": id,
                                "_token": "{{ csrf_token() }}"
                            },
                            timeout: 600000,
                            dataType: 'json',
                            success: function(res) {
                                for (var i=0; i < res['cities'].length; i++) {
                                html += '<option value="'+ res['cities'][i]['id'] +'">'+ res['cities'][i]['name'] +'</option>';
                                if ( i == res['cities'].length-1 ) {
                                    $city.html(html);
                                // $('.selectpicker').selectpicker('refresh')
                                }
                            }
                            }
                        });
                    }

                }
            });
    </script>
@stop
