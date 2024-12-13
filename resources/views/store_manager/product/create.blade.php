@extends("vendor.template.layout")

@section('header')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.js"></script>
@stop


@section('content')
    <div class="card">
      <meta name="csrf-token" content="{{ csrf_token() }}" />
        <div class="card-body">
            <form method="post" action="{{ url('/vendor/product/add_product') }}" id="admin-form" enctype="multipart/form-data" data-parsley-validate="true">
                <input type="hidden" name="id" value="{{ $id }}">
                @csrf
                <div class="row  d-flex justify-content-between align-items-center">
                    <div class="col-md-6 col-sm-6">
                        <label>Product Type<b class="text-danger">*</b></label>
                    <select class="form-control text-filed" id="txt_product_type"  name="product_type" @if($id) {{ "disabled"}} @endif >
                        <option value="1" <?php echo (($product_type == 1) ? 'selected="selected"' : '')?>>Simple</option>
                        <option value="2" <?php echo (($product_type == '2') ? 'selected="selected"' : '')?>>Variable</option>
                    </select>
                </div>
                
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
                    
                </style>

                    <div class="col-md-6 form-group">
                        <label style="margin-bottom: 0.9rem;">Category<b class="text-danger">*</b></label>
                        <select data-url="{{url('vendor/sellers_by_categories')}}" class="form-control jqv-input product_catd select2" data-jqv-required="true"
                            name="category_ids[]" data-role="select2" data-placeholder="Select Categories"
                            data-allow-clear="true" multiple="multiple" required
                            data-parsley-required-message="Select Category">
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
                    
                    <div class="col-md-6 form-group" style="display:none;">
                        <label>Default Category<b class="text-danger"></b></label>
                        <select data-url="" class="form-control jqv-input product_catd select2"
                            name="default_category_id" data-role="select2" data-placeholder="Select Categories"
                            data-allow-clear="true" >
                            @if(isset($category_list) && count($category_list) > 0)

                            @foreach($category_list as $parent_cat_id => $parent_cat_name)
                            
                            
                            <?php if ( isset($sub_category_list[$parent_cat_id]) && !empty($sub_category_list[$parent_cat_id]) ) { ?>
                            <optgroup label="<?php echo $parent_cat_name; ?>" <?php echo in_array($parent_cat_id, $category_ids) ? 'selected' : ''; ?>>
                                <?php foreach ($sub_category_list[$parent_cat_id] as $sub_cat_id => $sub_cat_name): ?>
                                <?php if ($id > 0 && $id == $sub_cat_id) {
                                    continue;
                                } ?>
                                <?php if ( isset($sub_category_list[$sub_cat_id]) && !empty($sub_category_list[$sub_cat_id]) ){ ?>
                            <optgroup label="<?php echo str_repeat('&nbsp;', 4) . $sub_cat_name; ?>" <?php echo ($sub_cat_id== $default_category_id) ? 'selected' : ''; ?>>
                                <?php foreach ($sub_category_list[$sub_cat_id] as $sub_cat_id2 => $sub_cat_name2): ?>
                                <?php if ($id > 0 && $id == $sub_cat_id2) {
                                    continue;
                                } ?>
                                <?php if ( isset($sub_category_list[$sub_cat_id2]) && !empty($sub_category_list[$sub_cat_id2]) ){ ?>
                            <optgroup label="<?php echo str_repeat('&nbsp;', 6) . $sub_cat_name2; ?>" <?php echo ($sub_cat_id2 == $default_category_id) ? 'selected' : ''; ?>>
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
                                    <?php echo ($sub_cat_id3== $default_category_id) ? 'selected' : ''; ?>>
                                    <?php echo str_repeat('&nbsp;', 8) . $sub_cat_name3; ?>
                                </option>
                                <?php } ?>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php }else{ ?>
                            <option value="<?php echo $sub_cat_id2; ?>" <?php echo ($sub_cat_id2==$default_category_id) ? 'selected' : ''; ?>>
                                <?php echo str_repeat('&nbsp;', 6) . $sub_cat_name2; ?>
                            </option>
                            <?php } ?>
                            <?php endforeach; ?>
                            </optgroup>
                            <?php }else{ ?>
                            <option value="<?php echo $sub_cat_id; ?>" <?php echo ($sub_cat_id==$default_category_id) ? 'selected' : ''; ?>>
                                <?php echo str_repeat('&nbsp;', 4) . $sub_cat_name; ?>
                            </option>
                            <?php } ?>
                            <?php endforeach; ?>
                            </optgroup>
                            <?php }else{ ?>
                            <option value="<?php echo $parent_cat_id; ?>" <?php echo ($parent_cat_id==$default_category_id) ? 'selected' : ''; ?>>
                                <?php echo $parent_cat_name; ?>
                            </option>
                            <?php } ?>


                            @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="col-md-6 form-group">
                        <label>Product Name<b class="text-danger">*</b></label>
                        <input type="text" name="product_name" class="form-control jqv-input" required
                            data-parsley-required-message="Enter Product Name"
                            value="{{ $name }}">
                    </div>
                    <div class="col-md-6 form-group" style="display: none;">
                        <input type="hidden" name="seller_id" id="seller_id" value="{{ auth()->user()->id }}">
                    </div>
                     <div class="col-md-6 form-group">
                        <label>Stores<b class="text-danger">*</b></label>
                        <select class="form-control jqv-input select2" name="store_id" required
                            data-parsley-required-message="Select a Store" id="store-id">
                            <option value="">Select Stores</option>
                            @foreach ($stores as $sel)
                                <option value="{{$sel->id }}" @if ($sel->id == $store_id) selected @endif>{{ $sel->store_name }}
                                </option>
                            @endforeach
                            
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Brand<b class="text-danger">*</b></label>
                         <select name="brand" class="form-control jqv-input select2" required
                        data-parsley-required-message="Select Brand" id="brand">
                            <option value="">Select</option>

                            @foreach ($brand as $cnt)
                                <option <?php if(!empty($product->brand)) { ?> {{$product->brand == $cnt->id ? 'selected' : '' }} <?php } ?> value="{{ $cnt->id }}">
                                    {{ $cnt->name }}</option>
                            @endforeach;
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Status</label>
                        <select name="active" class="form-control">
                            <option <?= $active == 1 ? 'selected' : '' ?> value="1">Active</option>
                            <option <?= $active == 0 ? 'selected' : '' ?> value="0">Inactive</option>
                        </select>
                    </div>
                  
                    
                    <div class="col-md-12 form-group other-specs-wrap">
                        <div class="top-bar">
                        <label class="badge bg-light d-flex justify-content-between align-items-center">Other Specs <button class="btn btn-primary pull-right" type="button"
                                data-role="add-spec"><i class="flaticon-plus-1"></i></button> </label>
                        </div>
                        <input type="hidden" id="spec_counter" value="{{ count($specs) }}">
                        <div id="spec-holder">
                            @if (!empty($specs))
                                <?php $i = 0; ?>
                                @foreach ($specs as $spec)
                                    <div class="row">
                                        <div class="col-md-5 form-group">
                                            <input type="text" name="spec[{{ $i }}][title]" placeholder="Title"
                                                value="{{ $spec->spec_title }}" class="form-control jqv-input"
                                                data-jqv-required="true">
                                        </div>
                                        <div class="col-md-5 form-group">
                                            <textarea name="spec[{{ $i }}][description]"
                                                placeholder="Description" class="form-control jqv-input"
                                                data-jqv-required="true">{{ $spec->spec_descp }}</textarea>
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

                   
                    <div class="col-md-6 form-group">
                        <label>Meta Title<b class="text-danger"></b></label>
                        <input type="text" name="meta_title" class="form-control jqv-input" data-jqv-required="true"
                            value="{{ $meta_title }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Meta Keyword<b class="text-danger"></b></label>
                        <input type="text" name="meta_keyword" class="form-control jqv-input" data-jqv-required="true"
                            value="{{ $meta_keyword }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Meta Description<b class="text-danger"></b></label>
                        <input type="text" name="meta_description" class="form-control jqv-input" data-jqv-required="true"
                            value="{{ $meta_description }}">
                    </div>
                    <div class="col-md-6 form-group">
                    </div>
                    <div class="col-md-12 form-group">
                        <fieldset id="product-single-variant" class="mt-3" <?php echo ($product_type != 1 ? 'style="display:none;"' : '') ?>>
                             @include('vendor/product/simple_inventory')
                        </fieldset>
                    </div>
                    <input type="hidden" name="mode" id="mode" value="{{$mode}}">
                    <div class="col-md-12 form-group">
                     <fieldset id="product-attribute-wrapper" class="mt-3" <?php echo ((empty($attribute_list) || ($mode == 'add') || ($product_type == 1)) ? 'style="display:none;"' : '') ?>>
                        <legend>Create Variants</legend>
                        <div id="product-variant-alert" class="alert alert-warning mb-0" style="display:none;"></div>
                        <div id="product-attribute-box">
                            <?php   if ( $mode == 'edit' ): ?>
                                @include('vendor/product/category_attribute_ajax_list')
                              
                            <?php endif; ?>
                        </div>
                        <div id="product-multi-variant" class="mt-3 mb-3" <?php echo ($product_type != 2 ? 'style="display:none;"' : '') ?>>
                            @include('vendor/product/product_variant_form')
                        
                    </div>
                    </fieldset>
                </div>
                    <div class="col-md-12 text-center mt-3">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

<div class="modal fade" id="crop_modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
       <div class="modal-content">
          <div class="modal-header">
             <h5 class="modal-title" id="modalLabel">Crop Image</h5>
             <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
             </button>
          </div>
          <div class="modal-body">
             <div class="img-container">
                <div class="row">
                   <div class="col-md-8">
                      <img id="image_crop_section" src="">
                   </div>
                   
                </div>
             </div>
          </div>
          <div class="modal-footer">
             <button type="button" class="btn btn-primary" id="crop">Crop</button>
          </div>
       </div>
    </div>
 </div>

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script> 
        $('.form-control').on("click", function (e) {
    $(this).closest('div.form-group').find(".invalid-feedback").html("");
    $(this).closest('div.form-group').find(".invalid-feedback").remove('invalid-feedback');
    $(this).closest('div.form-group').find(".is-invalid").removeClass("is-invalid");

});
$('.form-control').on("change", function (e) {
    $(this).closest('div.form-group').find(".invalid-feedback").html("");
    $(this).closest('div.form-group').find(".invalid-feedback").remove('invalid-feedback');
    $(this).closest('div.form-group').find(".is-invalid").removeClass("is-invalid");

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
    <script>
        App.initFormView();
        $(document).ready(function() {
            $('.select2').select2();

        });
        $('body').on("click", '[data-role="remove-spec"]', function() {
            $(this).parent().parent().remove();
        });
        var form_uploaded_images = {};
        $('[data-role="add-spec"]').click(function() {
            let counter = $("#spec_counter").val();
            counter++;
            var html = '<div class="row">' +
                '<div class="col-md-5 form-group">' +
                '<input type="text" name="spec[' + counter +
                '][title]" placeholder="Title" class="form-control jqv-input" data-jqv-required="true">' +
                '</div>' +
                '<div class="col-md-5 form-group">' +
                '<textarea name="spec[' + counter +
                '][description]" placeholder="Description" class="form-control jqv-input" data-jqv-required="true"></textarea>' +
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
            var i = 0;
            
            $.each(form_uploaded_images, function (k, v) { 
                    if ( k == 'product-simple-image' ) {
                        formData.delete('product_simple_image[]');
                        i = 0;
                        $.each(v, function (k1, v1) {
                            formData.append('product_simple_image_'+i, v1);
                            i++;
                        });
                    } else {
                        var k_idx = k.split(/\s*\-\s*/g);
                        k_idx = k_idx[k_idx.length-1];
                        formData.delete('product_variant_image_'+k_idx+'[]');
                        i = 0;
                        $.each(v, function (k1, v1) {                            
                            formData.append('product_variant_image_'+k_idx+'_'+i, v1);
                             i++;
                        });
                    }
                });

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
                            window.location.href = App.siteUrl('/vendor/products');
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
        $(".product_cat").change(function(){
            $(".slrs").attr('disabled','');
            _cat = $(this).val();
            html = '<option value="">Select Seller</option>';
            $(".slrs").html(html);
            $.ajax({
                type: "POST",
                enctype: 'multipart/form-data',
                url: $(this).data('url'),
                data: {
                    "id" :$(this).data('id'),
                    'cat': _cat,
                    "_token": "{{ csrf_token() }}"
                },
                dataType: 'json',
                success: function(res) {
                    for (var i=0; i < res['data'].length; i++) {
                        html += '<option value="'+ res['data'][i]['id'] +'">'+ res['data'][i]['business_name'] +'</option>';
                    }
                    $(".slrs").html(html);
                    $(".slrs").removeAttr('disabled');
                    $(".slrs").change();
                },
                error: function(e) {
                    App.alert(e.responseText, 'Oops!');
                }
            });
        })

        $('[data-role="add_more_button"]').click(function(){ 
    var html = '';
    let counter = $("#button_counter").val();
    counter++;
    html=html+'<div class="row">'
                +'<div class="col-md-5">'
                    +'<div class="form-group">'
                      +'<input type="text" class="form-control jqv-input" data-jqv-required="true" name="spec_doc_title_'+counter+'" placeholder="Enter title">'
                    +'</div>'
                  +'</div>'
                  +'<div class="col-md-5">'
                    +'<div class="form-group">'
                    +'<div class="custom-file">'
                        +'<input type="file" class="custom-file-input jqv-input" data-jqv-required="true" name="spec_doc_image_'+counter+'" id="trade_licenece">'
                        +'<label class="custom-file-label" for="customFile">Choose file</label>'
                    +'</div>'
                    +'</div>'
                  +'</div>'
                    +'<div class="col-md-2 d-flex justify-content-end align-items-start">'
                      +'<button class="btn btn-danger" type="button" data-role="remover"><i class="flaticon-minus-2"></i></button>'
                    +'</div>'
                
              +'</div>';
    $('[data-role="doc-holder"]').append(html);
    $("#button_counter").val(counter);
  });
  $("body").on("click",'[data-role="remover"]',function(){
    $(this).parent().parent().remove();
  });

  var $modal = $('#crop_modal');
      var image = document.getElementById('image_crop_section');
      var cropper;
      $("body").on("change", ".crop_image", function (e) {
         var files = e.target.files;

            var  fileType = files[0]['type'];
            var validImageTypes = ['image/gif', 'image/jpeg', 'image/png'];
            if (!validImageTypes.includes(fileType)) {
                return false;
            }

         var done = function (url) {
            image.src = url;
            $modal.modal('show');
         };
         var reader;
         var file;
         var url;
         if (files && files.length > 0) {
            file = files[0];


            if (URL) {
               done(URL.createObjectURL(file));
            } else if (FileReader) {
               reader = new FileReader();
               reader.onload = function (e) {
                  done(reader.result);
               };
               reader.readAsDataURL(file);
            }
         }
      });
      $modal.on('shown.bs.modal', function () {
        // var finalCropWidth = 320;
        // var finalCropHeight = 200;
        // var finalAspectRatio = finalCropWidth / finalCropHeight;
        //  cropper = new Cropper(image, {
        //     // aspectRatio: finalAspectRatio,
        //     aspectRatio: 1,
        //     viewMode: 3,
        //     preview: '.crop_image_preview_section',
        //  });


        // $('#crop_image').cropper('destroy')
  cropper = new Cropper(image, {
    aspectRatio: 1,
    autoCropArea: 0.7,
    viewMode: 1,
    center: true,
    dragMode: 'move',
    movable: true,
    scalable: true,
    guides: true,
    zoomOnWheel: true,
    cropBoxMovable: true,
    wheelZoomRatio: 0.1,
    ready: function () {
      //Should set crop box data first here
      cropper.setCropBoxData(cropBoxData).setCanvasData(canvasData);
    }
  })


      }).on('hidden.bs.modal', function () {
         cropper.destroy();
         cropper = null;
      });
      $("#crop").click(function () {
         canvas = cropper.getCroppedCanvas({
            // width: 900,
            // height: 'auto',
         });
         canvas.toBlob(function (blob) {
            url = URL.createObjectURL(blob);
            var reader = new FileReader();
            reader.readAsDataURL(blob);
            reader.onloadend = function () {
               var base64data = reader.result;
               $("#cropped_upload_image").val(base64data);
               $("#image-preview").attr('src',base64data);
               $modal.modal('hide');
            }
         });
      })
 $('body').off('change', '[name="product_type"]');
        $('body').on('change', '[name="product_type"]', function () {
            var $t = $(this);
            var action = $t.closest('form').data('action');            
             $.ajax({
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                enctype: 'multipart/form-data',
                url: '{{ url("vendor/products/loadProductAttribute")}}',
                data: {
                    'product_type': $t.val(),
                    'action' : $('#mode').val()
                },
                dataType: 'json',
                success: function(res) {
                    if ( res.html != '' ) {                    
                    $('#product-attribute-wrapper').show();
                    $('#product-single-variant').hide();
                    $('#product-attribute-box').html(res.html);
                    $('#product-attribute-box').find('select.select2').select2();
                } else {
                    $('#product-attribute-wrapper').hide();
                    $('#product-single-variant').show();
                    $('#product-attribute-box').html('');
                    $('#product-multi-variant')
                        .html('')
                        .hide();
                }
                }
            })
          
        });

        // Add attribute
        $('body').off('click', '[data-role="add-attribute"]');
        $('body').on('click', '[data-role="add-attribute"]', function(e) {
            e.preventDefault();
            var attr_id = $('[name="select_attribute"]').val();
            if ( attr_id != '' ) {
                $('[data-attribute-id="'+ attr_id +'"]').show();
                $('[data-attribute-id="'+ attr_id +'"]').find('select.select2').select2();
                $('[name="select_attribute"]').val('').trigger('change');
            }
        });

        // Remove attribute
        $('body').off('click', '[data-role="remove-attribute"]');
        $('body').on('click', '[data-role="remove-attribute"]', function(e) {
            e.preventDefault();
            var $box = $(this).closest('.form-group');
            $box.find('.select2').val(null).trigger('change');
            $box.closest('[data-role="attribute-col"]').hide();
        });

         $('body').off('change', '[data-role="attribute-select"]');
        $('body').on('change', '[data-role="attribute-select"]', function (e) { 
            var $t = $(this);
            var action = $('#mode').val();
            
            if ( action == 'Create' ) { 
                var formData = $('[name^="product_attribute"]').serializeArray();
                $('#variant-ajax-loading').show();
                $.ajax({
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    enctype: 'multipart/form-data',
                    url: "{{ url('vendor/products/loadProductVariations')}}",
                    data: formData,
                    dataType: 'json',
                    success: function(data) {
                        $('#product-multi-variant')
                            .show()
                            .html(data['html']);
                    }
                })
               /* $.post(App.siteUrl('vendor/products/loadProductVariantAttributes'), formData, function (data) {
                    $('#variant-ajax-loading').hide();
                    if ( typeof data['html'] !== 'undefined' ) {
                        $('#product-multi-variant')
                            .show()
                            .html(data['html']);
                            $('textarea[data-editor="ck"]').each(function(){  console.log( $(this).attr('id') );
                                CKEDITOR.replace( $(this).attr('id') );
                                          
                             });
                    }
                    if ( data['total_variants'] > 0 ) {
                        $('#product-variant-alert')
                            .text('')
                            .hide();
                    } else {
                        $('#product-variant-alert')
                            .text('You must create at least one product variant')
                            .show();
                        setTimeout(function() {
                            $('#product-variant-alert').fadeOut(500);
                        }, 3000);
                    }
                });*/
            }
        });


        // Selecting a new attribute
        $('body').on('select2:select', '[data-role="attribute-select"]', function (e) {
            var $t = $(this);            
            var action = $('#mode').val();

            if ( action == 'edit' ) {
                var attr_val_id = e.params.data.id;
                var variant_count = $('#product-multi-variant-accordion').data('variant-count');
                var formData = $t.closest('[data-role="attribute-col"]').siblings().find('[name^="product_attribute"]').serializeArray();
                formData.push({name: 'product_id', 'value': $('[name="id"]').val()});
                formData.push({name: 'attr_val_id', value: attr_val_id});
                formData.push({name: 'start_index', value: variant_count});
            
                

                $.ajax({
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    enctype: 'multipart/form-data',
                    url: '{{ url("vendor/products/linkNewAttrForProduct")}}',
                    data: formData,
                    dataType: 'json',
                    success: function(data) {
                        if ( data['status'] == 0 ) {
                            var msg = data['message']||'Unable to add new attribute. Please try again later.';
                            App.Growl([msg, 'warning']);
                            $t.find('option[value="'+ attr_val_id +'"]').prop("selected", false);
                            $t.select2('destroy');
                            $t.select2();
                            return false;
                        } else if ( data['status'] == 1 ) {
                            $('#product-multi-variant-accordion').data('variant-count', data['total_variants']);
                           console.log(data['html']);
                            if ( typeof data['html'] !== 'undefined' ) { console.log('fff');
                                $('#product-multi-variant').append(data['html']);
                            }
                             
                        } 

                        let total_variants = data['total_variants'];
                        let num = total_variants - 1;
                        
                    }
                })

               
            }
        });

        // Removing an attribute
        $('body').on('select2:unselect', '[data-role="attribute-select"]', function (e) {
            var $t = $(this);
            var action = $t.closest('form').data('action');

            if ( action == 'edit' ) {
                var attr_val_id = e.params.data.id;
                App.confirm({
                    title: 'Confirm Action',
                    body: 'Are you sure that you want to remove this attribute? This action can\'t be undone.',
                    onConfirm: function() {
                        $.blockUI();
                        var $def = $.Deferred();
                        var product_id = $('[name="id"]').val();                    
                        $.post(App.siteUrl('vendor/products/unlinkAttrFromProduct'), { 'product_id': product_id, 'attr_val_id': attr_val_id }, function (data) {
                            $def.resolve(data);
                        });
                        $def.done(function (data) {
                            $.unblockUI();
                            if ( data['status'] == 0 ) {
                                var msg = data['message']||'Unable to remove attribute. Please try again later.';
                                App.alert([msg, 'warning']);
                            } else {
                                App.alert(['Done! attribute removed successfully.', 'success']);
                                location.reload();
                            }
                        });
                    }
                });

                $t.find('option[value="'+ attr_val_id +'"]').prop("selected", true);
                $t.select2('destroy');
                $t.select2();
                return false;
            }
        });

         var config = {
        'list_page_url': 'vendor_products',
        'rateYo': {
            // rating: 4.5,
            readOnly: true,
            starWidth: "20px",
            normalFill: "#e0e0e0",
            ratedFill: "#d2a07a"
        },
        'max_image_uploads': 5,
        'image_allowed_types': [
            'image/gif',
            'image/png',
            'image/jpg',
            'image/jpeg',
            'image/svg+xml'
        ],
    }
    var file_upload_index = 1;
         // Preview of uploaded image
        $('body').off('change', '[data-role="product-img-upload"]');
        $('body').on('change', '[data-role="product-img-upload"]', function (e) {
            e.preventDefault();            
            var _URL = window.URL || window.webkitURL;
            
            var $parent = $(this).closest('div.uploaded-prev-imd');            
            var $imgBox = $('<div class="uploaded-prev-imd"><img /><a href="javascript:void(0)" class="del-product-img" data-role="product-img-trash" data-image-file=""><i class="flaticon-delete"></i> Delete</a></div>');
            var image_key = App.makeSafeName($(this).attr('name'), '-');
            var countval = $(this).attr('counter');
            var counter = $parent.siblings('div.uploaded-prev-imd').length;
            var vval = 0;
            for (var i = 0; i < (this.files).length; i++) {
                if ( counter >= config.max_image_uploads ) {
                    return false;
                }
                counter++;
                (function(file) { 
                    var img = new Image();
                    img.src = _URL.createObjectURL(file);
                    img.onload = function() { 
                    var maxwidth = '<?php echo  config('global.product_image_width')?>';
                    var maxheight = '<?php echo config('global.product_image_height')?>';  
                       
                        if(this.width > maxwidth || this.height > maxheight){
                            App.alert("Maximum Image upload size issue","Opss");

                            return;
                        }else{
                            if( $.inArray(file['type'], config.image_allowed_types) == -1 ) {
                                swal('Oops!', 'Please upload image files (gif, png or jpg)', 'warning');
                                return false;
                            }
                            var reader  = new FileReader();
                            reader.onloadend = function () { 
                                var $clone = $imgBox.clone();
                                $clone.append('<img src="'+reader.result+'" width="100" height="100">');
                                $clone.data('file-uid', file_upload_index);
                                //$clone.find('img').attr('src', reader.result);
                                $clone.insertBefore($parent);
                                if ( $parent.siblings('div.uploaded-prev-imd').length == config.max_image_uploads ) {
                                    $parent.hide();
                                }
                                if ( typeof(form_uploaded_images[image_key]) === 'undefined' ) {
                                    form_uploaded_images[image_key] = {};
                                }
                                vval = $('#image_counter_'+countval).val();
                                vval++;
                                $('#image_counter_'+countval).val(vval);
                                form_uploaded_images[image_key][file_upload_index] = file;
                                $('#image_counter').val(file_upload_index);
                                file_upload_index++;

                            };
                            reader.readAsDataURL(file);
                        }
                    };
                            
                })(this.files[i]);
            }
        });

        // Removing an attribute
        $('body').on('select2:unselect', '[data-role="attribute-select"]', function (e) {
            var $t = $(this);
            var action = $('#mode').val();

            if ( action == 'edit' ) {
                var attr_val_id = e.params.data.id;
                App.confirm({
                    title: 'Confirm Action',
                    body: 'Are you sure that you want to remove this attribute? This action can\'t be undone.',
                    onConfirm: function() {
                        
                    var $def = $.Deferred();
                    var product_id = $('[name="id"]').val();    
                    $.ajax({
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        enctype: 'multipart/form-data',
                        url: '{{ url("vendor/products/unlinkAttrFromProduct")}}',
                        data: { 'product_id': product_id, 'attr_val_id': attr_val_id },
                        dataType: 'json',
                        success: function(data) {
                            if ( data['status'] == 0 ) {
                                    var msg = data['message']||'Unable to remove attribute. Please try again later.';
                                    App.alert([msg, 'warning']);
                                } else {
                                    App.alert(['Done! attribute removed successfully.', 'success']);
                                    location.reload();
                                }
                        }
                    })                    
                       /* $.post(App.siteUrl('/vendor/products/unlinkAttrFromProduct'), { 'product_id': product_id, 'attr_val_id': attr_val_id }, function (data) {
                            $def.resolve(data);
                        });
                        $def.done(function (data) {
                           
                            if ( data['status'] == 0 ) {
                                var msg = data['message']||'Unable to remove attribute. Please try again later.';
                                App.alert([msg, 'warning']);
                            } else {
                                App.alert(['Done! attribute removed successfully.', 'success']);
                                location.reload();
                            }
                        });*/
                    }
                });

                $t.find('option[value="'+ attr_val_id +'"]').prop("selected", true);
                $t.select2('destroy');
                $t.select2();
                return false;
            }
        });

    
    $(document).on('click','.del-product-img',function(){ 
        var image = $(this).attr('data-image-file');
        var $imgList = $(this).closest('div.upload-img-product-items');
        var $target = $(this).closest('div.uploaded-prev-imd');
        var product_id = $('[name="id"]').val();
        var product_type = $('[name="product_type"]').val();
        var variant_id = $imgList.data('variant-id');  
        if(image!="") { 
            $.ajax({
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                enctype: 'multipart/form-data',
                url: '{{ url("vendor/products/removeProductImage")}}',
                data: { 'image': image, 'product_type': product_type,'product_id':product_id,'variant_id':variant_id },
                dataType: 'json',
                success: function(data) {
                    if ( data['status'] == 0 ) {
                            var msg = data['message']||'Unable to remove attribute. Please try again later.';
                            App.alert([msg, 'warning']);
                        } else {
                            App.alert(['Done! Image removed successfully.', 'success']);
                            $(this).parent().find('.uploaded-prev-imd').remove();
                            $target.remove();
                            
                        }
                }
            }) 
        }else { 
            $target.remove();
            $(this).parent().find('.uploaded-prev-imd').remove();
        }  
    })
    $(document).on('click','.default_attribute_id',function(){
        var sel = $(this).val(); 
        $('.default_attribute_id').prop('checked',false);       
        $(this).prop('checked',true);
        
    })
    </script>
@stop
