@extends("admin.template.layout")

@section("header")
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/custom_dt_customer.css">
@stop


@section("content")
<div class="card mb-5">
    @if ( GetUserPermissions("admin_users_create") )
    <div class="card-header">
        <a href="{{url('admin/admin_users/create')}}" class="btn-custom btn mr-2 mt-2 mb-2"><i class="fa-solid fa-plus"></i> Create Admin User</a>
    </div>
    @endif
    <div class="card-body">
        <div class="table-responsive">
        <table class="table table-condensed table-striped" id="example2">
            <thead>
                <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <!-- <th>User name</th> -->
                <th>Designation</th>
                <th>Created Date</th>
                <th>Active</th>
                <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=0; ?>
                @foreach($datamain as $datarow)
                    <?php $i++ ?>
                    <tr>
                        <td>{{$i}}</td>
                        <td>{{$datarow->first_name}} {{$datarow->last_name}}</td>
                        <td>{{$datarow->email}}</td>
                        <!-- <td>{{$datarow->name}}</td> -->
                        <td>{{$datarow->designation_name}}</td>
                        <td>{{ get_date_in_timezone($datarow->created_at, config('global.datetime_format')) }}</td>
                        <td>
                            <label class="switch s-icons s-outline  s-outline-warning  mb-2 mt-2 mr-2">
                                        <input type="checkbox" class="change_status" data-id="{{ $datarow->id }}"
                                            data-url="{{ url('admin/admin_users/change_status') }}"
                                            @if ($datarow->active) checked @endif>
                                        <span class="slider round"></span>
                            </label>
                        </td>
                        <td class="text-center">
                            <div class="dropdown custom-dropdown">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink7" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    <i class="flaticon-dot-three"></i>
                                </a>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuLink7">
                                    @if ( GetUserPermissions("admin_users_edit") )
                                    <a class="dropdown-item" href="{{url('admin/admin_users/'.$datarow->id.'/edit')}}"><i class="flaticon-pencil-1"></i> Edit</a>

                                    <a class="dropdown-item" userid="{{$datarow->id}}" data-role="change_password" ><i class="flaticon-plus"></i> Change Password</a>

                                    <a class="dropdown-item" href="{{url('admin/admin_users/update_permission/'.$datarow->id)}}"><i class="flaticon-pencil-1"></i> Update Permission </a>
                                    @endif

                                    @if ( GetUserPermissions("admin_users_delete") )
                                    <a class="dropdown-item" data-role="unlink"
                                    data-message="Do you want to remove this Admin user?"
                                    href="{{ url('admin/admin_users/' . $datarow->id) }}"><i
                                        class="flaticon-delete-1"></i> Delete</a>
                                    @endif
                                </div>
                            </div>
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</div>
@stop

@section("script")
<script src="{{asset('')}}admin-assets/plugins/table/datatable/datatables.js"></script>
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
                            window.location.href = App.siteUrl('/admin/admin_users');
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
