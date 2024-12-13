@extends("admin.template.layout")

@section('header')
@stop


@section('content')
    <div class="card mb-5">
      
        <div class="card-body">
            <form method="post" action="{{ url('/admin/faq/update') }}" id="admin-form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{$faq->id}}">
                <div class="row  d-flex justify-content-between align-items-center">
                 
                    <div class="col-md-6 form-group">
                        <label>Question<b class="text-danger">*</b></label>
                        <input type="text" name="question" class="form-control jqv-input" data-jqv-required="true" value="{{$faq->title}}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Status</label>
                        <select name="active" class="form-control">
                            <option <?= $faq->active == 1 ? 'selected' : '' ?> value="1">Active</option>
                            <option <?= $faq->active == 0 ? 'selected' : '' ?> value="0">Inactive</option>
                        </select>
                    </div>
                   
                    <div class="col-md-12 form-group">
                        <label>Answer<b class="text-danger">*</b></label>
                        <textarea name="answer" class="form-control jqv-input" data-jqv-required="true" >{{$faq->description}}</textarea>
                    </div>

                 
                    <div class="col-md-12 text-center mt-3">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('script')
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
                            window.location.href = App.siteUrl('/admin/faq');
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
@stop
