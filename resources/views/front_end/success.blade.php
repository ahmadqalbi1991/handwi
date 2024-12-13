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
  <body class="register" style="background: url('{{ asset('') }}admin-assets/assets/img/Admin-background.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
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
    </style>
    <div class="container registraion-box">
        <div class="row justify-content-center mt-3 mb-2">
            <div class="col-12 text-center">
                <img alt="logo" src="{{ asset('') }}admin-assets/assets/img/logo-provider.svg" class="img-fluid mb-2">
                <h2 id="heading">Vendor Registration successfull</h2>
                <p class="text-white">Fill all form field to go to next step</p>
            </div>
            <div class="col-12">
                <div class="form-card" id="msform">
                     
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
    
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB8QkOt74HuPCD8N6m1OfwSzyb0NWnjorg&v=weekly&libraries=places"></script>
    <script src="{{ asset('') }}front_end/vendor-assets/js/app.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('admin-assets/plugins/notification/toastr/toastr.min.js') }}"></script>
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
                        toastr["success"](res['message']);
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