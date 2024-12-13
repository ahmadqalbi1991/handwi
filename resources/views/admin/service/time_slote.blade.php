@extends('admin.template.layout')

@section('content')
@php
$id = 1;
@endphp
@if(!empty($datamain->vendordatils))
@php
 $vendor     = $datamain->vendordatils;
 $bankdata   = $datamain->bankdetails;
 
@endphp
@endif
    <div class="mb-5">
    <link href="{{ asset('') }}admin-assets/jquery.timepicker.min.css" rel="stylesheet" type="text/css" />
    <style>
        #parsley-id-23{
            bottom:0 !important
        }
        #parsley-id-66, #parsley-id-60, #parsley-id-21{
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
    </style>
                <!--<div class="card p-4">-->
                   
                    


                            

                            
                            @php $days = Config('global.days');  @endphp
                            <form method="post" id="admin-form" action="{{ url('admin/save_time_slote') }}" enctype="multipart/form-data"
                    data-parsley-validate="true">
                    <div class="">
                    <input type="hidden" name="id" value="{{ $id }}">
                    @csrf()
                            <div class="card mt-4 weekly" style="border-radius: 5px; overflow: hidden;" >
                                            <div class="card-body">
                                                <h4><b>Service Timing</b></h4>
                                               
                                                <table class="table table-condensed pl-2 mt-2 workinghours align-top" >
                                                   
                                                @foreach($days as $key => $val)
                                                            @php 
                                                                $st = $key.'_from'; $ed = $key.'_to';
                                                                $timings = $selected_days[$val]??[];
                                                                //printr($timings);
                                                            @endphp
                                                            <tr class="align-top">
                                                                <td class="align-top">
                                                                    <div class="custom-checkbox">
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
                                                                <!-- <td style="display:{{(!empty($timings) && !empty($timings['from_hours']) )?'none':'none'}}">
                                                                    <div class="custom-checkbox">
                                                                        <input @if(!empty($timings)) @if($timings['open_24']==1) checked @endif @endif type="checkbox" class="24_hour_open" id="grm_day_24_{{$val}}"  name="{{$val}}_24" value="1"> &nbsp;
                                                                        <label for="grm_day_24_{{$val}}"> 24 Hour</label>
                                                                    </div>
                                                                
                                        
                                                                </td> -->
                                                                <td class="{{$key}}-from-div days-div align-top" style="display:{{$style;}};">
                                                                    @if(!empty($timings['from_hours']))
                                                                        @foreach($timings['from_hours'] as $index=>$time_key)
                                                                        <input type="text"  class="time form-control r-{{$index}}" data-parsley-required id="{{$key}}_from_grooming"  name="{{$key}}_from_grooming[]" value="{{$time_key}}"  placeholder="Start Time">
                                                                        @endforeach
                                                                    @else
                                                                    <input type="text"  class="time form-control" data-parsley-required id="{{$key}}_from_grooming"  name="{{$key}}_from_grooming[]" value=""   placeholder="Start Time">
                                                                    @endif
                                                                </td>
                                                                <td class="{{$key}}-to-div days-div align-top" style="display:{{$style;}};">
                                                                    @if(!empty($timings['to_hours']))
                                                                        @foreach($timings['to_hours'] as $index=> $time_key2)
                                                                            <input type="text" value="{{$time_key2}}"  class="time form-control r-{{$index}}" data-parsley-required data-parsley-daterangevalidation{{$key}} data-parsley-daterangevalidation{{$key}}-requirement="#{{$key}}_from_grooming"  data-parsley-greater-than-message="End time should be after start time" name="{{$key}}_to_grooming[]"  placeholder="End Time">
                                                                        @endforeach
                                                                    @else
                                                                    <input type="text"   class="time form-control" data-parsley-required data-parsley-daterangevalidation{{$key}} data-parsley-daterangevalidation{{$key}}-requirement="#{{$key}}_from_grooming"  data-parsley-greater-than-message="End time should be after start time" name="{{$key}}_to_grooming[]"  placeholder="End Time">
                                                                    @endif
                                                                </td>
                                                                <td class="{{$key}}-btn-div align-top" style="display:{{$style;}};">
                                                                    <button type="button" class="btn btn-primary time-add-btn " style="padding:0 !important;" data-val="{{$key}}"  data-name="{{$key}}_to_grooming">+</button>
                                                                    @php 
                                                                    for($i=0;$i< count($timings['from_hours'])-1; $i++){
                                                                    @endphp
                                                                    <button type="button" data-k="{{$key}}" data-rem="r-{{$i+1}}" class="btn btn-danger rm-btn time_remove_btn_1" style="padding:0 !important;">-</button>
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









                            </div>
                </form>
                </div>
@stop

@section('script')
       
        <script src="//jonthornton.github.io/jquery-timepicker/jquery.timepicker.js"></script>
    <script>
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
            var from_html = `<input type="text"   class="time form-control rem-`+uniqueString+`"   name="`+key_val+`_from_grooming[]"   placeholder="Start Time">`;
            var to_html = `<input type="text"  class="time form-control rem-`+uniqueString+`"   name="`+key_val+`_to_grooming[]"   placeholder="End Time">`;
            var btn_html = `<button type="button" data-rem="rem-`+uniqueString+`" class="btn btn-danger remove-btn time_remove_btn_1" style="padding:0 !important;">-</button>`;
            
            $(`.`+key_val+'-from-div').append(from_html);
            $(`.`+key_val+'-to-div').append(to_html);
            $(`.`+key_val+'-btn-div').append(btn_html);
            $('.time').timepicker({
                timeFormat:'h:i A',
                step: '60',
                minTime: '9:00 AM',
                maxTime: '9:00 PM',
                dynamic: false,
                dropdown: true,
                scrollbar: true
            });
        });


        $(document).ready(function() {
            $('select').select2();
        });


        $('.time').timepicker({
            timeFormat:'h:i A',
            step: '60',
minTime: '9:00 AM',
maxTime: '9:00 PM',
dynamic: false,
dropdown: true,
scrollbar: true
            });

       
        App.initFormView();
       
       
        $('body').off('submit', '#admin-form');
        $('body').on('submit', '#admin-form', function(e) {
            e.preventDefault();
            $( ".invalid-feedback" ).remove();
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
                            window.location.href = App.siteUrl('/admin/services_time_slote');
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

@stop
