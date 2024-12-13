<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" style="background-color: #87ceeb;">
    <tbody>
        <tr>
            <td align="center" valign="top">
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color:#ffffff;border:0px solid #dadada;border-radius:10px!important; overflow: hidden;">
                    <tbody>
                        <tr>
                            <td>
                                <div style="padding:15px 20px;background:#2C93FA;padding-bottom:15px">
                                    <table style="background:#2C93FA;font-family:Roboto,RobotoDraft,Helvetica,Arial,sans-serif;font-size:14px;width:100%">
                                        <tbody>
                                            <tr>
                                                <td>
                                                   
                                                    <img src="{{ asset('') }}admin-assets/assets/img/logo.png" alt="" style="max-width: 190px; margin-bottom: 0px; ">
                                                    <h1 style="color:#fff;font-size:30px;line-height:100%">Great news!</h1>
                                                </td>

                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    <tr>
                        <td align="center" valign="top">
                            <table border="0" cellpadding="0" cellspacing="0" width="600">
                                <tbody>
                                <tr>
                                    <td valign="top" style="background-color:#ffffff;padding:px px 0">
                                        <table border="0" cellpadding="20" cellspacing="0" width="100%" style="font-family:Roboto,RobotoDraft,Helvetica,Arial,sans-serif">
                                            <tbody>
                                            <tr>
                                                <td valign="top" style="padding-bottom:0px">

                                                    <div style="color:#636363;font-family:Roboto,RobotoDraft,Helvetica,Arial,sans-serif;font-size:14px;line-height:150%;text-align:left;margin-top:30px">
                                                        <h4 style="font-weight:600;font-size:18px">Hi {{$name}},</h4>
                                                        <p style="margin:0 0 16px;font-size:14px;line-height:26px;color:#000000;text-align:left">
                                                           New order <b> #{{config('global.sale_order_prefix').date(date('Ymd', strtotime($order->created_at))).$order->order_id}}</b> has been received on {{  get_date_in_timezone($order->created_at, 'd-M-y h:i A') }}.</p>
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
                                                    <p style="font-weight:600;color:#5dbf13;font-size:18px">Received!</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <table>
                                                        <tbody>
                                                            <tr>
                                                                <td style="width:50%;background:#f0f0f0;padding:15px 10px;font-size:14px; border-radius: 10px;">
                                                                    
                                                                    <table style="font-size:14px;width:100%">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td style="width:50%;padding:15px 10px;font-size:14px">
                                                                                    
                                                                                    <table style="font-size:14px;width:100%">
                                                                        <tbody>
                                                                            <tr>
                                                                              <td colspan="2">  <h4>ORDER SUMMARY</h4></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="padding:5px">Order No:</td>
                                                                                <td style="padding:5px"><?php echo config('global.sale_order_prefix').date(date('Ymd', strtotime($order->created_at))).$order->order_id; ?></td>
                                                                            </tr>

                                                                            {{--@if($user)
                                                                            <tr>
                                                                                <td>Customer name</td>
                                                                                <td>{{$user->first_name}} {{$user->last_name}}</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Customer contact</td>
                                                                                <td>+{{$user->dial_code}} {{$user->phone}}</td>
                                                                            </tr>
                                                                            @endif--}}

                                                                            <tr>
                                                                                <td style="padding:5px">Sub Total:</td>
                                                                                <td style="padding:5px">{{$currency}} {{$order->total}}</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="padding:5px">Tax:</td>
                                                                                <td style="padding:5px">{{$currency}} {{$order->vat}}</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="padding:5px">Discount:</td>
                                                                                <td style="padding:5px">{{$currency}} {{$order->discount}}</td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td style="padding:5px">Grand Total:</td>
                                                                                <td style="padding:5px">{{$currency}} {{$order->grand_total}}</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td style="padding:5px">Payment :</td>
                                                                                <td style="padding:5px">
                                                                                    {{payment_mode($order->payment_mode)}} 
                                                                            </tr>
                                                                    </tbody>
                                                                    </table>
                                                                </td>
                                                                <td style="width:50%;padding:15px 10px;font-size:14px;    vertical-align: top;">
                                                                    @if($order->address)
                                                                    <h4>SHIPPING ADDRESS</h4>
                                                                    <p style="font-weight:700;margin:0px">{{$order->address->full_name}}</p>
                                                                    <p style="margin-top:5px;line-height:22px">
                                                                        {{$order->address->dial_code.$order->address->phone}} <br>

                                                                        {{$order->address->apartment??''}} <br>
                                                                        {{$order->address->building_name??''}}<br>
                                                                        {{$order->address->street??''}}<br>
                                                                        
                                                                        {{$order->address->area_name}}<br>
                                                                        {{$order->address->city_name}}<br>
                                                                        {{$order->address->country_name}}<br>
                                                                        {{$order->address->land_mark}}<br>
                                                                    </p>
                                                                    @elseif($order->order_type == 1)
                                                                    Order Type: Pick Up
                                                                    @else
                                                                    
                                                                    @endif
                                                                </td>
                                                            </tr></tbody></table></td></tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>
                                                    <table style="font-size:14px;width:100%;border-top:1px solid #dadada;border-bottom:1px solid #dadada">
                                                        <tbody>
                                                            @foreach ($order->products as $prd)
                                                            <tr>

                                                                <td style="width:100px">
                                                                    <img style="max-width:100px;padding-right:5px" src="{{$prd->image}}" alt="" >
                                                                </td>
                                                                <td>
                                                                    <div style="float:left">
                                                                        <p>{{$prd->product_name}}</p>
                                                                        @if(isset($prd->attribute_name) && $prd->attribute_name)
                                                                            <p>{{$prd->attribute_name}}: {{$prd->attribute_values}}</p>
                                                                        @endif
                                                                        <p></p>
                                                                        <p></p>
                                                                        <p>Quantity : {{$prd->quantity}}</p>


                                                                    </div>

                                                                </td>

                                                                <td>
                                                                    <div style="float:left">
                                                                        <p>Price : {{$currency}} {{$prd->price}}</p>

                                                                    </div>

                                                                </td>


                                                                <td>
                                                                    <div style="float:left">
                                                                        <p>Discount : {{$currency}} {{$prd->discount}}</p>

                                                                    </div>

                                                                </td>

                                                                <td>
                                                                    <div style="float:left">
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
                                                <td style="background-color:#2C93FA">
                                                    <table border="0" cellpadding="10" cellspacing="0" width="600" >
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
                                                                                    <td>
                                                                                        <h4 style="color:#fff;font-size:14px;margin:0px 0px 8px;text-align:left;font-weight:700">Much love,</h4>
                                                                                        <p style="color:#fff;font-size:16px;margin:0px 0px 10px;text-align:left;font-weight:700">{{ env('APP_NAME') }}</p>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td colspan="2" valign="middle" style="padding:0;border:0;color:#fff;font-family:Arial;font-size:12px;line-height:125%;text-align:center">
                                                                                        <p style="color:#fff;padding:20px 0;margin-top:0px">
                                                                                            © 2023 {{ env('APP_NAME') }}. All Rights Reserved.</p>
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                </td></tr>
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
