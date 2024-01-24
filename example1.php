<?php
/*
www.WhatFontIs.com

Find any font from any image (commercial or free)

Using a catalogue of 990K+ fonts (commercial or free) and font finder AI, for every image uploaded we show over 60 similar fonts ( free or commercial).

More info here:

https://www.whatfontis.com/API-identify-fonts-from-image.html

Please modify XXXXXXXX from API_KEY with your API key.
*/

$json_example=<<<END
{
   "FONT": {
      "API_KEY": "4e4ab51e5ef04a4765aa87ed2ca7ddf0351fa9aa5a04ca897468c5c8f3fe650e",
      "BASE64": 1,
      "NOTTEXTBOXSDETECTION": 0,
      "INFO": {
         "urlimage": "",
         "urlimagebase64": "iVBORw0KGgoAAAANSUhEUgAAAtoAAADIBAMAAAAkQVu5AAAAG1BMVEX///8AAACfn59/f3/f398fHx+/v79fX18/Pz+aE4kAAAADD0lEQVR4nO3cS27TUBQG4LR5DnGhLcMWNkBUFkB20Awyb3ZAdpDunOble/IQk/rmSvb3TUCRkI6OIuf/7Wt6PQAAAAAAAAAAAAAAAAAAAAAAAAAAAOAzfr68vPwuPURXDKoPX0tP0RX9zbbvS0/RFcPNtqu30mN0xHK77b+lx+iI1Xbbr6XH6IjtsquH0mN0w+1u24+l5+iGyW7b1Z/Sg3TCbL/tp9KDdMJ0v+116UE6Yb7f9rfSg3TBaL9s3f0axodt6+5XMDxsW3e/gud627p7fu/1tr+UHqX9BvWyq7vSs7RfP237e+lZ2i/9SOru+S3v07Z/lR6m9VZ3advr0sO03nw9r7etu2c2qp5W9bZ198zG1f6xpO5+BbPHmEreSo/Tcs8PMXHr7nm9v6Zbrrp7ZoNNxk6hRHfPqr/pj1Pd/TqGm/2mUKK7Z7XcROwQSnT3nFab+hhCieNpOW1vjYRQ4nhaRre7IzsplDieltGk2v6RQonuntFs910OoeSt7ECtNt1dp0Mo0d3zme8ySAglbnFnM9rna8/dr2F86I4plOju2dwcdptCie6ezfPh0dgsbVt3z+X98JsYQonunsmgPtIQQonunkm/ftUmhBLdPZNhVf81hZLqP/+AT1im73EIJU/lBmq11eOPg7DtdemxWipcPgLdPYvbi8vW3fOYhN4YQonunsUs7jW9faO7ZzGN14z0ZpnunsU8nkMLd0p09wwGR89pJmnbunsG/aNnkCGg6O4ZDI+fr4cMWGigVlsef4dXuntOq+Prcwgl6zIDtdngJHvc6O4Z3Z7k6nHatlfLGjc5ORYVQonjaY2bne40hJK3EgO12vT0ehFCieNpTZuf/haGUKK7N2x0lvNCKNHdGzY+u9UXQonu3rCbs9vY4X0Qt7gb9nz+iCZs++n6A7Xa+/njxxBK1tcfqM0GF+p5eB9Ed29U/8LXNxy91N0bNbxwaQ6hRHdv0GIxr+4Xi8XRRx+fJYuFhtOUw04vfFTzX5UAAAAAAAAAAAAAAAAAAAAAAAAAAAAA0Ov9A9K7YpDhgkqCAAAAAElFTkSuQmCC"
      }
   }
}
END;

$file_to_send_toserver='https://www.whatfontis.com/api2/?base64=1&file='.base64_encode($json_example);

$read_fonts_json = file_get_contents($file_to_send_toserver);

$read_fonts=json_decode($read_fonts_json);

print_r($read_fonts);
