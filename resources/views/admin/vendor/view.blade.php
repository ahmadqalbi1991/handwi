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
        #parsley-id-23{
            bottom:0 !important
        }
        #parsley-id-66, #parsley-id-60, #parsley-id-21{
            position: absolute;
            bottom: -20px;
        }
    </style>
                <!--<div class="card p-4">-->
                    
                    <input type="hidden" name="id" value="{{ $id }}">
                    @csrf()
                    <div class="">


                            <div class="card mb-2">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-4 col-xs-12">
                                        <div class="form-group d-flex align-items-center">
                                            <div>
                                            
                                            </div>
                                                                                            <img id="logo-preview" class="img-thumbnail w-50" style="margin-left: 5px; height:150px; width:150px !important;" src="{{empty($vendor->logo) ? asset('uploads/company/17395e3aa87745c8b488cf2d722d824c.jpg'): $vendor->logo}}">
                                                                                    </div>
                                    </div>
                                        <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                                <label>Company Name <span style="color:red;">*<span></span></span></label>
                                                <input type="text" class="form-control" data-jqv-maxlength="100" name="company_legal_name" value="{{empty($vendor->company_name) ? '': $vendor->company_name}}" required data-parsley-required-message="Enter Company Legal Name" disabled>
                                            </div>
                                        </div>
                                         <div class="col-sm-4 col-xs-12">
                                                <div class="form-group">
                                                    <label>Trade License  </label><br>
                                                    @php if(!empty($vendor->trade_license_doc)) { @endphp <a href='{{asset($vendor->trade_license_doc)}}' target='_blank'><strong>View Trade License</strong></a>@php }  @endphp
                                                </div>
                                        </div>
                                        
                                        <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                                <label>Trade Licence Number <span style="color:red;">*<span></span></span></label>
                                                <input type="text" class="form-control jqv-input" data-jqv-required="true" name="trade_licene_number" data-jqv-maxlength="100" value="{{empty($vendor->trade_license) ? '': $vendor->trade_license}}" required data-parsley-required-message="Enter Trade Licence Number" disabled>
                                            </div>
                                        </div>
                                        
                                        <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                                <label>Trade Licence Expiry <span style="color:red;">*<span></span></span></label>
                                                <input type="text" class="form-control flatpickr-input" data-date-format="yyyy-mm-dd" name="trade_licene_expiry" value="{{empty($vendor->trade_license_expiry) ? '': date('Y-m-d', strtotime($vendor->trade_license_expiry))}}" required data-parsley-required-message="Enter Trade Licence Expiry" disabled>
                                            </div>
                                        </div>              
                                    
                                        
                                         <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                <label>Vendor type<b class="text-danger">*</b></label>
                                <select name="store_type" class="form-control select2" required
                                data-parsley-required-message="Select Store type" disabled>
                                    <option value="">Select</option>
                                    @foreach ($storetype as $cnt)
                                        <option <?php if(!empty($vendor->store_type)) { ?> {{$vendor->store_type == $cnt['id'] ? 'selected' : '' }} <?php } ?> value="{{ $cnt['id'] }}">
                                            {{ $cnt['name'] }}</option>
                                    @endforeach;
                                </select>
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
                                                <input type="text" class="form-control" data-jqv-maxlength="100" name="first_name" value="{{empty($datamain->first_name) ? '': $datamain->first_name}}" required data-parsley-required-message="Enter First Name" disabled>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                                <label>Last Name <span style="color:red;">*<span></span></span></label>
                                                <input type="text" class="form-control" data-jqv-maxlength="100" name="last_name" value="{{empty($datamain->last_name) ? '': $datamain->last_name}}" required data-parsley-required-message="Enter Last Name" disabled>
                                            </div>
                                        </div>

                                        <div class="col-sm-4 col-xs-12">
                                        <div class="form-group">
                                            <label>Email <span style="color:red;">*<span></span></span></label>
                                            <input type="email" class="form-control" name="email" data-jqv-maxlength="50" value="{{empty($datamain->email) ? '': $datamain->email}}" required
                            data-parsley-required-message="Enter Email" autocomplete="off" disabled>
                                            
                                        </div>
                                    </div>


                                        
                                       

                                    </div>

                                   
                                <div class="row">

                                     <div class="col-sm-4 col-xs-12">
                                    <div class="form-group">
                        <label>Dial Code<b class="text-danger">*</b></label>
                        <select name="dial_code" class="form-control select2 dial_code-select" required
                        data-parsley-required-message="Select Dial Code" disabled>
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
    data-parsley-maxlength="12" data-parsley-trigger="keyup" disabled>
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
                            data-parsley-required-message="Enter Company account" disabled>
                                            </div>
                                        </div>

                                        <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                                <label>Bank Account Number <span style="color:red;">*<span></span></span></label>
                                                <input type="text" name="bank_account_number" class="form-control" value="{{empty($bankdata->account_no) ? '': $bankdata->account_no}}" required
                            data-parsley-required-message="Enter Bank account number" disabled>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 col-xs-12">
                                        <div class="form-group">
                            <label>Country<b class="text-danger">*</b></label>
                            <select name="bankcountry" class="form-control select2" required
                            data-parsley-required-message="Select Country" id="bankcountry" disabled>
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
                                                <input type="text" name="bank_branch_code" class="form-control" value="{{empty($bankdata->branch_code) ? '': $bankdata->branch_code}}" required
                            data-parsley-required-message="Enter Bank Branch code" disabled>
                                            </div>
                                        </div>

                                         <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                                <label>Bank Name<span style="color:red;">*<span></span></span></label>
                                                <select class="form-control bank_id-select" name="bank_id" required
                            data-parsley-required-message="Select Bank" disabled>
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
                                                <input type="text" name="branch_name" class="form-control" value="{{empty($bankdata->branch_name) ? '': $bankdata->branch_name}}" required
                            data-parsley-required-message="Enter Bank Branch name" disabled>
                                            </div>
                                        </div>
                                        

                                        
                                        
                                       
                                    </div>
                                    <div class="row">

                                        <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                                <label>IBAN <span style="color:red;">*<span></span></span></label>
                                                <input type="text" name="iban" class="form-control" value="{{empty($bankdata->iban) ? '': $bankdata->iban}}" required
                            data-parsley-required-message="Enter IBAN" disabled>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 col-xs-12" style="display:none;">
                                            <div class="form-group">
                                                <label>Bank Code Type <span style="color:red;">*<span></span></span></label>
                                                <select name="bank_code_type" class="form-control" >
                                                    <option value="">Select</option>
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
                                               
                                            </div>
                                        </div>
                                        <div class="col-sm-4 col-xs-12" style="display:none;">
                                            <div class="form-group">
                                                <label>Credit Card Statement @php if(!empty($bankdata->credit_card_sta_doc)) { @endphp <a href='{{asset($bankdata->credit_card_sta_doc)}}' target='_blank'><strong>View</strong></a>@php }  @endphp</label>
                                               
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

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
                            <input type="text" name="txt_location" id="txt_location" class="form-control autocomplete" placeholder="Location" required data-parsley-required-message="Enter Location" @if($id) value="{{$vendor->location}}" @endif>
                            <input type="hidden" id="location" name="location" readonly>                            
                        </div>

                        <div class="form-group col-md-12">
                            <div id="map_canvas" style="height: 200px;width:100%;"></div>
                        </div>

                                                        <div class="col-sm-4 col-xs-12">
                                            <div class="form-group">
                                <label>Country<b class="text-danger">*</b></label>
                                <select name="country_id" class="form-control select2" required
                                data-parsley-required-message="Select Country" data-role="country-change" id="country" data-input-state="city-state-id" disabled>
                                    <option value="">Select</option>
                                    @foreach ($countries as $cnt)
                                        <option <?php if(!empty($datamain->country_id)) { ?> {{$datamain->country_id == $cnt->id ? 'selected' : '' }} <?php } ?> value="{{ $cnt->id }}">
                                            {{ $cnt->name }}</option>
                                    @endforeach;
                                </select>
                            </div>
                            </div>
                                                        <div class="col-lg-4 col-md-4 col-12">
                                                            <label>Address Line 1 <span style="color:red;">*<span></span></span></label>
                                                            <input type="text" class="form-control" name="address1" value="{{empty($vendor->address1) ? '': $vendor->address1}}" data-jqv-maxlength="100" required
                                    data-parsley-required-message="Enter Address Line 1" disabled>
                                                        </div>
                                                        <div class="col-lg-4 col-md-4 col-12">
                                                            <label>Address Line 2</label>
                                                            <input type="text" class="form-control" name="address2" value="{{empty($vendor->address2) ? '': $vendor->address2}}" data-jqv-maxlength="100" disabled>
                                                        </div>
                                                        <div class="col-lg-4 col-md-4 col-12">
                                                            <label>Street Name/No <span style="color:red;">*<span></span></span></label>
                                                            <input type="text" class="form-control" name="street" value="{{empty($vendor->street) ? '': $vendor->street}}" data-jqv-maxlength="100" required
                                    data-parsley-required-message="Enter Street Name/No" disabled>
                                                        </div>

                                                    
                                                        
                                                    <div class="form-group col-md-4">
                                    <label>State/Province<b class="text-danger">*</b></label>
                                    <select name="state_id" class="form-control" required
                                    data-parsley-required-message="Select State/Province" id="city-state-id" data-role="state-change" data-input-city="city-id" disabled>
                                        <option value="">Select</option>
                                        @foreach ($states as $st)
                                            <option  @if($id) @if($datamain->state_id==$st->id) selected @endif @endif value="{{$st->id}}">{{$st->name}}</option>
                                        @endforeach
                                    
                                    </select>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>City<b class="text-danger">*</b></label>
                                    <select name="city_id" class="form-control" required
                                    data-parsley-required-message="Select City" id="city-id" disabled>
                                        <option value="">Select</option>

                                        @foreach ($cities as $ct)
                                            <option  @if($id) @if($datamain->city_id==$ct->id) selected @endif @endif value="{{$ct->id}}">{{$ct->name}}</option>
                                        @endforeach
                                        
                                    </select>
                                </div>
                                                        
                                                        <div class="col-lg-4 col-md-4 col-12">
                                                            <label>Zip <span style="color:red;">*<span></span></span></label>
                                                            <input type="text" class="form-control" name="zip" value="{{empty($vendor->zip) ? '': $vendor->zip}}" data-jqv-maxlength="10" required
                                    data-parsley-required-message="Enter Zip code" disabled>
                                                            <div class="error"></div>
                                                        </div>
                                                    </div>
                                                <!--</div>-->
                                            </div>
                                            
                                             <div class="col-sm-4 col-xs-12 other_docs mt-3" id="certificate_product_registration_div" >
                                                <div class="form-group">
                                                    <button type="button" class="btn btn-primary change_status" statusid="1" data-id="{{ $datamain->id }}"
                                            data-url="{{ url('admin/vendors/approve') }}" @if($datamain->approve == 1) disabled @endif > @if($datamain->approve == 1) Approved @else Approve @endif</button>
                                                    
                                                    <button type="button" class="btn btn-primary change_status" statusid="2" data-id="{{ $datamain->id }}"
                                            data-url="{{ url('admin/vendors/approve') }}" @if($datamain->approve == 2) disabled @endif > @if($datamain->approve == 2) Rejected @else Reject @endif</button>
                                                </div>
                                            </div>
                                           
                                        </div>
                                    </div>
                                </div>
                            </div>

                            




                         
                            </div>
                
                </div>
@stop

@section('script')
        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB8QkOt74HuPCD8N6m1OfwSzyb0NWnjorg&v=weekly&libraries=places"></script>
    <script>

        

        $(document).ready(function() {
            $('select').select2();
        });
       

        
       
        var currentLat = <?php echo $id ? $vendor->latitude : 25.204819 ?>;
        var currentLong = <?php echo $id ? $vendor->longitude : 55.270931 ?>;
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
        $(".change_status").click(function() {
            status = $(this).attr('statusid');
           
            $.ajax({
                type: "POST",
                enctype: 'multipart/form-data',
                url: $(this).data('url'),
                data: {
                    "id": $(this).data('id'),
                    'status': status,
                    "_token": "{{ csrf_token() }}"
                },
                timeout: 600000,
                dataType: 'json',
                success: function(res) {
                    App.loading(false);

                    if (res['status'] == 0) {
                        var m = res['message']
                        App.alert(m, 'Oops!');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    } else {
                        App.alert(res['message']);
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    }
                },
                error: function(e) {
                    App.alert(e.responseText, 'Oops!');
                }
            });
        });


    </script>

@stop
