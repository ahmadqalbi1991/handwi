<?php


$config['server_mode']                  = env("server_mode",'live');
$config['site_name']                                    = env("APP_NAME", 'SERVICE_APP');
$config['date_timezone']                                = 'Asia/Dubai';
$config['datetime_format']                              = 'M d, Y h:i A';
$config['date_format']                                  = 'M d, Y';
$config['date_format_excel']                            = 'd/m/Y';
$config['default_currency_code']                        = 'JOD';
$config['sale_order_prefix']                            = 'LC';
$config['upload_bucket']                                = 'public'; //s3
$config['upload_path']                                  = 'uploads/';
$config['aws_url']                                      = env("AWS_URL",'');
$config['user_image_upload_dir']                        = 'users/';
$config['category_image_upload_dir']                    = 'category/';
$config['product_image_upload_dir']                     = 'products/';
$config['post_image_upload_dir']                        = 'posts/';
$config['banner_image_upload_dir']                      = 'banner_images/';
$config['landing_page_images_upload_dir']                      = 'landing_page_images/';
$config['service_image_upload_dir']                     = 'service/';
$config['service_includes_image_upload_dir']            = 'service/service_includes/';
$config['contracts_image_upload_dir']                   = 'contracts/';
$config['maintainance_image_upload_dir']                = 'maintainance/';
$config['document_image_upload_dir']                    = 'document/';
$config['coupon_image_upload_dir']                      = 'coupon/';

$config['otp']                                          = '1111'; //rand(1000, 9999);

$config['withdraw_status'] = [
// 	0=>'Requested For Payment',
	0=>'Pending',
	1=>'Request Sent',
	2=>'Request Approved',
	3=>'Payment Approved',
	4=>'Payment Declined'
];

$config['order_type'] = [
//  0=>'Requested For Payment',
    0=>'Delivery',
    1=>'Pick Up'
];
//order status
$config['order_status_pending']                                 = 0;
$config['order_status_accepted']                                = 1;
$config['order_status_ready_for_delivery']                      = 2;
$config['order_status_dispatched']                              = 3;
$config['order_status_delivered']                               = 4;
$config['order_status_cancelled']                               = 10;
$config['order_status_rejected']                                = 11;//vendor reject

$config['order_prefix']                                 = 'SAsT-';
$config['product_image_width']                          = '700';
$config['product_image_height']                          = '700';

$config['wowza_key']                              = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI5NTU3NzEwZS0yYzhlLTQ1MDgtOTEwOS1hZWMxNTEwODAxY2UiLCJqdGkiOiI1N2M3ZDk5MDRhMDYwY2ZlNGQ0NjBjYmI3ZTI2NGE3NTAwYzU1Y2FjMzdkNWI0MDI0YTg0Njk5NzQxYzAyMjZjYjY1ODlmZDc4YmJhZTczYyIsImlhdCI6MTY2MzA0MDA3MywibmJmIjoxNjYzMDQwMDczLCJleHAiOjIyOTQxOTIwNzMsInN1YiI6Ik9VLTRmMjQzYTM2LThjZTItNDcyYS04MDhlLWE3Njg2NDE4MzViOCJ9.s4TXFbAO1J-MqfxxT7Bw3x8Ohjm6tmPvcZemcs6whQIP1LHPb4BPcDVlqt8HnsGnpWgI0DMARmxpOHR1d43nOYAxgBekIgPZn59BHB8gb-ovKvdOkqXYu7u1olvxPfs0tpJ1w_ey-3oxaeVdLIbYtSiyvB8KALN90Xpy1ueSyhcAdtulfRlcwUj5cXZkaeMJleCujpU7X_NSvAHG1xjAKk0yd3Tt9bt4a71VpP7B8wpkaSsf1vQ_PQphfFgEG0xqPOeTxPPIUUIHLfC46vVDySh8Kgo0Hxm1ZXRB0futXf8h6bCvB3HPIOzmdmUUtrmK_XRfkARPYRF5yserjX7vJ8674fqMyusroIBRfErlw5aDHnh4VKlLuZAIlizYlnoTWdF1cFCntTnsTo_tso0LjAFP-eAShitrSAzsAnJvymsXjslIBQdPixtNY32f8srowxnFqXY52UHEfae1jmZk-6F5TjxU7n6dCjaIukVJ_uOmpIq9crhE2wB5jQVkgQHJWEQpSsQ2q1Mob4OWhTPHT6xCsce3R0vS4dnHfreLMF5jRFnugH9vUurwNul3miDMFjzSVhU788xudLAmCcIFnfbozms2KjeijstpiH77BCD8-NNZzXAlcJLAfpYZxyacQaEAseEPnCCxiZPTrB7ccxStVh6DXLMo8ewnXjEWWp8';
$config['wowza_token_name']                       = 'api_token_v1';
$config['days'] = array(
    'sun'=>'sunday',
    'mon'=>'monday',
    'tues'=>'tuesday',
    'wed'   =>'wednesday',
    'thurs'=>'thursday',
    'fri'=>'friday',
    'sat'=>'saturday'
);

return $config;