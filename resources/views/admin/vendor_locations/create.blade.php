@extends('admin.template.layout')
@section('header')
   
@stop
@section('content')
    <div class="card mb-5">
        <div class="card-body">
            
                <form method="post" id="admin-form" action="{{ url('admin/vendor/location/save') }}" enctype="multipart/form-data"
                    data-parsley-validate="true">
                    <input type="hidden" name="id" id="cid" value="{{ $id }}">
                    <input type="hidden" name="user_id" id="user_id" value="{{ $user_id }}">
                    @csrf()
                    <div class="row">
                        
                        <div class=" col-md-12 mb-1">
                            <label class="control-label">Enter the Center of Area or Drag the marker<b class="text-danger">*</b></label>
                            <input type="text" name="txt_location" id="txt_location" class="form-control autocomplete" placeholder="Location" required data-parsley-required-message="Enter Location" value="{{$location}}" >
                            <input type="hidden" id="location" name="location" value="{{$dblocation->latitude}},{{$dblocation->longitude}}">                            
                        </div>

                        <div class="col-md-12 mb-2">
                            <div id="map_canvas" style="height: 300px;width:100%;"></div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </div>
                </form>
            <div class="col-xs-12 col-sm-6">
            </div>
        </div>
    </div>
@stop
@section('script')
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAvYQkf-70Ka1kpQnAy2DB2-KB36RqMF8o&v=weekly&libraries=places"></script>

    <script>
        App.initFormView();
        $('.sel2').select2();
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
                            var m = res['message'];
                            App.alert(m, 'Oops!');
                        }
                    } else {
                        App.alert(res['message'], 'Success!');
                                setTimeout(function(){
                                    window.location.href = App.siteUrl('/admin/vendor/locations/')+'{{$user_id}}';
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



        var currentLat = {{ $latitude  }};
        var currentLong = {{ $longitude  }};
        $("#location").val(currentLat + "," + currentLong);
        currentlocation = {
            lat: currentLat,
            lng: currentLong,
        };
        initMap();
        initAutocomplete();
        function initMap() {
            map2 = new google.maps.Map(document.getElementById("map_canvas"), {
                center: {
                    lat: currentlocation.lat,
                    lng: currentlocation.lng,
                },
                zoom: 14,
                gestureHandling: "greedy",
                mapTypeControl: false,
                mapTypeControlOptions: {
                    style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
                },
                streetViewControlOptions: {
                    position: google.maps.ControlPosition.LEFT_BOTTOM,
                },
            });

            geocoder = new google.maps.Geocoder();

            // geocoder2 = new google.maps.Geocoder;
            usermarker = new google.maps.Marker({
                position: {
                    lat: currentlocation.lat,
                    lng: currentlocation.lng,
                },
                map: map2,
                draggable: true,

                animation: google.maps.Animation.BOUNCE,
            });

            //map click
            google.maps.event.addListener(map2, "click", function (event) {
                updatepostition(event.latLng, "movemarker");
                //drag end event
                usermarker.addListener("dragend", function (event) {
                    // alert();
                    updatepostition(event.latLng, "movemarker");
                });
            });

            //drag end event
            usermarker.addListener("dragend", function (event) {
                // alert();
                updatepostition(event.latLng);
            });
        }
        updatepostition = function (position, movemarker) {
            geocodePosition(position);
            usermarker.setPosition(position);
            map2.panTo(position);
            map2.setZoom(15);
            let createLatLong = position.lat() + "," + position.lng();
            console.log("Address Lat/long=" + createLatLong);
            $("#location").val(createLatLong);
        };
        function geocodePosition(pos) {
            geocoder.geocode(
                {
                    latLng: pos,
                },
                function (responses) {
                    if (responses && responses.length > 0) {
                        usermarker.formatted_address = responses[0].formatted_address;
                    } else {
                        usermarker.formatted_address = "Cannot determine address at this location.";
                    }
                    $("#txt_location").val(usermarker.formatted_address);
                }
            );
        }
        function initAutocomplete() {
            // Create the search box and link it to the UI element.
            var input2 = document.getElementById("txt_location");
            var searchBox2 = new google.maps.places.SearchBox(input2);

            map2.addListener("bounds_changed", function () {
                searchBox2.setBounds(map2.getBounds());
            });

            searchBox2.addListener("places_changed", function () {
                var places2 = searchBox2.getPlaces();

                if (places2.length == 0) {
                    return;
                }
                $("#txt_location").val(input2.value);

                var bounds2 = new google.maps.LatLngBounds();
                places2.forEach(function (place) {
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
        updatepostition = function (position, movemarker) {
            console.log(position);
            geocodePosition(position);
            usermarker.setPosition(position);
            map2.panTo(position);
            map2.setZoom(15);
            let createLatLong = position.lat() + "," + position.lng();
            // console.log("Address Lat/long="+createLatLong);
            $("#location").val(createLatLong);
        };

    </script>
@stop
