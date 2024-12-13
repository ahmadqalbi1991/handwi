<legend>Price, Inventory &amp; Images</legend>
<hr>
<div class="form-row">
    <div class="col-lg-3">
        <div class="form-group profile-form">
            <label>Regular Price <span class="text-danger">*</span></label>
            <input type="text" name="regular_price" oninput="validateNumber(this);" id="regular_price" value="{{$regular_price}}" class="form-control" data-role="regular-price" {{ $readonly}} />
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group profile-form">
            <label>Sale Price <span class="text-danger">*</span></label>
            <input type="text" name="sale_price" oninput="validateNumber(this);"  id="sale_price" value="{{ $sale_price}}" class="form-control" data-role="sale-price" {{ $readonly}} />
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group profile-form">
            <label>Stock Quantity <span class="text-danger">*</span></label>
            <input type="number" name="stock_quantity" value="{{ $stock_quantity }}" class="form-control" data-role="stock_quantity"  {{ $readonly}} />
        </div>
    </div>

    <div class="col-lg-3">
        <div class="form-group profile-form">
            <label>SKU <span class="text-danger">*</span></label>
            <input type="text" name="product_code" value="{{ $pr_code }}" class="form-control" data-role="product_code"  {{ $readonly}} />
        </div>
    </div>

    <div class="col-lg-3">
        <div class="form-group profile-form">
            <label>Weight (kg)</label>
            <input type="text" name="weight" value="{{empty($product->weight) ? '': $product->weight}}" class="form-control" data-role="weight"/>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group profile-form">
            <label>Length (cm)</label>
            <input type="text" name="length" value="{{empty($product->length) ? '': $product->length}}" class="form-control" data-role="length"  />
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group profile-form">
            <label>Width (cm) </label>
            <input type="text" name="width" value="{{empty($product->width) ? '': $product->width}}" class="form-control" data-role="width" />
        </div>
    </div>
    <div class="col-lg-3">
        <div class="form-group profile-form">
            <label>Height (cm)</label>
            <input type="text" name="height" value="{{empty($product->height) ? '': $product->height}}" class="form-control" data-role="height"/>
        </div>
    </div>

    <div class="col-lg-3 d-none">
        <div class="form-group profile-form">
            <label>Allow Back Orders</label>
            <div class="form-check">
                <label class="form-check-label">
                    <input class="product_simple_allow_backorder" type="checkbox" name="product_variant_allow_backorder[<?=$input_index?>]" value="1" <?php echo ($t_variant_allow_backorder == 1 ? 'checked': '') ?> <?php echo ($readonly ? 'disabled': '') ?> /> Yes
                </label>
            </div>
            
        </div>
    </div>
    <div class="col-lg-3 d-none">
        <div class="form-group profile-form">
            <label>Bar code <span class="text-danger"></span></label>
            <input type="text" name="bar_code" value="{{$bar_code}}" class="form-control" data-role="stock_quantity" {{ $readonly}} />
        </div>
    </div>
    
    
    <div class="col-lg-6">
        <div class="form-group profile-form">
            <label>Short Description <span class="text-danger"></span></label>
            <textarea name="product_desc" class="form-control" data-role="product_desc" {{ $readonly }} />{{$product_desc}}</textarea>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-group profile-form">
            <label>Full Description <span class="text-danger"></span></label>
            <textarea type="text" name="product_full_descr"  class="form-control" data-role="product_full_descr"  {{ $readonly }} />{{$product_desc_full}}</textarea>
        </div>
    </div>

     <div class="form-row mt-3">
        <div class="col-lg-12">
            <div class="upload-product-img">
                <label for="" class="">Upload Images (Maximum 5 images)</label>
                <div id="product-simple-images" class="upload-img-product-items" data-variant-id="<?php echo $default_attribute_id ?>">
                    <?php if (! empty($product_simple_image) ): ?>
                        <?php foreach ($product_simple_image as $t_name): ?>
                            <?php
                            if ( !empty($t_name) && file_exists(FCPATH . "uploads/products/{$t_name}") ) {
                                $t_img = base_url("uploads/products/{$t_name}");
                            } else {
                                $t_img = base_url('assets/images/placeholder.png');
                            }
                            ?>
                            <div class="uploaded-prev-imd">
                                <img src="<?php echo $t_img ?>" alt="" />
                                <div class="del-product-img" data-role="product-img-trash"  data-image-file="<?php echo $t_name ?>" <?php echo ($readonly ? 'data-disabled="1"' : '') ?>><i class="far fa-trash-alt"></i> Delete</div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                     
                        @if(!empty($product->image)) 
                        <?php  
                        $imageList = explode(",",$product->image); ?>
                            @if(!empty($imageList)) 
                                @foreach ($imageList as $key => $value) 
                                    <img src="{{url(config('global.upload_path') . '/' . config('global.product_image_upload_dir').$value)}}" width="100" height="100">
                                    <div class="del-product-img" data-role="product-img-trash"  data-image-file="{{$value}}" <?php echo ($readonly ? 'data-disabled="1"' : '') ?>><i class="far fa-trash-alt"></i> Delete</div>
                                @endforeach
                            @endif
                        @endif
                    
                    <div class="uploaded-prev-imd" <?php echo ($readonly ? 'style="display:none;"' : '') ?>>

                        <div class="image_wrap">
                            <label class="Pic_upload">
                                <input counter="0" type="file" name="product_simple_image[]" class="upload_pro" data-role="product-img-upload" multiple />
                                <i class="ti-plus"></i>
                            </label>
                        </div>
                    </div>
                </div>
                <small class="text-info">
                    Maximum size should be 2MB. Maximum dimension allowed is 1024 x 1024.<br> Allowed types are jpg, jpeg, png and gif.
                </small>
            </div>
        </div>
    </div>
    <input type="hidden" name="image_counter" value="0" id="image_counter">
</div>