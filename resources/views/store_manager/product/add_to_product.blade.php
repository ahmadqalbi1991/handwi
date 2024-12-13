@extends("vendor.template.layout")

@section('header')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.js"></script>
@stop


@section('content')
    <div class="card">
      
        <div class="card-body">
            <form method="post" action="{{ url('/admin/product/req_to_prd') }}" id="admin-form" enctype="multipart/form-data">
                <input type="hidden" name="id" value="">
                <input type="hidden" name="req_id" value="{{$id}}">
                @csrf
                <div class="row  d-flex justify-content-between align-items-center">

                    <div class="col-md-6 form-group">
                        <label>Category<b class="text-danger">*</b></label>
                        <select data-url="{{url('admin/sellers_by_categories')}}" class="form-control jqv-input product_cat select2" data-jqv-required="true"
                            name="category_ids[]" data-role="select2" data-placeholder="Select Categories"
                            data-allow-clear="true" multiple="multiple">
                            <?php if(isset($category_list) && count($category_list) > 0): ?>

                            <?php foreach($category_list as $parent_cat_id => $parent_cat_name): ?>
                            <?php
                            // When trying to edit a category, DON'T allow to select itself as the parent category.
                            //if ($id > 0 && $id == $parent_cat_id) continue;
                            ?>
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


                            <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6 form-group"></div>

                    <div class="col-md-6 form-group">
                        <label>Product Name<b class="text-danger">*</b></label>
                        <input type="text" name="product_name" class="form-control jqv-input" data-jqv-required="true"
                            value="{{ $name }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Seller<b class="text-danger">*</b></label>
                        <select class="form-control jqv-input select2 slrs" name="seller_id" data-jqv-required="true">
                            <option value="">Select Seller</option>
                            @foreach ($sellers as $sel)
                                <option value="{{$sel->id }}" @if ($sel->id == $seller_user_id) selected @endif>{{ $sel->business_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-12 form-group">
                        <label>Description<b class="text-danger">*</b></label>
                        <textarea class="form-control jqv-input" data-jqv-required="true" rows="4"
                            name="description">{{ $description }}</textarea>
                    </div>
                    @if ($image_path != '')
                    <div  class="col-md-12 form-group" >
                        <img id="image-preview" style="width:100px; height:100px;" class="img-responsive mb-2"  data-image="{{'https://d3yyal9qow7g9.cloudfront.net/'.config('global.product_image_upload_dir').$image_path}}" src="{{'https://d3yyal9qow7g9.cloudfront.net/'.config('global.product_image_upload_dir').$image_path}}">
                      </div>
                      @endif
                      <input type="hidden" id="cropped_upload_image" name="cropped_upload_image">
                      <input type="hidden" value="{{$image_path}}" name="image_path">
                    <div class="col-md-6 form-group">
                        <label>Upload Product Image @if ($image_path != '') <a target="blank" href="{{'https://d3yyal9qow7g9.cloudfront.net/'.config('global.product_image_upload_dir').$image_path}}"><i class="flaticon-eye"></i></a>@else<b class="text-danger">*</b> @endif</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input jqv-input crop_image" onclick="this.value=null;" name="product_image"
                                data-role="file-image"  data-previews="image-preview" 
                                <?= $id == '' ? 'data-jqv-required="true"' : '' ?> name="upload_image" id="product-image">
                            <label class="custom-file-label" for="customFile">Choose file</label>
                        </div>
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
                                                value="{{ $spec->title }}" class="form-control jqv-input"
                                                data-jqv-required="true">
                                        </div>
                                        <div class="col-md-5 form-group">
                                            <textarea name="spec[{{ $i }}][description]"
                                                placeholder="Description" class="form-control jqv-input"
                                                data-jqv-required="true">{{ $spec->description }}</textarea>
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

                    <div class="col-md-12 mt-3">
                        <h4 class="d-flex justify-content-between">Specification Documents </h4>
                        <div class="table-responsive">
                            <table class="table table-condensedd table-stripedss">
                                <thead>
                                    <tr>
                                    <th>#</th>
                                    <th width="40%">Title</th>
                                    <th>Document</th>
                                    <th width="15%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i=0; ?>
                                    @foreach($docs as $doc)
                                        <?php $i++ ?>
                                        <tr>
                                            <td>{{$i}}</td>
                                            <td>{{$doc->title}}</td>
                                            <td><a href="{{'https://d3yyal9qow7g9.cloudfront.net/'.config('global.product_image_upload_dir').$doc->doc_path}}" target="_blank" rel="noopener noreferrer">View Document</a></td>
                                            
                                            <td>
                                                <div class="dropdown custom-dropdown">
                                                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                        <i class="flaticon-dot-three"></i>
                                                    </a>
                    
                                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                                        <a class="dropdown-item" data-role="unlink" data-message="Do you want to remove this document?" href="{{url('admin/products/delete_prd_req_doc/'.$doc->id)}}"><i class="flaticon-delete-1"></i> Delete</a>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                    </div>
                    </div>
                    <div class="col-md-12 mt-3">
                        <h5 class="d-flex justify-content-between">Upload Documents <button class="btn btn-primary pull-right" type="button" data-role="add_more_button"><i class="flaticon-plus-1"></i></button></h5>
                        <hr>
                        <input type="hidden" value="0" id="button_counter" name="button_counter">
                        <div data-role="doc-holder">
                         
                         </div>
                    </div>
                    <div class="col-md-12 text-center mt-3">
                        <button type="submit" class="btn btn-primary">Add To Product</button>
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
                      <img id="image_crop_section" src="https://avatars0.githubusercontent.com/u/3456749">
                   </div>
                   {{-- <div class="col-md-4">
                      <div class="crop_image_preview_section"></div>
                   </div> --}}
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
        App.initFormView();
        $(document).ready(function() {
            $('.select2').select2();
        });
        $('body').on("click", '[data-role="remove-spec"]', function() {
            $(this).parent().parent().remove();
        });

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
            var validation = $.Deferred();
            var $form = $(this);
            var formData = new FormData(this);

            $form.validate({
                rules: {},
                errorElement: 'div',
                errorPlacement: function(error, element) {
                    //element.addClass('is-invalid');
                    error.addClass('invalid-feedback');
                    error.insertAfter(element);
                }
            });

            // Bind extra rules. This must be called after .validate()
            App.setJQueryValidationRules();

            if ($form.valid()) {
                validation.resolve();
            } else {
                var error = $form.find('.is-invalid').eq(0);
                $('html, body').animate({
                    scrollTop: (error.offset().top - 100),
                }, 500);
                validation.reject();
            }

            validation.done(function() {
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('div.invalid-feedback').remove();

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
                                        if(e_field == "category_ids"){
                                            e_field = "category_ids[]";
                                        }
                                        $('[name="' + e_field + '"]').eq(0).addClass(
                                            'is-invalid');
                                        $('<div class="invalid-feedback">' + e_message +
                                            '</div>').insertAfter($('[name="' +
                                            e_field + '"]').eq(0));
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
                                window.location.href = App.siteUrl(
                                    '/admin/products_requests');
                            }, 1500);
                        }

                        $form.find('button[type="submit"]')
                            .text('Add To Product')
                            .attr('disabled', false);
                    },
                    error: function(e) {
                        App.loading(false);
                        $form.find('button[type="submit"]')
                            .text('Add To Product')
                            .attr('disabled', false);
                        App.alert(e.responseText, 'Oops!');
                    }
                });
            });
        });
        // $(".product_cat").change(function(){
        //     $(".slrs").attr('disabled','');
        //     _cat = $(this).val();
        //     html = '<option value="">Select Seller</option>';
        //     $(".slrs").html(html);
        //     $.ajax({
        //         type: "POST",
        //         enctype: 'multipart/form-data',
        //         url: $(this).data('url'),
        //         data: {
        //             "id" :$(this).data('id'),
        //             'cat': _cat,
        //             "_token": "{{ csrf_token() }}"
        //         },
        //         dataType: 'json',
        //         success: function(res) {
        //             for (var i=0; i < res['data'].length; i++) {
        //                 html += '<option value="'+ res['data'][i]['id'] +'">'+ res['data'][i]['business_name'] +'</option>';
        //             }
        //             $(".slrs").html(html);
        //             $(".slrs").removeAttr('disabled');
        //             $(".slrs").change();
        //         },
        //         error: function(e) {
        //             App.alert(e.responseText, 'Oops!');
        //         }
        //     });
        // })

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

    </script>
@stop
