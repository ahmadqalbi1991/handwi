@extends('admin.template.layout')

@section('content')
@if(!empty($datamain->vendordatils)) 
@php
 $vendor     = $datamain->vendordatils;
 $bankdata   = $datamain->bankdetails;
@endphp
@endif
    <div class="mb-5">


    <style>
        #parsley-id-15, #parsley-id-23{
            bottom: auto;
        }
        #parsley-id-33{
            bottom: -10px
        }
    </style>
                <!--<div class="card p-4">-->
                    <form method="post" id="admin-form" action="{{ url('admin/customers') }}" enctype="multipart/form-data"
                    data-parsley-validate="true">
                    <input type="hidden" name="id" value="{{ $id }}">
                    @csrf()
                    <div class="">

                            <div class="card mb-2">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-4 col-xs-12" style="display: none;">
                                            <div class="form-group">
                                                <label>Full Name <span style="color:red;">*<span></span></span></label>
                                                <input type="text" class="form-control" data-jqv-maxlength="100" name="name" value="{{empty($datamain->name) ? '': $datamain->name}}">
                                            </div>
                                        </div>
                                        <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                                <label>First Name <span style="color:red;">*<span></span></span></label>
                                                <input type="text" class="form-control" data-jqv-maxlength="100" name="first_name" value="{{empty($datamain->first_name) ? '': $datamain->first_name}}" required data-parsley-required-message="Enter First Name">
                                            </div>
                                        </div>
                                        <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                                <label>Last Name <span style="color:red;">*<span></span></span></label>
                                                <input type="text" class="form-control" data-jqv-maxlength="100" name="last_name" value="{{empty($datamain->last_name) ? '': $datamain->last_name}}" required data-parsley-required-message="Enter Last Name">
                                            </div>
                                        </div>
                                        <div class="col-sm-4 col-xs-12" >
                                            <div class="form-group">
                                                <label>Email <span style="color:red;">*<span></span></span></label>
                                                <input type="email" class="form-control" name="email" data-jqv-maxlength="50" value="{{empty($datamain->email) ? '': $datamain->email}}" required 
                                                {{empty($datamain->email) ? '': 'disabled'}}
                                                data-parsley-required-message="Enter Email" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-sm-4 col-xs-12" style="display:none;">
                                            <div class="form-group">
                                                <label>DOB <span style="color:red;">*<span></span></span></label>
                                                <input type="text" class="form-control flatpickr-input" data-date-format="yyyy-mm-dd" name="dob" value="{{empty($datamain->dob) ? '': date('Y-m-d', strtotime($datamain->dob))}}">
                                            </div>
                                        </div>

                                        <div class="col-lg-4 col-md-4 col-12 mb-2" style="display:none;">
                                                    <label>Wallet </label>
                                                    <input type="text" class="form-control" name="wallet_amount" value="{{empty($datamain->wallet_amount) ? '': $datamain->wallet_amount}}" data-jqv-maxlength="10" oninput="validateNumber(this);">
                                                    <div class="error"></div>
                                        </div>
                                        <div class="col-sm-2 col-xs-12 mb-2">
                                            <div class="form-group">
                                                <label>Dial Code<b class="text-danger">*</b></label>
                                                <select name="dial_code" class="form-control select2" required
                                                data-parsley-required-message="Select Dial Code">
                                                    <option value="">Select</option>
                                                    @foreach ($countries as $cnt)
                                                        <option <?php if (!empty($datamain->dial_code))
                                                            { ?> {{$datamain->dial_code == $cnt->dial_code ? 'selected' : '' }} <?php
                                                            } ?> value="{{ $cnt->dial_code }}">
                                                         +{{$cnt->dial_code}}</option>
                                                    @endforeach;
                                                </select>
                                            </div>
                                        </div>


                                        <div class="col-sm-2 col-xs-12 mb-2">
                                            <div class="form-group">
                                                <label>Phone Number <span style="color:red;">*<span></span></span></label>
                                                <input type="number" class="form-control" name="phone" value="{{empty($datamain->phone) ? '': $datamain->phone}}" data-jqv-required="true" required data-parsley-required-message="Enter Phone number" data-parsley-type="digits" data-parsley-minlength="5"  data-parsley-maxlength="12" data-parsley-trigger="keyup">
                                            </div>
                                        </div>

                                        <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                                <label>Password </label>
                                                <input type="password" class="form-control" id="password1" name="password" data-jqv-maxlength="50" value="" data-parsley-minlength="8" autocomplete="off" @if(empty($id)) required data-parsley-required-message="Enter Password" @endif
                                                >
                                                
                                            </div>
                                        </div>

                                        <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                                <label>Confirm Password </label>
                                                <input type="password" class="form-control" name="confirm_password" data-jqv-maxlength="50" value="" data-parsley-minlength="8"
                                                data-parsley-equalto="#password1" autocomplete="off"
                                                @if(empty($id)) required data-parsley-required-message="Please re-enter your new password" @endif
                                                data-parsley-required-if="#password1">
                                                
                                            </div>
                                        </div>


                                        


                                        <div class="col-sm-4 col-xs-12">
                                            <div class="form-group d-flex align-items-end">
                                                <div>
                                                    <label>Upload Profile Picture (gif,jpg,png,jpeg) <span style="color:red;">*<span></span></span></label>
                                                    <input type="file" class="form-control jqv-input" name="user_image" data-role="file-image" data-preview="logo-preview" value="" data-parsley-trigger="change">
                                                </div>
                                                    <img id="logo-preview" class="img-thumbnail w-50" style="margin-left: 5px; height:50px; width:50px !important;" src="{{empty($datamain->user_image) ? asset('uploads/company/17395e3aa87745c8b488cf2d722d824c.jpg'): ($datamain->user_image)}}">
                                                </div>
                                        </div>



                                        <div class="col-12 other_docs" id="certificate_product_registration_div" >
                                            <div class="form-group">
                                                <button type="submit" class="btn btn-primary">Submit</button>
                                            </div>
                                        </div>    

                                    </div>
                                </div>
                            </div>

                            <div class="card mb-2 d-none">
                                <div class="card-body">
                                 
                                <div class="col-xs-12">
                                    
                                    <div class="form-group mb-0">
                                            <div class="row">

                                                
                                              <!--   <div class="col-lg-4 col-md-4 col-12 mb-3">
                                                    <label>Address Line 1 <span style="color:red;">*<span></span></span></label>
                                                    <input type="text" class="form-control" name="address1" value="{{empty($vendor->address1) ? '': $vendor->address1}}" data-jqv-maxlength="100" required data-parsley-required-message="Enter Address Line 1" >
                                                </div>
                                                <div class="col-lg-4 col-md-4 col-12 mb-3">
                                                    <label>Address Line 2</label>
                                                    <input type="text" class="form-control" name="address2" value="{{empty($vendor->address2) ? '': $vendor->address2}}" data-jqv-maxlength="100">
                                                </div>
                                                <div class="col-lg-4 col-md-4 col-12 mb-3">
                                                    <label>Street Name/No <span style="color:red;">*<span></span></span></label>
                                                    <input type="text" class="form-control" name="street" value="{{empty($vendor->street) ? '': $vendor->street}}" data-jqv-maxlength="100" required data-parsley-required-message="Enter Street Name/No">
                                                </div> -->

                                                <div class="col-sm-4 col-xs-12 mb-2" style="display:none;"> <div class="form-group"> 
                                                    <label>Country<b class="text-danger">*</b></label>
                                                    <select name="country_id" class="form-control select2"  data-role="country-change" id="country" data-input-state="city-state-id">
                                                        <option value="">Select</option>
                                                            @foreach ($countries as $cnt)
                                                        <option <?php if (!empty($datamain->country_id))
                                                        { ?> {{$datamain->country_id == $cnt->id ? 'selected' : '' }} <?php
                                                        } ?> value="{{ $cnt->id }}">
                                                        {{ $cnt->name }}</option>
                                                        @endforeach;
                                                    </select>
                                                </div>
                                            </div>

                                                
                                                
                                                <div class="form-group col-md-4 mb-2" style="display:none;">
                                                    <label>State/Province<b class="text-danger">*</b></label>
                                                    <select name="state_id" class="form-control"  id="city-state-id" data-role="state-change" data-input-city="city-id">
                                                        <option value="">Select</option>
                                                        @foreach ($states as $st)
                                                            <option  @if($id) @if($datamain->state_id==$st->id) selected @endif @endif value="{{$st->id}}">{{$st->name}}</option>
                                                        @endforeach
                                                    
                                                    </select>
                                                </div>

                                                <div class="form-group col-md-4 mb-2" style="display:none;">
                                                    <label>City<b class="text-danger">*</b></label>
                                                    <select name="city_id" class="form-control" id="city-id">
                                                        <option value="">Select</option>

                                                        @foreach ($cities as $ct)
                                                            <option  @if($id) @if($datamain->city_id==$ct->id) selected @endif @endif value="{{$ct->id}}">{{$ct->name}}</option>
                                                        @endforeach
                                                        
                                                    </select>
                                                </div>
                                                
                                                <div class="col-lg-4 col-md-4 col-12 mb-2" style="display:none;">
                                                    <label>Zip <span style="color:red;">*<span></span></span></label>
                                                    <input type="text" class="form-control" name="zip" value="{{empty($vendor->zip) ? '': $vendor->zip}}" data-jqv-maxlength="10">
                                                    <div class="error"></div>
                                                </div>

                                                
                                    
                                    
                                            </div>
                                        <!--</div>-->
                                    </div>
                                </div>


                                <div class="row">

                                </div>

                                <div class="row">
                                                                                                                                   
                                </div>
                                
                                
                            </div>
                            </div>
                        
                            
                            
                        
                          

                           
                        
                    </div>
                </form>
                </div>
@stop

@section('script')
    <script>
        App.initFormView();
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
                            window.location.href = App.siteUrl('/admin/customers');
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
        var validNumber = new RegExp(/^\d*\.?\d*$/);
var lastValid = 0;
function validateNumber(elem) {
  if (validNumber.test(elem.value)) {
    lastValid = elem.value;
  } else {
    elem.value = lastValid;
  }
}

    </script>

@stop
