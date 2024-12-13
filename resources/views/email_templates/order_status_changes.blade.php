<!DOCTYPE html>
    <html
    xmlns="http://www.w3.org/1999/xhtml"
    xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office"
    >
        <head>
            <meta charset="UTF-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            <meta http-equiv="X-UA-Compatible" content="ie=edge" />
            <title>Fast Time</title>
        </head>
        <head>
            <style>
            @font-face {
                font-family: "Poppins";
                font-style: normal;
                font-weight: 500;
                src: url(https://fonts.gstatic.com/s/poppins/v20/pxiByp8kv8JHgFVrLGT9Z1xlFQ.woff2)
                format("woff2");
                unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6,
                U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193,
                U+2212, U+2215, U+FEFF, U+FFFD;
            }

            @font-face {
                font-family: "Montserrat";
                font-style: normal;
                font-weight: 400;
                font-display: swap;
                src: url(https://fonts.gstatic.com/s/montserrat/v25/JTUSjIg1_i6t8kCHKm459Wlhyw.woff2)
                format("woff2");
                unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6,
                U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193,
                U+2212, U+2215, U+FEFF, U+FFFD;
            }

            p {
                font-family: "Poppins", sans-serif;
            }
            h1,
            h2,
            h3,
            h4 {
                font-family: "Montserrat", sans-serif;
            }
            .color_big_heading {
                position: relative;
                z-index: 99;
            }
            .color_big_heading::after {
                content: "";
                position: absolute;
                width: 100%;
                height: 8px;
                background: #fde440;
                bottom: 4px;
                left: 0;
                z-index: -1;
            }
            </style>
        </head>

    <body style="margin: 0; color: #818090">
        <div marginwidth="0" marginheight="0">
        <div
            marginwidth="0"
            marginheight="0"
            id=""
            dir="ltr"
            style="
            background-color: #fde440;
            background-image: linear-gradient(
                to right top,
                #fde440,
                #f5c638,
                #f7d34d,
                #f9e062,
                #fbec77,
                #fbeb76,
                #faea74,
                #fae973,
                #f7db5c,
                #f5cc46,
                #f4bd2d,
                #f2ad0b
            );
            margin: 0;
            padding: 20px 0 20px 0;
            width: 100%;
            margin: 0;
            "
        >
            <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" color="#000">
                <tbody>
                     <tr>
                        <td>
                            <div style="padding:15px 20px;background:#EEF4F8;padding-bottom:15px">
                                <table style="background:#EEF4F8;font-family:Roboto,RobotoDraft,Helvetica,Arial,sans-serif;font-size:14px;width:100%">
                                    <tbody>
                                        <tr>
                                            <td>
                                            <img
                                                src="{{ asset('') }}/uploads/logo.png"
                                                alt=""
                                                style="max-width: 190px; margin-bottom: 0px"
                                                />
                                                <h1 style="color:#ffc727;font-size:30px;line-height:100%">Order Status Update</h1>
                                            </td>
                                        
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top" style="background-color:#ffffff;padding:px px 0">
                            <table border="0" cellpadding="20" cellspacing="0" width="100%" style="font-family:Roboto,RobotoDraft,Helvetica,Arial,sans-serif">
                                <tbody>
                                    <tr>
                                        <td valign="top" style="padding-bottom:0px">

                                            <div style="color:#636363;font-family:Roboto,RobotoDraft,Helvetica,Arial,sans-serif;font-size:14px;line-height:150%;text-align:left;margin-top:30px">
                                                <h4 style="font-weight:600;font-size:18px">Hi <span class="color_big_heading">{{ $order->customer_name }},</span></h4>
                                                <p style="margin:0 0 16px;font-size:14px;line-height:26px;color:#000000;text-align:left">
                                                Your order
                                                <b>  #{{$order->order_id}} </b >
                                                status updated to 
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <table style="width:100%">
                                                <tbody>
                                                    <tr>
                                                        <td style="width:20%"><div style="height:10px;background:#5dbf13;border-radius:3px"></div></td>
                                                        <td style="width:20%"><div style="height:10px;background:#5dbf13;border-radius:3px"></div></td>
                                                        <td style="width:20%"><div style="height:10px;background:#5dbf13;border-radius:3px"></div></td>
                                                        <td style="width:20%"><div style="height:10px;background:#5dbf13;border-radius:3px"></div></td>
                                                        <td style="width:20%"><div style="height:10px;background:#5dbf13;border-radius:3px"></div></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <p style="font-weight:600;color:#5dbf13;font-size:18px">
                                                <p class="pending" style="@if($order->service_details->order_status == config('global.order_status_pending')) display:block @else display:none @endif;"><span class="btn-design">Pending</span></p>
                                                @if($order->service_details->order_status != config('global.order_status_cancelled'))
                                                <p class="accepted " style="@if($order->service_details->order_status == config('global.order_status_accepted')) display:block @else display:none @endif;"><span class="btn-design">Accepted</span></p>
                                                <p class="ready-for-delivery" style="@if($order->service_details->order_status == config('global.order_status_ready_for_delivery')) display:block @else display:none @endif ;"><span class="btn-design">Ready For Delivery</span></p>
                                                <p class="dispatched" style="@if($order->service_details->order_status == config('global.order_status_dispatched')) display:block @else display:none @endif ;"><span class="btn-design">Dispatched</span></p>
                                                <p class="delivered " style="@if($order->service_details->order_status == config('global.order_status_delivered')) display:block @else display:none @endif ;"><span class="btn-design">Delivered</span></p>
                                                @else
                                                <p class="delivered"style="@if($order->service_details->order_status == config('global.order_status_cancelled')) display:block @else display:none @endif ;" ><span class="btn-design">Cancelled</span></p>
                                                @endif
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <table>
                                                <tbody>
                                                    <tr>
                                                        <td style="width:50%;padding:15px 10px;font-size:14px">
                                                            <h4>ORDER SUMMARY</h4>
                                                            <table style="font-size:14px;width:100%">
                                                                <tbody>
                                                                    <tr>
                                                                        <td style="padding:5px">Sub Total:</td>
                                                                        <td style="padding:5px">$ {{$order->total}}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding:5px">Tax:</td>
                                                                        <td style="padding:5px">$ {{$order->vat}}</td> 
                                                                    </tr>
                                                                
                                                                    
                                                                    <tr>
                                                                        <td style="padding:5px">Discount:</td>
                                                                        <td style="padding:5px">$ {{$order->discount}}</td>
                                                                    </tr>
                                                                                                                                                    
                                                                    <tr>
                                                                        <td style="padding:5px">Grand Total:</td>
                                                                        <td style="padding:5px">$ {{$order->grand_total}}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="padding:5px">Payment :</td>
                                                                        <td style="padding:5px">{{ payment_mode($order->payment_mode) }}                                                                                 
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                        <td style="width:50%;padding:15px 10px;font-size:14px">
                                                            <h4>SHIPPING ADDRESS</h4>

                                                           @if($order->user_address)     
                                                            <p style="font-weight:700;margin-bottom:0px">{{$order->user_address->full_name}}</p>
                                                            <p style="margin-top:5px;line-height:22px">
                                                                {{$order->user_address->dial_code}}.{{$order->user_address->phone}} <br>
                                                                {{$order->user_address->building_name}}<br>
                                                                {{$order->user_address->land_mark}}<br>
                                                            </p>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <table style="font-size:14px;width:100%;color:#000">
                                            <h4 style="color:#000;">Service</h4>
                                            <tbody><tr><td><table style="font-size:14px;width:100%">
                                            <tbody>
                                                <tr>
                                                    <td style="padding:5px">
                                                        <img src="{{get_uploaded_image_url($order->service_details->image,'service_image_upload_dir')}}" style="width:100px;height:100px;object-fit:cover;">
                                                    </td>
                                                    <td style="padding:5px"> 
                                                         <h4 class="product-name">{{$order->service_details->name}}</h4>
                                                         <p>{{$order->service_details->description}}</p>
                                                        <p><strong>Service rate: </strong> {{number_format($order->service_details->price, 2, '.', '')}}</p>
                                                        <p><strong>Schedule Date: </strong> {{ get_date_in_timezone($order->service_details->booking_date, 'd-M-y h:i A') }}</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <h4 style="color:#000000;font-size:14px;margin:0px 0px 8px;text-align:left;font-weight:700">Much love,</h4>
                            <p style="color:#000000;font-size:16px;margin:0px 0px 10px;text-align:left;font-weight:700">Fast Time</p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" valign="top">
                            <table border="0" cellpadding="10" cellspacing="0" width="600">
                                <tbody>
                                    <tr>
                                        <td align="center" valign="top">
                                            <table border="0" cellpadding="10" cellspacing="0" width="600">
                                                <tbody>
                                                <tr>
                                                    <td valign="top" style="padding:0">
                                                        <table border="0" cellpadding="10" cellspacing="0" width="100%">
                                                            <tbody>
                                                                <tr>
                                                                    <td colspan="2" valign="middle" style="padding:0;border:0;color:#aac482;font-family:Arial;font-size:12px;line-height:125%;text-align:center">
                                                                        <p style="color:#000000;padding-top:20px;margin-top:0px">
                                                                            © 2023 Fast Time. All Rights Reserved.</p>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </td>
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
