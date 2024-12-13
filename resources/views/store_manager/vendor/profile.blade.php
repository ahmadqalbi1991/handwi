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
        <form method="post" id="admin-form" action="{{ url('vendor/vendors') }}" enctype="multipart/form-data"
              data-parsley-validate="true">
            <input type="hidden" name="id" value="{{ $id }}">
            @csrf()
            <div class="">


                <div class="card-body">
                    <div class="card-title">Basic Details</div>
                    <div class="row">
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Have own delivery</label>
                                <div class="col-sm-12">
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input type="radio" class="form-check-input" name="has_own_delivery" value="1" @php if(!empty($vendor->homedelivery)) { @endphp {{$vendor->homedelivery == '1' ? 'checked' : '' }} @php } else { echo "checked";} @endphp>Yes  <i class="input-helper"></i>
                                            <i class="input-helper"></i></label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            <input type="radio" class="form-check-input" name="has_own_delivery" value="0" @php if(!empty($vendor)) { @endphp {{$vendor->homedelivery == '0' ? 'checked' : '' }} @php } @endphp>No<i class="input-helper"></i>
                                            <i class="input-helper"></i></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group d-flex align-items-center">
                                <div>
                                    <label>Logo <span style="color:red;">*<span></span></span></label>
                                    <input type="file" class="form-control jqv-input" name="logo" data-role="file-image" data-max-width="200" data-max-height="200" data-preview="logo-preview" value="" @if(empty($id)) required
                                           data-parsley-required-message="Logo is required" @endif >
                                    <p class="text-muted">Allowed Dim 200x200(px)</p>
                                </div>
                                <img id="logo-preview" class="img-thumbnail w-50" style="margin-left: 5px; height:50px; width:50px !important;" src="{{empty($vendor->logo) ? asset('uploads/company/17395e3aa87745c8b488cf2d722d824c.jpg'): $vendor->logo}}">
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group d-flex align-items-center">
                                <div>
                                    <label>Image <span style="color:red;">*<span></span></span></label>
                                    <input type="file" class="form-control jqv-input" name="image" data-role="file-image" data-max-width="600" data-max-height="400" data-min-width="500" data-min-height="330" data-preview="image-preview" @if(empty($id)) required
                                           data-parsley-required-message="Image is required" @endif>
                                    <p class="text-muted">Max dim 600x400 (pix). Min dim 500x330 (pix).</p>
                                </div>
                                <img id="image-preview" class="img-thumbnail w-50" style="margin-left: 5px; height:50px; width:50px !important;" src="{{empty($datamain->user_image) ? asset('uploads/company/17395e3aa87745c8b488cf2d722d824c.jpg'): $datamain->user_image}}">
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
                                <label>Name <span style="color:red;">*<span></span></span></label>
                                <input type="text" class="form-control" name="name" data-jqv-maxlength="50" value="{{empty($datamain->name) ? '': $datamain->name}}" required
                                       data-parsley-required-message="Enter Name">
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Industry Type<span style="color:red;">*<span></span></span></label>

                                <select name="industrytype" class="form-control jqv-input" data-jqv-required="true"
                                        required
                                        data-parsley-required-message="Enter Name" >

                                    <option value="">Select</option>
                                    @foreach ($industry as $cnt)
                                        <option  value="{{ $cnt->id }}" @if(!empty($vendor)) {{$vendor->industry_type==$cnt->id ? "selected" : null}} @endif>
                                            {{ $cnt->name }}</option>
                                    @endforeach;
                                </select>


                            </div>
                        </div>

                    </div>

                    <div class="row">


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
                                <input type="text" class="form-control" name="phone" value="{{empty($datamain->phone) ? '': $datamain->phone}}" data-jqv-required="true" required
                                       data-parsley-required-message="Enter Phone number">
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>No of branches <span style="color:red;">*<span></span></span></label>
                                <input type="text" class="form-control" name="no_of_branches" value="{{empty($vendor->branches) ? '': $vendor->branches}}"  data-jqv-maxlength="100" >
                                <div class="error"></div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="card-body">
                    <div class="card-title">Business Information</div>
                    <div class="row">
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Company Legal Name <span style="color:red;">*<span></span></span></label>
                                <input type="text" class="form-control" data-jqv-maxlength="100" name="company_legal_name" value="{{empty($vendor->company_name) ? '': $vendor->company_name}}" required
                                       data-parsley-required-message="Enter Company Legal Name">
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Company Brand Name <span style="color:red;">*<span></span></span></label>
                                <input type="text" class="form-control" data-jqv-maxlength="100" name="company_brand_name" value="{{empty($vendor->company_brand) ? '': $vendor->company_brand}}" required
                                       data-parsley-required-message="Enter Company Brand Name">
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Legal Status</label>
                                <select disabled="" name="legal_status" class="form-control jqv-input" data-jqv-required="true"><option value="">Select</option>
                                    <option selected="" value="2">Sole Propriotorship</option>
                                    <option value="3">Partnership</option>
                                    <option value="4">Limited Liability Company</option>
                                    <option value="6">I am an individual</option>
                                    <option value="5">Branch of a foreign Company</option>
                                    <option value="7">Others</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Business Registration Date <span style="color:red;">*<span></span></span></label>
                                <input type="text" class="form-control" data-provide="datepicker" data-date-format="yyyy-mm-dd" name="business_registration_date" value="{{empty($vendor->reg_date) ? '': date('Y-m-d', strtotime($vendor->reg_date))}}" required
                                       data-parsley-required-message="Enter Business Registration Date">
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Trade Licence Number <span style="color:red;">*<span></span></span></label>
                                <input type="text" class="form-control jqv-input" data-jqv-required="true" name="trade_licene_number" data-jqv-maxlength="100" value="{{empty($vendor->trade_license) ? '': $vendor->trade_license}}" required
                                       data-parsley-required-message="Enter Trade Licence Number">
                            </div>
                        </div>

                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Trade Licence Expiry <span style="color:red;">*<span></span></span></label>
                                <input type="text" class="form-control" data-provide="datepicker" data-date-format="yyyy-mm-dd" name="trade_licene_expiry" value="{{empty($vendor->trade_license_expiry) ? '': date('Y-m-d', strtotime($vendor->trade_license_expiry))}}" required
                                       data-parsley-required-message="Enter Trade Licence Expiry" >
                            </div>
                        </div>

                        <!-- <div class="col-sm-4 col-xs-12">
                            <label>Trade Licence  <a target="_blank" href="https://dx.co.ae/vhelp/vhelp_shop/uploads/company/b9e8b90b461e2aaa34da67c19ae29061.jpg"><i class="fa fa-eye"></i></a> </label>
                            <input type="file" name="trade_licence" class="form-control jqv-input"  >
                        </div> -->
                        <div class="col-sm-4 col-xs-12" style="display: block">
                            <div class="form-group">
                                <label>Vat Registration Number <span style="color:red;">*<span></span></span></label>
                                <input type="text" class="form-control jqv-input" name="vat_registration_number" data-jqv-maxlength="100" value="{{empty($vendor->vat_reg_number) ? '': $vendor->vat_reg_number}}" required
                                       data-parsley-required-message="Enter Vat Registration Number">
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12" style="display: block">
                            <div class="form-group">
                                <label>Vat Expiry Date <span style="color:red;">*<span></span></span></label>
                                <input type="text" class="form-control" data-provide="datepicker" data-date-format="yyyy-mm-dd" name="vat_expiry_date" value="{{empty($vendor->vat_reg_expiry) ? '': date('Y-m-d', strtotime($vendor->vat_reg_expiry))}}" required
                                       data-parsley-required-message="Enter Vat Expiry Date">
                            </div>
                        </div>

                        <!-- <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Vat Registration File  <a target="_blank" href="https://dx.co.ae/vhelp/vhelp_shop/uploads/company/1cdfe65492e33490fc06bdb98d7cd319.png"><i class="fa fa-eye"></i></a> </label>
                                <input type="file" name="vat_registration" class="form-control jqv-input" >
                            </div>
                        </div> -->
                    </div>

                    <div class="col-xs-12">

                        <div class="form-group">
                            <!--<h4 >Registred Business Address</h4>-->
                            <div class="card-title mt-3">Registred Business Address</div>
                            <!--<div class="col-sm-12">-->
                            <div class="row">
                                <div class="col-lg-4 col-md-4 col-12">
                                    <label>Address Line 1 <span style="color:red;">*<span></span></span></label>
                                    <input type="text" class="form-control" name="address1" value="{{empty($vendor->address1) ? '': $vendor->address1}}" data-jqv-maxlength="100" required
                                           data-parsley-required-message="Enter Address Line 1" >
                                </div>
                                <div class="col-lg-4 col-md-4 col-12">
                                    <label>Address Line 2</label>
                                    <input type="text" class="form-control" name="address2" value="{{empty($vendor->address2) ? '': $vendor->address2}}" data-jqv-maxlength="100" required>
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
                    <div class="card-title">Bank Information</div>
                    <div class="row">
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Country<b class="text-danger">*</b></label>
                                <select name="bankcountry" class="form-control select2" required
                                        data-parsley-required-message="Select Country" id="bankcountry">
                                    <option value="">Select</option>
                                    @foreach ($countries as $cnt)
                                        <option <?php if(!empty($bankdata->country)) { ?> {{$bankdata->country == $cnt->id ? 'selected' : '' }} <?php } ?> value="{{ $cnt->id }}">
                                            {{ $cnt->name }}</option>
                                    @endforeach;
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Bank <span style="color:red;">*<span></span></span></label>
                                <select class="form-control" name="bank_id" required
                                        data-parsley-required-message="Select Bank">
                                    <option value="">Select</option>
                                    @foreach ($banks as $cnt)
                                        <option <?php if(!empty($bankdata->bank_name)) { ?> {{$bankdata->bank_name == $cnt->id ? 'selected' : '' }} <?php } ?> value="{{ $cnt->id }}">
                                            {{ $cnt->name }}</option>
                                    @endforeach;
                                </select>

                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Company Account <span style="color:red;">*<span></span></span></label>
                                <input type="text" name="company_account" class="form-control" value="{{empty($bankdata->company_account) ? '': $bankdata->company_account}}" required
                                       data-parsley-required-message="Enter Company account">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Bank Code Type </label>
                                <select name="bank_code_type" class="form-control" ></select>
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Bank Account Number <span style="color:red;">*<span></span></span></label>
                                <input type="text" name="bank_account_number" class="form-control" value="{{empty($bankdata->account_no) ? '': $bankdata->account_no}}" required
                                       data-parsley-required-message="Enter Bank account number">
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Bank Branch Code <span style="color:red;">*<span></span></span></label>
                                <input type="text" name="bank_branch_code" class="form-control" value="{{empty($bankdata->branch_code) ? '': $bankdata->branch_code}}" required
                                       data-parsley-required-message="Enter Bank Branch code">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Branch Name <span style="color:red;">*<span></span></span></label>
                                <input type="text" name="branch_name" class="form-control" value="{{empty($bankdata->branch_name) ? '': $bankdata->branch_name}}" required
                                       data-parsley-required-message="Enter Bank Branch name">
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Bank Statement </label>
                                <input type="file" name="bank_statement" class="form-control jqv-input">
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Credit Card Statement </label>
                                <input type="file" name="credit_card_statement" class="form-control jqv-input">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="card-title">Other Documents</div>
                    <div class="row">
                        <!-- doc_solo_properatorship -->
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Trade License  </label>
                                <input type="file" name="trade_licence" class="form-control jqv-input">
                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Chamber of Commerce   </label>
                                <input data-upload-status="0" type="file" class="form-control jqv-input" id="chamber_of_commerce" name="chamber_of_commerce" accept="image/png, image/jpeg, image/jpg, .pdf">

                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Share Certificate  </label>
                                <input data-upload-status="0" type="file" class="form-control jqv-input" id="share_certificate" name="share_certificate" accept="image/png, image/jpeg, image/jpg, .pdf">

                            </div>
                        </div>

                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Power attorney </label>
                                <input data-upload-status="0" type="file" class="form-control jqv-input" id="power_of_attorney" name="power_of_attorney" accept="image/png, image/jpeg, image/jpg, .pdf">

                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Vat Registration Certificate </label>
                                <input data-upload-status="0" type="file" class="form-control jqv-input" id="vat_registration" name="vat_registration" accept="image/png, image/jpeg, image/jpg, .pdf">

                            </div>
                        </div>
                        <div class="col-sm-4 col-xs-12">
                            <div class="form-group">
                                <label>Signed Agreement  </label>
                                <input data-upload-status="0" type="file" class="form-control jqv-input" id="signed_agrement" name="signed_agrement" accept="image/png, image/jpeg, image/jpg, .pdf">

                            </div>
                        </div>
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
                        <div class="col-sm-12">
                            <!--<h4 class="mt-3">Company Proof of Address</h4>-->
                            <div class="card-title mt-3">Company Proof of Address</div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label>Identity Type</label>
                                <div class="custom-file">
                                    <select name="company_identity_value" id="company_identity_value" class="form-control jqv-input">
                                        <option value="">Select</option>
                                        <option selected="" value="Lease Agreement">Lease Agreement</option>
                                        <option value="Utility Bills (DEWA, Etisalat, DU)">Utility Bills (DEWA, Etisalat, DU)</option>
                                        <option value="Bank Statement">Bank Statement</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label>File   </label>
                                <input data-upload-status="0" type="file" class="form-control jqv-input" id="company_identity_file" name="company_identity_file" accept="image/png, image/jpeg, image/jpg, .pdf">

                            </div>
                        </div>

                        <div class="col-sm-12">
                            <!--<h4 class="mt-3">Authorize Signatory Residential Proof of Address</h4>-->
                            <div class="card-title mt-3">Authorize Signatory Residential Proof of Address</div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label>Identity Type</label>
                                <div class="custom-file">
                                    <select name="residential_proff_value" id="residential_proff_value" class="form-control jqv-input">
                                        <option value="">Select</option>

                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xs-12">
                            <div class="form-group">
                                <label>File</label>
                                <input data-upload-status="0" type="file" class="form-control jqv-input" id="residential_proff_file" name="residential_proff_file" accept="image/png, image/jpeg, image/jpg, .pdf">

                            </div>
                        </div>

                    </div>
                    <div class="row">

                        <div class="col-sm-4 col-xs-12 other_docs" id="halal_div" style="display:none;">
                            <div class="form-group">
                                <label>Halal Certificate </label>
                                <input type="file" name="halal_certificate" class="form-control jqv-input">
                            </div>
                        </div>

                        <div class="col-sm-4 col-xs-12 other_docs" id="licence_from_food_safety_div" style="display:none;">
                            <div class="form-group">
                                <label>Food License from Food Safety Department </label>
                                <input type="file" name="licence_from_food_safety" class="form-control jqv-input">
                            </div>
                        </div>

                        <div class="col-sm-4 col-xs-12 other_docs" id="pharmacy_licence_div" style="display:none;">
                            <div class="form-group">
                                <label>Pharmacy License from MOHAP </label>
                                <input type="file" name="pharmacy_licence" class="form-control jqv-input">
                            </div>
                        </div>

                        <div class="col-sm-4 col-xs-12 other_docs" id="licence_to_operate_div" style="display:none;">
                            <div class="form-group">
                                <label>License to Operate (LTO) from FDA </label>
                                <input type="file" name="licence_to_operate" class="form-control jqv-input">
                            </div>
                        </div>

                        <div class="col-sm-4 col-xs-12 other_docs" id="certificate_product_registration_div" style="display:none;">
                            <div class="form-group">
                                <label>Certificate of Product Registration (CPR) from FDA</label>
                                <input type="file" name="certificate_product_registration" class="form-control jqv-input">
                            </div>
                        </div>


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
                            window.location.href = App.siteUrl('/vendor/my_profile');
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
