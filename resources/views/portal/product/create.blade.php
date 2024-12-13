@extends("portal.template.layout")

@section('header')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.js"></script>
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
        textarea.form-control {
            height: auto !important;
            min-height: 70px;
        }
        .top-bar{
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }
    </style>
    <style>

        /* Create a custom checkbox */
        .checkmark {
            position: absolute;
            top: 0;
            left: 30px;
            height: 25px;
            width: 25px;
            background-color: #eee;
        }

        /* On mouse-over, add a grey background color */
        .container:hover input ~ .checkmark {
            background-color: #ccc;
        }

        /* When the checkbox is checked, add a blue background */
        .container input:checked ~ .checkmark {
            background-color: #2196F3;
        }

        /* Create the checkmark/indicator (hidden when not checked) */
        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }

        /* Show the checkmark when checked */
        .container input:checked ~ .checkmark:after {
            display: block;
        }

        /* Style the checkmark/indicator */
        .container .checkmark:after {
            left: 9px;
            top: 5px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 3px 3px 0;
            -webkit-transform: rotate(45deg);
            -ms-transform: rotate(45deg);
            transform: rotate(45deg);
        }
        
        
            #galleryImages, #cropper{
              width: 100%;
              float: left;
            }
            canvas{
              max-width: 100%;
              display: inline-block;
            }
            #cropperImg{
              /*max-width: 0;
              max-height: 0;*/
            }
            #cropImageBtn, .cropImageBtn{
              display: none;
            }
            #cropperImg img{
              width: 100%;
            }
            /*.img-preview{
              float: left;
            }*/
            .singleImageCanvasContainer{
              max-width: 300px;
              display: inline-block;
              position: relative;
              margin: 2px;
            }
            .singleImageCanvasCloseBtn{
              position: absolute;
              top: 5px;
              right: 5px;
            }

            .singleImageCanvasCloseBtn{
                    width: 30px;
                height: 30px;
                background: red;
                color: #fff;
                padding: 2px;
                border-radius: 5px;
                outline: 0;
                border: 0;
                font-size: 18px;
            }
            .cropImageBtn{
                color: #fff !important;
                width: 70px;
                background: linear-gradient(180deg, #2C93FA 0%, #2C93FA 100%);
                box-shadow: 0px 10px 10px rgba(195, 23, 24, 0.2);
                border: 0px solid #fff !important;
                text-shadow: none;
                font-size: 14px;
                font-weight: normal;
                white-space: normal;
                word-wrap: break-word;
                transition: .2s ease-out;
                touch-action: manipulation;
                cursor: pointer;
                background-color: #e9ecef;
                box-shadow: 0px 5px 20px 0 rgba(0, 0, 0, 0.1);
                will-change: opacity, transform;
                transition: all 0.3s ease-out;
                -webkit-transition: all 0.3s ease-out;
                padding: 10px 14px;
                margin-top: 5px;
                border-radius: 5px;
            }
    </style>
    <div class="mb-5">
      <meta name="csrf-token" content="{{ csrf_token() }}" />
        <div class="">
            <form method="post" action="{{ url('/portal/product/add_product') }}" id="admin-form" enctype="multipart/form-data" data-parsley-validate="true">
                <input type="hidden" name="id" value="{{ $id }}">
                @csrf

                <div class="card mb-2">
                    <div class="card-body">
                        <div class="row justify-content-between align-items-center" >
                            <div class="col-md-6 col-sm-6 form-group">
                            <label>Product Type<b class="text-danger">*</b></label>
                            <select class="form-control text-filed" id="txt_product_type"  name="product_type" @if($id) {{ "disabled"}} @endif >
                                <option value="1" <?php echo (($product_type == 1) ? 'selected="selected"' : '')?>>Simple</option>
                                <option value="2" <?php echo (($product_type == '2') ? 'selected="selected"' : '')?>>Variable</option>
                            </select>
                        </div>
                        <!-- @if($is_food) d-none @endif -->
                        <div class="col-md-6 form-group select-category-form-group 
                        ">
                            <label>Category<b class="text-danger">*</b></label>
                            <select data-url="{{url('admin/sellers_by_categories')}}" class="form-control jqv-input product_catd select2" data-jqv-required="true"
                                name="category_ids[]" data-role="select2" data-placeholder="Select Categories"
                                data-allow-clear="true" multiple="multiple" required
                                data-parsley-required-message="Select Category">
                                @if(isset($category_list) && count($category_list) > 0)

                                @foreach($category_list as $parent_cat_id => $parent_cat_name)
                                    @if(in_array(strtolower(str_replace(' ', '', $parent_cat_name)) ,['dinein']))
                                    @php
                                    @endphp
                                    @endif
                                
                                
                                <?php if ( isset($sub_category_list[$parent_cat_id]) && !empty($sub_category_list[$parent_cat_id]) ) { ?>
                                <optgroup label="<?php echo $parent_cat_name; ?>" <?php echo   in_array($parent_cat_id, $category_ids) ? 'selected' : ''; ?>>
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
                                <option value="<?php echo $parent_cat_id; ?>" <?php echo  in_array($parent_cat_id, $category_ids) ? 'selected' : ''; ?>>
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
                            <input type="text" name="product_name" required placeholder="Enter Product Name" class="form-control jqv-input" data-jqv-required="true" value="{{ $name }}">
                        </div>

                        

                        <div class="col-md-6 form-group" style="display:none;">
                            <label>Stores<b class="text-danger">*</b></label>
                            <select class="form-control jqv-input select2" name="store_id" id="store-id">
                                <option value="0">Select Stores</option>
                                @foreach ($stores as $sel)
                                    <option value="{{$sel->id }}" @if ($sel->id == $store_id) selected @endif>{{ $sel->store_name }}
                                    </option>
                                @endforeach
                                
                            </select>
                        </div>
                        @if($is_food)
                        <div class="col-lg-6 shown-on-click" >
                            <div class="form-group">
                                <label>Select Cuisines</label>
                                <select name="cuisines[]" multiple style="width: 100% !important" class="form-control jqv-input select2 brand" data-parsley-required-message="Select Cuisines" data-placeholder="None" id="cuisines" >
                                    @foreach ($Cuisines as $row)
                                        <option value="{{ $row->id }}" {{in_array($row->id, $Cuisines_ids) ? 'selected' : ""}}>{{ $row->name }}
                                            </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif
                        
                        <div class="col-md-6 form-group {{$is_food ? 'd-none' : '' }}">
                            <label>Brand</label>
                            <select name="brand" class="form-control jqv-input select2" 
                            data-parsley-required-message="Select Brand" id="brand">
                                <option value="">Select</option>

                                @foreach ($brand as $cnt)
                                    <option <?php if(!empty($product->product_brand_id)) { ?> {{$product->product_brand_id == $cnt->id ? 'selected' : '' }} <?php } ?> value="{{ $cnt->id }}">
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
                        
                        <div class="col-lg-3">
                            <label class="container ml-5">Featured
                                <input hidden="" name="featured" type="checkbox" value="1" @if(!empty($product->featured)) @if($product->featured == 1) checked @endif @endif data-parsley-multiple="featured">
                                <span class="checkmark"></span>
                            </label>
                           
                        </div>

                        <div class="col-lg-3">
                        <label class="container ml-5">Recommended
                                <input hidden="" name="recommended" type="checkbox" value="1" @if(!empty($product->recommended)) @if($product->recommended == 1) checked @endif @endif data-parsley-multiple="featured">
                                <span class="checkmark"></span>
                            </label>
                        </div>

                        <div class="col-md-3 form-group d-none">
                            <label>No of Box</label>
                            <input type="text" name="boxcount" value="@if(!empty($product->boxcount)){{$product->boxcount}}@endif" class="form-control" 
                                data-parsley-required-message="Enter No of Box">
                              
                        </div>
                        
                        </div>
                    </div>
                </div>

                <div class="card mb-2" style="display:none;">
                    <div class="card-body">
                        <div class="row  d-flex justify-content-between align-items-center">
                            <div class="col-md-6 form-group">
                                <label>Meta Title<b class="text-danger"></b></label>
                                <input type="text" name="meta_title" class="form-control jqv-input" 
                                    value="{{ $meta_title }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Meta Keyword<b class="text-danger"></b></label>
                                <input type="text" name="meta_keyword" class="form-control jqv-input"
                                    value="{{ $meta_keyword }}">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Meta Description<b class="text-danger"></b></label>
                                <input type="text" name="meta_description" class="form-control jqv-input" 
                                    value="{{ $meta_description }}">
                            </div>
                            <div class="col-md-6 form-group">
                            </div>
                        </div>
                    </div>
                </div>


                <div class="card mb-2">
                    <div class="card-body">
                        <div class="row  d-flex justify-content-between align-items-center">
                            <div class="col-md-12 form-group">
                                <fieldset id="product-single-variant" class="mt-3" <?php echo ($product_type != 1 ? 'style="display:none;"' : '') ?>>
                                    @include('portal/product/simple_inventory')
                                </fieldset>
                            </div>

                            <input type="hidden" name="mode" id="mode" value="{{$mode}}">
                            <div class="col-md-12 form-group">
                                <fieldset id="product-attribute-wrapper" class="" <?php echo ((empty($attribute_list) || ($mode == 'add') || ($product_type == 1)) ? 'style="display:none;"' : '') ?>>
                                    <!-- <legend>Create Variants</legend> -->
                                    <label>Variants</label>
                                    <div id="product-variant-alert" class="alert alert-warning mb-0" style="display:none;"></div>
                                    <div id="product-attribute-box">
                                        <?php   if ( $mode == 'edit' ): ?>
                                            @include('portal/product/category_attribute_ajax_list')
                                        
                                        <?php endif; ?>
                                    </div>
                                    <div id="product-multi-variant" class="mb-3" <?php echo ($product_type != 2 ? 'style="display:none;"' : '') ?>>
                                        @include('portal/product/product_variant_form')
                                    
                                </div>
                                </fieldset>
                            </div>

                          
                        </div>
                    </div>
                </div>

               

                
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="row  d-flex justify-content-between align-items-center">
                            <div class="col-md-12 form-group other-specs-wrap">
                                <div class="top-bar">
                                <label class="badge bg-light d-flex justify-content-between align-items-center">Other Specs  </label>
                                <button class="btn btn-primary pull-right" type="button"
                                        data-role="add-spec">
                                        <i class="flaticon-plus-1"></i>
                                    </button>
                                <input type="hidden" id="spec_counter" value="{{ count($specs) }}">
                                </div>
                                
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
                                                    <input type="text" name="spec[{{ $i }}][description]"
                                                        placeholder="Unit" class="form-control jqv-input"
                                                        data-jqv-required="true" value="{{ $spec->spec_descp }}">
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
                            <div class="col-md-12 text-center mt-3">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>

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
@stop



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
            $('#cuisines').select2();

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
                '<input type="text" name="spec[' + counter +
                '][description]" placeholder="Unit" class="form-control jqv-input" data-jqv-required="true">' +
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
                            window.location.href = App.siteUrl('/portal/products');
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
                url: '{{ url("portal/products/loadProductAttribute")}}',
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
                    url: "{{ url('portal/products/loadProductVariations')}}",
                    data: formData,
                    dataType: 'json',
                    success: function(data) {
                        $('#product-multi-variant')
                            .show()
                            .html(data['html']);
                    }
                })
               /* $.post(App.siteUrl('admin/products/loadProductVariantAttributes'), formData, function (data) {
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
                    url: '{{ url("portal/products/linkNewAttrForProduct")}}',
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
                        $.post(App.siteUrl('portal/products/unlinkAttrFromProduct'), { 'product_id': product_id, 'attr_val_id': attr_val_id }, function (data) {
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
                        url: '{{ url("portal/products/unlinkAttrFromProduct")}}',
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
                       /* $.post(App.siteUrl('/admin/products/unlinkAttrFromProduct'), { 'product_id': product_id, 'attr_val_id': attr_val_id }, function (data) {
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
                url: '{{ url("portal/products/removeProductImage")}}',
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
        $('.default_attribute_id').attr('checked',false);       
        $(this).attr('checked',true);
        
    })

    $('body').off('change', '[data-role="moda-category-change"]');
        $('body').on('change', '[data-role="moda-category-change"]', function() {
            var $t = $(this);
            var id   = $t.val();
            $sub_cat = $("#moda-sub-cat");
            var html = '<option value="">Select</option>';
            $sub_cat.html(html);
            if ( id != '' ) {
                $.ajax({
                    type: "POST",
                    enctype: 'multipart/form-data',
                    url: "{{url('portal/moda_sub_category_by_category')}}",
                    data: {
                        "id": id,
                        "_token": "{{ csrf_token() }}"
                    },
                    timeout: 600000,
                    dataType: 'json',
                    success: function(res) {
                        for (var i=0; i < res['list'].length; i++) {
                            html += '<option value="'+ res['list'][i]['id'] +'">'+ res['list'][i]['name'] +'</option>';
                            if ( i == res['list'].length-1 ) {
                                $sub_cat.html(html);
                            }
                        }
                    }
                });
            }
        });
        
        $('body').off('keyup','[data-role="regular-price"]');
        $('body').on('keyup','[data-role="regular-price"]',function(){
            let itemHolder = $(this);
            itemHolder.parent().parent().parent().find('[data-role="sale-price"]').val(itemHolder.val());
        });
        $('body').off('change','[data-role="sale-price"]');
$('body').on('change','[data-role="sale-price"]',function(){
    let itemHolder = $(this);
    let reg_price = itemHolder.parent().parent().parent().find('[data-role="regular-price"]').val();
    if(itemHolder.val() > reg_price){
        App.alert("Sale price cannot be greater than regular price");
        itemHolder.val(reg_price);
    }
});
        
    </script>
    {{--
    <script>
        var c;
        var galleryImagesContainer = document.getElementById('galleryImages');
        var imageCropFileInput = document.getElementById('imageCropFileInput');
        var cropperImageInitCanvas = document.getElementById('cropperImg');
        var cropImageButton = document.getElementById('cropImageBtn');
        var var_img_counter = '';
        
        $('.imageCropFileInput').on("change", function (e) {
            var_img_counter = $(this).data('key');
            galleryImagesContainer = document.getElementById('galleryImages'+var_img_counter);
            imageCropFileInput = document.getElementById('imageCropFileInput'+var_img_counter);
            cropperImageInitCanvas = document.getElementById('cropperImg'+var_img_counter);
            cropImageButton = document.getElementById('cropImageBtn'+var_img_counter);
        console.log('cropImageBtn '+var_img_counter,e);
            imagesPreview(e.target);

        });
        $('.cropImageBtn').on("click", function (e) {
            // console.log('sadsad');
            image_crop();
        });

        // cropImageButton.addEventListener("click", function(){
        //     image_crop();
        //   });
        // Crop Function On change
          function imagesPreview(input) {
        // console.log(var_img_counter,'imageCropFileInput'+var_img_counter,imageCropFileInput);
            var cropper;
            galleryImagesContainer.innerHTML = '';
            var img = [];
            if(cropperImageInitCanvas.cropper){
              cropperImageInitCanvas.cropper.destroy();
              cropImageButton.style.display = 'none';
              cropperImageInitCanvas.width = 700;
              cropperImageInitCanvas.height = 0;
            }

            if (input.files.length) {
                $('#image_counter'+var_img_counter).val(input.files.length);
              var i = 0;
              var index = 0;
              for (let singleFile of input.files) {
                var reader = new FileReader();
                reader.onload = function(event) {
                  var blobUrl = event.target.result;
                  img.push(new Image());
                  img[i].onload = function(e) {
                    // Canvas Container
                    var singleCanvasImageContainer = document.createElement('div');
                    singleCanvasImageContainer.id = 'singleImageCanvasContainer'+index;
                    singleCanvasImageContainer.className = 'singleImageCanvasContainer';
                    // Canvas Close Btn
                    var singleCanvasImageCloseBtn = document.createElement('button');
                    // var singleCanvasImageCloseBtnText = document.createElement('');
                    var singleCanvasImageCloseBtnText = document.createElement('i');
                    singleCanvasImageCloseBtnText.className = 'bx bx-trash';
                    singleCanvasImageCloseBtn.id = 'singleImageCanvasCloseBtn'+index;
                    singleCanvasImageCloseBtn.className = 'singleImageCanvasCloseBtn';
                    singleCanvasImageCloseBtn.onclick = function() { removeSingleCanvas(this) };
                    singleCanvasImageCloseBtn.appendChild(singleCanvasImageCloseBtnText);
                    singleCanvasImageContainer.appendChild(singleCanvasImageCloseBtn);
                    // Image Canvas
                    var canvas = document.createElement('canvas');
                    canvas.id = 'imageCanvas'+index+var_img_counter;
                    canvas.className = 'imageCanvas singleImageCanvas'+var_img_counter;
                    canvas.width = e.currentTarget.width;
                    canvas.height = e.currentTarget.height;
                    canvas.onclick = function() { cropInit(canvas.id); };
                    singleCanvasImageContainer.appendChild(canvas)
                    // Canvas Context
                    var ctx = canvas.getContext('2d');
                    ctx.drawImage(e.currentTarget,0,0);
                    // galleryImagesContainer.append(canvas);
                    galleryImagesContainer.appendChild(singleCanvasImageContainer);
                    while (document.querySelectorAll('.singleImageCanvas'+var_img_counter).length == input.files.length) {
                      var allCanvasImages = document.querySelectorAll('.singleImageCanvas'+var_img_counter)[0].getAttribute('id');
                      cropInit(allCanvasImages);
                      break;
                    };
                    console.log('input.files.length',input.files.length,allCanvasImages);
                    urlConversion();
                    index++;
                  };
                  img[i].src = blobUrl;
                  i++;
                }
                reader.readAsDataURL(singleFile);
              }
              // addCropButton();
              // cropImageButton.style.display = 'block';
            }
          }
          // imageCropFileInput.addEventListener("change", function(event){
          //   imagesPreview(event.target);
          // });
        // Initialize Cropper
          function cropInit(selector) {
            c = document.getElementById(selector);
            console.log(document.getElementById(selector));
            if(cropperImageInitCanvas.cropper){
                cropperImageInitCanvas.cropper.destroy();
            }
            var allCloseButtons = document.querySelectorAll('.singleImageCanvasCloseBtn');
            for (let element of allCloseButtons) {
              element.style.display = 'block';
            }
            c.previousSibling.style.display = 'none';
            // c.id = croppedImg;
            var ctx=c.getContext('2d');
            var imgData=ctx.getImageData(0, 0, c.width, c.height);
            var image = cropperImageInitCanvas;
            image.width = c.width;
            image.height = c.height;
            var ctx = image.getContext('2d');
            ctx.putImageData(imgData,0,0);
            cropper = new Cropper(image, {
              aspectRatio: 1 / 1,
              preview: '.img-preview',
              data:{ //define cropbox size
                  width: 700,
                  height:  700,
                },
              crop: function(event) {
                cropImageButton.style.display = 'block';
              }
            });
        
          }
        // Initialize Cropper on CLick On Image
          // function cropInitOnClick(selector) {
          //   if(cropperImageInitCanvas.cropper){
          //       cropperImageInitCanvas.cropper.destroy();
          //       // cropImageButton.style.display = 'none';
          //       cropInit(selector);
          //       // addCropButton();
          //       // cropImageButton.style.display = 'block';
          //   } else {
          //       cropInit(selector);
          //       // addCropButton();
          //       // cropImageButton.style.display = 'block';
          //   }
          // }
        // Crop Image
          function image_crop() {
            if(cropperImageInitCanvas.cropper){
              var cropcanvas = cropperImageInitCanvas.cropper.getCroppedCanvas({width: 700, height: 700});
              // document.getElementById('cropImages').appendChild(cropcanvas);
              var ctx=cropcanvas.getContext('2d');
                var imgData=ctx.getImageData(0, 0, cropcanvas.width, cropcanvas.height);
                // var image = document.getElementById(c);
                c.width = cropcanvas.width;
                c.height = cropcanvas.height;
                var ctx = c.getContext('2d');
                ctx.putImageData(imgData,0,0);
                cropperImageInitCanvas.cropper.destroy();
                cropperImageInitCanvas.width = 0;
                cropperImageInitCanvas.height = 0;
                cropImageButton.style.display = 'none';
                var allCloseButtons = document.querySelectorAll('.singleImageCanvasCloseBtn');
                for (let element of allCloseButtons) {
                  element.style.display = 'block';
                }
                urlConversion();
                // cropperImageInitCanvas.style.display = 'none';
            } else {
              alert('Please select any Image you want to crop');
            }
          }
          // cropImageButton.addEventListener("click", function(){
          //   image_crop();
          // });
        // Image Close/Remove
          function removeSingleCanvas(selector) {
            selector.parentNode.remove();
            urlConversion();
          }
        // Dynamically Add Crop Btn
          // function addCropButton() {
          //   // add crop button
          //     var cropBtn = document.createElement('button');
          //     cropBtn.setAttribute('type', 'button');
          //     cropBtn.id = 'cropImageBtn';
          //     cropBtn.className = 'btn btn-block crop-button';
          //     var cropBtntext = document.createTextNode('crop');
          //     cropBtn.appendChild(cropBtntext);
          //     document.getElementById('cropper').appendChild(cropBtn);
          //     cropBtn.onclick = function() { image_crop(cropBtn.id); };
          // }
        // Get Converted Url
          function urlConversion() {
            var allImageCanvas = document.querySelectorAll('.singleImageCanvas'+var_img_counter);
            var convertedUrl = '';
            for (let element of allImageCanvas) {
              convertedUrl += element.toDataURL('image/jpeg');
              convertedUrl += 'img_url';
            }
            console.log(var_img_counter);
            document.getElementById('croped_image'+var_img_counter).value = convertedUrl;

            // let activeImage_gallary = $("#croped_image").val();
            // activeImage_gallary = activeImage_gallary + ' -> ' + convertedUrl;
            // $("#croped_image").val(activeImage_gallary);
            // removeimage();

          }
        function removeimage() {
            var input = document.getElementById("croped_image");
            if(input){
                console .log(input)
                input = (input).split("->");
                let all_files = [];
                for (let file of input) {
                    if (file && file.includes(input)) {
                        all_files.push(input);
                    }
                }
               all_files = all_files.join('->');
               $("#croped_image").val(all_files);
            }
        }
        
        
        

    </script>
    --}}
@stop
