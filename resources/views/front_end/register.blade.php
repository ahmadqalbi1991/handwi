<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Meta Data -->
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1" />
    <title>{{config('app.name')}} | Vendor Registration</title>
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('') }}admin-assets/assets/img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('') }}admin-assets/assets/img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('') }}admin-assets/assets/img/favicon/favicon-16x16.png">
    <link rel="manifest" href="{{ asset('') }}admin-assets/assets/img/favicon/site.webmanifest">
    <link rel="mask-icon" href="{{ asset('') }}admin-assets/assets/img/favicon/safari-pinned-tab.svg" color="#ac772b">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css" integrity="sha512-MQXduO8IQnJVq1qmySpN87QQkiR1bZHtorbJBD0tzy7/0U9+YIC93QWHeGTEoojMVHWWNkoCp8V6OzVSYrX0oQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('') }}front_end/vendor-assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="{{ asset('') }}front_end/vendor-assets/css/app.css" />
    <link href="{{ asset('') }}admin-assets/assets/css/parsley.css" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin-assets/plugins/notification/toastr/toastr.min.css') }}" rel="stylesheet" type="text/css" />
    
</head>
  <!--<body class="register" style="background: url('{{ asset('') }}admin-assets/assets/img/Admin-background.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">-->
  <body class="register" style="background: #121212;">
    <style>
        .select2-hidden-accessible.parsley-error .parsley-errors-list.filled{
            position: absolute;
            bottom: 0;
        }
        .parsley-errors-list.filled {
            opacity: 1;
            position: absolute;
            right: 20px;
            top: 10px;
        }
        .parsley-errors-list.filled#parsley-id-26{
            top: -20px
        }
        input[type="checkbox"] + label:before{
            border-color: #ffffff;
            /*border-color: #2C93FA;*/
        }
        .text-black{
            color: #000 !important;
        }
        .form-check-error .parsley-errors-list.filled{
            top: 40px;
            left: 36px;
            right: auto;
        }
        .gap-3{
            gap: 30px;
            flex-wrap: wrap;
        }
        
        /*@media(min-width:992px){*/
        /*    .right-left-border{*/
        /*        padding: 0 20px;*/
        /*        border-left: 1px solid #000;*/
        /*        border-right: 1px solid #000;*/
        /*    }*/
        /*}*/
        body {
  font-family: "Open Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", Helvetica, Arial, sans-serif; 
}
    </style>
    <div class="container registraion-box">
        <div class="row justify-content-center mt-3 mb-2">
            <div class="col-12 text-center">
                <img alt="logo" src="{{ asset('') }}admin-assets/assets/img/admin_logo_new.svg" class="img-fluid mb-2">
                <h2 id="heading">Vendor Registration</h2>
                <p class="text-white">Fill all form field to go to next step</p>
            </div>
            <div class="col-12">
                <div class="form-card" id="msform">
                     <form id="msform" class="reg_form" data-parsley-validate="true" action="{{ url('save_vendor') }}" enctype="multipart/form-data" method="post">
                        @csrf()
                    <div class="row">
                    <div class="col-lg-6 position-relative" style="display:none;">
                            <label class="fieldlabels">Activity type: <b class="text-danger">*</b></label>
                            <select class="form-control" name="activity_id" id="activity_id" required
                                            data-parsley-required-message="Select activity type">
                              <option value="7">Select activity type</option>
                           
                            </select>
                        </div>
                        <div class="col-lg-6 position-relative">
                            <label class="fieldlabels">Company Name: <b class="text-danger">*</b></label>
                            <input type="text" name="company_legal_name" placeholder="Company Name" required data-parsley-required-message="Enter Company Name">
                        </div>
                        <div  class="col-lg-12 position-relative" style="display:none;">
                            <label class="fieldlabels">Vendor Type: <b class="text-danger">*</b></label>
                            <div class="d-flex align-items-center pl-1">
                                <div class="mr-3">
                                    <input type="checkbox" id="Pharmacy" name="type[]" value="1" required data-parsley-required-message="Check at least one Vendor Type" data-parsley-mincheck="1" data-parsley-errors-container="#checkbox-errors"> 
                                    <label for="Pharmacy">Pharmacy</label>
                                </div>
                                <div class="">
                                    <input type="checkbox" id="health-services" name="type[]" value="2" checked>
                                    <label for="health-services">Health Services</label>
                                </div>
                                &nbsp;&nbsp;<div id="checkbox-errors"></div>
                            </div>
                        </div>
                    
                        <div class="col-lg-6 position-relative">
                            <label class="fieldlabels">First Name: <b class="text-danger">*</b></label>
                            <input type="text" name="first_name" placeholder="First Name" required data-parsley-required-message="Enter First Name">
                        </div>
                        <div class="col-lg-6 position-relative">
                            <label class="fieldlabels">Last Name: <b class="text-danger">*</b></label>
                            <input type="text" name="last_name" placeholder="Last Name" required data-parsley-required-message="Enter Last name">
                        </div>
                       
                        <div class="col-lg-6 position-relative">
                            <label class="fieldlabels">Email: <b class="text-danger">*</b></label>
                            <input type="email" name="email" placeholder="Email" required
                                            data-parsley-required-message="Enter Email">
                        </div>
                        <div class="col-lg-6 position-relative">
                            <label class="fieldlabels">Password: <b class="text-danger">*</b></label>
                            <input type="password" data-parsley-pattern="(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}" data-parsley-pattern-message="Password must contain minimum 8 characters at least 1 alphabet, 1 number , 1 special character without #" name="password" placeholder="Password" data-parsley-minlength="8" autocomplete="off"  required
                                            data-parsley-required-message="Enter Password" id="password">
                        </div>
                        <div class="col-lg-6 position-relative">
                            <label class="fieldlabels">Confirm Password: <b class="text-danger">*</b></label>
                            <input type="password" name="confirm_password" placeholder="Confirm Password" data-parsley-minlength="8"
                                            data-parsley-equalto="#password" autocomplete="off" required data-parsley-required-message="Please Confirm Password">
                        </div>
                        <div class="col-lg-6 position-relative">
                            <label class="fieldlabels">Phone Number: <b class="text-danger">*</b></label>
                            <div class="row">
                              <div class="col-lg-3 col-md-4">
                                <select class="form-control" name="dial_code" id="" required
                                                    data-parsley-required-message="">
                                  <option value="">Select</option>
                                   @foreach ($countries as $cnt)
                                                            <option value="{{ $cnt->dial_code }}">
                                                               +{{$cnt->dial_code}}</option>
                                                        @endforeach;
                                </select>
                              </div>
                              <div class="col-lg-9 col-md-8">
                                <input type="number" name="phone" placeholder="Phone Number" required data-parsley-required-message="Phone Number Required" data-parsley-type="digits" data-parsley-minlength="5"  data-parsley-maxlength="12"/>
                              </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-6 position-relative">
                            <label class="fieldlabels">Address Line 1: <b class="text-danger">*</b></label>
                            <input type="text" name="address1" placeholder="Address Line 1" required
                                            data-parsley-required-message="Enter Address Line 1" data-parsley-group="tb1"/>
                        </div>
                        <div class="col-lg-6 position-relative">
                            <label class="fieldlabels">Address Line 2:</label>
                            <input type="text" name="address2" placeholder="Address Line 2" />
                        </div>
                        <div class="col-lg-6 position-relative">
                            <label class="fieldlabels">Street Name/No: <b class="text-danger">*</b></label>
                            <input type="text" name="street" placeholder="Street Name/No" required
                                            data-parsley-required-message="Enter Street Name/No" />
                        </div>
                        
                        <div class="col-lg-6 position-relative">
                            <label class="fieldlabels">Country: <b class="text-danger">*</b></label>
                            <select class="form-control" name="country_id" id="country_id" required
                                            data-parsley-required-message="Select Country">
                              <option value="">Select Country</option>
                              @foreach ($countries as $cnt)
                                                    <option value="{{ $cnt->id }}">
                                                        {{ $cnt->name }}</option>
                                                @endforeach;
                            </select>
                        </div>
                        <div class="col-lg-6 position-relative">
                            <label class="fieldlabels">City: <b class="text-danger">*</b></label>
                            <select class="form-control" name="city_id" required
                                            data-parsley-required-message="Select City" id="city_id">
                              <option value="">Select</option>
                            </select>
                        </div>
                        <div class="col-lg-6 position-relative" style="display:none;">
                            <label class="fieldlabels">Area: <b class="text-danger">*</b></label>
                            <input type="text" name="area" placeholder="Area"
                                            data-parsley-required-message="Enter Area" />
                        </div>
                        <div class="col-lg-6 position-relative">
                            <label class="fieldlabels">Zip Code: <b class="text-danger">*</b></label>
                            <input type="number" name="zip" placeholder="Zip Code" required
                                            data-parsley-required-message="Enter Zip code" />
                        </div>
                            <div class="col-lg-6 position-relative">
                            <label class="fieldlabels">Logo: <b class="text-danger">*</b></label>
                            <input type="file" name="logo" placeholder="Logo" required
                                            data-parsley-required-message="Logo is required" data-parsley-trigger="change" data-parsley-fileextension="jpg,png,gif,jpeg" data-parsley-fileextension-message="Only files with type jpg, png, gif, jpeg are supported" >
                        </div>
                        
                        <div class="col-lg-6 position-relative">
                            <label class="fieldlabels">Trade License: <b class="text-danger">*</b></label>
                            <input type="file" name="trade_licence" placeholder="Trade License" data-parsley-fileextension="jpg,png,jpeg,doc,pdf" required 
                                          data-parsley-trigger="change"  data-parsley-required-message="Trade License is required" data-parsley-fileextension-message="Only files with type jpg, png, jpeg, pdf, doc are supported">
                        </div>
                        <div class="col-lg-6 position-relative">
                            <label class="fieldlabels">Trade License Number: <b class="text-danger">*</b></label>
                            <input type="number" name="trade_licene_number" placeholder="Trade License Number" required data-parsley-required-message="Enter Trade Licence Number">
                        </div>
                        <div class="col-lg-6 position-relative">
                            <label class="fieldlabels">Trade Licence Expiry: <b class="text-danger">*</b></label>
                            <input type="text" class="flat-picker-input" name="trade_licene_expiry" placeholder="Trade License Expiry" required data-parsley-required-message="Enter Trade Licence Expiry">
                        </div>

                         <div class="col-md-12" >
                            <label class="fieldlabels">Enter the location or Drag the marker<b class="text-danger">*</b></b></label>
                            <input type="text" name="txt_location" id="txt_location" class="form-control autocomplete" placeholder="673C+VFH - Dubai - United Arab Emirates" value="673C+VFH - Dubai - United Arab Emirates" required data-parsley-required-message="Enter Location" >
                            <input type="hidden" id="location" name="location">                            
                        </div>
                        
                        <div class="col-md-12 position-relative" style="padding-bottom: 20px;">
                            <div id="map_canvas" style="height: 200px;width:100%;"></div>
                        </div>
                        <!--<div class="col-2 position-relative text-left">-->
                        <!--    <button type="submit" name="submit" class="register-btn">Register</button>-->
                        <!--</div>-->
                        
                        <!--<div class="form-group col-lg-8 form-check-error">-->
                        <!--    <div class="form-check mt-3 p-0 d-flex gap-2 align-items-center">-->
                            <!--<div class="form-check mt-3 p-0 d-flex gap-2 align-items-center justify-content-end">-->
                        <!--        <input class="form-check-input" type="checkbox" required value="" id="flexCheckChecked" style="margin: 0; padding: 0 !important;" />-->
                        <!--        <label class="form-check-label text-black" for="flexCheckChecked"> I agree with <a href="https://leconciergeapp.ae/app/public/page/3" class="font-weight-bold" target="_blank">Terms and Conditions</a> </label>-->
                        <!--    </div>-->
                        <!--</div>-->
                        
                        <!--<div class="col-2 position-relative text-left">-->
                        <!--    <button type="button" name="goback" class="register-btn" onclick="window.location.href='{{url('portal')}}'">Login</button>-->
                        <!--</div>-->
                        
                        <div class="col-12">
                            <div class="d-flex align-items-center gap-3 form-check-error">
                                <button type="submit" name="submit" class="register-btn">Register</button>
                                <div class="position-relative right-left-border">
                                    <div class="form-check mt-3 p-0 d-flex gap-2 align-items-center justify-content-end" style="width: auto;">
                                        <input class="form-check-input" type="checkbox" required value="" id="flexCheckChecked" style="margin: 0; padding: 0 !important;" data-parsley-required-message="Kindly Accept Terms & Conditions"/>
                                        <label class="form-check-label text-black" for="flexCheckChecked"> I agree with <a href="#"  class="font-weight-bold" target="_blank">Terms and Conditions</a> </label>
                                    </div>
                                </div>
                                <button type="button" name="goback" class="register-btn" onclick="window.location.href='{{url('portal')}}'">Login</button>
                            </div>
                        </div>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('') }}front_end/vendor-assets/js/jquery.min.js"></script>
    <script src="{{ asset('') }}front_end/vendor-assets/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"
        integrity="sha512-eyHL1atYNycXNXZMDndxrDhNAegH2BDWt1TmkXJPoGf1WLlNYt08CSjkqF5lnCRmdm3IrkHid8s2jOUY4NIZVQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    
    @section('script')
    
    <script src="{{ asset('admin-assets/plugins/notification/toastr/toastr.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js" integrity="sha512-K/oyQtMXpxI4+K0W7H25UopjM8pzq0yrVdFdG21Fh5dBe91I40pDd9A4lzNlHPHBIP2cwZuoxaUSX0GJSObvGA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/js/all.min.js" integrity="sha512-naukR7I+Nk6gp7p5TMA4ycgfxaZBJ7MO5iC3Fp6ySQyKFHOGfpkSZkYVWV5R7u7cfAicxanwYQ5D1e17EfJcMA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
     
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAvYQkf-70Ka1kpQnAy2DB2-KB36RqMF8o&v=weekly&libraries=places"></script>
    <script src="{{ asset('') }}front_end/vendor-assets/js/app.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('admin-assets/plugins/notification/toastr/toastr.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        var currentLat = 25.204819;
        var currentLong = 55.270931;
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

    // Toaster options
        toastr.options = {
            "closeButton": false,
            "debug": false,
            "newestOnTop": false,
            "progressBar": false,
            "rtl": false,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": 300,
            "hideDuration": 1000,
            "timeOut": 2000,
            "extendedTimeOut": 1000,
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }

        $(document).ready(function() {
        @if (\Session::has('error') && \Session::get('error') != null)
            toastr["error"]("{{\Session::get('error')}}");
        @endif

        })

    $('body').off('submit', '#msform');
        $('body').on('submit', '#msform', function(e) {
            e.preventDefault();
            $('.invalid-feedback').remove();
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
                        $(".fld_set").addClass('d-none');
                        $(".sh_msg").trigger('click');
                        Swal.fire({
  title: "Registration success",
  text: "Your registration is complete! However, your account requires admin verification before you can log in. ",
  icon: "success"
});
                        setTimeout(function() {
                            window.location.href = 'portal';
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
         /* Country to city */
        jQuery(document).on('change', '#country_id', function() {
            $.ajax({
                type: "POST",
                url: '{{ route("getCountryToCity") }}',
                data: {
                    "id": $(this).val(),
                    "sid": 0,
                    "_token": "{{ csrf_token() }}"
                },
                timeout: 600000,
                dataType: 'json',
                success: function(res) {
                    if (res['status'] == true) {
                        $('#city_id').html(res['message']);
                    } else {
                        toastr["error"](response.message);
                    }
                },
                error: function(e) {

                }
            });
        });

         window.Parsley.addValidator('fileextension', {
            validateString: function(value, requirement) {
                var fileExtension = value.split('.').pop();
                extns = requirement.split(',');
                if (extns.indexOf(fileExtension.toLowerCase()) == -1) {
                    return fileExtension === requirement;
                }
            },
        });
        window.Parsley.addValidator('maxFileSize', {
            validateString: function(_value, maxSize, parsleyInstance) {
                var files = parsleyInstance.$element[0].files;
                return files.length != 1 || files[0].size <= maxSize * 1024;
            },
            requirementType: 'integer',
        });
        window.Parsley.addValidator('imagedimensions', {
            requirementType: 'string',
            validateString: function(value, requirement, parsleyInstance) {
                let file = parsleyInstance.$element[0].files[0];
                let [width, height] = requirement.split('x');
                let image = new Image();
                let deferred = $.Deferred();

                image.src = window.URL.createObjectURL(file);
                image.onload = function() {
                    if (image.width == width && image.height == height) {
                        deferred.resolve();
                    } else {
                        deferred.reject();
                    }
                };

                return deferred.promise();
            },
            messages: {
                en: 'Image dimensions should be  %spx'
            }
        });
         

        
</script>


    
  </body>
</html>