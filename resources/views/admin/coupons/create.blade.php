@extends('admin.template.layout')
@section('header')
    <link href="{{asset('')}}admin-assets/bootstrap/css/bootstrap-multiselect.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@stop
@section('content')
    <div class="card mb-5">
        <div class="card-body">
            
<style>
    .select2-container .select2-selection--multiple{
        min-height: 44px; 
    }
    .chekb-select-multi label{
        display: block;
    }
    .chekb-select-multi .custom-select{
        border: 1px solid #ced4da;
        text-align: left !important;
    }
    .chekb-select-multi .btn-group{
        width: 100%;
    }
    .chekb-select-multi .multiselect-container{
        width: 100%;
    }
    .form-group input[type="checkbox"] + label {
    color: #000 !important;
}
</style>

                <form method="post" id="admin-form" action="{{ url('admin/coupons') }}" enctype="multipart/form-data"
                    data-parsley-validate="true" data-parsley-trigger="keyup" >
                    <div class="row">
                    <input type="hidden" name="id" value="{{empty($datamain->id) ? '': $datamain->id}}">
                    @csrf()
                    <div class="col-md-6 form-group">
                        <label>Coupon Code<b class="text-danger">*</b></label>
                        <input type="text" name="coupone_code" class="form-control" required
                            data-parsley-required-message="Enter Coupon Code" value="{{empty($datamain->coupon_code) ? '': $datamain->coupon_code}}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Coupon Amount<b class="text-danger">*</b></label>
                        <input type="text" name="coupone_amount" class="form-control" required
                            data-parsley-required-message="Enter Coupon Amount" maxlength="5" value="{{empty($datamain->coupon_amount) ? '': $datamain->coupon_amount}}">
                    </div>
                     <div class="col-md-2 form-group">
                        <label>Type<b class="text-danger">*</b></label>
                        <select name="amount_type" class="form-control" required
                            data-parsley-required-message="Select Coupon Type">
                            @foreach($amounttype as $data)
                            <option value="{{$data->id}}" @if(!empty($datamain->amount_type)) {{$datamain->amount_type==$data->id ? "selected" : null}} @endif>{{$data->name}}</option>
                            @endforeach
                        </select>
                    </div>
                     
                    <div class="col-md-6 form-group chekb-select-multi">
                        <label>Vendor</label>
 
                        <select name="distributor[]" class="form-control" id="distributor">
                            <option value="">Select</option>
                            @foreach($dist_list as $data)
                            <option value="{{$data->id}}" data-activity="{{$data->activity_id}}" @if(!empty($datamain->coupon_vender_id)) {{$datamain->coupon_vender_id==$data->id ? "selected" : null}} @endif>{{$data->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 form-group" id="applyto">
                        <label>Applied To</label>
                        <select name="applies_to" class="form-control" id="applies_to">
                            <option value="0" selected="true">All</option>
                            <option value="1" selected="" data-show="#browse_category" @if(!empty($datamain->applied_to)) {{$datamain->applied_to==1 ? "selected" : null}} @endif >Category</option>
                            <option value="2" data-show="#browse_product" @if(!empty($datamain->applied_to)) {{$datamain->applied_to==2 ? "selected" : null}} @endif >Product</option>
                            <option value="3" data-show="#browse_services" @if(!empty($datamain->applied_to)) {{$datamain->applied_to==3 ? "selected" : null}} @endif >Services</option>
                        </select>
                    </div>
                      
                     <div class="col-md-6 form-group applies_to_select" id="browse_category">
                        <label>Category</label>
                        <select  class="form-control jqv-input product_catd select2" data-jqv-required="true"
                            name="category_ids[]" data-role="select2" data-placeholder="Select Categories"
                            data-allow-clear="true" multiple="multiple" id="categorylist">
                            @if(isset($category_list) && count($category_list) > 0)

                            @foreach($category_list as $parent_cat_id => $parent_cat_name)
                            
                            
                            <?php if ( isset($sub_category_list[$parent_cat_id]) && !empty($sub_category_list[$parent_cat_id]) ) { ?>
                            <optgroup label="<?php echo $parent_cat_name; ?>" <?php echo in_array($parent_cat_id, $category_ids) ? 'selected' : ''; ?>>
                                <?php foreach ($sub_category_list[$parent_cat_id] as $sub_cat_id => $sub_cat_name): ?>
                                <?php if ($id > 0 && $id == $sub_cat_id) {
                                    continue;
                                } ?>
                                <?php if ( isset($sub_category_list[$sub_cat_id]) && !empty($sub_category_list[$sub_cat_id]) ){ ?>
                            <optgroup label="<?php echo str_repeat('&nbsp;', 4) . $sub_cat_name; ?>" <?php echo in_array($sub_cat_id, $category_ids) ? 'selected' : ''; ?>>
                                <?php foreach ($sub_category_list[$sub_cat_id] as $sub_cat_id2 => $sub_cat_name2): ?>
                                <?php if ($id > 0 && $id == $sub_cat_id2) {
                                    continue;
                                } ?>
                                <?php if ( isset($sub_category_list[$sub_cat_id2]) && !empty($sub_category_list[$sub_cat_id2]) ){ ?>
                            <optgroup label="<?php echo str_repeat('&nbsp;', 6) . $sub_cat_name2; ?>" <?php echo in_array($sub_cat_id2, $category_ids) ? 'selected' : ''; ?>>
                                <?php foreach ($sub_category_list[$sub_cat_id2] as $sub_cat_id3 => $sub_cat_name3): ?>
                                <?php if ($id > 0 && $id == $sub_cat_id3) {
                                    continue;
                                } ?>
                                <?php if ( isset($sub_category_list[$sub_cat_id3]) && !empty($sub_category_list[$sub_cat_id3]) ){ ?>
                                <?php foreach ($sub_category_list[$sub_cat_id3] as $sub_cat_id4 => $sub_cat_name4): ?>
                                <?php if ($id > 0 && $id == $sub_cat_id4) {
                                    continue;
                                } ?>
                                <option data-style="background-color: #ff0000;" value="<?php echo $sub_cat_id4; ?>"
                                    <?php echo in_array($sub_cat_id4, $category_ids) ? 'selected' : ''; ?>>
                                    <?php echo str_repeat('&nbsp;', 10) . $sub_cat_name4; ?>
                                </option>
                                <?php endforeach; ?>
                                <?php }else{ ?>
                                <option data-style="background-color: #ff0000;" value="<?php echo $sub_cat_id3; ?>"
                                    <?php echo in_array($sub_cat_id3, $category_ids) ? 'selected' : ''; ?>>
                                    <?php echo str_repeat('&nbsp;', 8) . $sub_cat_name3; ?>
                                </option>
                                <?php } ?>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php }else{ ?>
                            <option value="<?php echo $sub_cat_id2; ?>" <?php echo in_array($sub_cat_id2, $category_ids) ? 'selected' : ''; ?>>
                                <?php echo str_repeat('&nbsp;', 6) . $sub_cat_name2; ?>
                            </option>
                            <?php } ?>
                            <?php endforeach; ?>
                            </optgroup>
                            <?php }else{ ?>
                            <option value="<?php echo $sub_cat_id; ?>" <?php echo in_array($sub_cat_id, $category_ids) ? 'selected' : ''; ?>>
                                <?php echo str_repeat('&nbsp;', 4) . $sub_cat_name; ?>
                            </option>
                            <?php } ?>
                            <?php endforeach; ?>
                            </optgroup>
                            <?php }else{ ?>
                            <option value="<?php echo $parent_cat_id; ?>" <?php echo in_array($parent_cat_id, $category_ids) ? 'selected' : ''; ?>>
                                <?php echo $parent_cat_name; ?>
                            </option>
                            <?php } ?>


                            @endforeach
                            @endif
                        </select>
                    </div>
                    
               

                    <div class="col-md-12 form-group applies_to_select" id="browse_product" style="display:none;">
                                                <label class="control-label">Browse Product</label>
                                                <select class="form-control product_search select2" id="product_search"  name="txt_products[]" style="width:100%" multiple="multiple">
                                                 @foreach($products as $prod)
                                                 <option value="<?=$prod->id?>"><?=$prod->product_name?></option>
                                                 @endforeach
                                                </select>
                                                <div class="error"></div>
                                            </div>

                       <div class="col-md-12 form-group applies_to_select" id="browse_services" style="display:none;">
                                                <label class="control-label">Browse Services</label>
                                                <select class="form-control service_search" id="service_search" data-live-search="true" name="txt_services[]" style="width:100%" multiple="multiple">
                                                @foreach($services as $ser)
                                                 <option value="<?=$ser->id?>" {{ in_array($ser->id,$couponservices) ? 'selected' : '' }}><?=$ser->name?> {{$ser->companies??''}}</option>
                                                 @endforeach
                                                </select>
                                                <div class="error"></div>
                                            </div>                     

                    <div class="col-md-6 form-group">
                        <label>Title<b class="text-danger">*</b></label>
                        <input type="text" name="title" class="form-control" required
                            data-parsley-required-message="Enter Title"
                            value="{{empty($datamain->coupon_title) ? '': $datamain->coupon_title}}">
                    </div>

                    <div class="col-md-6 form-group">
                        <label>Description<b class="text-danger">*</b></label>
                        <input type="text" name="description" class="form-control" required
                            data-parsley-required-message="Enter Description"
                            value="{{empty($datamain->coupon_description) ? '': $datamain->coupon_description}}">
                    </div>

                    <div class="col-md-6 form-group">
                                        
                                            <label>Start date <span style="color:red;">*<span></span></span></label>
                                            <input type="text" class="form-control basicDate" data-parsley-daterangevalidation data-parsley-daterangevalidation-requirement="#date_input_2" name="startdate" value="{{empty($datamain->start_date) ? '': date('Y-m-d', strtotime($datamain->start_date))}}" required
                            data-parsley-required-message="Select Start date">
                                       
                                    </div> 


                    <div class="col-md-6 form-group">
                                        
                                            <label>Expiry date <span style="color:red;">*<span></span></span></label>
                                            <input type="text" class="form-control basicDate" id="date_input_2" name="expirydate" value="{{empty($datamain->coupon_end_date) ? '': date('Y-m-d', strtotime($datamain->coupon_end_date))}}" required
                            data-parsley-required-message="Select Expiry date">
                                       
                                    </div> 


                    <div class="col-md-6 form-group">
                        <label>Minimum Amount</label>
                        <input type="text" name="minimum_amount" oninput="validateNumber(this);" class="form-control" maxlength="5" value="{{empty($datamain->minimum_amount) ? '': $datamain->minimum_amount}}">
                    </div>

                    <div class="col-md-6 form-group">
                        <label>Usage limit per Coupon</label>
                        <input type="text" name="coupon_usage_percoupon" oninput="validateNumber(this);" class="form-control" maxlength="5" value="{{empty($datamain->coupon_usage_percoupon) ? '': $datamain->coupon_usage_percoupon}}">
                    </div>

                    <div class="col-md-6 form-group">
                        <label>Usage limit per User</label>
                        <input type="text" name="coupon_usage_peruser" oninput="validateNumber(this);" class="form-control" maxlength="5" value="{{empty($datamain->coupon_usage_peruser) ? '': $datamain->coupon_usage_peruser}}">
                    </div>

                    <div class="col-md-6 form-group">
                        <label>Status</label>
                        <select name="active" class="form-control">
                            <option @if(!empty($datamain->coupon_status)) {{$datamain->coupon_status==1 ? "selected" : null}} @endif value="1">Active</option>
                            <option @if(!empty($datamain->coupon_status)) {{$datamain->coupon_status==0 ? "selected" : null}} @endif value="0">Inactive</option>
                        </select>
                    </div>

                    

                    </div>
                   <div class="row">
                    

                    <div class="col-md-6 form-group">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                    </div>
                </form>
            
            <div class="col-xs-12 col-sm-6">

            </div>
        </div>
    </div>
@stop

@section('script')
<script src="{{asset('')}}admin-assets/bootstrap/js/bootstrap-multiselect.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            // $('#distributor').multiselect({
            //     enableFiltering: true,
            //     includeSelectAllOption: true,
            //     maxHeight: 400,
            //     enableClickableOptGroups: true,
            //     enableFiltering: true,
            //     enableCaseInsensitiveFiltering: true,
            //     onChange: function(option, checked) {
                    
            //         //alert(option.length + ' options ' + (checked ? 'selected' : 'deselected'));
            //     }
            // });
            
        });

    $('body').off('change', '#distributor');
    $('body').on('change', '#distributor', function(e) {
        $( ".multiselect-search" ).trigger("keyup");
        $( ".multiselect-search" ).trigger("keyup");
     });
        
        // $(document).ready(function() {
        //     $('#service_search').multiselect({
        //         enableFiltering: true,
        //         includeSelectAllOption: true,
        //         maxHeight: 400,
        //         enableClickableOptGroups: true,
        //         onChange: function(option, checked) {
        //             //alert(option.length + ' options ' + (checked ? 'selected' : 'deselected'));
        //         }
        //     });
        // });
    </script>
    <script>
    function submitForm(formData, actionUrl, $form) 
    {
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
                            window.location.href = App.siteUrl('/admin/coupons');
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
        
    }
        App.initFormView();
        $(document).ready(function() {
            $('.select2').select2();

        });
        $('body').off('submit', '#admin-form');
        $('body').on('submit', '#admin-form', function(e) {
             var applies_to2 = $('#applies_to').val();
            console.log("applies_to is ", applies_to2);
            
            $('.invalid-feedback').remove();
            e.preventDefault();
            var $form = $(this);
            var formData = new FormData(this);

            App.loading(true);
            $form.find('button[type="submit"]')
                .text('Saving')
                .attr('disabled', true);
                var applies_to = $('#applies_to').val();

            if (applies_to == 0){
                
                formData.set('applies_to', 1);
                submitForm(formData, formData, $form);
    
              applies_to = $('#applies_to').val();
            console.log("applies_to1 is ", applies_to);
                // Example: submitting with applies_to = 2
                formData.set('applies_to', 2);
                submitForm(formData, formData, $form);
    
              applies_to = $('#applies_to').val();
            console.log("applies_to2 is ", applies_to);
                // Example: submitting with applies_to = 3
                formData.set('applies_to', 3);
                submitForm(formData, formData, $form);
            console.log("applies_to is ", applies_to);
            }
            else{
                submitForm(formData, $form.attr('action'), $form)
            }
                
                
                

        });
        $(document).delegate("#applies_to", "change", function() {
        var applies_to = $(this).val();
        var activity = $('option:selected', '#distributor').attr('data-activity');
        if(applies_to == 1)
        {
         
          $.ajax({
          dataType: "json",
          type: "POST",
          data: {'activity': activity,'_token':'{{ csrf_token() }}'},
          url: "{{url('admin/coupon_category_by_activity')}}",
          success: function(data){
            $("#categorylist").empty();
           //$("#categorylist").append('<option value="" ></option>');
          $.each(data, function(index) {
           $("#categorylist").append('<option value=' + data[index].id +' >'+data[index].text+'</option>');
           });
           $('#categorylist').val([<?php foreach($category_ids as $subje) { ?>'<?=$subje?>',<?php }?>]);
          }
        })
        }

        $(".applies_to_select").css("display","none");
        var show = $('option:selected', this).attr('data-show');
        $(show).css("display","block");     
        });
        $(".basicDate").flatpickr({
     minDate: "today"
});


$(function(){
    $("#distributor").trigger("change");
});
        $(document).ready(function() {
            
            $(".product_search").select2({
                minimumInputLength: 1,
                ajax: { 
                    url: "{{url('admin/coupon_product_search')}}",
                    type: "get",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                     return {
                       search_text: params.term,// search term
                       distributor: $("#distributor").val() 
                     };
                    },
                    processResults: function (response) {
                      return {
                         results: response
                      };
                    },
                    cache: true
                }
            });
            // $(".service_search").multiselect({
            //     enableFiltering: true,
            //     includeSelectAllOption: true,
            //     maxHeight: 400,
            //     enableClickableOptGroups: true,
            //     enableFiltering: true,
            //     enableCaseInsensitiveFiltering: true,
            //     onChange: function(option, checked) {
            //         //alert(option.length + ' options ' + (checked ? 'selected' : 'deselected'));
            //     },
            // });
            $(function(){
$( ".multiselect-search" ).trigger("keyup");
});
          
           $('body').off('click', '.multiselect-all');
            $('body').on('click', '.multiselect-all', function(e) {
                $( ".multiselect-search" ).trigger("keyup");
            });

            $('body').off('keyup', '.multiselect-search');
            $('body').on('keyup', '.multiselect-search', function(e) {
               
                var key = $(this).val();
              $.ajax({
               type: "post",
               url: "{{url('admin/coupon_service_search')}}",
               dataType: 'json',
               delay: 250,
               data: {'search_text': key,'distributor': $("#distributor").val(),'_token':'{{ csrf_token() }}'},
               success: function (data) {
                    if (data.length > 0) {
                        $("#service_search").html('');
                    }
                    else
                    {
                       $("#service_search").html('');
                       $("#service_search").append('<option hidden>No result</option>'); 
                    }
                   
                    $.each(data, function(index) {
                     $("#service_search").append('<option value=' + data[index].id +' >'+data[index].text+'</option>');
                        });
                    $(".service_search").multiselect('rebuild');
                    $('.multiselect-search').val(key);
                    $('.multiselect-search').focus();
                 },
                   cache: true
               });
            });

            $("#applies_to").trigger("change");
        });
        $('#product_search').val([<?php foreach($couponproducts as $subje) { ?>'<?=$subje->product_id?>',<?php }?>]);  
        
        $('#distributor').val([<?php foreach($vendors as $subje) { ?>'<?=$subje->vendor?>',<?php }?>]);  

        $('body').off('change', '#distributor');
$('body').on('change', '#distributor', function(e) {
    var activity = $('option:selected', this).attr('data-activity');
    var vendor = $('option:selected', this).val();
    $("#applyto").show();
    if(activity == 6 || activity == 4 || activity == 1)
    {

        $.ajax({
          dataType: "json",
          type: "POST",
          data: {'distributor': vendor,'_token':'{{ csrf_token() }}'},
          url: "{{url('admin/service_by_vendor')}}",
          success: function(data){
            $("#service_search").empty();
           //$("#categorylist").append('<option value="" ></option>');
          $.each(data, function(index) {
           $("#service_search").append('<option value=' + data[index].id +' >'+data[index].text+'</option>');
           });
          
           $('select#service_search').val([<?php foreach($couponservices as $subje) { ?>'<?=$subje?>',<?php }?>]);  
           $(".service_search").multiselect('rebuild');
          }
        })
        
        $("#applies_to").prop("selectedIndex", -1);

        $("#applies_to").val(3);
        $("#applies_to")
        .find("option")
        .show()
        .not("option[value='3']").hide();
        $("#applies_to").trigger("change");
        $("#applyto").hide();
    }
    else
    {
        $("#applies_to").val(1);
        $("#applies_to")
        .find("option")
        .show().not("option[value='0']").not("option[value='1']").not("option[value='2']").hide();
        $("#applies_to").trigger("change");
        $("select#applies_to").val("{{$datamain->applied_to??''}}");
    }
    
});

window.Parsley.addValidator('daterangevalidation', {
  validateString: function (value, requirement) {
    var date1 = new Date(value);
    var date2 = new Date($('#date_input_2').val());

    return date1 < date2;
  },
  messages: {
    en: 'Start date should not be greater than End date.'
  }
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
    
@stop
