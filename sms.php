<?php

function sendSMS($phone, $message){

    $username = "sandbox"; // change in production
    $apiKey   = "";

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
