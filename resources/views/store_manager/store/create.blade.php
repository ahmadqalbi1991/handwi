@extends('store_manager.template.layout')
@section('header')
<style>
    .img-wrap {
        position: relative;
        display: inline-block;
        font-size: 0;
    }
    .img-wrap .close {
        position: absolute;
        top: 2px;
        right: 2px;
        z-index: 100;
        background-color: #FFF;
        padding: 5px 2px 2px;
        color: #000;
        font-weight: bold;
        cursor: pointer;
        opacity: .2;
        text-align: center;
        font-size: 22px;
        line-height: 10px;
        border-radius: 50%;
    }
    .close:hover {
        opacity: 1;
    }
</style>

@stop
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="col-xs-12 col-sm-12">
                <form method="post" id="admin-form" action="{{ url('store/save_store') }}" enctype="multipart/form-data"
                    data-parsley-validate="true">
                    <input type="hidden" name="id" id="cid" value="{{ $id }}">
                    @csrf()

                    <div class="row">

                        <input type="hidden" id="vendor_id" name="vendor_id" value="{{ auth()->user()->id }}">

                        <div class="form-group col-md-6">
                            <label>Industry Type<b class="text-danger">*</b></label>
                            <select name="industry_type" class="form-control" required
                            data-parsley-required-message="Select Industry Type">
                                <option value="">Select</option>
                                @foreach ($industry_types as $it)
                                    <option @if($id) @if($store->industry_type==$it->id) selected @endif @endif value="{{ $it->id }}">
                                        {{ $it->name }}</option>
                                @endforeach;
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Store Name<b class="text-danger">*</b></label>
                            <input type="text" name="store_name" class="form-control" required
                                data-parsley-required-message="Enter Store Name" @if($id) value="{{$store->store_name}}" @endif>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Business Email<b class="text-danger">*</b></label>
                            <input type="email" name="business_email" class="form-control" required
                                data-parsley-required-message="Enter Business Email " @if($id) value="{{$store->business_email}}" @endif>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Mobile<b class="text-danger">*</b></label>
                                <div class="input-group">
                                  <div class="input-group-prepend">
                                      <select class="form-control" name="dial_code" required
                                      data-parsley-required-message="">
                                          <option value="">code</option>
                                          @foreach ($countries as $cnt)
                                            <option @if($id) @if($store->dial_code==$cnt->dial_code) selected @endif @endif  value="{{ $cnt->dial_code }}">+{{ $cnt->dial_code }}</option>
                                          @endforeach;
                                       </select>
                                  </div>
                                  <input type="text" class="form-control" name="mobile" required data-parsley-required-message="Enter Mobile" data-parsley-type="digits" data-parsley-type-message="Enter valid mobile number" data-parsley-errors-container="#mob_err" maxlength="20" @if($id) value="{{$store->mobile}}" @endif>
                              </div>
                              <span id="mob_err"></span>
                            </div>
                        </div>
                        <div class="form-group col-md-12">
                            <label>Description<b class="text-danger">*</b></label>
                            <textarea type="text" name="description" class="form-control" required
                                data-parsley-required-message="Enter Description ">@if($id) {{$store->mobile}} @endif</textarea>
                        </div>

                        <div class="form-group col-md-12">
                            <label class="control-label">Enter the location or Drag the marker<b class="text-danger">*</b></label>
                            <input type="text" name="txt_location" id="txt_location" class="form-control autocomplete" placeholder="Location" required data-parsley-required-message="Enter Location" @if($id) value="{{$store->location}}" @endif>
                            <input type="hidden" id="location" name="location">                            
                        </div>

                        <div class="form-group col-md-12">
                            <div id="map_canvas" style="height: 200px;width:100%;"></div>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Address Line 1<b class="text-danger">*</b></label>
                            <input type="text" name="address_line1" class="form-control" required
                                data-parsley-required-message="Enter Address Line 1" @if($id) value="{{$store->address_line1}}" @endif>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Address Line 2</label>
                            <input type="text" name="address_line2" class="form-control" @if($id) value="{{$store->address_line2}}" @endif>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Country<b class="text-danger">*</b></label>
                            <select name="country_id" class="form-control" required
                            data-parsley-required-message="Select Country" data-role="country-change" data-input-state="city-state-id">
                                <option value="">Select</option>
                                @foreach ($countries as $cnt)
                                    <option @if($id) @if($store->country_id==$cnt->id) selected @endif @endif value="{{ $cnt->id }}">
                                        {{ $cnt->name }}</option>
                                @endforeach;
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>State/Province<b class="text-danger">*</b></label>
                            <select name="state_id" class="form-control" required
                            data-parsley-required-message="Select State/Province" id="city-state-id" data-role="state-change" data-input-city="city-id">
                                <option value="">Select</option>
                                @foreach ($states as $st)
                                    <option  @if($id) @if($store->state_id==$st->id) selected @endif @endif value="{{$st->id}}">{{$st->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>City<b class="text-danger">*</b></label>
                            <select name="city_id" class="form-control" required
                            data-parsley-required-message="Select City" id="city-id">
                                <option value="">Select</option>
                                @foreach ($cities as $ct)
                                    <option  @if($id) @if($store->city_id==$ct->id) selected @endif @endif value="{{$ct->id}}">{{$ct->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Zip<b class="text-danger">*</b></label>
                            <input type="text" name="zip" class="form-control" required
                                data-parsley-required-message="Enter Zip" maxlength="10" @if($id) value="{{$store->zip}}" @endif>
                        </div>
                        <?php
                        $l_req = 'required';
                        $c_req = 'required';
                        if($id){
                            if($store->logo){
                                $l_req = '';
                            }
                            if($store->cover_image){
                                $c_req = '';
                            }
                        }
                        ?>

                        <div class="form-group col-md-6">
                            <label>Logo<b class="text-danger">*</b></label><br>
                            <img id="image-preview" style="width:109px; height:109px;" class="img-responsive" @if($id && $store->logo) src="{{ public_url() }}{{ $store->logo }}" @endif>
                            <br><br>
                            <input type="file" name="logo" class="form-control" data-role="file-image" data-preview="image-preview" data-parsley-trigger="change"
                                data-parsley-fileextension="jpg,png,gif,jpeg"
                                data-parsley-fileextension-message="Only files with type jpg,png,gif,jpeg are supported" data-parsley-max-file-size="5120" data-parsley-max-file-size-message="Max file size should be 5MB" data-parsley-imagedimension="300x300" {{$l_req}} data-parsley-required-message="Select Logo">
                           <span class="text-info">Upload image with dimension 300x300</span>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Cover Image<b class="text-danger">*</b></label><br>
                            <img id="image-preview-b" style="width:205px; height:109px;" class="img-responsive" @if($id  && $store->cover_image) src="{{ public_url() }}{{ $store->cover_image }}" @endif>
    
                            <br><br>
                            <input type="file" name="cover_image" class="form-control" data-role="file-image" data-preview="image-preview-b" data-parsley-trigger="change"
                                data-parsley-fileextension="jpg,png,gif,jpeg"
                                data-parsley-fileextension-message="Only files with type jpg,png,gif,jpeg are supported" data-parsley-max-file-size="5120" data-parsley-max-file-size-message="Max file size should be 5MB" data-parsley-imagedimensions="1024x547" {{$c_req}} data-parsley-required-message="Select Cover Image">
                                <span class="text-info">Upload image with dimension 1024x547</span>
                        </div>

                        <div class="form-group col-md-6">
                            <label>License Number<b class="text-danger">*</b></label>
                            <input type="text" name="license_number" class="form-control" required
                                data-parsley-required-message="Enter License Number" maxlength="10" @if($id) value="{{$store->license_number}}" @endif>
                        </div>
                        <?php
                        $lic_doc_req = 'required';
                        $vat_doc_req = 'required';
                        if($id){
                            if($store->license_doc){
                                $lic_doc_req = '';
                            }
                            if($store->vat_cert_doc){
                                $vat_doc_req = '';
                            }
                        }
                        ?>
                        <div class="form-group col-md-6">
                            <label>License Document<b class="text-danger">*</b></label><br>
                            <input type="file" name="license_doc" class="form-control" data-parsley-trigger="change"
                                data-parsley-fileextension="jpg,png,jpeg,pdf,doc,docx"
                                data-parsley-fileextension-message="Only files with type jpg,png,jpeg,pdf,doc are supported" data-parsley-max-file-size="5120" data-parsley-max-file-size-message="Max file size should be 5MB" {{$lic_doc_req}} data-parsley-required-message="Select License Document">
                                @if($id  && $store->license_doc) <a href="{{ public_url() }}{{ $store->license_doc }}" target="_blank" rel="noopener noreferrer">View Document</a> @endif
                        </div>

                        <div class="form-group col-md-6">
                            <label>VAT Cert. Number<b class="text-danger">*</b></label>
                            <input type="text" name="vat_cert_number" class="form-control" required
                                data-parsley-required-message="Enter VAT Cert. Number" maxlength="10" @if($id) value="{{$store->vat_cert_number}}" @endif>
                        </div>
                        <div class="form-group col-md-6">
                            <label>VAT Cert. Document<b class="text-danger">*</b></label><br>
                            <input type="file" name="vat_cert_doc" class="form-control" data-parsley-trigger="change"
                                data-parsley-fileextension="jpg,png,jpeg,pdf,doc,docx"
                                data-parsley-fileextension-message="Only files with type jpg,png,jpeg,pdf,doc are supported" data-parsley-max-file-size="5120" data-parsley-max-file-size-message="Max file size should be 5MB" {{$vat_doc_req}} data-parsley-required-message="Select VAT Cert. Document">
                                @if($id  && $store->vat_cert_doc) <a href="{{ public_url() }}{{ $store->vat_cert_doc }}" target="_blank" rel="noopener noreferrer">View Document</a> @endif
                        </div>
                        

                        <div class="col-md-12 form-group  imgs-wrap">
                            <div class="top-bar">
                            <label class="badge bg-light d-flex justify-content-between align-items-center">Banner Images<button class="btn btn-button-7 pull-right" type="button" data-role="add-imgs"><i class="flaticon-plus-1"></i></button> </label>
                            </div>
                            <input type="hidden" id="imgs_counter" value="0">
                            @if($id)
                            <div class="row">
                                @foreach ($images as $img)
                                <div class="col-md-3 img-wrap">
                                    <span class="close" title="Delete" data-role="unlink"
                                        data-message="Do you want to remove this image?"
                                        href="{{ url('store/store/delete_image/' . $img->id) }}">&times;</span>
                                    <img style="width:205px; height:109px;" class="img-responsive" src="{{ public_url() }}{{ $img->image }}">
                                </div>
                                @endforeach
                                
                            </div>
                            @endif
                            <div id="imgs-holder" class="row mt-3"></div>
                        </div>
                    </div>
                  
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
            <div class="col-xs-12 col-sm-6">
            </div>
        </div>
    </div>
@stop
@section('script')
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB8QkOt74HuPCD8N6m1OfwSzyb0NWnjorg&v=weekly&libraries=places"></script>
    <script>
       
        var currentLat = <?php echo $id ? $store->latitude : 25.204819 ?>;
        var currentLong = <?php echo $id ? $store->longitude : 55.270931 ?>;
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
        
        $('body').off('submit', '#admin-form');
        $('body').on('submit', '#admin-form', function(e) {
            e.preventDefault();
            var $form = $(this);
            var formData = new FormData(this);

            App.loading(true);
            $form.find('button[type="submit"]')
                .text('Saving')
                .attr('disabled', true);

            var parent_tree = $('option:selected', "#parent_id").attr('data-tree');
            formData.append("parent_tree", parent_tree);

            $.ajax({
                type: "POST",
                enctype: 'multipart/form-data',
                url: $form.attr('action'),
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                timeout: 600000,
                dataType: 'json',
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
                            var m = res['message'] ||
                            'Unable to save store. Please try again later.';
                            App.alert(m, 'Oops!');
                        }
                    } else {
                        App.alert(res['message'], 'Success!');
                                setTimeout(function(){
                                    window.location.href = App.siteUrl('/store/store');
                                },1500);
                       
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

        $('body').on("click", '[data-role="remove-imgs"]', function() {
            $(this).parent().parent().remove();
        });
        let img_counter = $("#imgs_counter").val();
      $('[data-role="add-imgs"]').click(function() {
        img_counter++;
            var html = '<div class="form-group col-md-5">\
                            <label>Cover Image<b class="text-danger">*</b></label><br>\
                            <img id="image-preview-bnr_'+img_counter+'" style="width:205px; height:109px;" class="img-responsive" >\
                            <br><br>\
                            <input type="file" name="banners[]" class="form-control" data-role="file-image" data-preview="image-preview-bnr_'+img_counter+'" data-parsley-trigger="change" data-parsley-fileextension="jpg,png,gif,jpeg" data-parsley-fileextension-message="Only files with type jpg,png,gif,jpeg are supported" data-parsley-max-file-size="5120" data-parsley-max-file-size-message="Max file size should be 5MB" data-parsley-imagedimensions="1024x547" required data-parsley-required-message="Select Banner Image">\
                                <span class="text-info">Upload image with dimension 1024x547</span>\
                        </div>\
                        <div class="col-md-1">\
                            <button type="button" class="btn btn-danger" data-role="remove-imgs"><i class="flaticon-minus-2"></i></button>\
                        </div>';
                        $('#imgs-holder').append(html);
           
        });
    </script>
@stop
