@extends("admin.template.layout")

@section("header")
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin_assets/plugins/table/datatable/datatables.css">
    <link rel="stylesheet" type="text/css" href="{{asset('')}}admin_assets/plugins/table/datatable/custom_dt_customer.css">
@stop
@section('content')

<div class="card mb-5">
    
    <div class="row card-body">

        <div class="col-xl-12 col-lg-12 col-sm-12">
            @if ( session('success'))
                <div class="alert alert-success alert-dismissable custom-success-box" style="margin: 15px;">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <strong> {{ session('success') }} </strong>
                </div>
            @endif
            @if ( session('error'))
                <div class="alert alert-danger alert-dismissable custom-danger-box" style="margin: 15px;">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    <strong> {{ session('error') }} </strong>
                </div>
            @endif
            <div class="statbox widget box">
                <div class=" mt-3">
                    <form method="post" id="admin-form" action="{{route('admin.contact_us_setting_store')}}" enctype="multipart/form-data">
                        @csrf()
                        <input type="hidden" name="id" value=" {{$page->id}}">
                        <div class="row">   
                            <div class="col-md-6 form-group">
                                <label>Title (eng)</label>
                                <input type="text" name="title_en" class="form-control jqv-input" data-jqv-required="true" value="{{$page->title_en}} " placeholder=" Title (eng)" required>
                            </div>
                            <div class="col-md-6 form-group d-none">
                                <label>Title (ar) </label>
                                <input type="text" name="title_ar" class="form-control jqv-input" data-jqv-required="true" value="{{$page->title_ar}} " placeholder=" Title (ar)">
                            </div>

                            <div class="col-md-6 form-group">
                                <label>Email </label>
                                <input type="email" name="email" class="form-control jqv-input" data-jqv-required="true" value="{{$page->email}} " placeholder="Enter Email" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Mobile </label>
                                <input type="text" name="mobile" class="form-control jqv-input" data-jqv-required="true" value="{{$page->mobile}} " placeholder="Enter Mobile " required>
                            </div>
                             <div class="col-md-6 form-group">
                                <label>Facebook </label>
                                <input type="url" name="facebook" class="form-control jqv-input" data-jqv-required="true" value="{{$page->facebook}} " placeholder="Enter Facebook link " >
                            </div>
                             <div class="col-md-6 form-group">
                                <label>Twitter </label>
                                <input type="url" name="twitter" class="form-control jqv-input" data-jqv-required="true" value="{{$page->twitter}} " placeholder="Enter Twitter link " >
                            </div>
                             <div class="col-md-6 form-group">
                                <label>Instagram </label>
                                <input type="url" name="instagram" class="form-control jqv-input" data-jqv-required="true" value="{{$page->instagram}} " placeholder="Enter Instagram link " >
                            </div>
                             <div class="col-md-6 form-group">
                                <label>Youtube </label>
                                <input type="url" name="youtube" class="form-control jqv-input" data-jqv-required="true" value="{{$page->youtube}} " placeholder="Enter Youtube link " >
                            </div>
                             <div class="col-md-6 form-group">
                                <label>Linkedin </label>
                                <input type="url" name="linkedin" class="form-control jqv-input" data-jqv-required="true" value="{{$page->linkedin}} " placeholder="Enter Linkedin link " >
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Location </label>
                                <input type="text" name="location" class="form-control jqv-input" data-jqv-required="true" value="{{$page->location}} " placeholder="Enter Location" required>
 <!--id="location" -->
                                <input type="hidden" name="latitude" id="latitude" class="form-control jqv-input" data-jqv-required="true" value="{{$page->latitude}} " placeholder="Enter Location">

                                <input type="hidden" name="longitude" id="longitude" class="form-control jqv-input" data-jqv-required="true" value=" {{$page->longitude}} " placeholder="Enter Location">
                            </div>
                            <div id="map" class="map-wrap"></div>

                            <!--<div class="col-md-6 form-group">-->
                            <!--    <label>Transport Website Link </label>-->
                            <!--    <input type="url" name="transport_website_link" class="form-control jqv-input" data-jqv-required="true" value="{{$page->transport_website_link}} " placeholder="Transport Website Link" >-->
                            <!--</div>-->

                                    
                        </div>
                            <div class="row">   

                            <!--<div class="col-md-12 form-group">-->
                            <!--    <label>Description (eng) </label>-->
                            <!--    <textarea class="form-control description" id ="desc_en" placeholder="Description (eng)" name="desc_en">{{$page->desc_en}} </textarea>-->
                            <!--</div>-->
                            <!--<div class="col-md-6 form-group d-none">-->
                            <!--    <label>Description (ar) </label>-->
                            <!--    <textarea class="form-control description" id="desc_ar" placeholder="Description (ar)" name="desc_ar">{{$page->desc_ar}} </textarea>-->
                            <!--</div>-->
                            </div>
                            <div class="form-group">
                                <button type="submit" class="mt-4 btn btn-primary">Save </button>
                            </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')

                <script>
    var map;
    var marker = false;
    var geocoder;
    function initMap() {
        var latitude = 25.204819;
        var longitude = 55.270931;

        var myLatLng = {
            lat: latitude,
            lng: longitude
        };

        map = new google.maps.Map(document.getElementById('map'), {
            center: myLatLng,
            zoom: 14,
            mapTypeControl: false,
            mapTypeId: 'roadmap'
        });
        var iconBase = 'http://localhost/snabbkart/assets/web/images/map-pin.png';
        //var marker = false; ////Has the restaurant plotted their location marker?
        marker = new google.maps.Marker({
            draggable: true,
            position: myLatLng,
            map: map,
            icon: iconBase
        });

        geocoder = new google.maps.Geocoder();

        geocoder.geocode({
            'latLng': myLatLng
        }, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (results[0]) {
                    $('#location').val(results[0].formatted_address);
                }
            }
        });

        google.maps.event.addListener(marker, 'dragend', function() {
            geocoder.geocode({
                'latLng': marker.getPosition()
            }, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[0]) {
                        $('#location').val(results[0].formatted_address);
                    }
                }
            });
        });

        google.maps.event.addListener(map, 'click', function() {
            geocoder.geocode({
                'latLng': marker.getPosition()
            }, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[0]) {
                        $('#location').val(results[0].formatted_address);
                    }
                }
            });
        });

        //Listen for any clicks on the map.
        google.maps.event.addListener(map, 'click', function(event) {
            //Get the location that the restaurant clicked.
            var clickedLocation = event.latLng;
            //If the marker hasn't been added.
            if (marker === false) {
                //Create the marker.
                marker = new google.maps.Marker({
                    position: clickedLocation,
                    map: map,
                    draggable: true //make it draggable
                });

            } else {
                //Marker has already been added, so just change its location.
                marker.setPosition(clickedLocation);
            }
            //Get the marker's location.
            var currentLocation = marker.getPosition();
            //Add lat and lng values to a field that we can save.
            document.getElementById('latitude').value = currentLocation.lat(); //latitude
            document.getElementById('longitude').value = currentLocation.lng(); //longitude


        });

        //Listen for drag events!
        google.maps.event.addListener(marker, 'dragend', function(event) {
            var currentLocation = marker.getPosition();
            //Add lat and lng values to a field that we can save.
            document.getElementById('latitude').value = currentLocation.lat(); //latitude
            document.getElementById('longitude').value = currentLocation.lng(); //longitude
        });

        // Create the search box and link it to the UI element.
        // var options = {
        //     types: ['(cities)']
        // };
        var options = {
            fields: ["formatted_address", "geometry", "name"],
            strictBounds: false,
            types: ["establishment"],
          };
        var input = document.getElementById('location');
        var searchBox = new google.maps.places.SearchBox(input, options);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

        // Bias the SearchBox results towards current map's viewport.
        map.addListener('bounds_changed', function() {
            searchBox.setBounds(map.getBounds());
        });

        var markers = [];
        // Listen for the event fired when the restaurant selects a prediction and retrieve
        // more details for that place.
        searchBox.addListener('places_changed', function() {
            var places = searchBox.getPlaces();

            if (places.length == 0) {
                return;
            }

            // Clear out the old markers.
            markers.forEach(function(marker) {
                marker.setMap(null);
            });
            markers = [];

            // For each place, get the icon, name and location.
            var bounds = new google.maps.LatLngBounds();
            places.forEach(function(place) {
                if (!place.geometry) {
                    console.log("Returned place contains no geometry");
                    return;
                }
                var icon = {
                    url: place.icon,
                    size: new google.maps.Size(71, 71),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point(17, 34),
                    scaledSize: new google.maps.Size(25, 25)
                };

                // Create a marker for each place.
                markers.push(new google.maps.Marker({
                    map: map,
                    icon: icon,
                    title: place.name,
                    position: place.geometry.location
                }));

                if (place.geometry.viewport) {
                    // Only geocodes have viewport.
                    bounds.union(place.geometry.viewport);
                } else {
                    bounds.extend(place.geometry.location);
                }

                //alert(place.geometry.location.lat());alert(place.geometry.location.lng());
                document.getElementById('latitude').value = place.geometry.location.lat(); //latitude
                document.getElementById('longitude').value = place.geometry.location.lng(); //longitude

                marker.setPosition({
                    lat: place.geometry.location.lat(),
                    lng: place.geometry.location.lng()
                });
                map.setCenter(marker.getPosition());

            });

            map.fitBounds(bounds);
        });
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAyW33QtbRMkT_-tjb5Ff3_Y2-B-aq98u8&libraries=places&callback=initMap"></script>

@endsection