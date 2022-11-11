<?php
$clientId = "xxxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxx";
$clientSecret = "mysecret";
$responseUri = "https%3A%2F%2Fmyserver.mycompany.com%2Fsugarcrmmaintest%2Fresponse.php";


$postUrl = "/mycomp.onmicrosoft.com/oauth2/v2.0/token";

$hostname = "login.microsoftonline.com";
$fullurl = "https://login.microsoftonline.com/mycompany.onmicrosoft.com/token";

$headers = array(
    "POST " . $postUrl . " HTTP/1.1",
    "Host: " . $hostname,
    "Content-type: application/x-www-form-urlencoded",
);

$post_params = array(
    "client_id" => $clientId,
    "scope" => "https%3A%2F%2Fgraph.microsoft.com%2F.default",
    "client_secret" => $clientSecret,
    "grant_type" => "client_credentials",
);

$curl = curl_init($fullurl);

curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_POSTFIELDS, $post_params);
curl_setopt($curl, CURLOPT_HTTPHEADER, array("application/x-www-form-urlencoded"));
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

$response = curl_exec($curl);

?>