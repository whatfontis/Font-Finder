<?php
/*
www.WhatFontIs.com

Find any font from any image (commercial or free)

Using a catalogue of 990K+ fonts (commercial or free) and font finder AI, for every image uploaded we show over 60 similar fonts ( free or commercial).

More info here:

https://www.whatfontis.com/API-identify-fonts-from-image.html

Please modify XXXXXXXX from API_KEY with your API key.
*/

$file=file_get_contents('A.png');

$encdeod= base64_encode($file);
 



$curl = curl_init();


$data = array(
    'API_KEY' => 'XXXXXXXX',
    'IMAGEBASE64' => '1',
    'NOTTEXTBOXSDETECTION' => '0',
    'urlimage' => '',
    'urlimagebase64' => $encdeod,
    'limit' => '20'
);


curl_setopt($curl, CURLOPT_URL, 'https://www.whatfontis.com/api2/');
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

// Execută cererea și capturează răspunsul
$read_fonts_json = curl_exec($curl);


if(curl_errno($curl)){
    echo 'Curl error: ' . curl_error($curl);
}


curl_close($curl);

$read_fonts=json_decode($read_fonts_json);

print_r($read_fonts);


?>
