@extends('vendor.template.layout')

@section('content')
@if(!empty($datamain->vendordatils)) 
@php
 $vendor     = $datamain->vendordatils;
 $bankdata   = $datamain->bankdetails;
@endphp
@endif
    <div class="card">
                <!--<div class="card p-4">-->
                    <form method="post" id="admin-form" action="{{ url('vendor/store_managers') }}" enctype="multipart/form-data"
                    data-parsley-validate="true">
                    <input type="hidden" name="id" value="{{ $id }}">
                    @csrf()
                    <div class="">
                   
                        
                            <div class="card-body">
                                <div class="card-title">Basic Details</div>
                                <div class="row">
                                    
                                    
                                    <div class="col-sm-4 col-xs-12">
                                        <div class="form-group d-flex align-items-center">
                                            <div>
                                            <label>Image <span style="color:red;">*<span></span></span></label>
                                            <input type="file" class="form-control jqv-input" name="image" data-role="file-image" data-preview="image-preview" @if(empty($id)) required
                            data-parsley-required-message="Image is required" @endif>
                                            <p class="text-muted">Max dim 400x400 (pix).</p>
                                            </div>
                                                                                            <img id="image-preview" class="img-thumbnail w-50" style="margin-left: 5px; height:50px; width:50px !important;" src="{{empty($datamain->user_image) ? asset('uploads/company/17395e3aa87745c8b488cf2d722d824c.jpg'): $datamain->user_image}}">
                                                                                    </div>
                                    </div>

                                    <div class="col-sm-4 col-xs-12">
                                        <div class="form-group">
                                            <label>Name <span style="color:red;">*<span></span></span></label>
                                            <input type="text" class="form-control" name="name" data-jqv-maxlength="50" value="{{empty($datamain->name) ? '': $datamain->name}}" required
                            data-parsley-required-message="Enter Name">
                                        </div>
                                    </div>

                                     <div class="col-sm-4 col-xs-12">
                                        <div class="form-group">
                                            <label>Email <span style="color:red;">*<span></span></span></label>
                                            <input type="email" class="form-control" name="email" data-jqv-maxlength="50" value="{{empty($datamain->email) ? '': $datamain->email}}" required
                            data-parsley-required-message="Enter Email">
                                            
                                        </div>
                                    </div>

                                </div>
                                <div class="row">
                                   <div class="col-sm-4 col-xs-12">
                                    <div class="form-group">
                        <label>Country<b class="text-danger">*</b></label>
                        <select name="country_id" class="form-control select2" required
                        data-parsley-required-message="Select Country" data-role="country-change" id="country" data-input-state="city-state-id">
                            <option value="">Select</option>
                            @foreach ($countries as $cnt)
                                <option <?php if(!empty($datamain->country_id)) { ?> {{$datamain->country_id == $cnt->id ? 'selected' : '' }} <?php } ?> value="{{ $cnt->id }}">
                                    {{ $cnt->name }}</option>
                            @endforeach;
                        </select>
                    </div>
                    </div>

                      
                                  
                                     <div class="col-sm-4 col-xs-12">
                                        <div class="form-group">
                                            <label>Privilege Type<span style="color:red;">*<span></span></span></label>
                                            
                                                <select name="previllegetype" class="form-control jqv-input" data-jqv-required="true"
                                                required
                            data-parsley-required-message="Select Privilege Type" >

                                                    <option value="">Select</option>
                            @foreach ($managertype as $cnt)
                                <option  value="{{ $cnt->id }}" @if(!empty($vendor)) {{$datamain->previllege==$cnt->id ? "selected" : null}} @endif>
                                    {{ $cnt->name }}</option>
                            @endforeach;
                                                </select>
                                                
                                            
                                        </div>
                                    </div>
                                   

                                    <div class="col-sm-4 col-xs-12" style="display: none;">
                                        <input type="hidden" name="seller_id" id="seller_id" value="{{ auth()->user()->id }}">
                                    </div>

                                    
                                    
                                </div>

                                <div class="row">

                                    <div class="col-md-4 col-xs-12 form-group">
                        <label>Stores<b class="text-danger">*</b></label>
                        <select class="form-control jqv-input select2" name="store_id" required
                            data-parsley-required-message="Select a Store" id="store-id">
                            <option value="">Select Stores</option>

                            @php
                                    $s_id = 0;
                                    if( isset ( $datamain->store ) ){
                                        $s_id = $datamain->store;
                                    }
                            @endphp

                            @foreach ($stores as $sel)
                                <option value="{{$sel->id }}" @if ($sel->id == $s_id) selected @endif>{{ $sel->store_name }}
                                </option>
                            @endforeach
                            
                        </select>
                    </div>
                                   
                                    
                                    <div class="col-sm-4 col-xs-12">
                                        <div class="form-group">
                                            <label>Password </label>
                                            <input type="password" class="form-control" id="password" name="password" data-jqv-maxlength="50" value="" data-parsley-minlength="8"
                                            >
                                           
                                        </div>
                                    </div>

                                    <div class="col-sm-4 col-xs-12">
                                        <div class="form-group">
                                            <label>Confirm Password </label>
                                            <input type="password" class="form-control" name="confirm_password" data-jqv-maxlength="50" value="" data-parsley-minlength="8"
                                            data-parsley-equalto="#password"
                                            data-parsley-required-message="Please re-enter your new password."
                                            data-parsley-required-if="#password">
                                        </div>
                                    </div>

                                    

                                    
                                   
                                    
                                  
                                </div>
                                <div class="row">

                                  
                                  

                                     <div class="col-sm-4 col-xs-12">
                                    <div class="form-group">
                        <label>Dial Code<b class="text-danger">*</b></label>
                        <select name="dial_code" class="form-control select2" required
                        data-parsley-required-message="Select Dial Code">
                            <option value="">Select</option>
                            @foreach ($countries as $cnt)
                                <option <?php if(!empty($datamain->dial_code)) { ?> {{$datamain->dial_code == $cnt->dial_code ? 'selected' : '' }} <?php } ?> value="{{ $cnt->dial_code }}">
                                    {{ $cnt->name }} +{{$cnt->dial_code}}</option>
                            @endforeach;
                        </select>
                    </div>
                    </div>
                                   
                                    <div class="col-sm-4 col-xs-12">
                                        <div class="form-group">
                                            <label>Phone Number <span style="color:red;">*<span></span></span></label>
                                            <input type="number" class="form-control" name="phone" value="{{empty($datamain->phone) ? '': $datamain->phone}}" data-jqv-required="true" required
                            data-parsley-required-message="Enter Phone number" data-parsley-type="digits" data-parsley-minlength="5" 
    data-parsley-maxlength="12" data-parsley-trigger="keyup">
                                        </div>
                                    </div>
                                    
                                </div>
                                
                            </div>
                            <div class="card-body">
                            
                                
                                <div class="col-xs-12">
                                    
                                    <div class="form-group">
                                        <!--<h4 >Registred Business Address</h4>-->
                                        <div class="card-title mt-3">Manager Address</div>
                                        <!--<div class="col-sm-12">-->
                                            <div class="row">
                                                <div class="col-lg-4 col-md-4 col-12">
                                                    <label>Address Line 1 <span style="color:red;">*<span></span></span></label>
                                                    <input type="text" class="form-control" name="address1" value="{{empty($vendor->address1) ? '': $vendor->address1}}" data-jqv-maxlength="100" required
                            data-parsley-required-message="Enter Address Line 1" >
                                                </div>
                                                <div class="col-lg-4 col-md-4 col-12">
                                                    <label>Address Line 2</label>
                                                    <input type="text" class="form-control" name="address2" value="{{empty($vendor->address2) ? '': $vendor->address2}}" data-jqv-maxlength="100">
                                                </div>
                                                <div class="col-lg-4 col-md-4 col-12">
                                                    <label>Street Name/No <span style="color:red;">*<span></span></span></label>
                                                    <input type="text" class="form-control" name="street" value="{{empty($vendor->street) ? '': $vendor->street}}" data-jqv-maxlength="100" required
                            data-parsley-required-message="Enter Street Name/No">
                                                </div>

                                               
                                                
                                               <div class="form-group col-md-4">
                            <label>State/Province<b class="text-danger">*</b></label>
                            <select name="state_id" class="form-control" required
                            data-parsley-required-message="Select State/Province" id="city-state-id" data-role="state-change" data-input-city="city-id">
                                <option value="">Select</option>
                                 @foreach ($states as $st)
                                    <option  @if($id) @if($datamain->state_id==$st->id) selected @endif @endif value="{{$st->id}}">{{$st->name}}</option>
                                @endforeach
                               
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>City<b class="text-danger">*</b></label>
                            <select name="city_id" class="form-control" required
                            data-parsley-required-message="Select City" id="city-id">
                                <option value="">Select</option>

                                  @foreach ($cities as $ct)
                                    <option  @if($id) @if($datamain->city_id==$ct->id) selected @endif @endif value="{{$ct->id}}">{{$ct->name}}</option>
                                @endforeach
                                
                            </select>
                        </div>
                                                
                                                <div class="col-lg-4 col-md-4 col-12">
                                                    <label>Zip <span style="color:red;">*<span></span></span></label>
                                                    <input type="text" class="form-control" name="zip" value="{{empty($vendor->zip) ? '': $vendor->zip}}" data-jqv-maxlength="10" required
                            data-parsley-required-message="Enter Zip code">
                                                    <div class="error"></div>
                                                </div>
                                            </div>
                                        <!--</div>-->
                                    </div>
                                </div>
                            </div>
                        
                           
                            <div class="card-body">
                                
                                <div class="row">
                                                                            <!-- doc_solo_properatorship -->
                                      
                                       
                                        
                                       
                                        <div class="col-sm-12">
                                            <!--<h4 class="mt-3">PROOF OF IDENTITY</h4>-->
                                            <div class="card-title mt-3">PROOF OF IDENTITY</div>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-xs-12">
                                          <div class="form-group">
                                            <label>Identity Type</label>
                                            <div class="custom-file">
                                              <select name="identity_file_name_1" id="identity_file_name_1" class="form-control jqv-input" data-jqv-required="true">
                                                <option value="">Select</option>
                                                <option selected="" value="Passport with Valid Visa">Passport with Valid Visa</option>
                                                <option value="Emirates ID (front and back)">Emirates ID (front and back)</option>
                                                <option value="Passport Copy of Local Sponsor ">Passport Copy of Local Sponsor </option>
                                              </select>
                                            </div>
                                          </div>
                                        </div>
                                        <div class="col-sm-3 col-xs-12">
                                            <div class="form-group">
                                              <label>File  </label>
                                                <input data-upload-status="0" type="file" class="form-control jqv-input" id="identity_file_value_1" name="identity_file_value_1" accept="image/png, image/jpeg, image/jpg, .pdf">
                                              
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-xs-12">
                                          <div class="form-group">
                                            <label>Identity Type</label>
                                            <div class="custom-file">
                                              <select name="identity_file_name_2" id="identity_file_name_2" class="form-control jqv-input" data-jqv-required="true">
                                                <option value="">Select</option>
                                                <option value="Passport with Valid Visa">Passport with Valid Visa</option>
                                                <option selected="" value="Emirates ID (front and back)">Emirates ID (front and back)</option>
                                                <option value="Passport Copy of Local Sponsor">Passport Copy of Local Sponsor </option>
                                              </select>
                                            </div>
                                          </div>
                                        </div>
                                        <div class="col-sm-3 col-xs-12">
                                            <div class="form-group">
                                              <label>File   </label>
                                                <input data-upload-status="0" type="file" class="form-control jqv-input" id="identity_file_value_2" name="identity_file_value_2" accept="image/png, image/jpeg, image/jpg, .pdf">
                                              
                                            </div>
                                        </div>
                                        
                                      
                                       

                                       
                                       
                                      
                                                                            
                                </div>
                                <div class="row">
                                    

                                    <div class="col-sm-4 col-xs-12 other_docs" id="certificate_product_registration_div" >
                                        <div class="form-group">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
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
                            window.location.href = App.siteUrl('/vendor/store_managers');
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
