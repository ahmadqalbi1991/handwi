@extends("admin.template.layout")

@section('header')
@stop


@section('content')
    <div class="card mb-5">
      
        <div class="card-body">
        <form method="post" action="{{route('admin.notifications.save')}}" id="admin-form" enctype="multipart/form-data">
                        @csrf
                        <div class="row  d-flex justify-content-between align-items-center">
                            <div class="col-md-12 form-group d-none">
                                <label>User Type *</label>
                                <select name="usertype" class="form-control jqv-input" data-jqv-required="true"
                                    value="" placeholder="">
                                    <option value="">All</option>
                                    <option value="2">Users</option>
                                    <option value="3">Vendors</option>
                                </select>
                            </div>
                            <input type="hidden" name="usertype" id="usertype" value="2">
                            <div class="col-md-12 form-group">
                                <label>Title *</label>
                                <input type="text" name="title" class="form-control jqv-input" data-jqv-required="true"
                                    value="" placeholder="Title" required>
                            </div>
                            <div class="col-md-12 form-group">
                                <label>Description *</label>
                                <textarea type="text" name="description" class="form-control jqv-input" data-jqv-required="true"
                                    value="" placeholder="Description" required></textarea>
                            </div>
                            
                            <div class="col-md-12 form-group">
                                <label>Image</label>
                                    <input type="file" class="form-control jqv-input" name="image"
                                       id="product-image" accept=".jpg, .jpeg, .png">
                                  
                            </div>
                           
                            <div class="col-md-12 text-center mt-3">
                                <button type="submit" class="mt-4 btn btn-primary">Submit</button>
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
                            window.location.href = App.siteUrl('/admin/notifications');
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


