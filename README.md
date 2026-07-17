# Font-Finder

Font Finder — [WhatFontIs.com](https://www.WhatFontIs.com) API example, and the 624-font
test set behind our font-identification benchmark.

Find any font from any image (commercial or free). Using a catalogue of 990K+ fonts
(commercial or free) and font finder AI, for every image uploaded we show over 60 similar
fonts (free or commercial).

API key and docs: https://www.whatfontis.com/API-identify-fonts-from-image.html

---

## The test set

624 fonts, each rendered as the same word — **Abrupt** — so the typeface is the only thing
that changes between images. Sourced from three places: 200+ from Adobe Fonts, nearly 200
from Google Fonts, and 200+ from dafont.com.

| File | Contents |
|---|---|
| `anospace.zip` | `imagenospace/` — 624 JPGs, normal letter spacing |
| `awithspace.zip` | `image/` — the same 624 fonts, letters spaced apart |
| `info.txt` | ground truth: `filename\|font name`, 624 lines (also inside each zip) |

Both zips cover the same 624 fonts; only the letter spacing differs. The spaced version is
easier to segment into individual characters, which is the first step of our pipeline —
use whichever suits how you cut letters.

The filename is the answer. `Abel.jpg` is the font *Abel* rendered as the word "Abrupt".
`info.txt` states it explicitly so you do not have to parse filenames:

```
A Another Tag.jpg|A Another Tag
ABeeZee.jpg|ABeeZee
Abel.jpg|Abel
```

## Reproducing our numbers

`example.php` sends one image to the API and prints the matches, best first. Point it at
each image in a zip, compare the returned names against `info.txt`, and count how often the
correct name appears in the first N results.

Grading is exact-name: "Montserrat" counts, "a geometric sans similar to Montserrat" does not.

What WhatFontIs scored on these 624 images, best configuration, `limit=20`:

| Result depth | Correct match rate |
|---|---|
| Top-1 | 81.4% |
| Top-5 | 92.8% |
| Top-10 | 94.5% |
| Top-20 | 96.3% |

Average response time: 3.2 seconds per image.

The default `api2/` endpoint that `example.php` calls is tuned differently: same Top-1
(81.9% in our run) but it plateaus near 88.8% by Top-20 rather than climbing to 96.3%.
Worth knowing before you compare your own count against the table.

We also ran the same 624 images against several general-purpose AI models. Those results,
and what we make of them, are in the write-up:
[Can GPT-5, Claude or Gemini identify fonts better than WhatFontIs.com?](https://www.whatfontis.com/blog/can-gpt-5-claude-or-gemini-identify-fonts-better-than-whatfontis-com/)
The images here are the same ones we used, so you can put them in front of any model you like.

## Usage

```bash
# add your API key to example.php first
php example.php
```

`A.png` is a single sample image, separate from the test set, so you can check your key works
before running the whole thing.
