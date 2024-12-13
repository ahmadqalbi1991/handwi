@extends('admin.template.layout')

@section('content')
@if(!empty($datamain->vendordatils)) 
@php
 $vendor     = $datamain->vendordatils;
 $bankdata   = $datamain->bankdetails;
@endphp
@endif
    <div class="card mb-5">
                <!--<div class="card p-4">-->
                    <form method="post" id="admin-form" action="{{ url('admin/admin_users') }}" enctype="multipart/form-data"
                    data-parsley-validate="true">
                    <input type="hidden" name="id" value="{{ $id }}">
                    @csrf()
                    <div class="">
                              <div class="card-body">
                              
                                <div class="row">
                                    <div class="col-sm-4 col-xs-12">
                                        <div class="form-group">
                                            <label>First Name <span style="color:red;">*<span></span></span></label>
                                            <input type="text" class="form-control" data-jqv-maxlength="100" name="first_name" value="{{empty($datamain->first_name) ? '': $datamain->first_name}}" required
                            data-parsley-required-message="Enter First Name">
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-xs-12">
                                        <div class="form-group">
                                            <label>Last Name <span style="color:red;">*<span></span></span></label>
                                            <input type="text" class="form-control" data-jqv-maxlength="100" name="last_name" value="{{empty($datamain->last_name) ? '': $datamain->last_name}}" required
                            data-parsley-required-message="Enter Last Name">
                                        </div>
                                    </div>

                                    <!-- <div class="col-sm-4 col-xs-12">
                                        <div class="form-group">
                                            <label>Username <span style="color:red;">*<span></span></span></label>
                                            <input type="text" class="form-control" data-jqv-maxlength="100" name="username" value="{{empty($datamain->name) ? '': $datamain->name}}" 
                            data-parsley-required-message="Enter Username">
                                        </div>
                                    </div> -->
                                    <div class="col-sm-4 col-xs-12">
                                        <div class="form-group">
                                            <label>Designation <span style="color:red;">*<span></span></span></label>
                                            <input type="text" class="form-control" data-jqv-maxlength="100" name="designation_name" value="{{empty($datamain->designation_name) ? '': $datamain->designation_name}}" required data-parsley-required-message="Enter Designation">
                                        </div>
                                    </div>


                                    
                                </div>
                                 <div class="row">
                                    <div class="col-sm-4 col-xs-12">
                                        <div class="form-group">
                                            <label>Email <span style="color:red;">*<span></span></span></label>
                                            <input type="email" class="form-control" name="email" data-jqv-maxlength="50" value="{{empty($datamain->email) ? '': $datamain->email}}" required                             data-parsley-required-message="Enter Email" autocomplete="off">
                                            
                                        </div>
                                    </div>
                                   
                                    
                                    <div class="col-sm-4 col-xs-12">
                                        <div class="form-group">
                                            <label>Password </label>
                                            <input type="password" class="form-control" id="password" name="password" data-jqv-maxlength="50" value="" data-parsley-minlength="8" autocomplete="off"
                                            >
                                           
                                        </div>
                                    </div>

                                    <div class="col-sm-4 col-xs-12">
                                        <div class="form-group">
                                            <label>Confirm Password </label>
                                            <input type="password" class="form-control" name="confirm_password" data-jqv-maxlength="50" value="" data-parsley-minlength="8"
                                            data-parsley-equalto="#password" autocomplete="off"
                                            data-parsley-required-message="Please re-enter your new password."
                                            data-parsley-required-if="#password">
                                           
                                        </div>
                                    </div>
                                     
                                   
                                   
                                    
                                  
                                </div>

                                <div class="row">
                                    {{-- 
                                    <div class="col-sm-4 col-xs-12">
                                        <div class="form-group">
                                            <label>Designation <span style="color:red;">*<span></span></span></label>
                                            <select name="designation" class="form-control select2" 
                                            data-parsley-required-message="Select Designation" id="designation">
                                                                <option value="">Select</option>
                                                @foreach ($designation as $cnt)
                                                    <option @if(!empty($datamain->designation_id)) {{$datamain->designation_id==$cnt->id ? "selected" : null}} @endif value="{{ $cnt->id }}">
                                                        {{ $cnt->name }}</option>
                                                @endforeach;
                                            </select>
                                            
                                        </div>
                                    </div>
                                    --}}
                                    

                                   
                                      <div class="col-sm-4 col-xs-12">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select name="active" class="form-control status-selection">
                                                <option @if(!empty($datamain)) {{$datamain->active==1 ? "selected" : null}} @endif value="1">Active</option>
                                                <option @if(!empty($datamain)) {{$datamain->active==0 ? "selected" : null}} @endif value="0">Inactive</option>
                                            </select>
                                        </div>
                                    </div>      
                                   
                                    
                                    
                                   
                                    
                                  
                                </div>

                                <div class="row mt-2">
                                    <div class="col-sm-4 col-xs-12 other_docs" id="certificate_product_registration_div" >
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Submit</button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        
                            
                            <!-- <div class="card-body">
                                
                               
                                <div class="row">
                                    
                                  




                                    
                                                                                                                                                    
                                </div>
                            </div>
                         -->


                           
                        
                    </div>
                </form>
                </div>
@stop

@section('script')
    <script>
        App.initFormView();
        $(document).ready(function() {
            $(".status-selection").select2();
        });

        $(document).ready(function() {
            $('#designation').select2();
            
        });
        $('body').off('submit', '#admin-form');
        $('body').on('submit', '#admin-form', function(e) {
            e.preventDefault();
            $(".invalid-feedback").remove();
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
