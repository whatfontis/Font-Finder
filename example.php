<?php
/*
www.WhatFontIs.com

Find any font from any image (commercial or free)

Using a catalogue of 500k+ fonts (commercial or free) and font finder AI, for every image uploaded we show over 60 similar fonts ( free or commercial).

More info here:

https://www.whatfontis.com/API-identify-fonts-from-image.html

Please modify XXXXXXXX from API_KEY with your API key.
*/

$json_example=<<<END
{
   "FONT": {
      "API_KEY": "XXXXXXXX",
      "BASE64": 0,
      "NOTTEXTBOXSDETECTION": 0,
      "INFO": {
         "urlimage": "https://d1ly52g9wjvbd2.cloudfront.net/img16/A/D/ADBE_Lobster-RegularA.png",
         "urlimagebase64": ""
      }
   }
}
END;

$file_to_send_toserver='https://www.whatfontis.com/api/?base64=1&file='.base64_encode($json_example);

$read_fonts_json = file_get_contents($file_to_send_toserver);

$read_fonts=json_decode($read_fonts_json);

print_r($read_fonts);
