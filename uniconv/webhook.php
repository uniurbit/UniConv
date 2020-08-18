<?php

function redirect($url, $statusCode = 303)
{
   header('Location: ' . $url, true, $statusCode);
   die();
}

$url = "https://jenkins.uniurb.it/job/uniconv_backend/build?token=uniconv_build";     

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);

curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

// Run query
echo "Querying $url ...\n";
$response = curl_exec($curl);
// $output contains the output string
$httpResponseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
if ($httpResponseCode !== 200) {
    throw new Exception("Cannot make request, HTTP status code " . $httpResponseCode);
}
if ($response !== false) {
    return json_decode($response);
}

curl_close($curl);

return false;



