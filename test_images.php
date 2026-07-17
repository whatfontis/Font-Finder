<?php
/*
www.WhatFontIs.com

Run the whole test set through the API and score it.

  1. unzip anospace.zip     (or awithspace.zip)
  2. put your API key below
  3. php test_images.php imagenospace

It sends every image to https://www.whatfontis.com/api2/, compares the returned
font names against info.txt, and prints how often the right font came back in the
first 1 / 5 / 10 / 20 results.

Expect it to take a while: ~3s per image, 624 images, so roughly 30 minutes.
Results are written to results.csv as it goes, so you can stop and inspect.

More info: https://www.whatfontis.com/API-identify-fonts-from-image.html
*/

$API_KEY = 'XXXXXXXX';
$LIMIT   = 20;                 // must be >= the deepest rank you want to score
$DEPTHS  = [1, 5, 10, 20];
$SLEEP   = 0;                  // seconds between calls, raise if you get rate limited

// ── input folder ────────────────────────────────────────────────────────────
$folder = $argv[1] ?? 'imagenospace';
$folder = rtrim($folder, '/\\');
if (!is_dir($folder)) {
    fwrite(STDERR, "Not a folder: $folder\nUnzip anospace.zip or awithspace.zip first, then pass the folder name.\n");
    exit(1);
}
if ($API_KEY === 'XXXXXXXX') {
    fwrite(STDERR, "Put your API key in \$API_KEY first.\nGet one: https://www.whatfontis.com/API-identify-fonts-from-image.html\n");
    exit(1);
}

// ── ground truth: filename|font name ────────────────────────────────────────
$infoPath = file_exists("$folder/info.txt") ? "$folder/info.txt" : 'info.txt';
if (!file_exists($infoPath)) {
    fwrite(STDERR, "info.txt not found (looked in $folder/ and here).\n");
    exit(1);
}
$truth = [];
foreach (file($infoPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (strpos($line, '|') === false) continue;
    list($file, $font) = explode('|', $line, 2);
    $truth[trim($file)] = trim($font);
}
if (!$truth) { fwrite(STDERR, "info.txt has no usable lines.\n"); exit(1); }

// info.txt holds FAMILY names ("Alfa Slab", "Abramo"); the catalogue holds the full
// name of each cut ("Alfa Slab One", "Abramo Script", "Akzidenz Grotesk Pro Ext Med
// Italic"). So a hit is: same name, or the returned name is a member of the expected
// family — it starts with the family name followed by a space. The trailing space
// matters, or "Abel" would also match "Abelina".
// Hyphens and underscores are treated as spaces ("Akzidenz-Grotesk" = "Akzidenz Grotesk").
function norm($s) {
    $s = mb_strtolower(trim((string)$s));
    $s = str_replace(['-', '_'], ' ', $s);
    return preg_replace('/\s+/', ' ', $s);
}
function isHit($returned, $expected) {
    $r = norm($returned);
    $e = norm($expected);
    return $r === $e || strpos($r, $e . ' ') === 0;
}

function identify($path, $API_KEY, $LIMIT) {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://www.whatfontis.com/api2/',
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_POSTFIELDS => http_build_query([
            'API_KEY' => $API_KEY,
            'IMAGEBASE64' => '1',
            // these are clean renders of a single word, already tightly cropped
            'NOTTEXTBOXSDETECTION' => '1',
            'urlimage' => '',
            'urlimagebase64' => base64_encode(file_get_contents($path)),
            'limit' => (string)$LIMIT,
        ]),
    ]);
    $t0   = microtime(true);
    $body = curl_exec($curl);
    $ms   = (int)round((microtime(true) - $t0) * 1000);
    $err  = curl_errno($curl) ? curl_error($curl) : '';
    curl_close($curl);
    if ($err !== '') return ['error' => $err, 'ms' => $ms];

    $j = json_decode($body, true);
    if (!is_array($j)) return ['error' => 'bad JSON: ' . substr((string)$body, 0, 120), 'ms' => $ms];
    // the API returns a flat list; asking for a model/debug wraps it in "results"
    $list = isset($j['results']) && is_array($j['results']) ? $j['results'] : $j;
    if (!is_array($list) || (isset($list['error']))) {
        return ['error' => is_array($list) ? json_encode($list) : 'unexpected shape', 'ms' => $ms];
    }
    $names = [];
    foreach ($list as $row) {
        if (is_array($row) && isset($row['title'])) $names[] = (string)$row['title'];
    }
    return ['names' => $names, 'ms' => $ms];
}

// ── run ─────────────────────────────────────────────────────────────────────
$files = glob("$folder/*.jpg") ?: [];
sort($files, SORT_NATURAL);
if (!$files) { fwrite(STDERR, "No .jpg files in $folder\n"); exit(1); }

$hits = array_fill_keys($DEPTHS, 0);
$tested = 0; $errors = 0; $notfound = 0; $totalMs = 0;

$csv = fopen('results.csv', 'w');
fputcsv($csv, array_merge(['file', 'expected', 'rank', 'ms'], array_map(fn($d) => "top$d", $DEPTHS)));

foreach ($files as $path) {
    $base = basename($path);
    if (!isset($truth[$base])) continue;          // not part of the set
    $want = $truth[$base];
    $tested++;

    $r = identify($path, $API_KEY, $LIMIT);
    $totalMs += $r['ms'];

    if (isset($r['error'])) {
        $errors++;
        fputcsv($csv, [$base, $truth[$base], 'ERROR: ' . $r['error'], $r['ms'], '', '', '', '']);
        printf("%4d/%d  %-42s ERROR %s\n", $tested, count($files), $base, $r['error']);
        continue;
    }

    $rank = 0;
    foreach ($r['names'] as $i => $name) {
        if (isHit($name, $want)) { $rank = $i + 1; break; }
    }
    if ($rank === 0) $notfound++;
    $row = [$base, $truth[$base], $rank ?: 'not found', $r['ms']];
    foreach ($DEPTHS as $d) {
        $ok = ($rank > 0 && $rank <= $d);
        if ($ok) $hits[$d]++;
        $row[] = $ok ? 1 : 0;
    }
    fputcsv($csv, $row);

    if ($tested % 25 === 0 || $rank === 0) {
        $eff = max(1, $tested - $errors);
        printf("%4d/%d  %-42s %-10s  running Top1 %.1f%%\n",
            $tested, count($files), $base, $rank ?: 'not found', $hits[1] * 100 / $eff);
    }
    if ($SLEEP > 0) sleep($SLEEP);
}
fclose($csv);

// ── score ───────────────────────────────────────────────────────────────────
// Errors are excluded from the denominator (an API/network failure is not a wrong
// answer); a font we simply did not return IS counted as a miss.
$eff = $tested - $errors;
echo "\n";
echo "folder   : $folder\n";
echo "tested   : $tested   errors: $errors   scored on: $eff\n";
echo "not found: $notfound\n";
printf("avg time : %dms\n\n", $tested ? (int)round($totalMs / $tested) : 0);
foreach ($DEPTHS as $d) {
    printf("  Top-%-3d %6.1f%%   (%d/%d)\n", $d, $eff ? $hits[$d] * 100 / $eff : 0, $hits[$d], $eff);
}
echo "\nper-image detail: results.csv\n";
