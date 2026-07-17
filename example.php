<?php
/*
www.WhatFontIs.com

Find any font from any image (commercial or free)

Using a catalogue of 1.2M+ fonts (commercial or free) and font finder AI, for every image uploaded we show over 60 similar fonts ( free or commercial).

More info here:

https://www.whatfontis.com/API-identify-fonts-from-image.html

Please modify XXXXXXXX from API_KEY with your API key, then run:  php example.php
*/

$file=file_get_contents('A.png');

$encdeod= base64_encode($file);

$curl = curl_init();

$data = array(
    'API_KEY' => 'XXXXXXXX',
    'IMAGEBASE64' => '1',
    // 0 = find the text in the image first (photos, screenshots).
    // 1 = the image is already a tight crop of the letters.
    'NOTTEXTBOXSDETECTION' => '0',
    'urlimage' => '',
    'urlimagebase64' => $encdeod,
    // how many matches to return, best first
    'limit' => '20'
);


curl_setopt($curl, CURLOPT_URL, 'https://www.whatfontis.com/api2/');
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$read_fonts_json = curl_exec($curl);

if(curl_errno($curl)){
    echo 'Curl error: ' . curl_error($curl) . "\n";
    curl_close($curl);
    exit(1);
}

curl_close($curl);

$read_fonts=json_decode($read_fonts_json);

if ($read_fonts === null) {
    echo "Could not decode the API response:\n" . $read_fonts_json . "\n";
    exit(1);
}

print_r($read_fonts);
