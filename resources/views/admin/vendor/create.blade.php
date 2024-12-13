@extends('admin.template.layout')

@section('content')
@if(!empty($datamain->vendordatils))
@php
 $vendor     = $datamain->vendordatils;
 $bankdata   = $datamain->bankdetails;
@endphp
@endif
    <div class="mb-5">
    <link href="{{ asset('') }}admin-assets/jquery.timepicker.min.css" rel="stylesheet" type="text/css" />
    
    <style>
        textarea.form-control {
    height: 200px!important;
    resize: none !important;
    min-height: 200px;
}
        /*#parsley-id-23{*/
        /*    bottom:0 !important*/
        /*}*/
        #parsley-id-66, #parsley-id-60, #parsley-id-21, #parsley-id-57, #parsley-id-53, #parsley-id-34{
            position: absolute;
            bottom: -20px;
        }
        #cover-preview{
            width: 1170px;
            /* height: 525px; */
            object-fit: fill;
        }

        .form-group input[type="checkbox"] { display: none; }

        .form-group input[type="checkbox"] + label {
        display: block;
        position: relative;
        padding-left: 35px;
        margin-bottom: 20px;
        font: 14px/20px;
        color: #252525;
        cursor: pointer;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        }

        .form-group input[type="checkbox"] + label:last-child { margin-bottom: 0; }

        .form-group input[type="checkbox"] + label:before {
        content: '';
        display: block;
        width: 20px;
        height: 20px;
        border: 1px solid #C31718;
        position: absolute;
        left: 0;
        top: 0;
        opacity: .6;
        -webkit-transition: all .12s, border-color .08s;
        transition: all .12s, border-color .08s;
        }

        .form-group input[type="checkbox"]:checked + label:before {
        width: 10px;
        top: -5px;
        left: 5px;
        border-radius: 0;
        opacity: 1;
        border-top-color: transparent;
        border-left-color: transparent;
        -webkit-transform: rotate(45deg);
        transform: rotate(45deg);
        }
        .table td, .table th {

                border-top: 0px solid #dee2e6 !important;
            }
            .select2-container--default .select2-selection--multiple{
                min-height: 38px;
                height: auto !important;
            }
            .form-group input[type="checkbox"] + label {
                color: #000000 !important;
            }
            .password-toggle{
                position: absolute;
                right: 0;
                border: 0;
                z-index: 9;
            }
            .password-toggle button{
                border-radius: 0 !important;
                background: #2c93fa;
                color: #fff !important;
                border: 0 !important;
                height: 44px;
                width: 44px;

            }
    </style>
                <!--<div class="card p-4">-->
                    <form method="post" id="admin-form" action="{{ url('admin/vendors') }}" enctype="multipart/form-data"
                    data-parsley-validate="true">
                    <input type="hidden" name="id" value="{{ $id }}">
                    @csrf()
                    <div class="">


                            <div class="card mb-2">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12 form-group" style="display:none;">
                                            <label>Activity Type<b class="text-danger">*</b></label>
                                            <select name="activity_id" class="form-control jqv-input select2"
                                                    data-parsley-required-message="Select Activity Type" id="activity_id" required  {{$activity_id ? 'disabled' : ''}}>
                                                <option value="7">Select Activity Type</option>
                                               
                                            </select>
                                        </div>

                                        <div class="col-md-12 col-xs-12 is_food {{$datamain->activity_id == 5 ? '':'d-none'}}" >
                                            <div class="row mt-4" style="padding: 0 15px;align-items: center;">
                                                <div class="col-lg-auto mb-3">
                                                    <div class="form-group m-0">
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" class="week_days" id="is_dinein"  name="is_dinein" value="1" @if( $id && $datamain->is_dinein) checked @endif> 
                                                        <label for="is_dinein"> Pick Up</label>
                                                    </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-auto mb-3">
                                                    <div class="form-group m-0">
                                                    <div class="custom-checkbox">
                                                        <input type="checkbox" class="week_days" id="is_delivery"  name="is_delivery" value="1" @if( $id && $datamain->is_delivery) checked @endif> 
                                                        <label for="is_delivery">Delivery</label>
                                                    </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 mb-3 " >
                                                    <div class="shown-on-click_button" style="display:{{$id && $datamain->is_delivery?'':'none';}};">
                                                        
                                                        
                                                            <a href="{{url('admin/products')}}?activity_id=5" class="btn btn-primary" style="padding: 8px 15px !important;font-size: 13px !important;">Add Dishes</a>
                                                        
                                                        
                                                    </div>
                                                </div>
                                               
                                                <div class="col-lg-12 col-xs-12 d-none">
                                                    <div class="form-group">
                                                        <div class="row">
                                                            
                                                            <div class="col-lg-6 shown-on-click" style="display:{{$id && $datamain->is_dinein?'':'none';}};">
                                                                <div class="row">
                                                                    <div class="form-group w-100">
                                                                        <label>Upload Menu Images</label>
                                                                        <input type="file" name="menus[]" multiple class="form-control jqv-input">
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    @foreach($datamain->menu_images as $row)
                                                                    <div class="col-auto uploaded-prev-imd p-0">
                                                                        <img id="cover-preview" style="margin-left: 5px; height:50px; width:50px !important;"  class="img-thumbnail img-fluid" src="{{asset($row->image)}}">
                                                                         <label class="del-menu-img" role="button" data-id={{$row->id}} data-role="product-img-trash"  data-image-file="{{$row->image}}" ><i class="far fa-trash-alt"></i> Delete</label>
                                                                    </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-6 shown-on-click" style="display:{{$id && $datamain->is_dinein?'':'none';}};">
                                                                <div class="col-md-12 form-group">
                                                                    <div class="form-group">
                                                                        <label>Select Cuisines</label>
                                                                        <select name="cuisines[]" multiple style="width: 100% !important" class="form-control jqv-input select2" data-parsley-required-message="Select Cuisines" id="cuisines" >
                                                                            @foreach ($Cuisines as $row)
                                                                                <option value="{{ $row->id }}" {{in_array($row->id, $Cuisines_ids) ? 'selected' : ""}}>{{ $row->name }}
                                                                                    </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                           
                                        </div>


                                        <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                                <label>Company Name <span style="color:red;">*<span></span></span></label>
                                                <input type="text" class="form-control" data-jqv-maxlength="100" name="company_legal_name" value="{{empty($vendor->company_name) ? '': $vendor->company_name}}" required data-parsley-required-message="Enter Company Legal Name">
                                            </div>
                                        </div>


                                        <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                                <label>Trade Licence Number <span style="color:red;">*<span></span></span></label>
                                                <input type="text" class="form-control jqv-input" data-jqv-required="true" name="trade_licene_number" data-jqv-maxlength="100" value="{{empty($vendor->trade_license) ? '': $vendor->trade_license}}" required data-parsley-required-message="Enter Trade Licence Number">
                                            </div>
                                        </div>

                                        <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                                <label>Trade Licence Expiry <span style="color:red;">*<span></span></span></label>
                                                <input type="text" class="form-control flatpickr-input" data-date-format="yyyy-mm-dd" name="trade_licene_expiry" value="{{empty($vendor->trade_license_expiry) ? '': date('Y-m-d', strtotime($vendor->trade_license_expiry))}}" required data-parsley-required-message="Enter Trade Licence Expiry" >
                                            </div>
                                        </div>

                                        <div class="col-lg-4 col-xs-12">
                                                <div class="form-group">
                                                    <label>Trade License  @php if(!empty($vendor->trade_license_doc)) { @endphp <a href='{{asset($vendor->trade_license_doc)}}' target='_blank'><strong>View</strong></a>@php }  @endphp</label>
                                                    <input type="file" name="trade_licence" class="form-control jqv-input">
                                                </div>
                                        </div>

                                        @if($datamain->activity_id != 6 && $datamain->activity_id != 4 && $datamain->activity_id != 1)
                                        <div class="col-lg-4 col-xs-12">
                                            <div class="form-group">
                                                <label>Delivery charge </label>
                                                <input type="text" class="form-control" oninput="validateNumber(this);" name="delivery_charge" value="{{$datamain->delivery_charge??0}}" data-parsley-required-message="Enter Delivery charge" >
                                            </div>
                                        </div>
                                        @endif

                                        <div class="col-lg-4 col-xs-12">
                                            <div class="form-group">
                                                <label>Minimum Order amount </label>
                                                <input type="text" class="form-control" oninput="validateNumber(this);" name="minimum_amount" value="{{$datamain->minimum_order_amount??0}}" data-parsley-required-message="Enter Minimum order amount" >
                                            </div>
                                        </div>


                                         <div class="col-sm-4 col-xs-12" style="display: none">
                                            <div class="form-group">
                                             <label>Vendor Type <span style="color:red;">*<span></span></span></label>
                                            <div class="d-flex align-items-center pl-1 mt-2">
                                                <div class="mr-3">
                                                    <input type="checkbox" id="Pharmacy" name="type[]" value="1"  data-parsley-required-message="Check at least one Vendor Type" data-parsley-mincheck="1" data-parsley-errors-container="#checkbox-errors"@if(!empty($vendor)) @if($vendor->industry_type == 1 || $vendor->industry_type == 3) checked @endif @endif>
                                                    <label for="Pharmacy">Services</label>
                                                </div>
                                                <div class="">
                                                    <input type="checkbox" id="health-services" name="type[]" value="2" @if(!empty($vendor)) @if($vendor->industry_type == 2 || $vendor->industry_type == 3) checked @endif @endif>
                                                    <label for="health-services">Contract</label>
                                                </div>
                                                &nbsp;&nbsp;<div id="checkbox-errors"></div>
                                            </div>
                                             </div>

                                            </div>
                                        <div class="col-sm-4 col-xs-12" style="display: none;">
                                            <div class="form-group">
                                                <label>Vat Registration Number <span style="color:red;">*<span></span></span></label>
                                                <input type="text" class="form-control jqv-input" name="vat_registration_number" data-jqv-maxlength="100" value="{{empty($vendor->vat_reg_number) ? '': $vendor->vat_reg_number}}">
                                            </div>
                                        </div>
                                        <div class="col-sm-4 col-xs-12" style="display: none;">
                                            <div class="form-group">
                                                <label>Vat Expiry Date <span style="color:red;">*<span></span></span></label>
                                                <input type="text" class="form-control flatpickr-input" data-date-format="yyyy-mm-dd" name="vat_expiry_date" value="{{empty($vendor->vat_reg_expiry) ? '': date('Y-m-d', strtotime($vendor->vat_reg_expiry))}}" >
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

                                        <div class="col-sm-4 col-xs-12">
                                        <div class="form-group mb-0">
                                            <label>Email <span style="color:red;">*<span></span></span></label>
                                            <input type="email" class="form-control" name="email" data-jqv-maxlength="50" value="{{empty($datamain->email) ? '': $datamain->email}}" required
                            data-parsley-required-message="Enter Email" autocomplete="off" {{empty($datamain->email) ? '': 'readonly'}}>

                                        </div>
                                    </div>

                                     <div class="col-sm-4 col-xs-12 mb-4">
                                        <div class="form-group mb-0">
                                            <label>Password </label>
                                            <div class="input-group">
                                            <input type="password" data-parsley-pattern="(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}" data-parsley-pattern-message="Password must contain minimum 8 characters at least 1 alphabet, 1 number , 1 special character without #" class="form-control" id="password" name="password" data-jqv-maxlength="50" value="" data-parsley-minlength="8" autocomplete="off" @if(empty($id)) required data-parsley-required-message="Enter Password" @endif
                                            >
                                            <div class="input-group-append password-toggle">
                                                <button class="btn btn-outline-secondary" type="button" id="togglePassword"><i class="fa fa-eye"></i></button>
                                            </div>
                                             </div>

                                        </div>
                                    </div>

                                    <div class="col-sm-4 col-xs-12 mb-4">
                                        
                                            <label>Confirm Password </label>
                                            <div class="input-group position-relative">
                                            <input type="password" class="form-control w-100" id="confirm_password" name="confirm_password" data-jqv-maxlength="50" value="" data-parsley-minlength="8" data-parsley-equalto="#password" autocomplete="off" @if(empty($id)) required data-parsley-required-message="Please re-enter your new password" @endif data-parsley-required-if="#password">
                                            <div class="input-group-append password-toggle">
                                                <button class="btn btn-outline-secondary" type="button" id="togglePassword1"><i class="fa fa-eye"></i></button>
                                            </div>
                                           </div>
                                        
                                    </div>




                                    </div>


                                <div class="row">

                                     <div class="col-sm-1 col-xs-12 pr-0">
                                    <div class="form-group">
                        <label>Code</label>
                        <select name="dial_code" class="form-control select2 dial_code-select" required
                        data-parsley-required-message="Select Dial Code">
                            <option value="">Select</option>
                            @foreach ($countries as $cnt)
                                <option <?php if(!empty($datamain->dial_code)) { ?> {{$datamain->dial_code == $cnt->dial_code ? 'selected' : '' }} <?php } ?> value="{{ $cnt->dial_code }}">
                                    +{{$cnt->dial_code}}</option>
                            @endforeach;
                        </select>
                    </div>
                    </div>

                                    <div class="col-sm-3 col-xs-12">
                                        <div class="form-group">
                                            <label>Phone Number <span style="color:red;">*<span></span></span></label>
                                            <input type="tel" class="form-control" name="phone" value="{{empty($datamain->phone) ? '': $datamain->phone}}" data-jqv-required="true" required
                            data-parsley-required-message="Enter Phone number" data-parsley-type="digits" data-parsley-minlength="5"
    data-parsley-maxlength="12" data-parsley-trigger="keyup">
                                        </div>
                                    </div>
                                    <?php $pharmacy = 0; ?>
                                    @if(!empty($vendor)) @if($vendor->industry_type == 1 || $vendor->industry_type == 3) <?php $pharmacy = 1;?> @endif @else <?php $pharmacy = 1; ?> @endif
                                      @php if($pharmacy == 1) { @endphp
                                        <div class="col-sm-4 col-xs-12" style="display: none">
                                            <div class="form-group">
                                                <label>Pharmacy Commission (%) <span style="color:red;">*<span></span></span></label>
                                                <input type="text" class="form-control" data-jqv-maxlength="100" name="pharmacycommission" value="{{empty($vendor->pharmacycommission) ? '': $vendor->pharmacycommission}}" oninput="validateNumber(this);"  data-parsley-required-message="Enter Pharmacy Commission">
                                            </div>
                                        </div>
                                      @php } @endphp

                                        
                                         <div class="col-sm-4 col-xs-12" >
                                        @php  $com = ''; if(!empty($vendor)) { $com = 100 - ($vendor->servicecommission??0); } @endphp
                                        <div class="form-group">
                                            <label>Vendor Commission (%) <span style="color:red;">*<span></span></span></label>
                                            <input  type="text" min="1" max="100" class="form-control jqv-input floatnumber" data-jqv-number="true" required data-jqv-maxlength="100" name="vendor_commission" value="{{ $com}}" id="vendor_commission" >
                                        </div>
                                      </div>
                                      
                                      
                                       <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                                <label>Admin Commission (%)<span style="color:red;">*<span></span></span></label>
                                                <input readonly type="text" class="form-control cvalue" data-jqv-maxlength="100" name="servicecommission" value="{{empty($vendor->servicecommission) ? '0': $vendor->servicecommission}}" oninput="validateNumber(this);" required data-parsley-required-message="Enter Vendor Commission">
                                            </div>
                                        </div>

                                        
                                  
                                    </div> 
                                  <div class="row">
                                    
                                     <div class="col-sm-4 col-xs-12" style="display:none;">
                                            <div class="form-group">
                             <label>Featured</label>
                            <div class="d-flex align-items-center pl-1">
                                <div class="mr-3">
                                    <input type="checkbox" id="vendor" name="featured" value="1" @if(!empty($vendor)) @if($vendor->featured_flag == 1) checked @endif @endif>
                                    <label for="vendor">Is Featured</label>
                                </div>


                            </div>
                             </div>

                            </div>


                                    <div class="col-sm-12 col-xs-12">
                                        <div class="form-group">
                                            <label>About <span style="color:red;">*<span></label>
                                            <textarea class="form-control flatpickrtime" rows="6" id="description" name="description"  required data-parsley-required-message="Enter About"
                                            >{{empty($vendor->description) ? '': $vendor->description}}</textarea>

                                        </div>
                                    </div>




                                </div>
                                </div>
                            </div>

                            <div class="card mb-2">
                                <div class="card-body">
                                    <h6 class="text-xl mb-2">Bank Information</h6>
                                    <!-- <div class="card-title">Bank Information</div> -->
                                    <div class="row">
                                        <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                                <label>Beneficiary Name <span style="color:red;">*<span></span></span></label>
                                                <input type="text" name="company_account" class="form-control" value="{{empty($bankdata->company_account) ? '': $bankdata->company_account}}" required
                            data-parsley-required-message="Enter Company account">
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


                                    </div>
                                    <div class="row">

                                         <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                                <label>SWIFT Code/Routing number <span style="color:red;">*<span></span></span></label>
                                                <input type="text" name="bank_branch_code" class="form-control" value="{{empty($bankdata->branch_code) ? '': $bankdata->branch_code}}" required data-parsley-required-message="Enter Bank Branch code">
                                            </div>
                                        </div>

                                         <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                                <label>Bank Name<span style="color:red;">*<span></span></span></label>
                                                <select class="form-control bank_id-select" name="bank_id" required data-parsley-required-message="Select Bank">
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
                                                <label>Branch Name <span style="color:red;">*<span></span></span></label>
                                                <input type="text" name="branch_name" class="form-control" value="{{empty($bankdata->branch_name) ? '': $bankdata->branch_name}}" required data-parsley-required-message="Enter Bank Branch name">
                                            </div>
                                        </div>





                                    </div>
                                    <div class="row">

                                        <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                                <label>IBAN <span style="color:red;">*<span></span></span></label>
                                                <input type="text" name="iban" class="form-control" value="{{empty($bankdata->iban) ? '': $bankdata->iban}}" required data-parsley-required-message="Enter IBAN">
                                            </div>
                                        </div>
                                        <div class="col-sm-4 col-xs-12" style="display:none;">
                                            <div class="form-group">
                                                <label>Bank Code Type <span style="color:red;">*<span></span></span></label>
                                                <select name="bank_code_type" class="form-control">
                                                    <option value="0">Select</option>
                                                    @foreach ($banks_codes as $cnt)
                                                        <option <?php if(!empty($bankdata->code_type)) { ?> {{$bankdata->code_type == $cnt->id ? 'selected' : '' }} <?php } ?> value="{{ $cnt->id }}">
                                                            {{ $cnt->name }}</option>
                                                    @endforeach;
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                                <label>Bank Account Proof @php if(!empty($bankdata->bank_statement_doc)) { @endphp <a href='{{asset($bankdata->bank_statement_doc)}}' target='_blank'><strong>View</strong></a>@php }  @endphp</label>
                                                <input type="file" name="bank_statement" class="form-control jqv-input">
                                            </div>
                                        </div>
                                        <div class="col-sm-4 col-xs-12" style="display:none;">
                                            <div class="form-group">
                                                <label>Credit Card Statement @php if(!empty($bankdata->credit_card_sta_doc)) { @endphp <a href='{{asset($bankdata->credit_card_sta_doc)}}' target='_blank'><strong>View</strong></a>@php }  @endphp</label>
                                                <input type="file" name="credit_card_statement" class="form-control jqv-input">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php $days = Config('global.days');  @endphp
                           

                            

                            <div class="card mb-2">
                                <div class="card-body">
                                        <div class="col-xs-12">

                                            <div class="form-group">
                                                <!--<h4 >Registred Business Address</h4>-->
                                                <!-- <div class="card-title mt-3">Registred Business Address</div> -->
                                                <h6 class="text-xl mb-2">Registred Business Address</h6>
                                                <!--<div class="col-sm-12">-->
                                                    <div class="row">

                                                        <div class="form-group col-md-12">
                                                            <label class="control-label">Enter the location or Drag the marker<b class="text-danger">*</b></label>
                                                            <input type="text" name="txt_location" id="txt_location" class="form-control autocomplete" placeholder="673C+VFH - Dubai - United Arab Emirates" required data-parsley-required-message="Enter Location" @if($id) value="{{empty($vendor->location) ? '': $vendor->location}}" @endif>
                                                            <input type="hidden" id="location" name="location">
                                                        </div>

                                                        <div class="form-group col-md-12">
                                                            <div id="map_canvas" style="height: 200px;width:100%;"></div>
                                                        </div>

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

                                                        <div class="form-group col-md-4">
                                                        <label>City<b class="text-danger">*</b></label>
                                                        <select name="city_id" class="form-control" required
                                                        data-parsley-required-message="Select City" id="cityid">
                                                            <option value="">Select</option>
                                                            @foreach ($cities as $ct)
                                                                <option  @if($id) @if($datamain->city_id==$ct->id) selected @endif @endif value="{{$ct->id}}">{{$ct->name}}</option>
                                                            @endforeach

                                                        </select>
                                                    </div>
                                                    
                                                        <div class="col-lg-4 col-md-4 col-12">
                                                            <label>Address Line 1 <span style="color:red;">*<span></span></span></label>
                                                            <input type="text" class="form-control" name="address1" value="{{empty($vendor->address1) ? '': $vendor->address1}}" data-jqv-maxlength="100" required data-parsley-required-message="Enter Address Line 1" >
                                                        </div>
                                                        <div class="col-lg-4 col-md-4 col-12">
                                                            <label>Address Line 2</label>
                                                            <input type="text" class="form-control" name="address2" value="{{empty($vendor->address2) ? '': $vendor->address2}}" data-jqv-maxlength="100">
                                                        </div>
                                                        <div class="col-lg-4 col-md-4 col-12">
                                                            <label>Street Name/No <span style="color:red;">*<span></span></span></label>
                                                            <input type="text" class="form-control" name="street" value="{{empty($vendor->street) ? '': $vendor->street}}" data-jqv-maxlength="100" required data-parsley-required-message="Enter Street Name/No">
                                                        </div>



                                                    <div class="form-group col-md-4" style="display:none;">
                                                        <label>State/Province<b class="text-danger">*</b></label>
                                                        <select name="state_id" class="form-control"  id="city-state-id" data-role="state-change" data-input-city="city-id">
                                                            <option value="0">Select</option>
                                                            @foreach ($states as $st)
                                                                <option  @if($id) @if($datamain->state_id==$st->id) selected @endif @endif value="{{$st->id}}">{{$st->name}}</option>
                                                            @endforeach

                                                        </select>
                                                    </div>

                                                  

                                                        <div class="col-lg-4 col-md-4 col-12">
                                                            <label>Zip <span style="color:red;">*<span></span></span></label>
                                                            <input type="text" class="form-control" name="zip" value="{{empty($vendor->zip) ? '': $vendor->zip}}" data-jqv-maxlength="10" required data-parsley-required-message="Enter Zip code">
                                                            <div class="error"></div>
                                                        </div>

                                                        <div class="col-sm-4 col-xs-12">
                                                        <div class="form-group d-flex align-items-center">
                                                            <div>
                                            <!-- data-parsley-imagedimensions="200x200" -->
                                                            <label>Logo <span style="color:red;">*<span></span></span></label>
                                                            <input type="file" class="form-control jqv-input" name="logo" data-role="file-image" data-preview="logo-preview" value="" @if(empty($id)) required data-parsley-required-message="Logo is required" @endif   data-parsley-trigger="change">
                                                            <p class="text-muted">Allowed Dim 200x200(px)</p>
                                                            </div>
                                                                <img id="logo-preview" class="img-thumbnail w-50" style="margin-left: 5px; height:50px; width:50px !important;" src="{{empty($vendor->logo) ? asset('uploads/company/17395e3aa87745c8b488cf2d722d824c.jpg'): $vendor->logo}}">
                                                            </div>
                                                        </div>
                                                        </div>
                                                        <div class="row">



                                                    </div>

                                                    <div class="row" >
                                                        <div class="col-sm-12 col-xs-12">
                                                         <div class="form-group d-flex align-items-center">
                                                            <div>
                                                            <label>Cover Image <span style="color:red;">*<span></span></span></label>
                                                             <!-- data-parsley-imagedimensions="1170x525" -->
                                                            <input type="file" class="form-control jqv-input" name="cover_image" data-role="file-image" data-preview="cover-preview" value="" @if(empty($id)) required data-parsley-required-message="Cover image is required" @endif  data-parsley-trigger="change" >
                                                            <p class="text-muted">Allowed Dim 1170x525(px)</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12" >
                                                        <img id="cover-preview" style="margin-left: 5px; height:200px; width:500px !important;"  class="img-thumbnail img-fluid" src="{{empty($vendor->cover_image) ? asset('uploads/company/17395e3aa87745c8b488cf2d722d824c.jpg'): asset($vendor->cover_image)}}">
                                                    </div>
                                                   @if(!empty($datamain->activity_id)) @if($datamain->activity_id == 6 || $datamain->activity_id == 4 || $datamain->activity_id == 1) 
                                                    <div class="col-sm-4 col-xs-12 other_docs mt-3" id="certificate_product_registration_div" >
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                </div>
                                            </div>
                                            @endif
                                            @endif
                                            @if(empty($datamain->id))
                                            <div class="col-sm-4 col-xs-12 other_docs mt-3" id="certificate_product_registration_div" >
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                </div>
                                            @endif
                                            </div>

                                            
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if(!empty($datamain->activity_id) && $datamain->activity_id != 6 && $datamain->activity_id != 4 && $datamain->activity_id != 1)

                                        <div class="card mt-4 weekly" style="border-radius: 5px; overflow: hidden;" >
                                            <div class="card-body">
                                                <h4><b>Store Timing</b></h4>
                                                <table class="table table-condensed pl-2 mt-2 workinghours align-top d-flex" >
                                                   
                                                        @foreach($days as $key => $val)
                                                            @php 
                                                                $st = $key.'_from'; $ed = $key.'_to';
                                                                $timings = $selected_days[$val]??[];
                                                                //printr($timings);
                                                            @endphp
                                                            <tr class="align-top">
                                                                <td class="align-top" style="max-width:150px; width:150px">
                                                                    <div class="custom-checkbox" style="margin-top: 10px;">
                                                                        <input type="checkbox" class="week_days" id="grm_day_{{$val}}"  name="{{$val}}_grooming" value="1" @if(!empty($timings) && !empty($timings['from_hours']))  checked @endif> &nbsp;
                                                                        <label for="grm_day_{{$val}}"> {{ucfirst($val)}}</label>
                                                                    </div>
                                                                
                                        
                                                                </td>
                                                                @php
                                                                    $style = '';
                                                                    if(!empty($timings)){
                                                                        if(empty($timings['from_hours']) || $timings['open_24']==1 ){
                                                                            $style='none';
                                                                        }
                                                                    }
                                                                @endphp
                                                                <td class="align-top" style="max-width:150px; width:150px; display:{{(!empty($timings) && !empty($timings['from_hours']) )?'':'none'}};">
                                                                    <div class="custom-checkbox" style="margin-top: 10px;">
                                                                        <input @if(!empty($timings)) @if($timings['open_24']==1) checked @endif @endif type="checkbox" class="24_hour_open" id="grm_day_24_{{$val}}"  name="{{$val}}_24" value="1"> &nbsp;
                                                                        <label for="grm_day_24_{{$val}}"> 24 Hour</label>
                                                                    </div>
                                                                
                                        
                                                                </td>
                                                                <td data-sec-name="{{ucfirst($val)}}" class="{{$key}}-from-div from-td align-top days-div" style="display:{{$style;}};">
                                                                    @if(!empty($timings['from_hours']))
                                                                        @foreach($timings['from_hours'] as $index=>$time_key)
                                                                        <input type="text"  class="time from_time form-control r-{{$index}}" data-parsley-required id="{{$key}}_from_grooming"  name="{{$key}}_from_grooming[]" value="{{$time_key}}"  placeholder="Start Time">
                                                                        @endforeach
                                                                    @else
                                                                    <input type="text"  class="time from_time form-control" data-parsley-required id="{{$key}}_from_grooming"  name="{{$key}}_from_grooming[]" value=""   placeholder="Start Time">
                                                                    @endif
                                                                </td>
                                                                <td data-sec-name="{{ucfirst($val)}}" class="{{$key}}-to-div to-td align-top days-div" style="display:{{$style;}};">
                                                                    @if(!empty($timings['to_hours']))
                                                                        @foreach($timings['to_hours'] as $index=> $time_key2)
                                                                            <input type="text" value="{{$time_key2}}"  class="time to_time form-control r-{{$index}}" data-parsley-required data-parsley-daterangevalidation{{$key}} data-parsley-daterangevalidation{{$key}}-requirement="#{{$key}}_from_grooming"  data-parsley-greater-than-message="End time should be after start time" name="{{$key}}_to_grooming[]"  placeholder="End Time">
                                                                        @endforeach
                                                                    @else
                                                                    <input type="text"   class="time to_time form-control" data-parsley-required data-parsley-daterangevalidation{{$key}} data-parsley-daterangevalidation{{$key}}-requirement="#{{$key}}_from_grooming"  data-parsley-greater-than-message="End time should be after start time" name="{{$key}}_to_grooming[]"  placeholder="End Time">
                                                                    @endif
                                                                </td>
                                                                <td data-sec-name="{{ucfirst($val)}}" class="{{$key}}-btn-div align-top" style="display:{{$style;}};">
                                                                    <button type="button" class="btn btn-primary time-add-btn " style="padding:0 !important;" data-val="{{$key}}"  data-name="{{$key}}_to_grooming">+</button>
                                                                    @php 
                                                                    for($i=0;$i< count($timings['from_hours'])-1; $i++){
                                                                    @endphp
                                                                    <button type="button" data-k="{{$key}}" data-rem="r-{{$i+1}}" style="padding:0 !important;" class="btn btn-danger rm-btn time_remove_btn_1">-</button>
                                                                    @php
                                                                    }
                                                                    @endphp
                                                                </td>
                                                                
                                                            </tr>
                                                            
                                                        @endforeach
                                                </table>
                                            </div>

                                            <div class="col-sm-4 col-xs-12 other_docs mt-3" id="certificate_product_registration_div" >
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                </div>
                                            </div>
                                        </div>



                               @endif





                            </div>
                </form>
                </div>
@stop

@section('script')
        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAvYQkf-70Ka1kpQnAy2DB2-KB36RqMF8o&v=weekly&libraries=places"></script>
        <script src="//jonthornton.github.io/jquery-timepicker/jquery.timepicker.js"></script>
    <script>
        $("body").on("click",".modal-footer .btn-primary",function() {
          $('#password').val("");
        });

        function validatePassword(password) {
            // Regular expression pattern to match the criteria
            var pattern = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            // Explanation of the pattern:
            // (?=.*[A-Za-z]) - At least one alphabet
            // (?=.*\d) - At least one digit
            // (?=.*[@$!%*?&]) - At least one special character
            // [A-Za-z\d@$!%*?&]{8,} - Allowed characters with a minimum length of 8
            
            // Check if the password matches the pattern
            return pattern.test(password);
        }
        $('#password').change(function(){
            if (!validatePassword($(this).val())) {
                App.alert("Password must contain minimum 8 characters at least 1 alphabet, 1 number , 1 special character without #");
               
            }
        });
        function generateUniqueString() {
        var timestamp = new Date().getTime(); // Get current timestamp
        var randomString = Math.random().toString(36).substring(2, 8); // Generate random string
        return timestamp + '-' + randomString; // Combine timestamp and random string
        }
        $('body').off("click",'.rm-btn');
        $('body').on("click",'.rm-btn',function(){
            let id = $(this).attr("data-rem");
            let day = $(this).attr("data-k");
            $(this).parent().siblings('.'+day+'-from-div').find('.'+id).remove();
            $(this).parent().siblings('.'+day+'-to-div').find('.'+id).remove();
            $(this).remove();
        });
        $('body').off("click",'.remove-btn');
        $('body').on("click",'.remove-btn',function(){
            let id = $(this).attr("data-rem");
            
            $('.'+id).remove();
            $(this).remove();
        });
        $('body').off('click','.time-add-btn');
        $('body').on('click','.time-add-btn',function(e){
            
            e.preventDefault();
            let key_val = $(this).attr("data-val");
            let name = $(this).attr("data-name");
            var uniqueString = generateUniqueString();
            var from_html = `<input type="text"   class="time form-control from_time data-parsley-required rem-`+uniqueString+`"   name="`+key_val+`_from_grooming[]"   placeholder="Start Time">`;
            var to_html = `<input type="text"  class="time form-control to_time data-parsley-required rem-`+uniqueString+`"   name="`+key_val+`_to_grooming[]"   placeholder="End Time">`;
            var btn_html = `<button type="button" data-rem="rem-`+uniqueString+`" class="btn btn-danger remove-btn time_remove_btn_1" style="padding:0 !important;">-</button>`;
            
            $(`.`+key_val+'-from-div').append(from_html);
            $(`.`+key_val+'-to-div').append(to_html);
            $(`.`+key_val+'-btn-div').append(btn_html);
            $('.time').timepicker({
                timeFormat:'h:i A',
                step: '60'
            });
        });
        $(document).on('click','.del-menu-img',function(){ 
            var image = $(this).attr('data-image-file');
            var $target = $(this).closest('div.uploaded-prev-imd');
            var id = $(this).data('id');
            if(image!="") { 
                $.ajax({
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    enctype: 'multipart/form-data',
                    url: '{{ url("admin/vendor/removevendorMenuImage")}}',
                    data: { 'image': image, 'id':id },
                    dataType: 'json',
                    success: function(data) {
                        if ( data['status'] == 0 ) {
                            var msg = data['message']||'Unable to remove image. Please try again later.';
                            App.alert([msg, 'warning']);
                        } else {
                            App.alert(['Done! Image removed successfully.', 'success']);
                            $(this).parent().find('.uploaded-prev-imd').remove();
                            $target.remove();
                        }
                    }
                }) 
            }else { 
                $target.remove();
                $(this).parent().find('.uploaded-prev-imd').remove();
            }  
        })
         $(document).on('change','#activity_id',function(){ 
            $('.is_food').addClass('d-none');
            if($(this).val() == '5'){
                $('.is_food').removeClass('d-none');
            }
         })


        $(document).ready(function() {
            $('select').select2();
        });


        $('.time').timepicker({
            timeFormat:'h:i A',
            step: '60'
        });

        var currentLat = {{empty($vendor->latitude) ? 25.204819: $vendor->latitude}};
        var currentLong = {{empty($vendor->longitude) ? 55.270931: $vendor->longitude}};
        $("#location").val(currentLat+","+currentLong);

        currentlocation = {
            "lat": currentLat,
            "lng": currentLong,
        };
        initMap();
        initAutocomplete();
        function initMap() {
        map2 = new google.maps.Map(document.getElementById('map_canvas'), {
            center: {
                lat: currentlocation.lat,
                lng: currentlocation.lng
            },
            zoom: 14,
            gestureHandling: 'greedy',
            mapTypeControl: false,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
            },
            streetViewControlOptions: {
                position: google.maps.ControlPosition.LEFT_BOTTOM
            },
        });

        geocoder = new google.maps.Geocoder();

        // geocoder2 = new google.maps.Geocoder;
        usermarker = new google.maps.Marker({
            position: {
                lat: currentlocation.lat,
                lng: currentlocation.lng
            },
            map: map2,
            draggable: true,

            animation: google.maps.Animation.BOUNCE
        });


        //map click
        google.maps.event.addListener(map2, 'click', function(event) {
            updatepostition(event.latLng, "movemarker");
            //drag end event
            usermarker.addListener('dragend', function(event) {
                // alert();
                updatepostition(event.latLng, "movemarker");

            });
        });

        //drag end event
        usermarker.addListener('dragend', function(event) {
            // alert();
            updatepostition(event.latLng);

        });
    }
    updatepostition = function(position, movemarker) {
        geocodePosition(position);
        usermarker.setPosition(position);
        map2.panTo(position);
        map2.setZoom(15);
        let createLatLong = position.lat()+","+position.lng();
        console.log("Address Lat/long="+createLatLong);
        $("#location").val(createLatLong);
    }
    function geocodePosition(pos) {
        geocoder.geocode({
            latLng: pos
        }, function(responses) {
            if (responses && responses.length > 0) {
                usermarker.formatted_address = responses[0].formatted_address;
            } else {
                usermarker.formatted_address = 'Cannot determine address at this location.';
            }
            $('#txt_location').val(usermarker.formatted_address);
        });
    }
    function initAutocomplete() {
        // Create the search box and link it to the UI element.
        var input2 = document.getElementById('txt_location');
        var searchBox2 = new google.maps.places.SearchBox(input2);

        map2.addListener('bounds_changed', function() {
            searchBox2.setBounds(map2.getBounds());
        });

        searchBox2.addListener('places_changed', function() {
            var places2 = searchBox2.getPlaces();

            if (places2.length == 0) {
                return;
            }
            $('#txt_location').val(input2.value)

            var bounds2 = new google.maps.LatLngBounds();
            places2.forEach(function(place) {
                if (!place.geometry) {
                    console.log("Returned place contains no geometry");
                    return;
                }

                updatepostition(place.geometry.location);

                if (place.geometry.viewport) {
                    // Only geocodes have viewport.
                    bounds2.union(place.geometry.viewport);
                } else {
                    bounds2.extend(place.geometry.location);
                }
            });
            map2.fitBounds(bounds2);
        });
    }
    updatepostition = function(position, movemarker) {
        console.log(position);
        geocodePosition(position);
        usermarker.setPosition(position);
        map2.panTo(position);
        map2.setZoom(15);
        let createLatLong = position.lat()+","+position.lng();
        // console.log("Address Lat/long="+createLatLong);
        $("#location").val(createLatLong);
    }
        App.initFormView();
        // $(document).ready(function() {
        //     $('select').select2();
        // });
        $(document).ready(function() {
            $('#activity_id').select2();
            $('#country').select2();
        });

        $(document).ready(function() {
            $('#city-state-id').select2();
        });
        $(document).ready(function() {
            $('#city-id').select2();
            $('#cityid').select2();
        });
        $(document).ready(function() {
            $('.industrytype-select').select2();
        });
        $(document).ready(function() {
            $('#bankcountry').select2();
        });
        $(document).ready(function() {
            $('.dial_code-select').select2();
        });
        $(document).ready(function() {
            $('.bank_id-select').select2();
        });
        $(document).ready(function() {
            $('#identity_file_name_1').select2();
        });
        $(document).ready(function() {
            $('#identity_file_name_2').select2();
        });
        $(document).ready(function() {
            $('#company_identity_value').select2();
        });
        $(document).ready(function() {
            $('#residential_proff_value').select2();
        });
        $('body').off('change','.from_time');
        $('body').on('change','.from_time',function(){
            let fromArray = [];
            let toArray   = [];

            let timeTd = $(this).parent();
            let timeDiv = jQuery(timeTd).find(".time");
            jQuery(timeDiv).each(function(indexTime, currentTimeElement) {
                fromArray.push(jQuery(currentTimeElement).val());
            });

            let timeTd2 = timeTd.siblings().find('.to-td');
            let timeDiv2 = timeTd2.find(".time");
            console.log(timeDiv2);
            jQuery(timeDiv2).each(function(indexTime, currentTimeElement) {
                toArray.push(jQuery(currentTimeElement).val());
            });

            console.log(fromArray);
            console.log(toArray);
        });
        $('body').off('submit', '#admin-form');
        $('body').on('submit', '#admin-form', function(e) {
            e.preventDefault();
            let continue_more = 1;
            jQuery('table.workinghours tr').each(function(index, currentElement) {
                if( jQuery(currentElement).find('.24_hour_open').prop("checked") == false ){
                        let fromArray = [];
                        let toArray   = [];
                        
                        let timeTd = jQuery(currentElement).find('.from-td');
                        let timeDiv = jQuery(timeTd).find(".time");
                        let section_name = $(timeTd).attr("data-sec-name");
                        jQuery(timeDiv).each(function(indexTime, currentTimeElement) {
                            fromArray.push(jQuery(currentTimeElement).val());
                        });

                        let timeTd2 = jQuery(currentElement).find('.to-td');
                        let timeDiv2 = jQuery(timeTd2).find(".time");
                        jQuery(timeDiv2).each(function(indexTime, currentTimeElement) {
                            toArray.push(jQuery(currentTimeElement).val());
                        });

                        console.log(fromArray);
                        console.log(toArray);

                        jQuery(fromArray).each(function(ind, val) {
                            if(ind==0){
                                
                            }else{
                                console.log(val);
                                jQuery(fromArray).each(function(ind2, val2) {
                                    console.log(ind,ind2);
                                    if(ind2 < ind){
                                        console.log(val2,toArray[ind2]);
                                        var startDate = new Date("November 13, 2013 " + val2);
                                        var endDate = new Date("November 13, 2013 " + toArray[ind2]);
                                        var fromDate = new Date("November 13, 2013 " + val);
                                        console.log(startDate.getTime(),endDate.getTime(),fromDate.getTime());
                                        if(fromDate.getTime() >=  startDate.getTime() && fromDate.getTime() <= endDate.getTime()){
                                            App.alert("Please check the time slots selected for "+section_name);
                                            continue_more=0;
                                            return false;
                                        }
                                    }
                                });
                            }
                        });
                }
                //console.log(jQuery(currentElement).find('.from-td').val());
            });
            
            $( ".invalid-feedback" ).remove();
            if(continue_more == 1){
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
                                window.location.href = App.siteUrl('/admin/vendors');
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
            }
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

$( '.flatpickrtime' ).flatpickr({
    noCalendar: true,
    enableTime: true,
    dateFormat: 'h:i K'
  });
  $(document).ready(function() {
   $('#vendor_commission').keyup(function(){
                let vc = $(this).val();
                let c = 100 - parseFloat(vc);
                if(c > 0){
                    $('.cvalue').val(c);
                }else{
                    $('.cvalue').val(0);
                }
            })
});
$('body').off('click', '.week_days');
$('body').on('click', '.week_days', function(e) {
    if(!$(this).is(':checked'))
    {
        $(this).closest('td').siblings().hide();
    }
    else
    {
        $(this).closest('td').siblings().show();
    }
});
$('body').off('click', '.24_hour_open');
$('body').on('click', '.24_hour_open', function(e) {
    if($(this).is(':checked'))
    {
        $(this).closest('td').siblings().nextAll().hide();
        $(this).closest('td').show();
    }
    else
    {
        $(this).closest('td').siblings().nextAll().show();
    }
});
$("#is_dinein").on('click', function(e){
    if(!$(this).is(':checked'))
    {
        // $(this).closest('td').siblings().hide();
        $(".shown-on-click").hide();
    }
    else
    {
        $(".shown-on-click").show();
    }
});
$("#is_delivery").on('click', function(e){
    if(!$(this).is(':checked'))
    {
        // $(this).closest('td').siblings().hide();
       // $(".shown-on-click_button").hide();
    }
    else
    {
       // $(".shown-on-click_button").show();
    }
});
$('body').off('keyup change', '.time_selected');
$('body').on('keyup change', '.time_selected', function(e) {
    
});
$('#admin-form').parsley({
  excluded: 'input[type=button], input[type=submit], input[type=reset], :hidden'
});
@foreach($days as $key => $val)
window.Parsley.addValidator('daterangevalidation{{$key}}', {
  validateString: function (value, requirement) {
    
    var date1 = convertFrom12To24Format(value);
    var date2 = convertFrom12To24Format($('#{{$key}}_from_grooming').val());

    return date1 > date2;
  },
  messages: {
    en: 'End time should be after start time'
  }
});
@endforeach

const convertFrom12To24Format = (time12) => {
  const [sHours, minutes, period] = time12.match(/([0-9]{1,2}):([0-9]{2}) (AM|PM)/).slice(1);
  const PM = period === 'PM';
  const hours = (+sHours % 12) + (PM ? 12 : 0);

  return `${('0' + hours).slice(-2)}:${minutes}`;
}
    </script>

    <script>
    $(document).ready(function() {
        $('#togglePassword').click(function() {
            var passwordInput = $('#password');
            var fieldType = passwordInput.attr('type');
            passwordInput.attr('type', fieldType === 'password' ? 'text' : 'password');
            $(this).find('svg').toggleClass('fa-eye fa-eye-slash');
        });
    });
</script>


<script>
    $(document).ready(function() {
        $('#togglePassword1').click(function() {
            var passwordInput = $('#confirm_password');
            var fieldType = passwordInput.attr('type');
            passwordInput.attr('type', fieldType === 'password' ? 'text' : 'password');
            $(this).find('svg').toggleClass('fa-eye fa-eye-slash');
        });
    });
</script>



@stop
