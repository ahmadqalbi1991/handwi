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
                    <!-- <tr>
                        <td>
                            <table style="width:100%; padding: 0 40px;margin-top: 20px;">
                                <tr>
                                    <td>
                                        <div>
                                            <h4 style="font-weight: 600; font-size: 18px; text-align: left !important; display: block  !important;">Hi {{$name}},</h4>
                                            <p style="margin: 0 0 16px; font-size: 14px; line-height: 26px; color: #000000; text-align: left !important; display: block  !important;">
                                                Your order <b> #{{$order->order_number}}</b> has been received on {{ get_date_in_timezone($order->created_at, 'd-M-y h:i A') }}. We will contact you
                                                shortly and proceed with the order.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr> -->
                    <tr>
                        <td>
                            <table style="width:100%; margin-top: 20px; text-align: left;">
                            <!--<table style="width:100%; padding: 0 40px;margin-top: 20px;">-->
                                <thead>
                                    <tr>
                                        <th style=" text-align: left;">Tax Invoice</th>
                                        <th style=" text-align: left;">Seller</th>
                                        <th style=" text-align: left;">Buyer</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td style="width: 40%;" align="left" >
                                        <table style="width: 100%;">
                                            <tbody>
                                                <tr>
                                                    <td style="padding: 3px 0; text-align: left;width: 40%;">Order No</td>
                                                    <td style="padding: 3px 0;text-align: right !important; padding-right: 20px !important;width: 60%;" align="right" >{{$order->order_number}}</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 3px 0; text-align: left;width: 40%;">Invoice Date</td>
                                                    <td style="padding: 3px 0;text-align: right !important; padding-right: 20px !important;width: 60%;" align="right" >{{ get_date_in_timezone($order->created_at, 'd-M-y h:i A') }}</td>
                                                </tr>
                                                @if($order->order_type == 1)
                                                <tr>
                                                    <td style="padding: 3px 0; text-align: left;width: 40%;">Pick Up Date</td>
                                                    <td style="padding: 3px 0;text-align: right !important; padding-right: 20px !important;width: 60%;" align="right" >{{ $order->pick_up_date ? date('d-M-y',strtotime($order->pick_up_date)) : '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 3px 0; text-align: left;width: 40%;">Pick Up Time</td>
                                                    <td style="padding: 3px 0;text-align: right !important; padding-right: 20px !important;width: 60%;" align="right" >{{ $order->pick_up_time ?? '-' }}</td>
                                                </tr>
                                                @endif
                                            
                                            </tbody>
                                        </table>
                                    </td>
                                    <td style="width: 30%;" align="left" valign="top">
                                        <div>
                                            <h4 style="margin: 10px 0 10px !important;">{{$order->vendor->vendordata->company_name ?? ''}}</h4>
                                            <p style="margin-top:10px !important; font-size:13px; line-height: 28px; ">
                                                <!-- Shop No 4,Hamad Fadhel Salem butti Al H <br>
                                                amli Bldg, Bur Dubai, al souq al kabeer, D<br>
                                                UBAI, Dubai, United Arab Emirates<br>
                                                United Arab Emirates</p> -->
                                                {{$order->vendor->vendordata->location ?? ''}} 
                                            </p>
                                        </div>
                                    </td>
                                    <td style="width: 30%;" align="left"  valign="top">
                                        <div>
                                            <h4 style="margin: 10px 0 10px !important;">{{$name}}</h4>
                                            <p style="margin-top:10px !important; font-size:13px; line-height: 28px;">
                                                @if($order->address)
                                                    {{$order->address->dial_code.$order->address->phone}} 
                                                    {{($order->address->apartment.',')??''}} 
                                                    {{($order->address->building_name .',' )??''}}
                                                    {{($order->address->street.',')??''}}
                                                    {{$order->address->area_name}}<br>
                                                    {{$order->address->city_name}}<br>
                                                    {{$order->address->country_name}}<br>
                                                @endif

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
                                        <th style="padding:10px !important; border: none !important; width: 30%;" align="left">Product Name</th>
                                        <th style="padding:10px !important; border: none !important; text-align: center !important" align="center">Qty</th>
                                        <th style="padding:10px !important; border: none !important; text-align: right !important" align="right">Price ({{$currency}} )</th>
                                        <!-- <th style="padding:10px !important; border: none !important; text-align: right !important">VAT ({{$currency}} )</th> -->
                                        <!-- <th style="padding:10px !important; border: none !important; text-align: right !important">Discount ({{$currency}} )</th> -->
                                        <th style="padding:10px !important; border: none !important; text-align: right !important" align="right">Grand Total ({{$currency}} )</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->products as $key=>$prd)
                                    <tr>
                                        <td style="padding:10px !important" align="left">{{$key+1}}</td>
                                        <td style="padding:10px !important;  width: 30%;" align="left">
                                            <p>{{$prd->product_name}}</p>
                                            @php
                                                $product_attributes_full = \App\Models\ProductModel::getSelectedProductAttributeValsFull($prd->product_variant_id);
                                            @endphp
                                            @if(!empty($product_attributes_full))
                                                @foreach($product_attributes_full as $vv)
                                                    <p style="text-align: left !important; margin-bottom: 5px 1important;">{{$vv->attribute_name}}: {{$vv->attribute_values}}</p>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td style="padding:10px !important; text-align: center !important" align="center"> {{$prd->quantity}}</td>
                                        <td style="padding:10px !important; text-align: right !important;" align="right">{{$prd->price}}</td>
                                        <!-- <td style="padding:10px !important; text-align: right !important;">5.00%</td> -->
                                        <!-- <td style="padding:10px !important; text-align: right !important;">0</td> -->
                                        <td style="padding:10px !important; text-align: right !important;" align="right">{{$prd->total}}</td>
                                    </tr>
                                    @endforeach
                                    
                                    <tr>
                                        <td colspan="3"></td>
                                        <th colspan="1" style="padding:5px !important; text-align: right !important;" align="right">Payment Type</th>
                                        <td colspan="1" style="padding:5px !important; text-align: right !important;" align="right">{{payment_mode($order->payment_mode)}}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" style="height: 2px; background-color: #eee !important;"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td colspan="1" style="padding:5px !important; text-align: right !important;" align="right">Subtotal</td>
                                        <td colspan="1" style="padding:5px !important; text-align: right !important;" align="right">{{$currency}} {{number_format($order->total, 2, '.', '')}}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td colspan="1" style="padding:5px !important; text-align: right !important;" align="right">Delivery Charge</td>
                                        <td colspan="1" style="padding:5px !important; text-align: right !important;" align="right">{{$currency}} {{number_format($order->shipping_charge, 2, '.', '')}}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td colspan="1" style="padding:5px !important; text-align: right !important;" align="right">Service Charge</td>
                                        <td colspan="1" style="padding:5px !important; text-align: right !important;" align="right">{{$currency}} {{number_format($order->service_charge, 2, '.', '')}}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td colspan="1" style="padding:5px !important; text-align: right !important;" align="right">Discount</td>
                                        <td colspan="1" style="padding:5px !important; text-align: right !important;" align="right">{{$currency}} {{number_format($order->discount, 2, '.', '')}}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td colspan="1" style="padding:5px !important; text-align: right !important;" align="right">VAT</td>
                                        <td colspan="1" style="padding:5px !important; text-align: right !important;" align="right">{{$currency}} {{number_format($order->vat, 2, '.', '')}}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"></td>
                                        <th colspan="1" style="padding:5px !important; text-align: right !important;" align="right">Grand Total</th>
                                        <th colspan="1" style="padding:5px !important; text-align: right !important;" align="right">{{$currency}} {{number_format($order->grand_total, 2, '.', '')}}</td>
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