@extends("admin.template.layout")

@section("header")
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin-assets/plugins/table/datatable/custom_dt_customer.css">
@stop


@section("content")

<style>
    #example2_info{
        display: none
    }
</style>

<div class="card mb-5">

    <div class="card-header">
        <h3>Referal Code History ({{$user->ref_code}})</h3>
    </div>

    <div class="card-body">
        
        <div class="table-responsive">
        <table class="table table-condensed table-striped" id="example2">
            <thead>
                <tr>
                <th>#</th>
                <th>Referred User</th>
                <th>Status</th>
                <th>Created</th>
                <th>Updated</th>
                
                </tr>
            </thead>
            <tbody>
                <?php $i = $ref_history->perPage() * ($ref_history->currentPage() - 1); ?>
                @foreach($ref_history as $datarow)
                    <?php $i++ ?>
                    <tr>
                        <td>{{$i}}</td>
                        <td>
                            {{$datarow->accepted_user->first_name .' '.$datarow->accepted_user->last_name}}
                        </td>
                        <td>
                            {{$datarow->status == 2 ?'Used':'Pending'}}
                        </td>
                        <td>{{web_date_in_timezone($datarow->created_at,'d-M-Y h:i A')}}</td>
                        <td>{{web_date_in_timezone($datarow->updated_at,'d-M-Y h:i A')}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="col-sm-12 col-md-12 pull-right">
            <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">
                {!! $ref_history->appends(request()->all())->links('admin.template.pagination') !!}
            </div>
        </div>
        </div>
    </div>
</div>
<div class="modal" tabindex="-1" role="dialog" id="verify_first">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Oops</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Please Verify the account first</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="modal-dismiss" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@stop

@section("script")
<script src="{{asset('')}}admin-assets/plugins/table/datatable/datatables.js"></script>
<script>
$('#example2').DataTable({
      "paging": false,
      "searching": false,
      "ordering": false,
      "info": false,
      "autoWidth": true,
      "responsive": true,
    });
    $(".numberonly").keypress(function (e) {
     //if the letter is not digit then display error and don't type anything
     if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
       
        return false;
    }
   });

    </script>
    <script>
        App.initFormView();
        $('body').off('submit', '#admin-form');
        $('body').on('submit', '#admin-form', function(e) {
            e.preventDefault();
            var $form = $(this);
            var formData = new FormData(this);
            $(".invalid-feedback").remove();

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


    </script>
@stop
