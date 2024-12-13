<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>

<body style="margin: 0; padding: 0; font-family: 'Montserrat', Arial, sans-serif !important;">
    <div marginwidth="0" marginheight="0" style=" padding: 0; margin: 0;">
        <div marginwidth="0" marginheight="0" id="" dir="ltr"
            style="margin: 0; padding: 0px 0 20px 0; width: 100%; margin: auto;">
            <table border="0" cellpadding="0" cellspacing="0" style="text-align: left !important;width: 100%; padding: 0; margin: 0;" >
                <tbody>
                    <tr>
                        <td align="center" valign="top">
                            <div style="">
                                <img alt="logo" src="{{ asset('') }}admin-assets/assets/img/main-logo.svg" style="height: 65px; margin-bottom: 10px;" class="theme-logo">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" valign="top" style="background: #2c93fa; padding: 8px 0px; ">
                            <div style="">
                                <h1 style="color: #fff; font-size: 30px; line-height: 100%; text-align: center;">Invoice </h1>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td>
                            <table style="width:100%; margin-top: 20px; text-align: left;">
                            <!--<table style="width:100%; padding: 0 40px;margin-top: 20px;">-->
                                <thead>
                                    <tr>
                                        <th style=" text-align: left;">Tax Invoice</th>
                                        <th style=" text-align: left;">Buyer</th>
                                        <th style=" text-align: left;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td style="width: 40%;" align="left" >
                                        <table style="width: 100%;">
                                            <tbody>
                                                <tr>
                                                    <td style="padding: 3px 0; text-align: left;width: 40%;">Order No</td>
                                                    <td style="padding: 3px 0;text-align: right !important; padding-right: 20px !important;width: 60%;" align="right" >{{$o_data->order_no}}</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 3px 0; text-align: left;width: 40%;">Invoice Date</td>
                                                    <td style="padding: 3px 0;text-align: right !important; padding-right: 20px !important;width: 60%;" align="right" >{{fetch_booking_created_at_date($o_data->order_id)}}</td>
                                                </tr>
                                                
                                            
                                            </tbody>
                                        </table>
                                    </td>
                                    <td style="width: 30%;" align="left"  valign="top">
                                        <div>
                                            <h4 style="margin: 10px 0 10px !important;">{{$name}}</h4>
                                            <p style="margin-top:10px !important; font-size:13px; line-height: 28px;">
                                                @if($o_data->address)
                                                    {{$o_data->address->dial_code.$o_data->address->phone}} 
                                                    {{($o_data->address->apartment.',')??''}} 
                                                    {{($o_data->address->building_name .',' )??''}}
                                                    {{($o_data->address->street.',')??''}}
                                                    {{$o_data->address->area_name}}<br>
                                                    {{$o_data->address->city_name}}<br>
                                                    {{$o_data->address->country_name}}<br>
                                                @endif

                                            </p>
                                        </div>
                                    </td>
                                    <td style="width: 30%;" align="left" valign="top">
                                        <div>
                                            <h4 style="margin: 10px 0 10px !important;">{{$o_data->vendor->vendordata->company_name ?? ''}}</h4>
                                            <p style="margin-top:10px !important; font-size:13px; line-height: 28px; ">
                                                
                                                {{$o_data->vendor->vendordata->location ?? ''}} 
                                            </p>
                                        </div>
                                    </td>
                                    
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table style="width:100%;margin-top: 20px; border-spacing: 0 !important;">
                            <!--<table style="width:100%; padding: 0 40px;margin-top: 20px; border-spacing: 0 !important;">-->
                                <thead style="background-color: #eee; padding: 3px 0;">
                                    <tr style="background-color: #eee; padding: 3px 0; border-collapse: collapse;">
                                        <th style="padding:10px !important; border: none !important;" align="left">No</th>
                                        <th style="padding:10px !important; border: none !important; width: 30%;" align="left">Service Name</th>
                                        <th style="padding:10px !important; border: none !important; text-align: center !important" align="center">Qty</th>
                                        <th style="padding:10px !important; border: none !important; text-align: right !important" align="right">Price (JOD)</th>
                                        <th style="padding:10px !important; border: none !important; text-align: right !important" align="right">Booking Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($o_data->services as $key=>$value)
                                    <tr>
                                        <td style="padding:10px !important" align="left">{{$key+1}}</td>
                                        <td style="padding:10px !important;  width: 30%;" align="left">
                                            <p>{{$value->name}}</p>
                                           
                                        </td>
                                        <td style="padding:10px !important; text-align: center !important" align="center"> {{$value->qty}}</td>
                                        <td style="padding:10px !important; text-align: right !important;" align="right">{{$value->hourly_rate}}</td>
                                        <!-- <td style="padding:10px !important; text-align: right !important;">5.00%</td> -->
                                        <!-- <td style="padding:10px !important; text-align: right !important;">0</td> -->
                                        @if($key == 0)
                                        <td style="padding:10px !important; text-align: right !important;" align="right">{{date(config('global.datetime_format'), strtotime($value->booking_date));}}</td>
                                        @else
                                        <td></td>
                                        @endif
                                    </tr>
                                    @endforeach
                                    
                                    <tr>
                                        <td colspan="3"></td>
                                        <th colspan="1" style="padding:5px !important; text-align: right !important;" align="right">Payment Type</th>
                                        <td colspan="1" style="padding:5px !important; text-align: right !important;" align="right">{{$o_data->payment_mode}}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" style="height: 2px; background-color: #eee !important;"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td colspan="1" style="padding:5px !important; text-align: right !important;" align="right">Subtotal</td>
                                        <td colspan="1" style="padding:5px !important; text-align: right !important;" align="right">JOD {{number_format($o_data->total, 2, '.', '')}}</td>
                                    </tr>
                                    
                                    <tr>
                                        <td colspan="3"></td>
                                        <td colspan="1" style="padding:5px !important; text-align: right !important;" align="right">Discount</td>
                                        <td colspan="1" style="padding:5px !important; text-align: right !important;" align="right">JOD {{number_format($o_data->discount, 2, '.', '')}}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td colspan="1" style="padding:5px !important; text-align: right !important;" align="right">Service Charge</td>
                                        <td colspan="1" style="padding:5px !important; text-align: right !important;" align="right">JOD {{number_format($o_data->service_charge, 2, '.', '')}}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td colspan="1" style="padding:5px !important; text-align: right !important;" align="right">VAT</td>
                                        <td colspan="1" style="padding:5px !important; text-align: right !important;" align="right">JOD {{number_format($o_data->vat, 2, '.', '')}}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"></td>
                                        <th colspan="1" style="padding:5px !important; text-align: right !important;" align="right">Grand Total</th>
                                        <th colspan="1" style="padding:5px !important; text-align: right !important;" align="right">JOD {{number_format($o_data->grand_total, 2, '.', '')}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>