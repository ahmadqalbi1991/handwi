@extends('admin.template.layout')

@section('header')
    <link rel="stylesheet" type="text/css" href="{{ asset('') }}admin-assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('') }}admin-assets/plugins/table/datatable/custom_dt_customer.css">
@stop


@section('content')
    <div class="card mb-5">
        <div class="card-header"><span id="subtitle">Create Attribute Values</span> - {{ $datamain['attribute_name'] }}</div>
        <div class="card-body">
            <div class="dataTables_wrapper container-fluid dt-bootstrap4">
                <form action="{{ url('/admin/attribute_value_save') }}" method="post" id="admin-form"
                    data-parsley-validate="true">
                    @csrf
                    <div class="row">
                        <div class="col-sm-6 col-md-6">
                            <select name="attribute_id" class="form-control" id="attribute_name" required
                                data-parsley-required-message="Select Attribute">
                                <option value="{{ $datamain['id'] }}">{{ $datamain['attribute_name'] }}</option>

                            </select>
                        </div>
                        <div class="col-sm-6 col-md-6">
                            <select name="attribute_value_in" class="form-control" id="attribute_value_in" required
                                data-parsley-required-message="Select Type">
                                <option value="1">Value in Text</option>
                                <option value="2">Value in Color</option>
                            </select>
                        </div>
                    </div><br />
                    <div class="row">
                        <div class="col-sm-6 col-md-6">
                            <input type="text" name="attribute_values" id="attribute_values" class="form-control"
                                placeholder="Attribute Value" required
                                data-parsley-required-message="Enter Attribute Value">
                        </div>
                        <div class="col-sm-6 col-md-6" id="cnt-color-picker" style="display: none;">
                            <input type="color" name="attribute_value_color" value="#ffffff" id="attribute_value_color"
                                style="height:38px;" class="form-control" placeholder="Pick Color" dir="rtl">
                        </div>
                    </div>

                    <br />
                    <div class="row">
                        <input type="hidden" name="id" value="0" id="id">
                        <div class="col-sm-6 col-md-6">

                            <input type="submit" name="Save" value="Save"
                                class="btn btn-warning mb-4 mr-2 btn-rounded">
                        </div>
                    </div>
                </form>


            </div>
        </div>
        <div class="card">
            <div class="card-body">

                <div class="row mt-3">
                    <div class="col-sm-12 col-md-6">
                        <div class="dataTables_length" id="column-filter_length">
                        </div>
                    </div>


                </div>
                <div class="table-responsive">
                    <table class="table table-condensed table-striped" id="example2">
                        <thead>
                            <tr>
                                <th>Attribute Name</th>
                                <th>Attribute Value</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($list as $item)
                                <tr>
                                    <td>{{ $datamain['attribute_name'] }}</td>

                                    <td>{{ $item->attribute_values }}</td>
                                    <td class="text-center">
                                        <div class="dropdown custom-dropdown">
                                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                <i class="flaticon-dot-three"></i>
                                            </a>

                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                                <a class="dropdown-item edit_attribute" href="#"
                                                    attid="{{ $item->id }}"><i class="flaticon-pencil-1"></i> Edit</a>
                                                <a class="dropdown-item" data-role="unlink"
                                                    data-message="Do you want to remove this attribute value?"
                                                    href="{{ url('admin/attribute_values/delete/' . $item->id) }}"><i
                                                        class="flaticon-delete-1"></i> Delete</a>

                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>



                <br>


            </div>
        </div>
    </div>
@stop

@section('script')
    <script src="{{ asset('') }}admin-assets/plugins/table/datatable/datatables.js"></script>
    <script>
        $('#example2').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": true,
            "responsive": true,
        });
    </script>
    <script>
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
                            window.location.reload();
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

        $('.edit_attribute').on('click', function() {
            var id = $(this).attr('attid');
            $.ajax({
                type: "get",
                enctype: 'multipart/form-data',
                url: "{{ url('admin/attribute_values_edit/') }}/" + id,
                data: {},
                dataType: 'json',
                success: function(res) {
                    $.each(res, function(index) {
                        $('#cnt-color-picker').hide();
                        $('#attribute_values').val(res[index].attribute_values);
                        $('select#attribute_value_in').val(res[index].attribute_value_in);
                        if (res[index].attribute_value_in == 2) {
                            $('#cnt-color-picker').show();
                            $('#attribute_value_color').val(res[index].attribute_value_color);
                        }
                        $('#id').val(res[index].id);
                        $('#subtitle').html('Edit attribute value');
                    });
                }
            })
        })
        $("#attribute_value_in").on("change", function(e) {

            if (e.target.value == "2")
                $("#cnt-color-picker").show();
            else
                $("#cnt-color-picker").hide();

        });
    </script>
@stop
