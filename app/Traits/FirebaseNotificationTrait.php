<?php

namespace App\Traits;

use Google\Client as GoogleClient;
use Illuminate\Http\Request;

trait FirebaseNotificationTrait
{
    public function sendFirebaseNotification($fcm, $title, $description)
    {
        // Define the project ID and credentials path
        $projectId = 'ma7zouz-cc1af'; // Use your project ID here
        $credentialsFilePath = public_path('mazouz.json');

        // Initialize Google client for Firebase
        $client = new GoogleClient();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();

        // Get the access token
        $token = $client->getAccessToken();
        $access_token = $token['access_token'];

        // Prepare headers
        $headers = [
            "Authorization: Bearer $access_token",
            'Content-Type: application/json',
        ];

        // Prepare notification payload
        $data = [
            "message" => [
                "token" => $fcm,
                "notification" => [
                    "title" => $title,
                    "body" => $description,
                ],
            ],
        ];
        $payload = json_encode($data);

        // Use CURL to send the notification
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        // Handle the response and errors
        if ($err) {
            return [
                'status' => false,
                'message' => 'Curl Error: ' . $err
            ];
        }

        return [
            'status' => true,
            'message' => 'Notification has been sent',
            'response' => json_decode($response, true)
        ];
    }
}
