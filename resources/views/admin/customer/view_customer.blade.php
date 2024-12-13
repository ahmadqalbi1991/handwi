@extends('admin.template.layout')

@section('content')
@if(!empty($datamain->vendordatils)) 
@php
 $vendor     = $datamain->vendordatils;
 $bankdata   = $datamain->bankdetails;
@endphp
@endif
<div class="row">
    <div class="col-lg-12" style="float: left;">
        <!--<span style="float: left;"><a href=" {{ url('admin/customers') }}">  << Back </a></span>-->
    </div>
</div>
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

                                        <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                                <label>First Name: </label>
                                                <label>{{empty($datamain->first_name) ? '': $datamain->first_name}}</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                                <label>Last Name: </label>
                                                <label>{{empty($datamain->last_name) ? '': $datamain->last_name}}</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                                <label>Email: </label>
                                                <label>{{empty($datamain->email) ? '': $datamain->email}}</label>
                                            </div>
                                        </div>

                                        <div class="col-lg-4 col-md-4 col-12 mb-2" style="display:none">
                                                    <label>Wallet: </label>
                                            {{empty($datamain->wallet_amount) ? '': $datamain->wallet_amount}}
                                        </div>

                                        <div class="col-sm-4 col-xs-12 mb-2" style="display:none">
                                                    <div class="form-group">
                                                        <label>Dial Code: </label>
                                                        {{$datamain->dial_code}}
                                                    </div>
                                        </div>


                                        <div class="col-sm-4 col-xs-12 mb-2">
                                            <div class="form-group">
                                                <label>Phone Number: </label>
                                                <label>{{empty($datamain->phone) ? '': '+'.str_replace('+', '', $datamain->dial_code).' '.$datamain->phone}}</label>
                                            </div>
                                        </div>


                                        <div class="col-sm-4 col-xs-12">
                                            <label>Profile Picture: </label>
                                            <div class="form-group d-flex align-items-center">
                                                    <img id="logo-preview" class="img-thumbnail w-50" style="margin-left: 5px; height:50px; width:50px !important;" src="{{empty($vendor->logo) ? asset('uploads/company/17395e3aa87745c8b488cf2d722d824c.jpg'): $vendor->logo}}">
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
