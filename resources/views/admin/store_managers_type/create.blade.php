@extends('admin.template.layout')

@section('content')
    <div class="card mb-5">
        <div class="card-body">
            <div class="col-xs-12 col-sm-12">
                <form method="post" id="admin-form" action="{{ url('admin/store_managers_type') }}" enctype="multipart/form-data"
                    data-parsley-validate="true">
                    <input type="hidden" name="id" value="{{ $id }}">
                    @csrf()
                    <div class="form-group">
                        <label>Type Name<b class="text-danger">*</b></label>
                        <input type="text" name="name" class="form-control" required
                            data-parsley-required-message="Enter Type Name Name" value="{{ $name }}">
                    </div>
                
                    <div class="form-group">
                          <fieldset>
                              <legend>Access Rights</legend>

                                                              <div class="form-group row mt-0 mb-0">
                                    <label class="col-sm-2 col-form-label">Reviews</label>
                                    <div class="col-sm-10">
                                        <div class="row">
                                            <div class="col-8" role="access-group-row">
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1123][]" value="1124" data-parsley-multiple="access_groups1123"> View                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                            </div>
                                            <div class="col-4 pt-1">
                                                <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all">Set All</button>
                                                <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all">Reset</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                                                <div class="form-group row mt-0 mb-0">
                                    <label class="col-sm-2 col-form-label">Brand</label>
                                    <div class="col-sm-10">
                                        <div class="row">
                                            <div class="col-8" role="access-group-row">
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1076][]" value="1077" data-parsley-multiple="access_groups1076"> View                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1076][]" value="1078" data-parsley-multiple="access_groups1076"> Create                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1076][]" value="1079" data-parsley-multiple="access_groups1076"> Edit                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1076][]" value="1080" data-parsley-multiple="access_groups1076"> Delete                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                            </div>
                                            <div class="col-4 pt-1">
                                                <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all">Set All</button>
                                                <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all">Reset</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                                                <div class="form-group row mt-0 mb-0">
                                    <label class="col-sm-2 col-form-label">Categories</label>
                                    <div class="col-sm-10">
                                        <div class="row">
                                            <div class="col-8" role="access-group-row">
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1111][]" value="1112" data-parsley-multiple="access_groups1111"> View                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1111][]" value="1113" data-parsley-multiple="access_groups1111"> Create                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1111][]" value="1114" data-parsley-multiple="access_groups1111"> Edit                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1111][]" value="1115" data-parsley-multiple="access_groups1111"> Delete                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                            </div>
                                            <div class="col-4 pt-1">
                                                <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all">Set All</button>
                                                <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all">Reset</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                                                <div class="form-group row mt-0 mb-0">
                                    <label class="col-sm-2 col-form-label">Product Attribute</label>
                                    <div class="col-sm-10">
                                        <div class="row">
                                            <div class="col-8" role="access-group-row">
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1116][]" value="1117" data-parsley-multiple="access_groups1116"> View                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1116][]" value="1118" data-parsley-multiple="access_groups1116"> Create                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1116][]" value="1119" data-parsley-multiple="access_groups1116"> Edit                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1116][]" value="1120" data-parsley-multiple="access_groups1116"> Delete                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                            </div>
                                            <div class="col-4 pt-1">
                                                <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all">Set All</button>
                                                <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all">Reset</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                                                <div class="form-group row mt-0 mb-0">
                                    <label class="col-sm-2 col-form-label">Complaints</label>
                                    <div class="col-sm-10">
                                        <div class="row">
                                            <div class="col-8" role="access-group-row">
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1121][]" value="1122" data-parsley-multiple="access_groups1121"> View                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                            </div>
                                            <div class="col-4 pt-1">
                                                <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all">Set All</button>
                                                <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all">Reset</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                                                <div class="form-group row mt-0 mb-0">
                                    <label class="col-sm-2 col-form-label">Users</label>
                                    <div class="col-sm-10">
                                        <div class="row">
                                            <div class="col-8" role="access-group-row">
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1016][]" value="1017" data-parsley-multiple="access_groups1016"> View                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1016][]" value="1018" data-parsley-multiple="access_groups1016"> Create                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1016][]" value="1019" data-parsley-multiple="access_groups1016"> Edit                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1016][]" value="1020" data-parsley-multiple="access_groups1016"> Delete                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                            </div>
                                            <div class="col-4 pt-1">
                                                <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all">Set All</button>
                                                <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all">Reset</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                                                <div class="form-group row mt-0 mb-0">
                                    <label class="col-sm-2 col-form-label">Sales</label>
                                    <div class="col-sm-10">
                                        <div class="row">
                                            <div class="col-8" role="access-group-row">
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1021][]" value="1022" data-parsley-multiple="access_groups1021"> View                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 invisible">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1021][]" value="1023" disabled="disabled" data-parsley-multiple="access_groups1021"> Create                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 invisible">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1021][]" value="1024" disabled="disabled" data-parsley-multiple="access_groups1021"> Edit                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 invisible">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1021][]" value="1025" disabled="disabled" data-parsley-multiple="access_groups1021"> Delete                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                            </div>
                                            <div class="col-4 pt-1">
                                                <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all">Set All</button>
                                                <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all">Reset</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                                                <div class="form-group row mt-0 mb-0">
                                    <label class="col-sm-2 col-form-label">Products</label>
                                    <div class="col-sm-10">
                                        <div class="row">
                                            <div class="col-8" role="access-group-row">
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1066][]" value="1067" data-parsley-multiple="access_groups1066"> View                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1066][]" value="1068" data-parsley-multiple="access_groups1066"> Create                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1066][]" value="1069" data-parsley-multiple="access_groups1066"> Edit                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1066][]" value="1070" data-parsley-multiple="access_groups1066"> Delete                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                            </div>
                                            <div class="col-4 pt-1">
                                                <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all">Set All</button>
                                                <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all">Reset</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                                                <div class="form-group row mt-0 mb-0">
                                    <label class="col-sm-2 col-form-label">Coupon Codes</label>
                                    <div class="col-sm-10">
                                        <div class="row">
                                            <div class="col-8" role="access-group-row">
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1036][]" value="1037" data-parsley-multiple="access_groups1036"> View                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1036][]" value="1038" data-parsley-multiple="access_groups1036"> Create                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1036][]" value="1039" data-parsley-multiple="access_groups1036"> Edit                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1036][]" value="1040" data-parsley-multiple="access_groups1036"> Delete                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                            </div>
                                            <div class="col-4 pt-1">
                                                <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all">Set All</button>
                                                <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all">Reset</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                                                <div class="form-group row mt-0 mb-0">
                                    <label class="col-sm-2 col-form-label">Reports</label>
                                    <div class="col-sm-10">
                                        <div class="row">
                                            <div class="col-8" role="access-group-row">
                                                                                                    <div class="form-check form-check-inline mr-5 ">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1041][]" value="1042" data-parsley-multiple="access_groups1041"> View                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 invisible">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1041][]" value="1043" disabled="disabled" data-parsley-multiple="access_groups1041"> Create                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 invisible">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1041][]" value="1044" disabled="disabled" data-parsley-multiple="access_groups1041"> Edit                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                                    <div class="form-check form-check-inline mr-5 invisible">
                                                        <label class="form-check-label">
                                                            <input type="checkbox" class="form-check-input" name="access_groups[1041][]" value="1045" disabled="disabled" data-parsley-multiple="access_groups1041"> Delete                                                        <i class="input-helper"></i></label>
                                                    </div>
                                                                                            </div>
                                            <div class="col-4 pt-1">
                                                <button type="button" class="btn btn-mini btn-outline-success" role="access-set-all">Set All</button>
                                                <button type="button" class="btn btn-mini btn-outline-warning ml-2" role="access-reset-all">Reset</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                                        </fieldset>     
                            </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
            <div class="col-xs-12 col-sm-6">

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
                            window.location.href = App.siteUrl('/admin/store_managers_type');
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
