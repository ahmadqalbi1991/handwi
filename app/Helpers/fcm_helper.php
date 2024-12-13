<?php
function headers()
{
    return array(
        "Authorization: key= " . env('FIREBASE_AUTH_KEY'),
        "Content-Type: application/json",
        "project_id: " . env('FIREBASE_PROJECT_ID')

    );
}

function prepare_notification($database, $user, $title, $description, $ntype = 'service', $record_id = 0, $booking_type = '', $imageURL = '')
{
    $notification_id = time();
    $device_token = $user->user_device_token;
    $firebase_user_key = $user->firebase_user_key;
    if ($firebase_user_key) {
        $notification_data["Notifications/" . $firebase_user_key . "/" . $notification_id] = [
            "title" => $title,
            "description" => $description,
            "notificationType" => $ntype,
            "created_at" => gmdate("d-m-Y H:i:s", $notification_id),
            "record_id" => (string)$record_id,
            "record_type" => $booking_type,
            "url" => "",
            "imageURL" => $imageURL,
            "read" => "0",
            "seen" => "0",
        ];

        $database->getReference()->update($notification_data);
    }

    if ($device_token) {
        $send_data =
            [
                "title" => $title,
                "body" => $description,
                "text" => $description,
                "icon" => 'myicon',
                "sound" => true,
                "click_action" => "EcomNotification",
            ];

        $other_data = [
            "type" => $ntype,
            "booking_type" => $booking_type,
            "notificationID" => $notification_id,
            "record_id" => (string)$record_id,
            "imageURL" => $imageURL,
            "title" => $title,
            "body" => $description,
            "text" => $description,
            "icon" => 'myicon',
            "sound" => true,
            "click_action" => "EcomNotification",
        ];
        $res = send_single_notification($device_token, $send_data, $other_data);
        // var_dump($res);exit;
        // dd($res,$device_token,$send_data,$other_data);
        return [$res, $device_token, $send_data, $other_data];
        // echo '<pre>';
        // var_dump($res);
        // die;
    }
}

//  function send_single_notification($fcm_token, $notification, $data, $priority = 'high') {
//     $fields = array(
//         'notification' => $notification,
//         'data'=>$data,
//         'content_available' => true,
//         'priority' =>  $priority,
//         'to' => $fcm_token
//          );

//      if ( $curl_response=send(json_encode($fields), "https://fcm.googleapis.com/fcm/send") ) {
//         return json_decode($curl_response);
//     }
//     else
//         return false;
// }

function send_single_notification($fcm_token, $notification, $data, $priority = 'high')
{
    // Set your project ID and access token
    $project_id = config('firebase.FIREBASE_PROJECT_ID');

    $access_token = getAccessToken(); // You'll need to generate this as described below
    //d($access_token);
    // Set the v1 endpoint
    $url = "https://fcm.googleapis.com/v1/projects/$project_id/messages:send";


    // Set the headers for the request
    $headers = [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ];

    // Make the request
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    //curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(['message' => $message]));

    $payload = json_encode([
        'message' => [
            'token' => $fcm_token,
            'notification' => [
                "title" => $notification['title'],
                "body" => $notification['body']
            ],
            'data' => convert_all_elements_to_string_fcm($data),
        ],
    ]);

    curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
    $curl_response = curl_exec($curl);

    curl_close($curl);

    if ($curl_response) {
        return json_decode($curl_response);
    } else {
        return false;
    }
}

function getAccessToken()
{

    //$jsonKey = json_decode(file_get_contents(config('firebase.FIREBASE_CREDENTIALS')), true);
    try {
        // Load the service account credentials JSON file
        $jsonKey = json_decode(file_get_contents(base_path(config('firebase.FIREBASE_CREDENTIALS'))), true);

        $now = time();
        $token = [
            'iss' => $jsonKey['client_email'], // issuer
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600, // Token expiration time, set to 1 hour
            'iat' => $now // Token issued at time
        ];

// Encode the JWT
        $jwtHeader = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $jwtHeader = base64_encode($jwtHeader);

        $jwtPayload = json_encode($token);
        $jwtPayload = base64_encode($jwtPayload);

// Sign the JWT using the private key
        openssl_sign($jwtHeader . '.' . $jwtPayload, $signature, $jsonKey['private_key'], 'sha256');
        $jwtSignature = base64_encode($signature);

// Concatenate the three parts to create the final JWT
        $assertion = $jwtHeader . '.' . $jwtPayload . '.' . $jwtSignature;

        // Prepare the cURL request
        // Now make the request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $assertion, // Use the generated JWT as the assertion
        ]));

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);

        $response = curl_exec($ch);


        if (curl_errno($ch)) {
            // Handle cURL error
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        $authToken = json_decode($response, true);

        return $authToken['access_token'];
    } catch (Exception $e) {
        // Handle exceptions, e.g., log errors or throw a custom exception
        return null; // Or handle differently based on your application's needs
    }
}

function convert_all_elements_to_string_fcm($data = null, $emptyArrayShouldBeObject = false)
{
    if ($data != null) {
        array_walk_recursive($data, function (&$value, $key) use ($emptyArrayShouldBeObject) {
            if (!is_object($value)) {
                if ($value) {
                    $value = (string)$value;
                } else {
                    $value = (string)$value;
                }
            } else {
                $json = json_encode($value);
                $array = json_decode($json, true);

                array_walk_recursive($array, function (&$obj_val, $obj_key) use ($emptyArrayShouldBeObject) {
                    $obj_val = (string)$obj_val;
                });

                if (!empty($array)) {
                    $json = json_encode($array);
                    $value = json_decode($json);
                } else {
                    if ($emptyArrayShouldBeObject) {
                        $value = (object)[];
                    } else {
                        $value = [];
                    }
                }
            }
        });
    }
    return $data;
}

function send_multicast_notification($fcm_tokens, $notification, $data, $priority = 'high')
{
    $fields = array(
        'notification' => $notification,
        'data' => $data,
        'content_available' => true,
        'priority' => $priority,
        'registration_ids' => $fcm_tokens
    );

    if ($curl_response = send(json_encode($fields), "https://fcm.googleapis.com/fcm/send")) {
        return json_decode($curl_response);
    } else
        return false;
}

function send_notification($notification_key, $notification, $data, $priority = 'high')
{
    $fields = array(
        'notification' => $notification,
        'data' => $data,
        'content_available' => true,
        'priority' => $priority,
        'to' => $notification_key
    );

    if ($curl_response = send(json_encode($fields), "https://fcm.googleapis.com/fcm/send")) {
        return json_decode($curl_response);
    } else
        return false;

}

function send($fields, $url = "", $headers = array())
{

    if (empty($url)) $url = FIREBASE_URL;

    $headers = array_merge(headers(), $headers);

    $ch = curl_init();

    if (!$ch) {
        $curl_error = "Couldn't initialize a cURL handle";
        return false;
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

    $curl_response = curl_exec($ch);

    if (curl_errno($ch))
        $curl_error = curl_error($ch);

    if ($curl_response == FALSE) {
        return false;
    } else {
        $curl_info = curl_getinfo($ch);
        //printr($curl_info);
        curl_close($ch);
        return $curl_response;
    }

}