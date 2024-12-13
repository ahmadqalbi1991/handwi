@extends("admin.template.layout")

@section('header')
<!-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> -->
    <link href="{{asset('/formstone/css/upload.css')}}"></link>
    <style type="text/css">
.fs-upload.fs-light .fs-upload-target{background:#fff;border:3px dashed #607d8b;border-radius:2px;color:#455a64;font-size:14px;margin:0;padding:25px;text-align:center;-webkit-transition:background .15s linear,border .15s linear,color .15s linear,opacity .15s linear;transition:background .15s linear,border .15s linear,color .15s linear,opacity .15s linear}
.filelists {
    margin: 20px 0;
}

.filelists h5 {
    margin: 10px 0 0;
}

.filelists .start_all {
    background: #455a64;
    border-radius: 2px;
    color: #fff;
    cursor: pointer;
    clear: both;
    display: inline-block;
    font-size: 10px;
    margin: 0 10px 0 0;
    padding: 8px 12px;
    text-transform: uppercase;
}

.filelists .cancel_all {
    color: red;
    cursor: pointer;
    clear: both;
    display: inline-block;
    font-size: 10px;
    margin: 0;
    text-transform: uppercase;
}

.filelist {
    margin: 0;
    padding: 10px 0;
}

.filelist li {
    background: #fff;
    border-bottom: 1px solid #ECEFF1;
    font-size: 14px;
    list-style: none;
    padding: 5px;
    position: relative;
}

.filelist li:before {
    display: none !important;
}
/* main site demos */

.filelist li .bar {
    background: #eceff1;
    content: '';
    height: 100%;
    left: 0;
    position: absolute;
    top: 0;
    width: 0;
    z-index: 0;
    -webkit-transition: width 0.1s linear;
    transition: width 0.1s linear;
}

.filelist li .f-content {
    display: block;
    overflow: hidden;
    position: relative;
    z-index: 1;
}

.filelist li .file {
    color: #455A64;
    float: left;
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 50%;
    white-space: nowrap;
}

.filelist li .progress {
    color: #B0BEC5;
    display: block;
    float: right;
    font-size: 10px;
    text-transform: uppercase;
}

.filelist li .cancel {
    color: red;
    cursor: pointer;
    display: block;
    float: right;
    font-size: 10px;
    margin: 0 0 0 10px;
    text-transform: uppercase;
}
/* .filelist.started li .cancel { display: block; } */
/* .filelist li .remove { color: red; cursor: pointer; display: block; float: right; font-size: 10px; margin: 0 0 0 10px; text-transform: uppercase; }
.filelist.started li .remove { display: none; } */

.filelist li.error .file {
    color: red;
}

.filelist li.error .progress {
    color: red;
}

.filelist li.error .cancel {
    display: none;
}
.fs-upload-input{
  opacity: 0;
}
</style>
@stop


@section('content')
    <div class="card mb-5">
         @if($message = Session::get('success'))
   <div class="alert alert-success alert-block">
    <button type="button" class="close" data-dismiss="alert">×</button>
           <strong>{{ $message }}</strong>
   </div>
   @endif
   @if($message = Session::get('error'))
   <div class="alert alert-danger alert-block">
    <button type="button" class="close" data-dismiss="alert">×</button>
           <strong>{{ $message }}</strong>
   </div>
   @endif





   <div class="row" style="padding: 20px;">
 <div class="col-md-6">
  
     <div class="row">
       <div class="col-md-8">
         <h4 class="mb-4">Import Products</h4>
       </div>
       <div class="col-md-4 text-right">
         <a href="{{url('admin/product/download_format')}}" class="btn btn-dark btn-sm"><i class="fa fa-download" aria-hidden="true"></i>&nbsp; Blank Excel</a>
       </div>
     </div>
     <div id="import-alert" class="alert alert-danger" style="display: none;"></div>
     <div class="progress mt-4" id="upload-progress" style="height: 15px; display: none;">
               <div class="progress-bar bg-info" role="progressbar" aria-valuenow="0"
               aria-valuemin="0" aria-valuemax="100" style="min-width:0%;width:0%">
                   0%
               </div>
           </div>
     <form action="#" method="post" class="form pt-5">
       <div id="xls-upload" data-upload-options='{"action":"<?=url('admin/Excel/upload_file')?>"}'>
       </div>
   </form>
   <div class="text-center pt-5">
     <a id="start-import" href="#" class="btn btn-primary" style="display: none;"><i class="fa fa-play" aria-hidden="true"></i>&nbsp; Start Import</a>

     <div id="import-status" class="mt-5" style="display: none;">
       <div class="progress" id="import-progress" style="height: 15px;">
                   <div class="progress-bar bg-success" role="progressbar" aria-valuenow="0"
                   aria-valuemin="0" aria-valuemax="100" style="min-width:0%;width:0%">
                       0%
                   </div>
               </div>
               <div class="row mt-5">
           <div class="col-md-6">
             <div class="card">
               <div class="card-body">
                 <h5 class="card-title"><i class="fa fa-check text-success" aria-hidden="true"></i> Success</h5>
                 <p class="card-text" id="success-count">0</p>
               </div>
             </div>
           </div>
           <div class="col-md-6">
             <div class="card">
               <div class="card-body">
                 <h5 class="card-title"><i class="fa fa-close text-danger" aria-hidden="true"></i> Skipped</h5>
                 <p class="card-text" id="failed-count">0</p>
                 <a href="#" id="download-failed-xls" class="btn btn-sm btn-secondary" style="display: none;">Download</a>
               </div>
             </div>
           </div>
         </div>
       </div>
   </div>
 

 <!-- Upload image zip file -->
 
     <div class="row">
       <div class="col-md-8">
         <h4 class="mb-4">Upload Images</h4>
       </div>
     </div>
     <div id="image-alert" class="alert alert-danger" style="display: none;"></div>
     <div class="progress mt-4" id="image-progress" style="height: 15px; display: none;">
               <div class="progress-bar bg-info" role="progressbar" aria-valuenow="0"
               aria-valuemin="0" aria-valuemax="100" style="min-width:0%;width:0%">
                   0%
               </div>
           </div>
     <form action="#" method="post" class="form pt-5">
       <div id="zip-upload" data-upload-options='{"action":"<?=url('admin/Excel/upload_zip_file')?>"}'></div>
   </form>
   <div class="text-center pt-5">
     <a id="start-unzip" href="<?=url('admin/Excel/startUnzipImage')?>" class="btn btn-primary" style="display: none;"><i class="fa fa-play" aria-hidden="true"></i>&nbsp; Unzip</a>
   </div>
   
</div>
 <div class="col-md-6">
   
   <h4 class="mb-4">Export Products</h4>
   <form id="exportForm" method="get" action="{{route('admin.product.export')}}">
     <div class="form-group">
         <label>Search</label>
         <input type="text" class="form-control" name="product_name" placeholder="Search product" />
       </div>
       <div class="form-group">
         <label>Category</label>
          <select id="category-select" data-url="{{url('admin/sellers_by_categories')}}" class="form-control jqv-input product_catd select2" data-jqv-required="true"
                            name="category[]" data-role="select2" data-placeholder="Select Categories"
                            data-allow-clear="true" multiple="multiple" >
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
       <div class="form-row">
         <div class="form-group col-md-6">
           <label>From Date</label>
           <input type="text" id="datefrom" name="date_from" class="form-control" autocomplete="off" readonly="readonly" placeholder="From Date" />
         </div>
         <div class="form-group col-md-6">
           <label>End Date</label>
           <input type="text" id="dateto" name="date_to" class="form-control" autocomplete="off" readonly="readonly" placeholder="To Date" />
         </div>
     </div>
     <div class="form-row">
         <div class="form-group col-md-6">
           <label>Price From</label>
           <input type="text" class="form-control" name="price_from" placeholder="Enter price" />
         </div>
         <div class="form-group col-md-6">
           <label>Price To</label>
           <input type="text" class="form-control" name="price_to" placeholder="Enter price" />
         </div>
     </div>
     <div class="form-group" style="display: none;">
       <div class="custom-control custom-checkbox">
         <input type="checkbox" class="custom-control-input" id="customCheck1" name="out_of_stock" value="1"/>
         <label class="custom-control-label" for="customCheck1">Export <strong><em>Nearing out of stock</em></strong> products only</label>
       </div>
     </div>
     <button type="submit" class="btn btn-primary mt-4"> Export</button>
   </form>
 
</div>
</div>
</div>
@stop
@section('script')
<!-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> -->
<script type="text/javascript" src="{{asset('/')}}formstone/js/modernizr.js"></script>
<script type="text/javascript" src="{{asset('/')}}formstone/js/core.js"></script>
<script type="text/javascript" src="{{asset('/')}}formstone/js/upload.js"></script>
<script type="text/javascript">
(function($) {



	$(function() {
        
        $('body').off('change', '[name="c_id"],[name="company_id"]');
        $('body').on('change', '[name="c_id"],[name="company_id"]', function() {
            var $t = $(this);
            var $store = $('#'+ $t.data('input-store'));
            var not_in = $(this).attr("data-not-in")||'';


            if ( $store.length > 0 ) {
                var id   = $t.val();
                var html = '<option value="">Select</option>';

                $store.html(html);

                if ( id != '' ) {
                    $.post(App.siteUrl('store/filter'), {
                        'company_id': id,
                        'not_in'    : not_in
                    }, function(res) {
                        for (var i=0; i < res['stores'].length; i++) {
                            html += '<option data-service-id="'+res['stores'][i]['service_id']+'" value="'+ res['stores'][i]['id'] +'">'+ res['stores'][i]['name'] +'</option>';
                            if ( i == res['stores'].length-1 ) {
                                $store.html(html);
                            }
                        }
                    });
                }
            }
        });
        
        $("#datefrom").flatpickr({enableTime: false,dateFormat: "Y-m-d"});
        $("#dateto").flatpickr({enableTime: false,dateFormat: "Y-m-d"});
        
        $('#category-select').select2();

        $('body').off('change', '[name="is_blank"]');
        $('body').on('change', '[name="is_blank"]', function(e) {
            e.preventDefault();
            if ( this.value == 1 ) {
                $('[role="extra-search-box"]').hide();
                $('[role="extra-search-box"]').find('select').select2('destroy');
            } else {
                $('[role="extra-search-box"]').show();
                $('[role="extra-search-box"]').find('select').select2();
            }
        });

        $('body').off('change', '[name="include_shared_items"]');
        $('body').on('change', '[name="include_shared_items"]', function(e) {
            e.preventDefault();
            if ( this.value == 1 ) {
                $('[role="field-store-id"]').hide();
            } else {
                $('[role="field-store-id"]').show();
            }
        });

        $('body').off('change', '#zip-store-id');
        $('body').on('change', '#zip-store-id', function(e) {
            e.preventDefault();

            if ( this.value != '' ) {
                $('[role="zip-upload-box"]').show();
            } else {
                $('[role="zip-upload-box"]').hide();
            }
        });

        // Saving form
        var form_in_progress = 0;
        $('body').off('submit', '#product-export-sample-xls');
        $('body').on('submit', '#product-export-sample-xls', function(e) {
            e.preventDefault();
            triggerFormSubmit($(this)).done(function() {
                // console.log('completed');
            });
        });

        function triggerFormSubmit($this, noRedirect) {
            var validation = $.Deferred();
            var $form = $this;
            var formData = new FormData($this[0]);
            var action = $.Deferred();
            var noRedirect = typeof noRedirect !== 'undefined' ? noRedirect : false;

            if ( form_in_progress ) return;

            $form.validate({
                rules: {},
                errorElement: 'div',
                errorPlacement: function(error, element) {
                    element.addClass('is-invalid');
                    error.addClass('invalid-feedback');
                    error.insertAfter(element);
                }
            });

            // Bind extra rules. This must be called after .validate()
            // App.setJQueryValidationRules();

            if ( $form.valid() ) {
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
                form_in_progress = 1;
                $form.find('button[type="submit"]').attr('disabled', true);

                $.ajax({
                    type: "POST",
                    enctype: 'multipart/form-data',
                    url: $form.attr('action'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    cache: false,
                    timeout: 600000,
                    success: function (res) {
                        form_in_progress = 0;
                        App.loading(false);

                        if ( res['status'] == 0 ) {
                            action.reject();
                            if ( typeof res['errors'] !== 'undefined' ) {
                                var error_def = $.Deferred();
                                var error_index = 0;
                                jQuery.each(res['errors'], function (e_field, e_message) {
                                    if ( e_message != '' ) {
                                        $('[name="'+ e_field +'"]').eq(0).addClass('is-invalid');
                                        $('<div class="invalid-feedback">'+ e_message +'</div>').insertAfter($('[name="'+ e_field +'"]').eq(0));
                                        if ( error_index == 0 ) {
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
                                var m = res['message']||'Unable to download. Please try again later.';
                                App.alert(m, 'Oops!');
                            }
                        } else {
                            action.resolve();
                            if ( !noRedirect ) {
                                window.location.href = App.siteUrl('product/download_import_compatible_xls');
                            }
                        }

                        $form.find('button[type="submit"]').attr('disabled', false);
                    },
                    error: function (e) {
                        form_in_progress = 0;
                        App.loading(false);
                        $form.find('button[type="submit"]').attr('disabled', false);
                        App.alert( e.responseText, 'Oops!');
                        action.reject();
                    }
                });
            });

            return action;
        }

        // Import
        $("#xls-upload").upload({
            maxSize: 1000000000,
            multiple: false,
            beforeSend: onBeforeSend,
            label: 'Drag and drop Excel file or click to select',
        }).on("start.upload", onStart)
        .on("complete.upload", onComplete)
        .on("filestart.upload", onFileStart)
        .on("fileprogress.upload", onFileProgress)
        .on("filecomplete.upload", onFileComplete)
        .on("fileerror.upload", onFileError)
        // .on("fileremove.upload", onFileRemove)
        .on("chunkstart.upload", onChunkStart)
        .on("chunkprogress.upload", onChunkProgress)
        .on("chunkcomplete.upload", onChunkComplete)
        .on("chunkerror.upload", onChunkError)
        .on("queued.upload", onQueued);

        function onCancel(e) {
            console.log("Cancel");
            var index = $(this).parents("li").data("index");
            $(this).parents("form").find(".upload").upload("abort", parseInt(index, 10));
        }

        function onCancelAll(e) {
            console.log("Cancel All");
            $(this).parents("form").find(".upload").upload("abort");
        }

        // function onRemove(e) {
        //   console.log("Remove");
        //   var index = $(this).parents("li").data("index");
        //   $(this).parents("form").find(".upload").upload("remove", parseInt(index, 10));
        // }

        function onBeforeSend(formData, file) {
            var f_name = (file.name).split('.');
            var f_ext  = '';
            if (f_name.length > 1) {
                f_ext = f_name[f_name.length - 1];
            }
            allowed_exts = new Array('xls', 'xlsx');
            if( (f_ext != '') && ($.inArray(f_ext, allowed_exts) !== -1) ) {
                return formData;
            }
            return false;
        }

        function onQueued(e, files) {
            /*var html = '';
            for (var i = 0; i < files.length; i++) {
                html += '<li data-index="' + files[i].index + '"><span class="f-content"><span class="file">' + files[i].name + '</span><span class="cancel">Cancel</span><span class="progress">Queued</span></span><span class="bar"></span></li>';
            }

            $(this).parents("form").find(".filelist.queue")
                .append(html);*/
        }

        function onComplete(e, files) {
            console.log("Complete");
            // All done!
        }

        function onFileStart(e, file) {
            showProgress(0, 1);
        }

        function onFileProgress(e, file, percent) {
            showProgress(percent);
        }

        function onFileComplete(e, file, response) {
            if (response.trim() === "" || response.toLowerCase().indexOf("error") > -1) {
                showProgress(0, 1);
                showMsg(response.replace('Error: ', ''), 'danger');
            } else {
                showProgress(0);
                hideMsg();
            }
        }

        function onFileError(e, file, error) {
            console.log("File Error");
        }

        function onFileRemove(e, file, error) {
            console.log("File Removed");
        }

        function onChunkStart(e, file) {
            console.log("Chunk Start");
        }

        function onChunkProgress(e, file, percent) {
            console.log("Chunk Progress");
        }

        function onChunkComplete(e, file, response) {
            console.log("Chunk Complete");
        }

        function onChunkError(e, file, error) {
            console.log("Chunk Error");
        }

        function onStart(e) {
            $(this).parents("form").find(".upload").upload("start");
            $('#upload-progress .progress-bar').text("0%").css("width", "0%");
        }

        function startTask() {
            var $def = $.Deferred();
            es = new EventSource('<?php echo url("admin/start_import") ?>');

            es.addEventListener('message', function(e) {
                var result = JSON.parse( e.data );

                if (e.lastEventId == 'CLOSE') {
                    // addLog('Received CLOSE closing');
                    es.close();
                    $def.resolve(e);
                    $('#import-progress .progress-bar').css("width", "100%" );
                    $('#import-progress .progress-bar').html("100%" );
                } else if (e.lastEventId == 'VALIDATION_ERROR') {
                    es.close();
                    $def.reject(e);
                    showMsg(result.message, 'danger');
                } else {
                    var progress = Math.floor((result.progress/result.total) * 100 );
                    $('#import-progress .progress-bar').css("width", (progress) + "%" );
                    $('#import-progress .progress-bar').html((progress) + "%");
                    $('#success-count').text(result.success);
                    $('#failed-count').text(result.failed);
                }
            });

            es.addEventListener('error', function(e) {
                if ( e.origin == '<?=rtrim(url('/'), '/')?>' ) {
                    addLog('Error occurred');
                    es.close();
                    $def.reject(e);
                } else {
                    return;
                }
            });

            return $def;
        }

        function stopTask() {
            es.close();
            addLog('Interrupted');
        }

        function addLog(message) {
            console.log(message)
        }

        function showMsg(msg, cls) {
            $('#import-alert').removeAttr('class').html(msg).addClass('alert alert-'+cls).show();
            setTimeout(function() {
                $('#import-alert').fadeOut(400);
            }, 4000);
        }

        function hideMsg() {
            $('#import-alert').hide();
        }

        function showProgress(percent, showStartBtn) {
            $('#upload-progress .progress-bar').text(percent + "%").css("width", percent + "%");
            if ( percent > 0 ) {
                $('#upload-progress').show();
                if ( percent >= 100 ) {
                    $('#start-import').show();
                    resetImportStatus();
                }
            } else {
                $('#upload-progress').hide();
            }
            if ( typeof showStartBtn != 'undefined' && showStartBtn == 1 ) {
                $('#start-import').hide();
            }
        }

        $('body').off('click', '#start-import');
        $('body').on('click', '#start-import', function(e) {
            e.preventDefault();
            var $t = $(this);
            var $importStatus = $('#import-status');

            if ( $t.hasClass('in-progress') ) {
                return false;
            }
            $t.addClass('in-progress').html('<i class="fa fa-spinner fa-pulse" aria-hidden="true"></i>&nbsp; Importing');
            resetImportStatus();
            $importStatus.show();

            $("#xls-upload").upload("disable");
            $("#zip-upload").upload("disable");
            var $def = startTask();

            $def.done(function(e) {
                $t.removeClass('in-progress').html('<i class="fa fa-play" aria-hidden="true"></i>&nbsp; Start Import').hide();
                var failed = $('#failed-count').text();
                failed = parseInt(failed);
                /*if ( failed > 0 ) {
                    $('#download-failed-xls').show();
                } else {
                    $('#download-failed-xls').hide();
                }*/
                $("#xls-upload").upload("enable");
                $("#zip-upload").upload("enable");
            });
            $def.fail(function(e) {
                $t.removeClass('in-progress').html('<i class="fa fa-play" aria-hidden="true"></i>&nbsp; Start Import').hide();
                $importStatus.hide();
                resetImportStatus();
                $("#xls-upload").upload("enable");
                $("#zip-upload").upload("enable");
            });
        });

        function resetImportStatus() {
            var $importStatus = $('#import-status');
            $importStatus.hide();
            $importStatus.find('#success-count, #failed-count').text('0');
            $importStatus.find('#download-failed').hide();
            $('#import-progress .progress-bar').css("width",  "0%" );
            $('#download-failed-xls').hide();
            $('#success-count').text(0);
            $('#failed-count').text(0);
        }

        // Image upload (zip)
        $("#zip-upload").upload({
            maxSize: 1000000000,
            multiple: false,
            beforeSend: onBeforeSendZip,
            label: 'Drag and drop zip file or click to select',
        }).on("start.upload", onZipStart)
        .on("filestart.upload", onZipFileStart)
        .on("fileprogress.upload", onZipFileProgress)
        .on("filecomplete.upload", onZipFileComplete);

        function onBeforeSendZip(formData, file) {
            var f_name = (file.name).split('.');
            var f_ext  = '';
            if (f_name.length > 1) {
                f_ext = f_name[f_name.length - 1];
            }
            allowed_exts = new Array('zip');
            if( (f_ext != '') && ($.inArray(f_ext, allowed_exts) !== -1) ) {
                return formData;
            }
            return false;
        }

        function onZipFileStart(e, file) {
            showImageProgress(0, 1);
        }

        function onZipFileProgress(e, file, percent) {
            showImageProgress(percent);
        }

        function onZipFileComplete(e, file, response) {
            if (response.trim() === "" || response.toLowerCase().indexOf("error") > -1) {
                showImageProgress(0, 1);
                showImageMsg(response.replace('Error: ', ''), 'danger');
            } else {
                showImageProgress(0);
                hideImageMsg();
            }
        }

        function onZipStart(e) {
            $(this).parents("form").find(".upload").upload("start");
            $('#zip-progress .progress-bar').text("0%").css("width", "0%");
        }

        function showImageMsg(msg, cls) {
            $('#image-alert').removeAttr('class').html(msg).addClass('alert alert-'+cls).show();
            setTimeout(function() {
                $('#image-alert').fadeOut(400);
            }, 4000);
        }

        function hideImageMsg() {
            $('#image-alert').hide();
        }

        function showImageProgress(percent, showStartBtn) {
            $('#image-progress .progress-bar').text(percent + "%").css("width", percent + "%");
            if ( percent > 0 ) {
                $('#image-progress').show();
                if ( percent >= 100 ) {
                    $('#start-unzip').show();
                    resetUnzipStatus();
                }
            } else {
                $('#image-progress').hide();
            }
            if ( typeof showStartBtn != 'undefined' && showStartBtn == 1 ) {
                $('#start-unzip').hide();
            }
        }

        function resetUnzipStatus()
        {
            $('#image-progress .progress-bar').css("width",  "0%" );
        }

        $('body').off('click', '#start-unzip');
        $('body').on('click', '#start-unzip', function(e) {
            e.preventDefault();
            var $t = $(this);
            var $form = $t.closest('form');
            var formData = new FormData($form[0]);

            if ( $t.hasClass('in-progress') ) {
                return false;
            }
            $t.addClass('in-progress').html('<i class="fa fa-spinner fa-pulse" aria-hidden="true"></i>&nbsp; Unzipping...');

            $("#xls-upload").upload("disable");
            $("#zip-upload").upload("disable");
            var $def = $.Deferred();

            $.ajax({
                type: "POST",
                url: $t.attr('href'),
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                timeout: 600000,
                success: function (res) {
                    $def.resolve(res);
                    if(res.status == 1){
                      App.alert(res.message||'Image uploaded successfully');
                    }
                },
                error: function (e) {
                    $def.reject(res);
                }
            });

            $def.done(function(res) {
                $t.removeClass('in-progress').html('<i class="fa fa-play" aria-hidden="true"></i>&nbsp; Unzip').hide();
                $("#xls-upload").upload("enable");
                $("#zip-upload").upload("enable");
                if ( res['status'] == 1 ) {
                    var m = res['message']||'';
                    if ( m != '' ) {
                        App.toast([[m, 'success']]);
                    }
                } else {
                    var m = res['message']||'Unable to unzip and process. Please try again later.';
                    App.toast([[m, 'danger']]);
                }
            });
            $def.fail(function(res) {
                $t.removeClass('in-progress').html('<i class="fa fa-play" aria-hidden="true"></i>&nbsp; Unzip').hide();
                $("#xls-upload").upload("enable");
                $("#zip-upload").upload("enable");
                var m = res['message']||'Unable to unzip and process. Please try again later.';
                App.toast([[m, 'danger']]);
            });
        });

	})

})(jQuery);
</script>
@stop
