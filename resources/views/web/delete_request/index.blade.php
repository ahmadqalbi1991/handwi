<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Delete Account</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Afacad:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css">
  <link href="{{asset('admin-assets/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
  <link href="{{asset('admin-assets/assets/css/parsley.css')}}" rel="stylesheet" type="text/css" />
  
  <style>
    body{
      font-family: 'Afacad', sans-serif;
    }
    *, ::after, ::before {
        box-sizing: border-box;
    }
    .row {
      display: flex;
    }

    .col-md-12 {
      width: 100%;
    }

    .text-center{
      text-align: center;
    }
    
    .d-flex{
      flex-wrap: wrap;
      display: flex;
    }

    .justify-content-center{
      justify-content: center;
    }
    .form-label {
      margin-bottom: .5rem;
      display: inline-block;
    }
    .form-control {
        display: block;
        width: 100%;
        padding: .375rem .75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #1e1e1e;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-color: #fbfbfb;
        background-clip: padding-box;
        border: 1px solid #ccc;
        border-radius: 5px;
        transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
    }
    .mb-3{
      margin-bottom: 16px;;
    }
    .from-container{
      width: 100%;
      max-width: 480px;
      background: #e9f2f7;
      padding: 40px 30px;
      border-radius: 15px;
    }
    .col-4{
      width: 35%;
    }
    .col-8{
      width: 65%;
    }
    .form-select {
      display: block;
      width: 100%;
      padding: .375rem 2.25rem .375rem .75rem;
      font-size: 1rem;
      font-weight: 400;
      line-height: 1.5;
        color: #1e1e1e;
      -webkit-appearance: none;
      -moz-appearance: none;
      appearance: none;
      border: 1px solid #ccc;
      border-radius: 5px;
      transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
  }
  .iti--allow-dropdown {
    width: 100%;
  }
  .w-100{
    width: 100%;
  }
  .btn {
    display: inline-block;
    font-weight: 400;
    color: #212529;
    text-align: center;
    vertical-align: middle;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    background-color: transparent;
    border: 1px solid transparent;
    padding: .375rem .75rem;
    font-size: 1rem;
    line-height: 1.5;
    border-radius: .25rem;
    transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
  }
  .btn-primary {
    color: #fff;
    background-color: #007bff;
    border-color: #007bff;
  }
  .btn:not(:disabled):not(.disabled) {
    cursor: pointer;
  }
  </style>
</head>
<body>

<div class="container">
  <div class="text-center mb-4"><br>
    <img src="https://leconciergeapp.ae/images/Le-Concierge-Logo.png" style=" padding: 5px; background: #2C93FA; border-radius: 5px; " width="180" /><br>
    <h1>Delete Account</h1>
  </div>
  <div class="row">
    <div class="col-md-12 d-flex justify-content-center">
      <form class="from-container" id="admin-form" action="{{url('delete_request_store')}}" methode="post"  data-parsley-validate="true">
      @csrf()
      <div class="mb-3">
          <label for="exampleInputEmail1" class="form-label">Name</label>
          <input type="text" class="form-control" name="name" required id="exampleInputEmail1" aria-describedby="emailHelp">
        </div>
        <div class="mb-3">
          <label for="exampleInputEmail1" class="form-label">Email address</label>
          <input type="email" class="form-control" name="email" required id="exampleInputEmail1" aria-describedby="emailHelp">
        </div>
        <div class="mb-3">
          <label for="exampleInputEmail1" class="form-label">Mobile Number</label>
          <input type="text" class="form-control" place_holder="" name="phone" data-parsley-minlength="5" oninput="validateNumber(this);" required id="phone_number">
        </div>
        <div class="mb-3">
          <label for="exampleFormControlTextarea1" class="form-label">Your comments</label>
          <textarea class="form-control" required name="message" id="exampleFormControlTextarea1" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-primary w-100">Submit</button>
      </form>
    </div>
  </div>
</div>
<script src="{{asset('admin-assets/assets/js/libs/jquery-3.1.1.min.js')}}"></script>
<script src="{{asset('admin-assets/bootstrap/js/popper.min.js')}}"></script>
<script src="{{asset('admin-assets/bootstrap/js/bootstrap.min.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js"></script>
<script
            src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"
            integrity="sha512-eyHL1atYNycXNXZMDndxrDhNAegH2BDWt1TmkXJPoGf1WLlNYt08CSjkqF5lnCRmdm3IrkHid8s2jOUY4NIZVQ=="
            crossorigin="anonymous"
            referrerpolicy="no-referrer"
        ></script>
<!-- <script>
  const input = document.querySelector("#phone");
  window.intlTelInput(input, {
    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js",
  });
</script> -->
  <script src="{{asset('https://healthywealthy.ae/public/js/app.js')}}"></script>
<script>
        App.initFormView();
        // $(document).ready(function() {
        //     if (!$("#cid").val()) {
        //         $(".b_img_div").removeClass("d-none");
        //     }
        // });
        // $(".parent_cat").change(function() {
        //     if (!$(this).val()) {
        //         $(".b_img_div").removeClass("d-none");
        //     } else {
        //         $(".b_img_div").addClass("d-none");
        //     }
        // });
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
                            'Unable to save service. Please try again later.';
                            App.alert(m, 'Oops!');
                        }
                    } else {
                        App.alert(res['message'], 'Success!');
                                setTimeout(function(){
                                  window.location.reload();
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
        var validNumber = new RegExp(/^\d*\.?\d*$/);
var lastValid = 0;
function validateNumber(elem) {
  if (validNumber.test(elem.value)) {
    lastValid = elem.value;
  } else {
    elem.value = lastValid;
  }
}
    </script>
    <script>
      var phone_number = window.intlTelInput(document.querySelector("#phone_number"), {
  separateDialCode: true,
  preferredCountries:["ae"],
  hiddenInput: "full",
  utilsScript: "//cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.3/js/utils.js",
});

$("form").submit(function() {
  var full_number = phone_number.getNumber(intlTelInputUtils.numberFormat.E164);
$("input[name='phone").val(full_number);
 
});
$(function(){
  $("#phone_number").attr("placeholder", "11");
});
    </script>
</body>
</html>
