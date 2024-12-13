[1mdiff --git a/resources/views/mail/order_success_to_vendor.blade.php b/resources/views/mail/order_success_to_vendor.blade.php[m
[1mindex 44cf480..a2c9dec 100644[m
[1m--- a/resources/views/mail/order_success_to_vendor.blade.php[m
[1m+++ b/resources/views/mail/order_success_to_vendor.blade.php[m
[36m@@ -36,7 +36,7 @@[m
                                                     <div style="color:#636363;font-family:Roboto,RobotoDraft,Helvetica,Arial,sans-serif;font-size:14px;line-height:150%;text-align:left;margin-top:30px">[m
                                                         <h4 style="font-weight:600;font-size:18px">Hi {{$name}},</h4>[m
                                                         <p style="margin:0 0 16px;font-size:14px;line-height:26px;color:#000000;text-align:left">[m
[31m-                                                           New order <b> #{{config('global.sale_order_prefix').date(date('Ymd', strtotime($order->created_at))).$order->order_id}}</b> has been received on {{  get_date_in_timezone($order->created_at, 'd-M-y h:i A')}}.</p>[m
[32m+[m[32m                                                           New order <b> #{{config('global.sale_order_prefix').date(date('Ymd', strtotime($order->created_at))).$order->order_id}}</b> has been received on {{  get_date_in_timezone($order->created_at, 'd-M-y h:i A') }}.</p>[m
                                                     </div>[m
                                                 </td>[m
                                             </tr>[m
