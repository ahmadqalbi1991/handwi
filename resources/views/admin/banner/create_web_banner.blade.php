@extends("admin.template.layout")

@section('header')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.6/cropper.js"></script>
@stop


@section('content')
    <div class="card mb-5">
      
        <div class="card-body">
            <form method="post" action="{{ url('/admin/web_banner/create') }}" id="admin-form" enctype="multipart/form-data">
                @csrf
                <div class="row  d-flex justify-content-between align-items-center">
                    <div class="col-md-6 form-group">
                        <label>Title 1</label>
                        <input type="text" name="banner_title_1" class="form-control jqv-input">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Title 2</label>
                        <input type="text" name="banner_title_2" class="form-control jqv-input">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Title 3</label>
                        <input type="text" name="banner_title_3" class="form-control jqv-input">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Title 4</label>
                        <input type="text" name="banner_title_4" class="form-control jqv-input">
                    </div>

                    <div class="col-md-6 form-group">
                        <label>Status</label>
                        <select name="active" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group"></div>
                    <div class="col-md-12 form-group" >
                        <img id="image-preview" style="width:300px; height:168px;" class="img-responsive mb-1">
                    </div>
                    <input type="hidden" id="cropped_upload_image" name="cropped_upload_image">
                    <div class="col-md-6 form-group">
                        <label>Upload Banner<b class="text-danger">*</b></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input jqv-input crop_image" onclick="this.value=null;" data-cropped-image-preview="image-preview" data-aspect-ratio="1.78" data-cropped-image-input="cropped_upload_image"
                                data-role="file-image" data-jqv-required="true" name="banner" id="banner">
                            <label class="custom-file-label" for="customFile">Choose file</label>
                        </div>
                        <small class="text-muted">
                            Upload Image With Dimension 1920X1079
                        </small>
                    </div>
                    
                 
                    <div class="col-md-12 text-center mt-3">
                        <button type="submit" class="btn btn-primary">Submit</button>
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
                 <input type="hidden" id="as_ra">
                 <input type="hidden" id="cropped_image_input">
                 <input type="hidden" id="cropped_image_preview">
                 <div class="img-container">
                    <div class="row">
                       <div class="col-md-8">
                          <img id="image_crop_section" src="https://avatars0.githubusercontent.com/u/3456749">
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
    <script>
        App.initFormView();
        
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
                            if (typeof res['errors'] !== 'undefined' && res['errors']) {
                                var error_def = $.Deferred();
                                var error_index = 0;
                                jQuery.each(res['errors'], function(e_field, e_message) {
                                    if (e_message != '') {
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
                                    '/admin/web_banners');
                            }, 1500);
                        }

                        $form.find('button[type="submit"]')
                            .text('Submit')
                            .attr('disabled', false);
                    },
                    error: function(e) {
                        App.loading(false);
                        $form.find('button[type="submit"]')
                            .text('Submit')
                            .attr('disabled', false);
                        App.alert(e.responseText, 'Oops!');
                    }
                });
            });
        });
        var $modal = $('#crop_modal');
         var image = document.getElementById('image_crop_section');
         var cropper;
         $("body").on("change", ".crop_image", function (e) {
            var files = e.target.files;
           $("#as_ra").val($(this).attr("data-aspect-ratio"));
           $("#cropped_image_input").val($(this).attr("data-cropped-image-input"));
           $("#cropped_image_preview").val($(this).attr("data-cropped-image-preview"));
   
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
           as_ra=$("#as_ra").val();
           
          
     cropper = new Cropper(image, {
       aspectRatio: as_ra,
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
       //   cropper.setCanvasData(canvasData);
       }
     })
   
   
         }).on('hidden.bs.modal', function () {
            cropper.destroy();
            cropper = null;
         });
         $("#crop").click(function () {
           cropped_image_input=$("#cropped_image_input").val();
           cropped_image_preview=$("#cropped_image_preview").val();
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
                  $("#"+cropped_image_input).val(base64data);
                  $("#"+cropped_image_preview).attr('src',base64data);
                  $modal.modal('hide');
               }
            });
         })
    </script>
@stop
