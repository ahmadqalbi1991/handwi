<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Meta Data -->
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
  <title>Healthy Wealthy - Mobile Application</title>

  <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('') }}front_end/assets/img/fav/apple-icon-57x57.png">
  <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('') }}front_end/assets/img/fav/apple-icon-60x60.png">
  <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('') }}front_end/assets/img/fav/apple-icon-72x72.png">
  <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('') }}front_end/assets/img/fav/apple-icon-76x76.png">
  <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('') }}front_end/assets/img/fav/apple-icon-114x114.png">
  <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('') }}front_end/assets/img/fav/apple-icon-120x120.png">
  <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('') }}front_end/assets/img/fav/apple-icon-144x144.png">
  <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('') }}front_end/assets/img/fav/apple-icon-152x152.png">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('') }}front_end/assets/img/fav/apple-icon-180x180.png">
  <link rel="icon" type="image/png" sizes="192x192"  href="{{ asset('') }}front_end/assets/img/fav/android-icon-192x192.png">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('') }}front_end/assets/img/fav/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('') }}front_end/assets/img/fav/favicon-96x96.png">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('') }}front_end/assets/img/fav/favicon-16x16.png">
  <link rel="manifest" href="{{ asset('') }}front_end/assets/img/fav/manifest.json">
  <meta name="msapplication-TileColor" content="#ffffff">
  <meta name="msapplication-TileImage" content="{{ asset('') }}front_end/assets/img/fav/ms-icon-144x144.png">
  <meta name="theme-color" content="#ffffff">

  <!-- Dependency Styles -->
  <link rel="stylesheet" href="{{ asset('') }}front_end/dependencies/bootstrap/css/bootstrap.min.css" type="text/css">
  <link rel="stylesheet" href="{{ asset('') }}front_end/dependencies/fontawesome/css/all.min.css" type="text/css">
  <link rel="stylesheet" href="{{ asset('') }}front_end/dependencies/swiper/css/swiper.min.css" type="text/css">
  <link rel="stylesheet" href="{{ asset('') }}front_end/dependencies/wow/css/animate.css" type="text/css">
  <link rel="stylesheet" href="{{ asset('') }}front_end/dependencies/magnific-popup/css/magnific-popup.css" type="text/css">
  <link rel="stylesheet" href="{{ asset('') }}front_end/dependencies/components-elegant-icons/css/elegant-icons.min.css" type="text/css">
  <link rel="stylesheet" href="{{ asset('') }}front_end/dependencies/simple-line-icons/css/simple-line-icons.css" type="text/css">



  <!-- Site Stylesheet -->
  <link rel="stylesheet" href="{{ asset('') }}front_end/assets/css/app.css" type="text/css">
  <link rel="stylesheet" href="{{ asset('') }}front_end/assets/css/custom.css" type="text/css">

  <!-- Google Web Fonts -->
  <link rel="preconnect" href="https://fonts.gstatic.com/">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;700;800;900&amp;family=Satisfy&amp;display=swap" rel="stylesheet">
@yield('header')
</head>

<body id="home-version-6" class="home-version-6" data-style="default">

  <a href="#main_content" data-type="section-switch" class="return-to-top">
    <i class="fa fa-chevron-up"></i>
  </a>

  <div class="page-loader">
    <div class="loader">
      <!-- Loader -->
      <div class="blobs">
        <div class="blob-center"></div>
        <div class="blob"></div>
        <div class="blob"></div>
        <div class="blob"></div>
        <div class="blob"></div>
        <div class="blob"></div>
        <div class="blob"></div>
      </div>
      <svg xmlns="http://www.w3.org/2000/svg" version="1.1">
        <defs>
          <filter id="goo">
            <feGaussianBlur in="SourceGraphic" stdDeviation="10" result="blur" />
            <feColorMatrix in="blur" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 18 -7" result="goo" />
            <feBlend in="SourceGraphic" in2="goo" />
          </filter>
        </defs>
      </svg>

    </div>
  </div><!-- /.page-loader -->

  <div id="main_content">


    <!--=========================-->
    <!--=        Navbar         =-->
    <!--=========================-->
    <header class="site-header header-eight header_trans-fixed" data-top="992">
      <div class="container">
        <div class="header-inner">
          <div class="toggle-menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
          </div>
          <!-- /.toggle-menu -->

          <div class="site-mobile-logo">
            <a href="{{url('/')}}" class="logo">
              <img src="{{ asset('') }}front_end/assets/img/logo.svg" alt="site logo" class="main-logo">
              <img src="{{ asset('') }}front_end/assets/img/logo.svg" alt="site logo" class="sticky-logo">
            </a>
          </div>

          <nav class="site-nav">
            <div class="close-menu">
              <span>Close</span>
              <i class="ei ei-icon_close"></i>
            </div>

            <div class="site-logo">
              <a href="{{url('/')}}" class="logo">
                <img src="{{ asset('') }}front_end/assets/img/logo.svg" alt="site logo" class="main-logo">
                <img src="{{ asset('') }}front_end/assets/img/logo.svg" alt="site logo" class="sticky-logo">
              </a>
            </div>
            <!-- /.site-logo -->

            <div class="menu-wrapper" data-top="992">
              <ul class="site-main-menu">
                <li><a href="{{url('/')}}#main_content">Home</a></li>
                <li><a href="{{url('/')}}#howItWorks">How it Work</a></li>
                <li><a href="{{url('/')}}#features">Features</a></li>
                <!-- <li><a href="{{url('/')}}#showcase">Showcase</a></li> -->
                <!-- <li><a href="{{url('/')}}#footer">Contact</a></li> -->
              </ul>
            </div>
            <!-- /.menu-wrapper -->

            <div class="nav-right">
              <a href="{{url('/portal')}}" class="nav-btn">Vendor Registration & Login</a>
            </div>

          </nav><!-- /.site-nav -->
        </div><!-- /.header-inner -->
      </div><!-- /.container -->
    </header><!-- /.site-header -->

   @yield('content')

    <!--=========================-->
    <!--=        Footer         =-->
    <!--=========================-->
    <footer id="footer" class="footer-app">
      <div class="container-wrap bg-footer-color">
        <div class="container">
          <div class="footer-inner">
            <div class="row wow fadeInUp">
              <div class="col-lg-4 col-md-6 col-sm-6">
                <div class="widget footer-widget widget-about">
                  <a href="#" class="footer-logo"><img src="{{ asset('') }}front_end/assets/img/logo-white.svg" alt="saaspik"></a>
                  <p>
                    Your Health is Your Wealth
                  </p>
                </div>
                <!-- /.widget footer-widget -->
              </div>
              <!-- /.col-lg-3 col-md-6 col-sm-6 -->
              <div class="col-lg-4 col-md-6 col-sm-6">
                <div class="widget footer-widget widget-contact">
                  <h3 class="widget-title">Main Office</h3>

                  <ul class="widget-contact-info">
                    <li><i class="ei ei-icon_pin_alt"></i>Dubai, UAE</li>
                    <li><i class="ei ei-icon_phone"></i>+971 55 986 0157</li>
                  </ul>


                </div>
                <!-- /.widget footer-widget -->
              </div>
              <!-- /.col-lg-3 col-md-6 col-sm-6 -->

              <div class="col-lg-2 col-md-6 col-sm-6">
                <div class="widget footer-widget">
                  <h3 class="widget-title">Company</h3>
                  <ul class="footer-menu">
                    <li><a href="{{url('/')}}#howItWorks">How it Work</a></li>
                    <li><a href="{{url('/')}}#features">Features</a></li>
                    <!-- <li><a href="{{url('/')}}#showcase">Showcase</a></li> -->
                    <!-- <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Privacy Policy</a></li> -->

                  </ul>
                </div>
                <!-- /.widget footer-widget -->
              </div>
              <!-- /.col-lg-3 col-md-6 col-sm-6 -->

              <div class="col-lg-2 col-md-6 col-sm-6">
                <div class="widget footer-widget">
                  <h4 class="widget-title">Social</h4>
                  <ul class="social-share-link">
                    <li><a href="#" class="share_facebook"><i class="fab fa-facebook-f"></i></a> Facebook</li>
                    <li><a href="#" class="share_instagram"><i class="fab fa-instagram"></i></a> Instagram</li>
                    <li><a href="#" class="share_snapchat"><i class="fab fa-snapchat-ghost"></i></a> Snapchat</li>
                    <li>
                      <a href="#" class="share_tiktok" style="display: flex; align-items: center; justify-content: center;">
                      <svg style="width: 20px; height: 20px; fill: #fff;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2859 3333" shape-rendering="geometricPrecision" text-rendering="geometricPrecision" image-rendering="optimizeQuality" fill-rule="evenodd" clip-rule="evenodd"><path d="M2081 0c55 473 319 755 778 785v532c-266 26-499-61-770-225v995c0 1264-1378 1659-1932 753-356-583-138-1606 1004-1647v561c-87 14-180 36-265 65-254 86-398 247-358 531 77 544 1075 705 992-358V1h551z"/></svg>
                      </a> TikTok
                    </li>
                    <!-- <li><a href="#" class="share_twitter"><i class="fab fa-twitter"></i> </a>Twitter</li>
                    <li><a href="#" class="share_pinterest"><i class="fab fa-pinterest-p"></i> </a>Pintrest</li> -->
                    <!-- <li><a href="#" class="share_linkedin"><i class="fab fa-linkedin-in"></i> </a>LinkedIn</li> -->
                  </ul>
                </div>
                <!-- /.widget footer-widget -->
              </div>
              <!-- /.col-lg-3 col-md-6 col-sm-6 -->
            </div>
            <!-- /.row -->

          </div><!-- /.footer-inner -->

          <div class="site-info">
            <div class="copyright text-center">
              <p>  © {{date('Y')}} {{ config('global.site_name') }} All Rights Reserved.
              </p>
            </div>
          </div>
        </div><!-- /.container -->


      </div>
      <!-- /.container-wrap -->
    </footer><!-- /#footer -->


  </div><!-- /#site -->

  <!-- Dependency Scripts -->
  <script src="{{ asset('') }}front_end/dependencies/jquery/jquery.min.js"></script>
  <script src="{{ asset('') }}front_end/dependencies/bootstrap/js/bootstrap.min.js"></script>
  <script src="{{ asset('') }}front_end/dependencies/swiper/js/swiper.min.js"></script>
  <script src="{{ asset('') }}front_end/dependencies/jquery.appear/jquery.appear.js"></script>
  <script src="{{ asset('') }}front_end/dependencies/wow/js/wow.min.js"></script>
  <script src="{{ asset('') }}front_end/dependencies/countUp.js/countUp.min.js"></script>
  <script src="{{ asset('') }}front_end/dependencies/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="{{ asset('') }}front_end/dependencies/imagesloaded/imagesloaded.pkgd.min.js"></script>
  <script src="{{ asset('') }}front_end/dependencies/jquery.parallax-scroll/js/jquery.parallax-scroll.js"></script>
  <script src="{{ asset('') }}front_end/dependencies/magnific-popup/js/jquery.magnific-popup.min.js"></script>
  <script src="{{ asset('') }}front_end/dependencies/gmap3/js/gmap3.min.js"></script>
  <script type='text/javascript' src='https://maps.googleapis.com/maps/api/js?key=AIzaSyDk2HrmqE4sWSei0XdKGbOMOHN3Mm2Bf-M&amp;ver=2.1.6'></script>


  <!-- Site Scripts -->
  <script src="{{ asset('') }}front_end/assets/js/header.js"></script>
  <script src="{{ asset('') }}front_end/assets/js/app.js"></script>
  <script>
    const tabs = document.querySelector("#pix-tabs");
const tabButton = document.querySelectorAll(".tab-button");
const contents = document.querySelectorAll(".content");

tabs.onclick = e => {
  const id = e.target.dataset.id;
  if (id) {
    tabButton.forEach(btn => {
      btn.classList.remove("active");
    });
    e.target.classList.add("active");

    contents.forEach(content => {
      content.classList.remove("active");
    });
    const element = document.getElementById(id);
    element.classList.add("active");
  }
}
  </script>
  <script>
    $(function() {
      $('a[href*=\\#]:not([href=\\#])').on('click', function() {
        var target = $(this.hash);
        target = target.length ? target : $('[name=' + this.hash.substr(1) +']');
        if (target.length) {
          $('html,body').animate({
            scrollTop: target.offset().top
          }, 800);
          return false;
        }
      });
    });
  </script>
  <script>
     jQuery(function() {
            App.init({
                site_url: '{{ url('/') }}',
                base_url: '{{ url('/') }}',
                site_name: '{{ config('global.site_name') }}',
            });
            App.toast([]);

            App.initTreeView();
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
            validateString: function (value, requirement, parsleyInstance) {
                let file = parsleyInstance.$element[0].files[0];
                let [width, height] = requirement.split('x');
                let image = new Image();
                let deferred = $.Deferred();

                image.src = window.URL.createObjectURL(file);
                image.onload = function() {
                    if (image.width == width && image.height == height) {
                        deferred.resolve();
                    }
                    else {
                        deferred.reject();
                    }
                };

                return deferred.promise();
            },
            messages: {
                en: 'Image dimensions should be  %spx'
            }
        });
        $('body').off('change', '[data-role="country-change"]');
        $('body').on('change', '[data-role="country-change"]', function() {
            var $t = $(this);
            var $dialcode = $('#'+ $t.data('input-dial-code'));
            var $state = $('#'+ $t.data('input-state'));

            if ( $dialcode.length > 0 ) {
                var code = $t.find('option:selected').data('phone-code');
                console.log(code)
                if ( code == '' ) {
                    $dialcode.val('');
                } else {
                    $dialcode.val(code);
                }
            }

            if ( $state.length > 0 ) {

                var id   = $t.val();                
                var html = '<option value="">Select</option>';
                $state.html(html);
                $state.trigger('change');

                if ( id != '' ) {
                    $.ajax({
                        type: "POST",
                        enctype: 'multipart/form-data',
                        url: "{{url('common/states/get_by_country')}}",
                        data: {
                            "id": id,
                            "_token": "{{ csrf_token() }}"
                        },
                        timeout: 600000,
                        dataType: 'json',
                        success: function(res) {
                            for (var i=0; i < res['states'].length; i++) {
                                html += '<option value="'+ res['states'][i]['id'] +'">'+ res['states'][i]['name'] +'</option>';
                                if ( i == res['states'].length-1 ) {
                                    $state.html(html);
                                }
                                
                            }
                            $state.niceSelect('update');
                        }
                    });
                }
            }
        });
        $('body').off('change', '[data-role="state-change"]');
        $('body').on('change', '[data-role="state-change"]', function() {
          $(this).niceSelect('update');
            var $t = $(this);
            var $city = $('#'+ $t.data('input-city'));

            if ( $city.length > 0 ) {
                var id   = $t.val();
                var html = '<option value="">Select</option>';

                $city.html(html);
                if ( id != '' ) {
                    $.ajax({
                        type: "POST",
                        enctype: 'multipart/form-data',
                        url: "{{url('common/cities/get_by_state')}}",
                        data: {
                            "id": id,
                            "_token": "{{ csrf_token() }}"
                        },
                        timeout: 600000,
                        dataType: 'json',
                        success: function(res) {
                            for (var i=0; i < res['cities'].length; i++) {
                            html += '<option value="'+ res['cities'][i]['id'] +'">'+ res['cities'][i]['name'] +'</option>';
                            if ( i == res['cities'].length-1 ) {
                                $city.html(html);
                               // $('.selectpicker').selectpicker('refresh')
                            }
                        }
                        $city.niceSelect('update');
                        }
                    });
                }

            }
        });  
  </script>      
  @yield('script')
</body>

</html>