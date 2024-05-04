<?php

use Google\Client;
use App\Models\User;
use App\Models\Settings;
use GuzzleHttp\HandlerStack;
use Illuminate\Support\Facades\Storage;
use Google\Auth\Middleware\AuthTokenMiddleware;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\Credentials\ServiceAccountJwtAccessCredentials;

function send_notification($user, $title, $body, $type, $image, $userinfo)
{

    $FcmToken1 = User::where('fcm_id', '!=', '')->whereIn('id', $user)->where('device_type', '=' ,'android')->get()->pluck('fcm_id');
    $FcmToken2 = User::where('fcm_id', '!=', '')->whereIn('id', $user)->where('device_type', '=' ,'ios')->get()->pluck('fcm_id');
    $device_type = User::whereIn('id', $user)->pluck('device_type');

    $project_id = Settings::select('message')->where('type', 'project_id')->pluck('message')->first();
    $sender_id = Settings::select('message')->where('type', 'sender_id')->pluck('message')->first();
    $url = 'https://fcm.googleapis.com/v1/projects/' . $project_id . '/messages:send';


    $access_token = getAccessToken();

    if($type == 'chat'){
        $userDetails = $userinfo;
        $userinfo = json_encode($userDetails);

        $notification_data = [
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            "title" => $title,
            "body" => $body,
            "type" => $type,
            "image" => $image,
            "sender_info" =>  $userinfo
        ];


    }else{
        $notification_data = [
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            "title" => $title,
            "body" => $body,
            "type" => $type,
            "image" => $image
        ];
    }

    if ($device_type->contains('android')) {
        $androidFcmTokens = $FcmToken1->toArray();
        foreach ($androidFcmTokens as $token) {
            $message1 = [
                "message"=>[
                "token" => $token,
                "data" => $notification_data
                ]
            ];
            $data1 = json_encode($message1);

            $result1 = sendNotificationToFCM($url, $access_token, $data1);
        }

    }

    if ($device_type->contains('ios')) {
        $iosFcmTokens = $FcmToken2->toArray();

        foreach ($iosFcmTokens as $token) {

            $message2 = [
                "message"=>[
                    "token" => $token,
                    "notification" => [
                        "title" => $title,
                        "body" => $body,
                    ],
                    "data" => $notification_data
                ]
            ];

            $data2 = json_encode($message2);
            // Send notification to iOS users
            $result2 = sendNotificationToFCM($url, $access_token, $data2);
        }
    }
}


function sendNotificationToFCM($url, $access_token, $Data) {
    $headers = [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json',
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

    // Disabling SSL Certificate support temporarly
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $Data);

    // Execute post

    $result = curl_exec($ch);

    if ($result == FALSE) {
        die('Curl failed: ' . curl_error($ch));
    }

    // Close connection
    curl_close($ch);
}

function getAccessToken()
{
    $file_name = Settings::select('message')->where('type', 'service_account_file')->pluck('message')->first();

    $file_path = base_path('public/storage/'. $file_name);

    $client = new Client();
    $client->setAuthConfig($file_path);
    $client->setScopes(['https://www.googleapis.com/auth/firebase.messaging']);
    $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];

    return $accessToken;
}
