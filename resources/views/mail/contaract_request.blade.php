<!DOCTYPE html>
<html>
<head>
    <title> </title>
</head>
<body>
<body style="margin: 0;">
    <div marginwidth="0" marginheight="0">
        <div marginwidth="0" marginheight="0" id="" dir="ltr" style="background-color:#87ceeb;margin:0;padding:20px 0 20px 0;width:100%; margin: 0;">
            <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
                <tbody>
                    <tr>
                        <td align="center" valign="top" style="">
                            <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color:#ffffff;border:0px solid #dadada;border-radius:10px!important;overflow: hidden;">
                                <tbody>
                                    <tr>
                                        <td>
                                            <div style="padding: 15px 20px; background:#2C93FA; padding-bottom: 15px;">
                                                <table style="background:#2C93FA; font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;font-size:14px;width: 100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="width: 75%;" align="center">
                                                              

                                                         <img src="{{ asset('') }}admin-assets/assets/img/logo.png" alt="" style="max-width: 190px; margin-bottom: 0px; ">

                                                                <h1 style="color: #fff; font-size: 25px; line-height: 26px; margin:30px 0 0 0;">Contract Request Placed Successfully!!
                                                                </h1>
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
                                                        <td valign="top" style="background-color:#f2f2f2; padding:0px;">
                                                            <table border="0" cellpadding="20" cellspacing="0" width="100%" style="font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;">
                                                                <tbody>
                                                                    <tr>
                                                                        <td valign="top" style="padding-bottom: 0px;">
                                                                            <div style="color:#000;font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;font-size:14px;line-height:150%;text-align:left;margin-top: 30px">
                                                                                <h4 style="font-size: 16px;">Hi {{$user->name}},</h4>
                                                                                <h4 style="font-size: 14px;">Email : <span>{{$user->email}}</span></h4>
                                                                                
                                                                                
                                                                                <br style="margin:0;padding:0">
                                                                                <p style="margin:0;padding:0;margin-bottom:5px;color:#000;font-weight:400;font-size:13px;line-height:1.6">Your Contract Request placed successfully.</p>
                                                                              
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>
                                                                            <h4 style="color: #000; font-size: 14px; margin: 0px 0px 8px; text-align: left;">Thank You,</h4>
                                                                            <p style="color: #000; font-size: 16px; margin: 0px 0px 10px; text-align: left;">Team {{ config('app.name') }}</p>
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
                                            <div style="padding: 20px; background:#2C93FA;">
                                                <table style="background:#2C93FA; font-family: Roboto,RobotoDraft,Helvetica,Arial,sans-serif;font-size:14px;width: 100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="text-align: center;">
                                                           
                                                            <p style="color: #fff;">&#169;{{date('Y')}} {{ config('app.name') }}. All Rights Reserved.</p>
                                                        </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
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
</body>
</html>
