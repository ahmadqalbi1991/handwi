@extends('admin.layouts.master')
@section('content')
    <link rel="stylesheet" href="{{asset('frontend-assets\css\jquery.timepicker.min.css')}}"
          integrity="sha512-GgUcFJ5lgRdt/8m5A0d0qEnsoi8cDoF0d6q+RirBPtL423Qsj5cI9OxQ5hWvPi5jjvTLM/YhaaFuIeWCLi6lyQ=="
          crossorigin="anonymous"/>
    <link href=
              'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/
              ui-lightness/jquery-ui.css'
          rel='stylesheet'>

          <style>
            .cover_preview img{
                /* height: 90px !important;
                width: 150px !important;
                border-radius: 10px;
                border: 1px solid #ccc;
                object-fit: cover;
                padding: 2px; */
                height: 90px !important;
                width: 150px !important;
                border-radius: 10px;
                object-fit: cover;
                border: 1px solid #ccc;
                padding: 2px;
                border-radius: 5px;
            }
            .image_preview img{
                height: 90px !important;
                width: 90px !important;
                border-radius: 50%;
                object-fit: cover;
                border: 1px solid #ccc;
                padding: 5px;
            }
            .new_val{
                color:green;
            }
            .remove_updation{
                position: absolute;
                right: 0;
                background: #CA2124;
                width: 25px;
                height: 25px;
                display: flex;
                align-items: center;
                justify-content: center;
                top: 0;
                color: #fff !important;
                padding: 12px;
                border-radius: 5px;
                font-size: 13px;
            }
            .passport_preview, .emirates_preview, .license_preview, .account_preview, .visa_preview {
                margin-right: 10px;
            }
          </style>
    <div class="container">
        <div class="page-header page-header_custom">
            <div class="page-title">
                <h3>{{ $page_heading }}</h3>
            </div>
            <a href="javascript:history.back()"><button class="btn btn-secondary gobackbtn"><i class='bx bx-chevron-left'></i> Back</button></a>
        </div>
        @php $new_updation = []; @endphp
        @if(isset($customer) && $customer->new_updation!=null)
            @php
                $new_updation = (array)json_decode($customer->new_updation);
            @endphp
        @endif
        <div class="row layout-spacing ">

            <div class="widget-content widget-content-area d-block">
                <div class="">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12  layout-spacing">
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissable custom-danger-box" style="margin: 15px;">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                <strong> {{ session('error') }} </strong>
                            </div>
                        @endif
                        <div class="">
                            <div class="" style="height: inherit">
                                <form method="post" action="{{ route('admin.chef.save') }}" id="admin-form"
                                      enctype="multipart/form-data" data-parsley-validate="true">
                                    @csrf
                                    <input type="hidden" name="id" id="id" value="{{ $customer->id }}">


                                    <div class="card" style="border-radius: 5px; overflow: hidden;">
                                        <h5 class="card-title font-weight-bold" style="padding: 10px; margin: 0; line-height: normal; width: fit-content; border-bottom-right-radius: 10px;">Information</h5>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-xl-3 col-lg-6 pr-lg-1 mb-2">
                                                    <label class="control-label">Brand Name *</label>
                                                    <input type="text" class="form-control jqv-input" name="brand_name" id="brand_name" data-jqv-required="true" required
                                                data-parsley-required-message="Enter Brand Name"
                                                        value="@if(isset($restaurent) && !empty($restaurent->brand_name)){{$restaurent->brand_name}}@elseif(!empty($new_updation) && array_key_exists('brand_name',$new_updation)){{$new_updation['brand_name']}}@endif">
                                                    @if((!empty($new_updation) && array_key_exists('brand_name',$new_updation)) && $new_updation['brand_name']!="" && $restaurent->brand_name!="")

                                                    <span class="new_val d-block mt-2 mb-2">
                                                            <input type="text" value="{{$new_updation['brand_name']}}" name="new_brand_name" class="form-control bg-light">
                                                    </span>
                                                @endif
                                                </div>

                                                <div class="col-xl-3 col-lg-6 pl-lg-1">
                                                    <label class="control-label">Brand Name AR*</label>
                                                    <input type="text" class="form-control jqv-input text-right" value="@if(isset($restaurent) && !empty($restaurent->brand_name_ar)){{$restaurent->brand_name_ar}} @endif" name="brand_name_ar" id="brand_name_ar" required>
                                                    @if((!empty($new_updation) && array_key_exists('brand_name_ar',$new_updation)) && $new_updation['brand_name_ar']!="" && $restaurent->brand_name_ar!="")

                                                    <span class="new_val d-block mt-2 mb-2">
                                                            <input type="text" value="{{$new_updation['brand_name_ar']}}" name="new_brand_name_ar" class="form-control bg-light">
                                                    </span>
                                                @endif
                                                </div>

                                                <div class="col-xl-3 col-lg-6 pr-lg-1 form-group">

                                                    <label for="t-text">First Name *</label>
                                                    <input type="text" data-jqv-required="true" name="first_name" id="first_name" required data-parsley-required-message="Enter First Name" value="{{ $customer->first_name }}" class="form-control   jqv-input" placeholder="Enter First Name">

                                                    @if(!empty($new_updation) && array_key_exists('first_name',$new_updation) && $new_updation['first_name']!=null)

                                                        <span class="new_val d-block mt-2 mb-2">
                                                                <input type="text" value="{{$new_updation['first_name']}}" name="new_first_name" class="form-control bg-light">
                                                        </span>
                                                    @endif
                                                </div>


                                                <div class="col-xl-3 col-lg-6 pl-lg-1 form-group">
                                                    <label for="t-text">Last Name *</label>
                                                    <input type="text" data-jqv-required="true" name="last_name" id="last_name"
                                                        value="{{ $customer->last_name }}" required
                                                data-parsley-required-message="Enter Last Name"
                                                        class="form-control   jqv-input"
                                                        placeholder="Enter last Name">

                                                    @if(!empty($new_updation) && array_key_exists('last_name',$new_updation) && $new_updation['last_name']!="")
                                                        <span class="new_val d-block mt-2 mb-2">
                                                                <input type="text" value="{{$new_updation['last_name']}}" name="new_last_name" class="form-control bg-light">
                                                        </span>

                                                    @endif
                                                </div>

                                                


                                                @if($customer->id ==null)
                                                    <div class="col-md-6 form-group">
                                                        <label for="t-text">Email *</label>
                                                        <input data-jqv-required="true" type="email" name="email" id="email"
                                                            value="{{ $customer->email }}" required
                                                data-parsley-required-message="Enter Email" class="form-control jqv-input"
                                                            placeholder="Enter Email">
                                                    </div>
                                                    <div class="col-md-6 form-group" id="show_hide_password">
                                                        <label for="t-text">Password @if ($customer->id == null)
                                                                *
                                                            @endif
                                                        </label>
                                                        <div class="input-group" id="show_hide_password">
                                                            <input type="password" name="password" id="password" required
                                                data-parsley-required-message="Enter Password"
                                                                class="form-control jqv-input" placeholder="Enter Password"
                                                                @if ($customer->id == null) required @endif>
                                                            <div class="input-group-addon">
                                                                <a href="#"><i class="flaticon-view" onclick="togglePass()"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif


                                                <div class="col-md-2 pr-lg-1 form-group">
                                                    <label>Country Code *</label>
                                                    <select class="custom-select  jqv-input" name="dial_code"
                                                                id="dial_code">
                                                            <option value="">Select Country Code</option>
                                                            @foreach ($codes as $country)
                                                                <option value="+{{ $country->country_dial_code }}"
                                                                    {{ $country->country_dial_code == $customer->dial_code ? 'selected' : ($country->country_dial_code == 971 ? 'selected' : '') }}>
                                                                    +{{ $country->country_dial_code }}</option>
                                                            @endforeach
                                                    </select>
                                                    
                                                </div>
                                                <div class="col-md-4 pl-lg-1 form-group">
                                                    <label for="t-text">Phone *</label>
                                                    <input type="number" name="phone_number" id="phone_number" required
                                                data-parsley-required-message="Enter Phone" data-parsley-minlength-message="Enter 9 digit phone number"
                                                data-parsley-maxlength-message="Enter 9 digit phone number"
                                                        value="{{ $customer->phone_number }}"
                                                        class="form-control number jqv-input"
                                                        placeholder="Enter Phone" data-parsley-minlength="9" data-parsley-maxlength="9"
                                                        oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');">
                                                </div>
                                                <div class="col-md-2 pr-lg-1 form-group">
                                                    <label for="t-text">Gender *</label>
                                                    <div class="select2-error">
                                                        <select data-jqv-required="true" class="custom-select mb-4 select-nosearch" name="gender" required data-parsley-required-message="Select Gender"
                                                                id="gender">
                                                            <option value="">Select</option>
                                                            <option value="1" {{ $customer->gender == 1 ? 'selected' : '' }}>Male
                                                            </option>
                                                            <option value="2" {{ $customer->gender == 2 ? 'selected' : '' }}>Female
                                                            </option>
                                                            <option value="3" {{ $customer->gender == 3 ? 'selected' : '' }}>Others
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 form-group pl-lg-1 pr-lg-1">
                                                    <label for="t-text">Status</label>
                                                    <select data-jqv-required="true" class="custom-select form-control mb-4 select-nosearch" name="status"
                                                            id="status" data-select2-id="activeinactive">
                                                        <option value="1" {{ $customer->status == 1 ? 'selected' : '' }}>Active
                                                        </option>
                                                        <option value="0" {{ $customer->status == 0 ? 'selected' : '' }}>Inactive
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2 form-group pl-lg-1">
                                                    <label for="t-text">Available</label>
                                                    <select data-jqv-required="true" class="custom-select mb-4 select-nosearch" name="available"
                                                            id="status" data-select2-id="available">
                                                        <option value="1" {{ $customer->available == 1 ? 'selected' : '' }}>Yes
                                                        </option>
                                                        <option value="0" {{ $customer->available == 0 ? 'selected' : '' }}>No
                                                        </option>
                                                    </select>
                                                </div>
                                                @php $newcuisine = []; $show_cusn_update =1; @endphp
                                                @if(array_key_exists('cuisine_id',$new_updation))
                                                    @php $newcuisine = explode(",",$new_updation['cuisine_id'])  @endphp
                                                @endif
                                                @if(empty($selectedCuisines) && !empty($newcuisine) )
                                                @php $selectedCuisines = $newcuisine; $show_cusn_update = 0 ;  @endphp 
                                                @endif

                                                <div class="col-md-6 col-sm-12 form-group">
                                                    <label for="" class="form-label">Cuisine *</label>
                                                    <div class="select2-customdiv">
                                                        <select class="custom-select  select2-cusinine" required name="cousine[]" multiple required data-parsley-required-message="Select Cuisine">
                                                           
                                                            @foreach($cuisine as $value)
                                                            <option value="{{$value->id}}" @if(in_array($value->id,$selectedCuisines)) selected="selected" @endif>{{$value->cuisine_name}}</option> 
                                                            @endforeach
                                                        </select>
                                                    </div> 
                                                    @if(array_key_exists('cuisine_id',$new_updation) && $show_cusn_update == 1)
                                                    @php $newcuisine = explode(",",$new_updation['cuisine_id'])  @endphp
                                                    <select class="custom-select  select2-cusinine" name="new_cousine[]" multiple required data-parsley-required-message="Select Cuisine">
                                                            
                                                            @foreach($cuisine as $value)
                                                            <option value="{{$value->id}}" @if(in_array($value->id,$newcuisine)) selected="selected" @endif>{{$value->cuisine_name}}</option> 
                                                            @endforeach
                                                        </select>

                                                    @endif
                                                </div>

                                            </div>
                                        </div>
                                    </div>


                                    <div class="card mt-3" style="border-radius: 5px; overflow: hidden;">
                                        <h5 class="card-title font-weight-bold" style="padding: 10px; margin: 0; line-height: normal; width: fit-content; border-bottom-right-radius: 10px;">Other Information</h5>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 form-group">
                                                    <label>License Expiration Date *</label>
                                                    <input type="text" class="form-control datepick" name="license_expiry" readonly style="background:white;cursor: default;color: black;" required
                                                data-parsley-required-message="Select License Expiration Date"
                                                        value="@if($customer->maintenance_expiry != '1970-01-01'){{$customer->license_expiry}}@endif">
                                                </div>

                                                <div class="col-md-6 form-group">
                                                    <label>Maintenance Expiration Date *</label>
                                                    <input type="text" class="form-control datepick" name="maintenance_expiry" readonly style="background:white;cursor: default;color: black;" required
                                                data-parsley-required-message="Select Maintenance Expiration Date"
                                                        value="@if($customer->maintenance_expiry != '1970-01-01'){{$customer->maintenance_expiry}}@endif">
                                                </div>

                                                <div class="col-md-6 form-group">
                                                    <label>Agreement Expiration Date *</label>
                                                    <input type="text" class="form-control datepick" name="agreement_expiry" readonly style="background:white;cursor: default;color: black;" required
                                                data-parsley-required-message="Select Agreement Expiration Date"
                                                        value="@if($customer->maintenance_expiry != '1970-01-01'){{$customer->agreement_expiry}}@endif">
                                                </div>

                                                <div class="col-md-3 pr-lg-0">

                                                    <label class="control-label">Preperation Time *</label>
                                                    <div class="row">
                                                        <div class="col-sm-6 pl-lg-0">

                                                            <input type="text" class="form-control" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" name="preparation_unit"
                                                                placeholder="from" data-parsley-min="1" required
                                                data-parsley-required-message="Enter Preperation Time"
                                                                value="{{ $restaurent->preparation_unit }}">
                                                        </div>
                                                        <div class="col-sm-6 pl-lg-0">
                                                            <input type="text"  class="form-control" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" name="preparation_unit_to"
                                                                placeholder="to"
                                                                value="{{ $restaurent->preparation_unit_to }}">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 pl-lg-0">
                                                    <label class="control-label d-block"></label>
                                                    <br>
                                                    <select name="preparation_time" class="form-control select-nosearch"
                                                            >
                                                        <option value="mins"
                                                                @if(isset($restaurent) &&$restaurent->preparation_time=='mins') selected @endif>
                                                            Mins
                                                        </option>
                                                        <option value="hour"
                                                                @if(isset($restaurent) &&$restaurent->preparation_time=='hour') selected @endif>
                                                            Hour
                                                        </option>
                                                        <option value="day"
                                                                @if(isset($restaurent) &&$restaurent->preparation_time=='day') selected @endif>
                                                            Day
                                                        </option>
                                                    </select>
                                                </div>

                                                <div class="col-md-6 mb-2">
                                                    <label class="control-label">Order Limit Per Hour</label>
                                                    <input type="text" class="form-control" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" name="order_limit_per_hour" required
                                                data-parsley-required-message="Enter Order Limit Per Hour"
                                                        value="@if(isset($restaurent)){{$restaurent->order_limit_per_hour}}@endif">
                                                    @if(!empty($new_updation) && array_key_exists('order_limit_per_hour',$new_updation) && $new_updation['order_limit_per_hour'] > 0)

                                                    <span class="new_val d-block mt-2 mb-2">
                                                            <input type="text" value="{{$new_updation['order_limit_per_hour']}}" name="new_order_limit_per_hour" class="form-control bg-light">
                                                    </span>

                                                @endif

                                                </div>

                                                <div class="col-md-6 mb-2">
                                                    <label class="control-label">TOM's Margin (%)</label>
                                                    <input type="text" class="form-control" name="admin_commission"  required
                                                data-parsley-required-message="Enter TOM's Margin (%)"
                                                        value="{{$customer->admin_commission??$config->admin_commission}}">
                                                </div>

                                                <div class="col-md-6 mb-2">
                                                    <label class="control-label">Delivery Fee</label> 
                                                    <input type="text" class="form-control" name="delivery_fee" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" required
                                                data-parsley-required-message="Enter Delivery Fee"
                                                        value="{{$customer->delivery_fee??$config->delivery_fee}}">
                                                </div>

                                                <div class="col-md-6 mb-2">
                                                    <label class="control-label">Allow Order</label>
                                                    <select name="allow_ordertype" class="form-control select-nosearch" required
                                                data-parsley-required-message="Select Allow Order" id="allow_ordertype">
                                                        <option value="0"
                                                                @if($customer->allow_ordertype==0) selected @endif>
                                                            Both
                                                        </option>
                                                        <option value="1"
                                                                @if($customer->allow_ordertype==1) selected @endif>
                                                            Current Orders
                                                        </option>
                                                        <option value="2"
                                                                @if($customer->allow_ordertype==2) selected @endif>
                                                            Scheduled Orders
                                                        </option>
                                                    </select>
                                                    @if(!empty($new_updation) && array_key_exists('allow_ordertype',$new_updation))

                                                    <span class="new_val d-block mt-2 mb-2">
                                                        <select name="new_allow_ordertype" class="form-control select-nosearch" required
                                                data-parsley-required-message="Select Allow Order">
                                                        <option value="0"
                                                                @if(!empty($new_updation) && array_key_exists('allow_ordertype',$new_updation) && $new_updation['allow_ordertype'] == 0 ){{'selected'}}@endif>
                                                            Both
                                                        </option>
                                                        <option value="1"
                                                                @if(!empty($new_updation) && array_key_exists('allow_ordertype',$new_updation) && $new_updation['allow_ordertype'] == 1 ){{'selected'}} @endif>
                                                            Current Orders
                                                        </option>
                                                        <option value="2"
                                                                @if(!empty($new_updation) && array_key_exists('allow_ordertype',$new_updation) && $new_updation['allow_ordertype'] == 2 ){{'selected'}}@endif>
                                                            Scheduled Orders
                                                        </option>
                                                    </select>
                                                    </span>

                                                @endif


                                                </div>

                                                

                                                <div class="col-md-6 mb-2">
                                                    <label class="control-label">License Ownership</label>
                                                    <select name="license_ownership" class="form-control select-nosearch" required data-parsley-required-message="Select License Ownership">
                                                        <option value="0"
                                                                @if(!empty($new_updation) && array_key_exists('license_ownership',$new_updation) && $new_updation['license_ownership'] == 0 )@elseif($customer->license_ownership==0) selected @endif>
                                                            Unset
                                                        </option>
                                                        <option value="1"
                                                                @if(!empty($new_updation) && array_key_exists('license_ownership',$new_updation) && $new_updation['license_ownership'] == 1 )@elseif($customer->license_ownership==1) selected @endif>
                                                            Chef License
                                                        </option>

                                                        <option value="2"
                                                                @if(!empty($new_updation) && array_key_exists('license_ownership',$new_updation) && $new_updation['license_ownership'] == 2 )@elseif($customer->license_ownership==2) selected @endif>
                                                            Tom License
                                                        </option>

                                                    </select>

                                                </div>
                                                <div class="col-lg-6"></div>

                                                <div class="col-lg-6">
                                                    <label class="control-label">Description(English) *</label>
                                                    <textarea data-jqv-required="true"
                                                            class="form-control   jqv-input" type="text" placeholder=""
                                                            id="about_me" name="about_me" required data-parsley-required-message="Enter Description"
                                                    >@if($customer->about_me!=null){{ $customer->about_me }}@elseif(!empty($new_updation) && array_key_exists('about_me',$new_updation)){{$new_updation['about_me']}}@endif</textarea>

                                                    @if(!empty($new_updation) && array_key_exists('about_me',$new_updation) && $customer->about_me!=null)
                                                        <span class="new_val d-block mt-2 mb-2">
                                                            <textarea   name="about_me" class="form-control bg-light">{{$new_updation['about_me']}}</textarea>
                                                        </span>
                                                    @endif
                                                </div>

                                                <div class="col-lg-6">
                                                    <label class="control-label">Description(Arabic) *</label>

                                                    <textarea dir="rtl" class="form-control   jqv-input" type="text" placeholder="" id="about_me_ar" name="about_me_ar" required data-parsley-required-message="Enter Description(Arabic)">{{ $customer->about_me_ar }}</textarea>
                                                    @if(!empty($new_updation) && array_key_exists('about_me_ar',$new_updation) && $new_updation['about_me_ar']!=null)
                                                        <span class="new_val d-block mt-2 mb-2">
                                                            <textarea dir="rtl" name="new_about_me_ar" class="form-control bg-light">{{$new_updation['about_me_ar']}}</textarea>
                                                                </span>
                                                    @endif
                                                </div>
                                                

                                            </div>
                                        </div>
                                    </div>


                                    <div class="card mt-3" style="border-radius: 5px; overflow: hidden;">
                                        <h5 class="card-title font-weight-bold" style="padding: 10px; margin: 0; line-height: normal; width: fit-content; border-bottom-right-radius: 10px;">Images</h5>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <p> Profile Image *</p>
                                                    <input @if (!$customer->image) data-jqv-required="true" @endif type="file" class="form-control   jqv-input jqv-inputboxed formImage image_upload" preview="image_preview" id="image" for="imagePreviewHomepage" placeholder="" name="image" accept=".jpg, .png, .jpeg" value="" @if(empty($customer->id)) required data-parsley-required-message="Select Profile Image" @endif/>
                                                    <p>Accepts .jpg, .jpeg, .png formats only.</p>
                                                    <div style="display: flex; align-items: center; gap: 10px;">
                                                        @if ($customer!=null )
                                                                <div class="form-group @if(!empty($new_updation) && array_key_exists('image',$new_updation)){{'image_preview1'}}@else{{'image_preview'}}@endif">
                                                                <a data-fancybox
                                                                data-src="{{ $customer->image }}"
                                                                data-caption="" href="">
                                                                <img id="@if(!empty($new_updation) && array_key_exists('image',$new_updation)){{'image-preview1'}}@else{{'image-preview'}}@endif"
                                                                        style="height: 90px !important; width: 90px !important; border-radius: 50%; object-fit: cover; border: 1px solid #ccc; padding: 5px;"
                                                                        class="img-responsive mb-2"
                                                                        data-image="{{ $customer->image }}"
                                                                        src="{{ $customer->image }}">
                                                            </a>
                                                            </div>
                                                        @else
                                                            <div class="form-group image_preview"></div>
                                                        @endif
                                                        @if(!empty($new_updation) && array_key_exists('image',$new_updation))
                                                        <div class="form-group position-relative image_preview newfiles">
                                                            <a data-fancybox
                                                                data-src="{{public_url().$new_updation['image']}}"
                                                                data-caption="" href="">
                                                                <img id="image-preview"
                                                                        style="height: 90px !important; width: 90px !important; border-radius: 50%; object-fit: cover; border: 1px solid #ccc; padding: 5px;"
                                                                        class="img-responsive mb-2"
                                                                        data-image="{{public_url().$new_updation['image']}}"
                                                                        src="{{public_url().$new_updation['image']}}">
                                                            </a>

                                                            <a href="javascript:void(0)" class="remove_updation" ftype="image"><i class="fa fa-trash"></i></a>
                                                            </div>
                                                        @endif
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-6">
                                                        <p> Cover Image *</p>
                                                        <input  type="file" @if(empty($customer->id)) required data-parsley-required-message="Select Cover Image" @endif class="form-control   jqv-input jqv-inputboxed formImage image_upload" preview="cover_preview" id="cover_image" for="imagePreviewHomepage" placeholder="" name="cover_image" accept=".jpg, .png, .jpeg" value=""/>
                                                        <p>Accepts .jpg, .jpeg, .png formats only.</p>

                                                        <div style="display: flex; align-items: center; gap: 10px;">


                                                            @if ($customer!=null )
                                                                     <div class="form-group @if(!empty($new_updation) && array_key_exists('cover_image',$new_updation)){{'cover_preview1'}}@else{{'cover_preview'}}@endif">
                                                                        <a data-fancybox
                                                                       data-src="{{ $restaurent->cover_image }}"
                                                                       data-caption="" href="">
                                                                        <img id="@if(!empty($new_updation) && array_key_exists('cover_image',$new_updation)){{'image-preview1'}}@else{{'image-preview'}}@endif"
                                                                             style="height: 90px !important; width: 150px !important; border-radius: 10px; object-fit: cover; border: 1px solid #ccc; padding: 2px; border-radius: 5px;"
                                                                             class="img-responsive mb-2"
                                                                             data-image="@if($restaurent->cover_image){{ $restaurent->cover_image }}@else{{asset('public/defaultbanner.png')}}@endif"
                                                                             src="@if($restaurent->cover_image){{ $restaurent->cover_image }}@else{{asset('public/defaultbanner.png')}}@endif">
                                                                    </a>
                                                                    </div>
                                                                @else
                                                                    <div class="form-group cover_preview"></div>
                                                                @endif

                                                                @if(!empty($new_updation) && array_key_exists('cover_image',$new_updation))
                                                                    <div class="form-group position-relative cover_preview newfiles">
                                                                    <a data-fancybox
                                                                       data-src="{{public_url().$new_updation['cover_image']}}"
                                                                       data-caption="" href="">
                                                                        <img id="image-preview"
                                                                             style="height: 90px !important; width: 150px !important; border-radius: 10px; object-fit: cover; border: 1px solid #ccc; padding: 2px; border-radius: 5px;"
                                                                             class="img-responsive mb-2"
                                                                             data-image="{{public_url().$new_updation['cover_image']}}"
                                                                             src="{{public_url().$new_updation['cover_image']}}">
                                                                    </a>
                                                                     <a href="javascript:void(0)" class="remove_updation" ftype="cover_image"><i class="fa fa-trash"></i></a>

                                                                    </div>

                                                                @endif

                                                        </div>



                                                    </div>
                                                </div>

                                                

                                            </div>
                                        </div>
                                    
                                    



                                    <div class="card mt-3" style="border-radius: 5px; overflow: hidden;">
                                        <h5 class="card-title font-weight-bold" style="padding: 10px; margin: 0; line-height: normal; width: fit-content; border-bottom-right-radius: 10px;">Bank Information</h5>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-lg-6 form-group">
                                                    <label class="control-label">Bank *</label>
                                                    <div class="select2-error">
                                                    <select type="text" name="bank_id" id="bank_id" class="form-control jqv-input select-nosearch" placeholder="Select Bank" data-parsley-required-message="Select Bank">
                                                        <option value="">Select Bank</option>
                                                        @if (!empty($banks))
                                                            @foreach ($banks as $row)
                                                                <option value="{{ $row->id }}"
                                                                        @if ($row->id == $customer->bank_id) selected="selected" @endif>
                                                                    {{ $row->name_en }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    @if(!empty($new_updation) && array_key_exists('bank_id',$new_updation) && $new_updation['bank_id'] >0)
                                                        <span class="new_val d-block mt-2 mb-2">
                                                            <select  class="custom-select  jqv-input" name="new_bank_id" id="new_bank_id" >
                                                                <option value="">Select Bank</option>
                                                                @if (!empty($banks))
                                                                    @foreach ($banks as $row)
                                                                        <option value="{{ $row->id }}"
                                                                                {{ $row->id == $new_updation['bank_id']  ? 'selected' : '' }}>
                                                                            {{ $row->name_en }}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </span>
                                                    @endif
                                                </div>
                                                </div>
                                                <div class="col-lg-6 form-group">
                                                    <label class="control-label">Branch *</label>
                                                    <input data-jqv-required="true" class="form-control  jqv-input" type="text" placeholder="" maxlength="200" id="bank_branch" name="bank_branch" value="{{ $customer->bank_branch }}" required data-parsley-required-message="Select Branch">
                                                           @if(!empty($new_updation) && array_key_exists('bank_branch',$new_updation) && $new_updation['bank_branch']!=null)
                                                             <span class="new_val d-block mt-2 mb-2">
                                                                <input type="text" name="new_bank_branch" value="{{$new_updation['bank_branch']}}" class="form-control bg-light">
                                                                   </span>
                                                            @endif
                                                </div>

                                                <div class="col-lg-6 form-group">
                                                    <label class="control-label">Account Number *</label>
                                                    <input data-jqv-required="true" class="form-control  jqv-input" required data-parsley-required-message="Enter Account Number" type="text" placeholder="" maxlength="20" id="account_no" name="account_no" value="{{ $customer->account_no }}">
                                                    @if(!empty($new_updation) && array_key_exists('account_no',$new_updation)&& $new_updation['account_no']!=null)
                                                        <span class="new_val d-block mt-2 mb-2">
                                                        <input type="text" name="new_account_no" value="{{$new_updation['account_no']}}" class="form-control bg-light">
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="col-lg-6 form-group">
                                                    <label class="control-label">Beneficiary Name *</label>
                                                    <input data-jqv-required="true" class="form-control  jqv-input" type="text" placeholder="" maxlength="200" id="benificiary" required data-parsley-required-message="Enter Beneficiary Name" name="benificiary" value="{{ $customer->benificiary }}">
                                                    @if(!empty($new_updation) && array_key_exists('benificiary',$new_updation) && $new_updation['benificiary']!=null)
                                                     <span class="new_val d-block mt-2 mb-2">
                                                        <input type="text" name="new_benificiary" value="{{$new_updation['benificiary']}}" class="form-control bg-light">
                                                    </span>
                                                    @endif
                                                </div>

                                                <div class="col-lg-6">
                                                    <label class="control-label">IBAN(23 Characters) *</label>
                                                    <input data-jqv-required="true" class="form-control   jqv-input" type="text" placeholder="" minlength="23" maxlength="23" id="ifsc" name="ifsc" value="{{ $customer->ifsc }}" required data-parsley-required-message="Enter IBAN">

                                                        @if(!empty($new_updation) && array_key_exists('ifsc',$new_updation)  && $new_updation['ifsc']!=null)
                                                            <span class="new_val d-block mt-2 mb-2">
                                                                <input type="text"  name="new_ifsc" value="{{$new_updation['ifsc']}}" class="form-control bg-light">
                                                            </span>
                                                        @endif
                                                </div>
                                                <div class="col-lg-6">
                                                    <label class="control-label">Swift(8 - 11 Characters) *</label>
                                                    <input data-jqv-required="true" class="form-control  jqv-input" type="text" placeholder="" title="Use combinations of Number and Letters" minlength="8" maxlength="11" required data-parsley-required-message="Enter Swift"  id="swift" name="swift" value="{{ $customer->swift }}">

                                                    @if(!empty($new_updation) && array_key_exists('swift',$new_updation) && $new_updation['swift']!=null)
                                                            <span class="new_val d-block mt-2 mb-2">
                                                                <input type="text" name="new_swift" value="{{$new_updation['swift']}}" class="form-control bg-light">
                                                            </span>
                                                    @endif
                                                </div>


                                            </div>

                                        </div>
                                    </div>


                                    <div class="card mt-3" style="border-radius: 5px; overflow: hidden;">
                                        <h5 class="card-title font-weight-bold" style="padding: 10px; margin: 0; line-height: normal; width: fit-content; border-bottom-right-radius: 10px;">Timing</h5>
                                        <div class="card-body">
                                            <div class="row">

                                                <div class="col-lg-6">
                                                    <label class="control-label">Start Time *</label>
                                                    <input type="text" class="form-control time" name="start_time" value="@if(!empty($customer->start_time)){{date('h:i A', strtotime($customer->start_time))}}@endif" required data-parsley-required-message="Enter Start Time">
                                                </div>
                                                <div class="col-lg-6">
                                                    <label class="control-label">End Time *</label>
                                                    <input type="text" class="form-control time" name="end_time" value="@if(!empty($customer->end_time)){{date('h:i A', strtotime($customer->end_time))}}@endif" required data-parsley-required-message="Enter End Time">
                                                </div>
                                                <div class="col-md-6 mt-2 weeklymode" @if($customer->allow_ordertype==1) style="display:none" @endif>
                                                    <label class="control-label">Weekly Order Mode</label>
                                                    <input type="checkbox" name="weekly_order_mode" id="weekly_order_mode" value="1" @if($customer !=null && $customer->weekly_order_mode==1){{ 'checked' }}@endif>


                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="card mt-3" style="border-radius: 5px; overflow: hidden;">
                                        <h5 class="card-title font-weight-bold" style="padding: 10px; margin: 0; line-height: normal; width: fit-content; border-bottom-right-radius: 10px;">Documents (.png, .jpg, .jpeg, .pdf)</h5>
                                        <div class="card-body">
                                            <div class="row">

                                                <div class="col-md-6 form-group g1d mb-2">
                                                        <div class="inp_grp">
                                                            <label>Emirates ID</label> <br>
                                                            <input type="file" name="emirates_id" accept=".png,.jpg,.jpeg,.pdf,.gif,.webp" class="image_upload"
                                                                preview="emirates_preview" @if(empty($customer->id)) required
                                            data-parsley-required-message="Select Emirates ID" @endif>
                                                        </div>
                                                        @if($customer->id > 0)
                                                        @if($customer->emirates_id)
                                                        <!-- {!! previewDocuments($customer->emirates_id,50,50,'emirates_preview') !!} -->
                                                        {!! viewDocument($customer->emirates_id) !!}
                                                        {!! downloadDocument($customer->emirates_id) !!}
                                                        @endif
                                                        
                                                        @if(!empty($new_updation) && array_key_exists('emirates_id',$new_updation))
                                                        <div class="newfiles position-relative"> 
                                                        <!-- {!! previewDocuments($new_updation['emirates_id'],50,50,'emirates_preview') !!} -->
                                                        {!! viewDocument($new_updation['emirates_id']) !!}
                                                        {!! downloadDocument($new_updation['emirates_id']) !!}
                                                            <a href="javascript:void(0)" class="remove_updation" ftype="emirates_id"><i class="fa fa-trash"></i></a>
                                                        </div>       
                                                        @endif
                                                        @endif
                                                                                                    </div>
                                                    <div class="col-md-6 form-group g1d mb-2">
                                                        <div class="inp_grp">
                                                            <label>Passport</label><br>
                                                            <input type="file" name="passport" accept=".png,.jpg,.jpeg,.pdf,.gif,.webp" class="image_upload"
                                                                preview="passport_preview" @if(empty($customer->id)) required
                                            data-parsley-required-message="Select Passport" @endif>
                                                        </div>
                                                        @if($customer->id > 0)
                                                        @if($customer->passport_id)
                                                        <!-- {!! previewDocuments($customer->passport_id,50,50,'passport_preview') !!} -->
                                                        {!! viewDocument($customer->passport_id) !!}
                                                        {!! downloadDocument($customer->passport_id) !!}
                                                        @endif
                                                        
                                                        @if(!empty($new_updation) && array_key_exists('passport',$new_updation))
                                                        <div class="newfiles position-relative"> 
                                                            <!-- {!! previewDocuments($new_updation['passport'],50,50,'passport_preview') !!} -->
                                                            {!! viewDocument($new_updation['passport']) !!}
                                                            {!! downloadDocument($new_updation['passport']) !!}
                                                            <a href="javascript:void(0)" class="remove_updation" ftype="passport"><i class="fa fa-trash"></i></a>
                                                        </div> 
                                                        @endif
                                                        @endif

                                                    </div>
                                                    <div class="col-md-6 form-group g1d mb-2">
                                                        <div class="inp_grp">
                                                            <label>Trade License</label><br>
                                                            <input type="file" name="trade_license" accept=".png,.jpg,.jpeg,.pdf,.gif,.webp" class="image_upload"
                                                                preview="license_preview" @if(empty($customer->id)) required
                                            data-parsley-required-message="Select Trade License" @endif>
                                                        </div>
                                                        @if($customer->id > 0)
                                                        @if($customer->trade_license)
                                                            <!-- {!! previewDocuments($customer->trade_license,50,50,'license_preview') !!} -->
                                                            {!! viewDocument($customer->trade_license) !!}
                                                            {!! downloadDocument($customer->trade_license) !!}
                                                        @endif
                                                        @if(!empty($new_updation) && array_key_exists('trade_license',$new_updation))
                                                            <div class="newfiles position-relative">     
                                                            <!-- {!! previewDocuments($new_updation['trade_license'],50,50,'license_preview') !!} -->
                                                            {!! viewDocument($new_updation['trade_license']) !!}
                                                            {!! downloadDocument($new_updation['trade_license']) !!}
                                                            <a href="javascript:void(0)" class="remove_updation" ftype="trade_license"><i class="fa fa-trash"></i></a>
                                                        </div>
                                                        @endif
                                                        @endif
                                                    </div>

                                                    <div class="col-md-6 form-group g1d mb-2">
                                                        <div class="inp_grp">
                                                            <label>Bank Account Proof</label><br>
                                                            <input type="file" name="bank_account_proof"
                                                                class="image_upload" accept=".png,.jpg,.jpeg,.pdf,.gif,.webp" preview="account_preview" @if(empty($customer->id)) required
                                            data-parsley-required-message="Select Bank Account Proof" @endif>
                                                        </div>
                                                        @if($customer->id > 0)
                                                        @if($customer->bank_account_proof)
                                                        <!-- {!! previewDocuments($customer->bank_account_proof,50,50,'account_preview') !!} -->
                                                        {!! viewDocument($customer->bank_account_proof) !!}
                                                        {!! downloadDocument($customer->bank_account_proof) !!}
                                                        
                                                        @endif
                                                        @if(!empty($new_updation) && array_key_exists('bank_account_proof',$new_updation))
                                                            <div class="newfiles position-relative">  
                                                            <!-- {!! previewDocuments($new_updation['bank_account_proof'],50,50,'account_preview') !!} -->
                                                                <a href="javascript:void(0)" class="remove_updation" ftype="bank_account_proof"><i class="fa fa-trash"></i></a>
                                                            {!! viewDocument($new_updation['bank_account_proof']) !!}
                                                            {!! downloadDocument($new_updation['bank_account_proof']) !!}
                                                            </div>
                                                        @endif
                                                        @endif
                                                    
                                                    
                                                </div>
                                                <div class="col-md-6 form-group g1d mb-2">
                                                        <div class="inp_grp">
                                                            <label>Residency Visa</label><br>
                                                            <input type="file" accept=".png,.jpg,.jpeg,.pdf,.gif,.webp" name="visa_copy" class="image_upload"
                                                                preview="visa_preview" @if(empty($customer->id)) required
                                            data-parsley-required-message="Select Residency Visa" @endif>
                                                        </div>
                                                        @if($customer->id > 0)
                                                        @if($customer->visa_copy)
                                                        <!-- {!! previewDocuments($customer->visa_copy,50,50,'visa_preview') !!} -->
                                                        {!! viewDocument($customer->visa_copy) !!}
                                                        {!! downloadDocument($customer->visa_copy) !!}
                                                        @endif
                                                        @if(!empty($new_updation) && array_key_exists('visa_copy',$new_updation))
                                                        <div class="newfiles position-relative">  
                                                            <!-- {!! previewDocuments($new_updation['visa_copy'],50,50,'visa_preview') !!} -->
                                                            {!! viewDocument($new_updation['visa_copy']) !!}
                                                            {!! downloadDocument($new_updation['visa_copy']) !!}
                                                        <a href="javascript:void(0)" class="remove_updation" ftype="visa_copy"><i class="fa fa-trash"></i></a>
                                                        </div>
                                                        @endif
                                                        @endif
                                                    
                                            </div>


                                            </div>
                                        </div>
                                    </div>


                                    <div class="card mt-3" style="border-radius: 5px; overflow: hidden;">
                                        <h5 class="card-title font-weight-bold" style="padding: 10px; margin: 0; line-height: normal; width: fit-content; border-bottom-right-radius: 10px;">Address</h5>
                                        <div class="card-body">
                                            <div class="row">

                                            <div class="col-md-6 form-group">
                                                    <label>Country *</label>
                                                    <div class="select2-customdiv">
                                                        <select data-jqv-required="true" class="custom-select  jqv-input" required data-parsley-required-message="Select Country"
                                                                name="country_id"
                                                                id="country_id">
                                                            <option value="">Select Country</option>
                                                            @foreach ($countries as $country)
                                                                <option value="{{ $country->country_id }}"
                                                                    {{ $country->country_id == $customer->country_id ? 'selected' : '' }}>
                                                                    {{ $country->country_name }}</option>
                                                            @endforeach
                                                        </select>
                                                        
                                                    </div>
                                                    
                                                    @if(!empty($new_updation) && array_key_exists('country_id',$new_updation) && $new_updation['country_id']>0)
                                                    

                                                    <span class="new_val d-block mt-2 mb-2">
                                                            <select  class="custom-select  jqv-input"
                                                            name="new_country_id" required
                                                data-parsley-required-message="Select Country"
                                                            id="country_id">
                                                        <option value="">Select Country</option>
                                                        @foreach ($countries as $country)
                                                            <option value="{{ $country->country_id }}"
                                                                {{ $country->country_id == $new_updation['country_id'] ? 'selected' : '' }}>
                                                                {{ $country->country_name }}</option>
                                                        @endforeach
                                                    </select>
                                                    </span>

                                                @endif
                                                </div>
                                                <div class="col-md-6 form-group">
                                                    <label>City *</label>
                                                    <div class="select2-customdiv">
                                                        <select data-jqv-required="true" class="custom-select  jqv-input" name="city_id" required data-parsley-required-message="Select City"
                                                                id="city_id">
                                                            <option value="">Select City</option>
                                                            @foreach ($cities as $city)
                                                                <option value="{{ $city->id }}"
                                                                    {{ $city->id == $customer->city_id ? 'selected' : '' }}>
                                                                    {{ $city->city_name_en }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    @if(!empty($new_updation) && array_key_exists('city_id',$new_updation) && $new_updation['city_id']>0)

                                                    <span class="new_val d-block mt-2 mb-2">
                                                            <select  class="custom-select  jqv-input" required
                                                data-parsley-required-message="Select City"
                                                            name="new_city_id"
                                                            id="new_city_id">
                                                        <option value="">Select City</option>
                                                        @foreach ($cities as $city)
                                                            <option value="{{ $city->id }}"
                                                                {{ $city->id == $new_updation['city_id']  ? 'selected' : '' }}>
                                                                {{ $city->city_name_en }}</option>
                                                        @endforeach
                                                    </select>
                                                    </span>

                                                @endif
                                                </div>
                                                <div class="col-md-6 col-sm-12 form-group">
                                                    <label for="" class="form-label">Nick Name</label>
                                                    <input type="text" name="loc_nickname" class="form-control" id="loc_nickname" value="@if($customer->loc_nickname!=null){{$customer->loc_nickname}}@elseif(!empty($new_updation) && array_key_exists('loc_nickname',$new_updation) && $new_updation['loc_nickname']!= $customer->loc_nickname ){{$new_updation['loc_nickname']}}@endif">
                                                    @if(!empty($new_updation) && array_key_exists('loc_nickname',$new_updation) && $new_updation['loc_nickname']!= $customer->loc_nickname && $customer->loc_nickname !=null )
                                                        <span class="new_val d-block mt-2 mb-2">
                                                            <input type="text" name="new_loc_nickname" value="{{$new_updation['loc_nickname']}}" class="form-control bg-light">
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="col-md-6 col-sm-12 form-group">
                                                    <label for="" class="form-label">Apartment Number <span class="asteristic">*</span></label>
                                                    <input type="text" name="apartment_num" class="form-control" id="apartment_num" required data-parsley-required-message="Enter Apartment Number" value="@if($customer->apartment_num!=null){{$customer->apartment_num}}@elseif(!empty($new_updation) && array_key_exists('apartment_num',$new_updation) && $customer->apartment_num!=$new_updation['apartment_num']){{$new_updation['apartment_num']}} @endif">
                                                    @if(!empty($new_updation) && array_key_exists('apartment_num',$new_updation) && $customer->apartment_num!=$new_updation['apartment_num'] && $customer->apartment_num !=null)
                                                     <span class="new_val d-block mt-2 mb-2">
                                                        <input type="text" name="new_apartment_num" value="{{$new_updation['apartment_num']}}" class="form-control bg-light">
                                                     </span>
                                                    @endif

                                                </div>
                                                <div class="col-md-6 col-sm-12 form-group">
                                                    <label for="" class="form-label">Building <span class="asteristic">*</span></label>
                                                    <input type="text" name="building" class="form-control" id="building" required data-parsley-required-message="Enter Building" value="@if($customer->building!=null){{$customer->building}}@else @endif">
                                                    @if(!empty($new_updation) && array_key_exists('building',$new_updation) && $customer->building!=$new_updation['building'] && $customer->building !=null)
                                                     <span class="new_val d-block mt-2 mb-2">
                                                        <input type="text" name="new_building" value="{{$new_updation['building']}}" class="form-control bg-light">
                                                     </span>
                                                    @endif
                                                </div>
                                                <div class="col-md-6 col-sm-12 form-group">
                                                    <label for="" class="form-label">Street <span class="asteristic">*</span></label>
                                                    <input type="text" name="street" class="form-control" id="street" value="@if($customer->street!=null){{$customer->street}}@elseif(!empty($new_updation) && array_key_exists('street',$new_updation)){{$new_updation['street']}} @endif" required data-parsley-required-message="Enter Street">
                                                    @if(!empty($new_updation) && array_key_exists('street',$new_updation)&& $customer->street!=$new_updation['street'] && $customer->street !=null)
                                                     <span class="new_val d-block mt-2 mb-2">
                                                        <input type="text" name="new_street" value="{{$new_updation['street']}}" class="form-control bg-light">
                                                     </span>
                                                    @endif
                                                </div>
                                                <div class="col-md-6 col-sm-12 form-group">
                                                    <label for="" class="form-label">Landmark </label>
                                                    <input type="text" name="landmark" class="form-control" id="landmark"  data-parsley-required-message="Enter Landmark" value="@if($customer->landmark!=null){{$customer->landmark}}@elseif(!empty($new_updation) && array_key_exists('landmark',$new_updation)){{$new_updation['landmark']}}@endif">
                                                    @if(!empty($new_updation) && array_key_exists('landmark',$new_updation) && $customer->landmark!=$new_updation['landmark'] && $customer->landmark !=null)
                                                     <span class="new_val d-block mt-2 mb-2">
                                                        <input type="text" name="new_landmark" value="{{$new_updation['landmark']}}" class="form-control bg-light">
                                                     </span>
                                                    @endif
                                                </div>
                                                <div class="col-md-12 col-sm-12 form-group">
                                                    <label for="pxp-candidate-location" class="form-label">Location
                                                        <span class="asteristic">*</span></label>
                                                        <input type="text" name="txt_location" id="txt_location" class="form-control autocomplete" placeholder="673C+VFH - Dubai - United Arab Emirates" required data-parsley-required-message="Enter Location" value="@if(!empty($new_updation) && array_key_exists('location',$new_updation)) {{$new_updation['location']}} @else{{$customer->location}}@endif" >
                                                        <input type="hidden" id="location" name="location">
                                                </div>
                                                <div class="form-group col-md-12">
                                                    <div id="map_canvas" style="height: 200px;width:100%;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="mb-2">
                                        <input type="submit" class="mt-4 btn btn-button-7" value="Approve">
                                                            @if($customer->new_updation!=null)
                                                                <button type="button" class="mt-4 btn btn-reject"
                                                                        id="reject_changes">Reject
                                                                </button>
                                                            @endif 
                                    </div>




                                    <div class="form-group d-none row">
                                        
                                        

                                        
                                        
                                        
                                        

                                        
                                        {{--<div class="col-md-12">
                                            <div class="form-group">
                                                <label class="control-label">Address *</label>
                                                <textarea data-jqv-required="true" class="form-control jqv-input" type="text" placeholder="Enter Address"
                                                    id="location" name="location" value="">{{ $customer->location }}</textarea>
                                            </div>
                                        </div>--}}
                                        
                                        <!-- <div class="col-md-12 p-0">
                                            <div class="row">
                                                <div class="col-md-6 form-group mt-2 p-0">
                                                    <div class="row">

                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            
                                                        </div>
                                                    </div>

                                                <div class="col-md-6 form-group mt-2 p-0">
                                                    <div class="row">

                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            



                                                        </div>
                                                    </div>
                                                </div>
                                                </div>
                                            </div>
                                        </div> -->

                                    <!--     <div
                                            class="col-md-12 mt-4 mb-2  @if(!empty($new_updation) && array_key_exists('weekly_order_mode',$new_updation) && $new_updation['weekly_order_mode'] == 1 )@elseif($customer->weekly_order_mode==1) @else {{'d-none'}} @endif  workingdays">

                                            <div class="row">

                                                <div class="col-lg-8 col-12">
                                                    <div class="form-group">
                                                        <h6>Shop Timing:-</h6>
                                                    </div>
                                                    <table class="table table-condensed pl-2">
                                                        @php $days = Config('global.days');  @endphp
                                                        @foreach($days as $key => $val)
                                                            @php $st = $key.'_from'; $ed = $key.'_to';  @endphp
                                                            <tr>
                                                                <td>
                                                                    <input type="checkbox" id="day{{$val}}"
                                                                           name="{{$val}}" value="1"
                                                                           @if(isset($restaurent)&&$restaurent->{$val} == 1) checked @endif>
                                                                    <label for="day{{$val}}">{{ucfirst($val)}}</label>

                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control time"
                                                                           name="{{$key}}_from" placeholder="Start Time"
                                                                           value="@if(isset($restaurent)&&$restaurent->$st!='00:00'){{$restaurent->$st}}@endif">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control time"
                                                                           name="{{$key}}_to" placeholder="End Time"
                                                                           value="@if(isset($restaurent)&&$restaurent->$ed!='00:00'){{$restaurent->$ed}}@endif">
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </table>
                                                </div>
                                            </div>
                                        </div> -->
                                        

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

@endsection

@section('footerJs')
    <script src="{{ asset('admin-assets/plugins/jqvalidation/jqBootstrapValidation-1.3.7.min.js') }}"></script>
    <script src="{{ asset('admin-assets/plugins/jqvalidation/jqBootstrapValidation-1.3.7.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.js"></script>
    <script src=
                "https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js">
    </script>
    <script src="{{ asset('admin-assets/plugins/select2/select2.min.js') }}"></script>
    <script src="{{asset('frontend-assets\js\jquery.timepicker.min.js')}}"
            integrity="sha512-WHnaxy6FscGMvbIB5EgmjW71v5BCQyz5kQTcZ5iMxann3HczLlBHH5PQk7030XmmK5siar66qzY+EJxKHZTPEQ=="
            crossorigin="anonymous"></script>
    <script src="//jonthornton.github.io/jquery-timepicker/jquery.timepicker.js"></script>

    <script>
       
       
        $(".select2-cusinine").select2();
        $('#reject_changes').on('click', function () {
            var chef_id = '{{$customer->id}}';
            jQuery.ajax({
                url: "{{ url('admin/reject_chef_changes') }}",
                method: 'post',
                data: {
                    chef_id: chef_id,
                    '_token': '{{csrf_token()}}'
                },
                success: function (result) {
                    window.location.reload();

                }
            });
        })
        $('.datepick').datepicker({
            changeMonth: true,
            changeYear: true,
            minDate: 0,
            startDate: new Date(),
            todayHighlight: true,
            autoclose: true,
            dateFormat: 'dd-mm-yy',
            timepicker: false,
        });

        App.initFormView();
        $('body').off('submit', '#admin-form');
        $('body').on('submit', '#admin-form', function (e) {
            e.preventDefault();
            var validation = $.Deferred();
            var $form = $(this);
            var formData = new FormData(this);

            $form.validate({
                rules: {},
                errorElement: 'div',
                errorPlacement: function (error, element) {
                    //element.addClass('is-invalid');
                    var placement = $(element).data('error');
                    if (placement) {
                        $(placement).append(error)
                        error.addClass('invalid-feedback');
                        error.insertAfter(placement);
                    } else {
                        error.addClass('invalid-feedback');
                        error.insertAfter(element);
                    }

                }
            });

            // Bind extra rules. This must be called after .validate()
            App.setJQueryValidationRules('#admin-form');

            if ($form.valid()) {
                validation.resolve();
            } else {
                var error = $form.find('.is-invalid').eq(0);
                $('html, body').animate({
                    scrollTop: (error.offset().top - 100),
                }, 500);
                validation.reject();
            }

            validation.done(function () {
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('div.invalid-feedback').remove();

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
                    success: function (res) {
                        App.loading(false);

                        if (res['status'] == 0) {
                            if (typeof res['errors'] !== 'undefined' && res['errors'].length >
                                0) {
                                var error_def = $.Deferred();
                                var error_index = 0;
                                jQuery.each(res['errors'][0], function (e_field, e_message) {
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
                                error_def.done(function () {
                                    var error = $form.find('.is-invalid').eq(0);
                                    $('html, body').animate({
                                        scrollTop: (error.offset().top - 100),
                                    }, 500);
                                });
                            } else {
                                var m = res['message'] ||
                                    'Unable to save data. Please try again later.';
                                App.alert(m, 'Oops!');
                            }
                        } else {

                            App.alert(res['message'] || 'Data saved successfully');
                            setTimeout(function () {
                                window.location.href =
                                    "{{ url('admin/chef') }}"
                            }, 1500);

                        }

                        $form.find('button[type="submit"]')
                            .text('Save')
                            .attr('disabled', false);
                    },
                    error: function (e) {
                        App.loading(false);
                        $form.find('button[type="submit"]')
                            .text('Save')
                            .attr('disabled', false);
                        App.alert(e.responseText, 'Oops!');

                    }
                });
            });
        });
        $('.time').timepicker({timeFormat: 'h:i A'});

        function togglePass() {

            if ($('#password').attr('type') === "password") {
                $('#password').attr('type', 'text');
            } else {
                $('#password').attr('type', 'password');
            }
        };

       
        $('#weekly_order_mode').on('change', function () {
            if ($(this).val() == 1) {
                $('.workingdays').removeClass('d-none');
            } else {
                $('.workingdays').addClass('d-none');
            }
        });
        $('.remove_updation').on('click',function(){
            var ftype = $(this).attr('ftype');
            var chef_id = '{{$customer->id}}';
            jQuery.ajax({
                url: "{{ url('admin/chef/remove_newfiles') }}",
                method: 'post',
                data: {
                    chef_id: chef_id,
                    'ftype':ftype,
                    '_token': '{{csrf_token()}}'
                },
                success: function (result) {


                }
            });
            $(this).parents('.newfiles').html('');
        })
    </script>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{Config('global.google_key')}}&callback=initAutocomplete&v=weekly&libraries=places"></script>
    <script type="text/javascript">
        <?php if((!empty($new_updation) && array_key_exists('latitude',$new_updation)) && $new_updation['latitude']!=null) { ?>
         var currentLat = {{$new_updation['latitude']}};
     <?php }  else { ?>
         var currentLat = {{empty($customer->latitude) ? 25.204819: $customer->latitude}};
    <?php } ?>
     <?php if((!empty($new_updation) && array_key_exists('longitude',$new_updation)) && $new_updation['longitude']!=null ) { ?>
         var currentLong = {{$new_updation['longitude']}};
     <?php }  else { ?>
         var currentLong = {{empty($customer->longitude) ? 25.204819: $customer->longitude}};
    <?php } ?>
    //var currentLat = {{empty($customer->latitude) ? 25.204819: $customer->latitude}};
       //var currentLong = {{empty($customer->longitude) ? 55.270931: $customer->longitude}};
        $("#location").val(currentLat+","+currentLong);

        currentlocation = {
            "lat": currentLat,
            "lng": currentLong,
        };
        initMap();
        window.initAutocomplete = initAutocomplete;
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


    $('#allow_ordertype').on('change',function(){
        if($(this).val()==1) {
            
            $('#weekly_order_mode').prop('checked',false);
            $('.weeklymode').hide();
        } else {
            $('.weeklymode').show();
        }
    })
</script>
@endsection
