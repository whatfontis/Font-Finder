<?php
/**
 * match_helpers.php — matching a font name against the API results.
 *
 * This is the same matcher we score our own runs with, so numbers you get from
 * test_images.php are comparable to ours.
 *
 * Why it is not a string comparison: info.txt holds the family name, the catalogue
 * holds the full name of each cut. "Alfa Slab" comes back as "Alfa Slab One",
 * "Abramo" as "Abramo Script", "Akzidenz-Grotesk" as "Berthold Akzidenz-Grotesk
 * Medium Extended Italic". All correct answers. Compare as strings and you score
 * ~20% instead of ~70% on the same responses.
 *
 * So: drop weight/style words, split CamelCase, strip mojibake, then match on the
 * remaining tokens — the part of the name that identifies the typeface.
 */

if (!isset($STYLE_WORDS)) {
    $STYLE_WORDS = array(
        'bold','italic','regular','light','medium','black','thin','heavy','demi','semi','semibold',
        'extrabold','ultrabold','ultralight','extralight','book','roman','oblique','plain','normal',
        'hairline','fine','blk','hvy','reg','xb','xl','xc','xcn','xd','xex','xexp',
        'xp','cn','bd','md','rg','bk','sb','eb','ul','hv','th','it','ob','ex','ext','wd',
        'opt','opti','hev','rom','con','exp','com','cnd','exd',
        'condensed','expanded','extended','narrow','wide','compressed','short','tall',
        'display','text','poster','headline','caption','subhead','small','smallcaps','sc',
        'inline','outline','shadow','solid','fill','filled','hollow','rough','grunge','distressed',
        'script','sans','serif','slab','gothic',
        'pro','std','lt','mt','bt','itc','urw','adobe','linotype','monotype','com','ot','otf','ttf',
        'no','number','version','ver','new','old','alt','alternate','alternates','one','two','three',
        'four','five','jnl','nf','hmk','let',
        'ff','adbe','nmy','enve','envg','fbr','nfs','cr','cf','fb','oth','swc',
        'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
    );
}

if (!function_exists('isStyleWord')) {
function isStyleWord($w) {
    global $STYLE_WORDS;
    $lw = strtolower($w);
    if (in_array($lw, $STYLE_WORDS, true)) return true;
    if (preg_match('/^\d+$/', $lw)) return true;
    return false;
}}

if (!function_exists('stripWeirdChars')) {
// Strip trademark/copyright + UTF-8→Latin-1 mojibake (Â®, Â™, Â©, Â°).
function stripWeirdChars($s) {
    $s = str_replace(array('Â®','Â™','Â©','Â°','Â '), ' ', $s);
    $s = str_replace(array('®','™','©','°','¢','£','¥','§','¶','†','‡','•','‰'), ' ', $s);
    $s = preg_replace('/[^\x20-\x7E]/', ' ', $s);  // strip non-ASCII
    return $s;
}}

if (!function_exists('splitCamelCase')) {
// "ChopinScript" → "Chopin Script", "AdobeGaramondPro" → "Adobe Garamond Pro"
function splitCamelCase($s) {
    $s = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $s);
    $s = preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1 $2', $s);
    return $s;
}}

if (!function_exists('coreTokens')) {
// The tokens that actually name the typeface: no parentheses, no "by Foundry",
// no weight/style words, no bare numbers.
function coreTokens($name) {
    $s = preg_replace('/\s*\([^)]*\)\s*/', ' ', $name);
    $s = preg_replace('/\s+by\s+.*$/i', ' ', $s);
    $s = stripWeirdChars($s);
    $s = splitCamelCase($s);
    $s = str_replace(array('-','_','/','&','.',':','+'), ' ', $s);
    $w = preg_split('/\s+/', trim($s));
    $core = array();
    foreach ($w as $t) {
        $t = trim($t);
        if ($t === '' || isStyleWord($t)) continue;
        $core[] = strtolower($t);
    }
    return $core;
}}

if (!function_exists('tokensMatch')) {
// $e = expected tokens, $a = tokens of one API result.
// A single distinctive token (>= 4 chars) present in $a is enough; shorter ones are
// too ambiguous to decide on. Substring either way needs >= 5 chars, which catches
// "garamond" inside "garamondpro". Otherwise every expected token must be present.
function tokensMatch($e, $a) {
    if (empty($e)) return false;
    foreach ($e as $et) {
        if (strlen($et) < 4) continue;
        if (in_array($et, $a, true)) return true;
        foreach ($a as $at) {
            if (strpos($at, $et) !== false || strpos($et, $at) !== false) {
                if (min(strlen($at), strlen($et)) >= 5) return true;
            }
        }
    }
    foreach ($e as $et) if (!in_array($et, $a, true)) return false;
    return true;
}}

if (!function_exists('findPosition')) {
// $expected: a font name, or an array of acceptable names.
// $apiResults: the API's list, each entry with 'title' and (optionally) 'url'.
// Returns the BEST (lowest) 1-based position across everything tried; pos -1 = not found.
function findPosition($expected, $apiResults) {
    $expectedList = is_array($expected) ? $expected : array($expected);
    $expectedList = array_filter(array_map('trim', $expectedList), 'strlen');
    if (empty($expectedList)) return array('pos'=>-1, 'in'=>'');

    // Tokenise each API result once, from the title AND the url slug — the slug often
    // keeps a name the title dropped. The ^[A-Z]{2,5}_ strip removes catalogue
    // prefixes like ADBE_ / OTH_ so they are not mistaken for part of the name.
    $apiTokensList = array();
    foreach ($apiResults as $it) {
        $title = $it['title'] ?? '';
        $url   = $it['url'] ?? '';
        $uc = preg_replace('#^.*/#', '', $url);
        $uc = preg_replace('/\.(font|ttf|otf|woff2?|fnt)$/i', '', $uc);
        $uc = preg_replace('/^[A-Z]{2,5}_/', '', $uc);
        $apiCore = array_unique(array_merge(coreTokens($title), coreTokens($uc)));
        $apiTokensList[] = array('title'=>$title, 'tokens'=>$apiCore);
    }

    $bestPos = PHP_INT_MAX; $bestTitle = '';

    foreach ($expectedList as $expectedName) {
        $expCore = coreTokens($expectedName);
        if (empty($expCore)) continue;

        // Pass 1: all tokens together.
        foreach ($apiTokensList as $i=>$entry) {
            if (tokensMatch($expCore, $entry['tokens'])) {
                if (($i+1) < $bestPos) { $bestPos = $i+1; $bestTitle = $entry['title']; }
                break; // first match for this name, then move to the next name
            }
        }
        if ($bestPos === 1) return array('pos'=>1, 'in'=>$bestTitle);  // can't do better

        // Pass 2: each expected token on its own.
        if (count($expCore) >= 2) {
            foreach ($expCore as $singleToken) {
                $partial = array($singleToken);
                foreach ($apiTokensList as $i=>$entry) {
                    if (tokensMatch($partial, $entry['tokens'])) {
                        if (($i+1) < $bestPos) { $bestPos = $i+1; $bestTitle = $entry['title']; }
                        break;
                    }
                }
                if ($bestPos === 1) return array('pos'=>1, 'in'=>$bestTitle);
            }
        }
    }

    if ($bestPos === PHP_INT_MAX) return array('pos'=>-1, 'in'=>'');
    return array('pos'=>$bestPos, 'in'=>$bestTitle);
}}
