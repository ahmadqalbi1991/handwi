<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge" />
        <title></title>
    </head>

    <body style="margin: 0;" >
        <div marginwidth="0" marginheight="0">
            <div marginwidth="0" marginheight="0" id="" dir="ltr" style="margin: 0; padding: 20px 0 20px 0; width: 100%; margin: auto;">
                <table border="0" cellpadding="0" cellspacing="0" style="text-align: left !important;width: 100%;">
                    <tbody>
                        <tr>
                            <td align="center" valign="top">
                                <table border="0" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border: 0px solid #dadada; border-radius: 10px !important; overflow: hidden;width: 100%;">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div style="padding: 15px 20px; background: #2c93fa; padding-bottom: 15px;">
                                                    <table style="background: #2c93fa; font-family: Roboto, RobotoDraft, Helvetica, Arial, sans-serif; font-size: 14px; width: 100%; text-align: center !important;">
                                                        <tbody>
                                                            <tr>
                                                                <td style="text-align: center !important;">
                                                                    <div>
                                                                        <img src="{{ asset('') }}admin-assets/assets/img/logo.png" alt="" style="max-width: 190px; width: 150px; margin: 10px 0px 15px;" />
                                                                        <h1 style="color: #fff; font-size: 30px; line-height: 100%;">Great news!</h1>
                                                                    
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" valign="top">
                                                <table border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td valign="top" style="background-color: #ffffff; padding: px px 0;">
                                                                <table border="0" cellpadding="20" cellspacing="0" style="font-family: Roboto, RobotoDraft, Helvetica, Arial, sans-serif; text-align: left !important; width: 100%;">
                                                                    <tbody>
                                                                        <tr>
                                                                            <td valign="top" style="padding-bottom: 0px;">
                                                                                <div
                                                                                    style="
                                                                                        color: #636363;
                                                                                        font-family: Roboto, RobotoDraft, Helvetica, Arial, sans-serif;
                                                                                        font-size: 14px;
                                                                                        line-height: 150%;
                                                                                        margin-top: 30px;
                                                                                        text-align: left !important;
                                                                                        display: block  !important;
                                                                                    "
                                                                                >
                                                                                    <h4 style="font-weight: 600; font-size: 18px; text-align: left !important; display: block  !important;">Hi {{$name}},</h4>
                                                                                    <p style="margin: 0 0 16px; font-size: 14px; line-height: 26px; color: #000000; text-align: left !important; display: block  !important;">
                                                                                        Your order <b> #{{$order->order_number}}</b> has been received on {{ get_date_in_timezone($order->created_at, 'd-M-y h:i A') }} We will contact you
                                                                                        shortly and proceed with the order.
                                                                                    </p>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <table style="width: 100%;">
                                                                                    <tbody>
                                                                                        <tr>
                                                                                            <td style="width: 20%;"><div style="height: 10px; background: #5dbf13; border-radius: 3px;"></div></td>
                                                                                            <td style="width: 20%;"><div style="height: 10px; background: #5dbf13; border-radius: 3px;"></div></td>
                                                                                            <td style="width: 20%;"><div style="height: 10px; background: #5dbf13; border-radius: 3px;"></div></td>
                                                                                            <td style="width: 20%;"><div style="height: 10px; background: #5dbf13; border-radius: 3px;"></div></td>
                                                                                            <td style="width: 20%;"><div style="height: 10px; background: #5dbf13; border-radius: 3px;"></div></td>
                                                                                        </tr>
                                                                                    </tbody>
                                                                                </table>
                                                                                <p style="font-weight: 600; color: #5dbf13; font-size: 18px; text-align: left !important; display: block  !important;">Received!</p>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <table style=" width: 100%; ">
                                                                                    <tbody>
                                                                                        <tr>
                                                                                            <td style="width: 50%; background: #f0f0f0; padding: 15px 10px; font-size: 14px; border-radius: 10px; text-align: left !important; overflow: hidden;">
                                                                                                <table style="font-size: 14px; width: 100%;">
                                                                                                    <tbody>
                                                                                                        <tr>
                                                                                                            <td style="width: 50%; padding: 15px 10px; font-size: 14px; text-align: left !important;">
                                                                                                                <table style="font-size: 14px; width: 100%;">
                                                                                                                    <tbody>
                                                                                                                        <tr>
                                                                                                                            <td colspan="2"><h4>ORDER SUMMARY</h4></td>
                                                                                                                        </tr>
                                                                                                                        <tr>
                                                                                                                            <td style="padding: 5px;">Order No:</td>
                                                                                                                            <td style="padding: 5px;">{{$order->order_number}}</td>
                                                                                                                        </tr>
                                                                                                                        <tr>
                                                                                                                            <td style="padding: 5px;">Sub Total:</td>
                                                                                                                            <td style="padding: 5px;">{{$currency}} {{$order->total}}</td>
                                                                                                                        </tr>
                                                                                                                        <tr>
                                                                                                                            <td style="padding: 5px;">Tax:</td>
                                                                                                                            <td style="padding: 5px;">{{$currency}} {{$order->vat}}</td>
                                                                                                                        </tr>
                                                                                                                        <tr>
                                                                                                                            <td style="padding: 5px;">Discount:</td>
                                                                                                                            <td style="padding: 5px;">{{$currency}} {{$order->discount}}</td>
                                                                                                                        </tr>
                                                                                                                        <tr>
                                                                                                                            <td style="padding: 5px;">Grand Total:</td>
                                                                                                                            <td style="padding: 5px;">{{$currency}} {{$order->grand_total}}</td>
                                                                                                                        </tr>
                                                                                                                        <tr>
                                                                                                                            <td style="padding: 5px;">Payment :</td>
                                                                                                                            <td style="padding: 5px;">
                                                                                                                                {{payment_mode($order->payment_mode)}}
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

                                                                        <tr>
                                                                            <td>
                                                                                <table style="font-size: 14px; width: 100%; border-top: 1px solid #dadada; border-bottom: 1px solid #dadada; text-align: left !important">
                                                                                    <tbody>
                                                                                        @foreach ($order->products as $prd)
                                                                                        <tr>
                                                                                            <!-- <td style="width: 100px;"></td> -->
                                                                                            <td style="text-align: left !important;">
                                                                                                <div style="text-align: left !important;">
                                                                                                    <p>{{$prd->product_name}}</p>
                                                                                                    @if(isset($prd->attribute_name) && $prd->attribute_name)
                                                                                                    <p style="text-align: left !important; margin-bottom: 5px 1important;">{{$prd->attribute_name}}: {{$prd->attribute_values}}</p>
                                                                                                    @endif
                                                                                                    <p></p>
                                                                                                    <p></p>
                                                                                                    <p style="text-align: left !important; margin: 5px 0 1important; padding: 5px 0 !important">Quantity : {{$prd->quantity}}</p>
                                                                                                </div>
                                                                                            </td>

                                                                                            <td style="text-align: left !important;">
                                                                                                <div style="text-align: left !important;">
                                                                                                    <p>Price : {{$currency}} {{$prd->price}}</p>
                                                                                                </div>
                                                                                            </td>

                                                                                            <td style="text-align: left !important;">
                                                                                                <div style="text-align: left !important;">
                                                                                                    <p>Discount : {{$currency}} {{$prd->discount}}</p>
                                                                                                </div>
                                                                                            </td>

                                                                                            <td style="text-align: left !important;">
                                                                                                <div style="text-align: left !important;">
                                                                                                    <p>Total : {{$currency}} {{$prd->total}}</p>
                                                                                                </div>
                                                                                            </td>
                                                                                        </tr>
                                                                                        @endforeach
                                                                                    </tbody>
                                                                                </table>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <h4 style="color: #000 !important; font-size: 14px; margin: 0px 0px 8px; text-align: left; font-weight: 700;">
                                                                                    Much love,
                                                                                </h4>
                                                                                <p style="color: #000 !important; font-size: 16px; margin: 0px 0px 10px; text-align: left; font-weight: 700;">
                                                                                    La concierge
                                                                                </p>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td style="background-color: #2c93fa;">
                                                                                <table border="0" cellpadding="10" cellspacing="0" width="600">
                                                                                    <tbody>
                                                                                        <tr>
                                                                                            <td align="center" valign="top">
                                                                                                <table border="0" cellpadding="10" cellspacing="0" width="600">
                                                                                                    <tbody>
                                                                                                        <tr>
                                                                                                            <td valign="top" style="padding: 0;">
                                                                                                                <table border="0" cellpadding="10" cellspacing="0" width="100%">
                                                                                                                    <tbody>
                                                                                                                        <tr>
                                                                                                                            <td
                                                                                                                                colspan="2"
                                                                                                                                valign="middle"
                                                                                                                                style="
                                                                                                                                    padding: 0;
                                                                                                                                    border: 0;
                                                                                                                                    color: #fff;
                                                                                                                                    font-family: Arial;
                                                                                                                                    font-size: 12px;
                                                                                                                                    line-height: 125%;
                                                                                                                                    text-align: center;
                                                                                                                                "
                                                                                                                            >
                                                                                                                                <p style="color: #fff; padding: 0; margin: 0px;">
                                                                                                                                    © 2024 La concierge. All Rights Reserved.
                                                                                                                                </p>
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
