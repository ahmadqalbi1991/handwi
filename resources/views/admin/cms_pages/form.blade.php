@extends("admin.template.layout")
@section("header")
   
    <script src="https://cdn.jsdelivr.net/npm/tinymce@5.9.1/tinymce.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   
@stop
@section("content")

<style>
    #parsley-id-5{
        bottom: 0px;
    }
</style>
<div class="card mb-5">
    <div class="card-body">

        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12  layout-spacing">
            @if ( session('error'))
                <div class="alert alert-danger alert-dismissable custom-danger-box" style="margin: 15px;">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <strong> {{ session('error') }} </strong>
                </div>
            @endif
            <div class="">
                
                <div class="mt-4">
                    <form method="post" action="{{route('admin.cms_pages.save')}}" id="admin-form" enctype="multipart/form-data" data-parsley-validate="true">
                        @csrf
                        <input type="hidden" name="id" id="id" value="{{ $cms_page->id }}" >

                        <div class="row">
                            <div class=" col-md-6 form-group">
                                <label for="t-text">Name</label>
                                <input type="text" name="title_en" id="title_en" value="{{ $cms_page->title_en }}" class="form-control jqv-input" placeholder="Enter title in English" required
                            data-parsley-required-message="Name en is required">
                            </div>

                            <div class=" col-md-6 form-group" style="display:none;">
                                <label for="t-text">Name Ar</label>
                                <input type="text" name="title_ar" id="title_ar" value="{{ $cms_page->title_ar }}" class="form-control jqv-input" placeholder="Enter title in Arabic" >
                            </div>
                            <div class=" col-md-6 form-group">
                                <label for="t-text">Status</label>                                            
                                <select class="custom-select mb-4  jqv-input form-control" name="status" id="status" required>
                                    <option value="1" {{ $cms_page->status == 1 ? "selected" : ""}} >Active</option>
                                    <option value="0" {{ $cms_page->status == 0 ? "selected" : ""}} >In Active</option>
                                </select>                            
                            </div>
                            <div class=" col-md-12 form-group row mb-4">
                                <div class="col-md-12 col-sm-12">
                                    <label for="t-text">Description En</label>
                                    <textarea id="desc_en" name="desc_en" class="form-control  jqv-input description" required
                            data-parsley-required-message="Description En is required">{{ $cms_page->desc_en  }}</textarea>
                                </div>                            
                            </div>
                            <div class=" col-md-12 form-group row" style="display:none;">
                                <div class="col-md-12 col-sm-12">
                                    <label for="t-text">Description Ar</label>
                                    <textarea id="desc_ar" name="desc_ar" class="form-control  jqv-input description" >{{ $cms_page->desc_ar  }}</textarea>
                                </div>                            
                            </div>
                        </div>

                        <div class="form-group">
                            <input type="submit" name="txt" class="mt-4 btn btn-primary">
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
    </div>

</div>

@endsection

@section("script")
<script>
        App.initFormView();
        $('body').off('submit', '#admin-form');
        $('body').on('submit', '#admin-form', function(e) {
            e.preventDefault();
            var $form = $(this);
            var formData = new FormData(this);

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
                            window.location.href = App.siteUrl('/admin/cms_pages');
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


    </script>
    <script>
    $(document).ready(function() {
     tinymce.init({
        selector: 'textarea.description',
        plugins: 'code',
        toolbar: 'undo redo | formatselect | bold italic | code',
        height: 300,
        setup: function (editor) {
            editor.ui.registry.addButton('code', {
                icon: 'sourcecode',
                tooltip: 'Insert Code',
                onAction: function (_) {
                    editor.windowManager.open({
                        title: 'Insert Code',
                        body: {
                            type: 'panel',
                            items: [
                            {
                                type: 'textarea',
                                name: 'code',
                                label: 'Code',
                                multiline: true,
                                minHeight: 200
                            }
                            ]
                        },
                        buttons: [
                        {
                            type: 'cancel',
                            text: 'Cancel'
                        },
                        {
                            type: 'submit',
                            text: 'Insert',
                            primary: true
                        }
                        ],
                        onSubmit: function (api) {
                            var code = api.getData().code;
                            editor.insertContent('<pre><code>' + code + '</code></pre>');
                            api.close();
                        }
                    });
                }
            });
        }
    });

 });
</script>
    @stop