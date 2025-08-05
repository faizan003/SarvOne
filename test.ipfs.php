<?php
$ch = curl_init('http://localhost:5001/api/v0/version');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
if ($response === false) {
    echo "cURL error: " . curl_error($ch);
} else {
    echo $response;
}
curl_close($ch);
