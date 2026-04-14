<?php

function sendSMS($phone, $message){

    $username = "sandbox"; // change in production
    $apiKey   = "atsk_6ecd379d45e488f675ed18739b7df6a5a5d7caed3122e92d0c494ccb394d08308f8f4890";

    $url = "https://api.africastalking.com/version1/messaging";

    $data = [
        'username' => $username,
        'to'       => $phone,
        'message'  => $message
    ];

    $headers = [
        "apiKey: $apiKey",
        "Content-Type: application/x-www-form-urlencoded"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}
?>
