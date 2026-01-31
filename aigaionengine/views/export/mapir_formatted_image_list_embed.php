<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
include_once('aigaionengine/helpers/my_userfields.php');

/*
 * mapir_formatted_image_list_embed.php
 * =====================================
 * EMBEDDABLE version of mapir_formatted_image_list.php
 * 
 * This view outputs ONLY the publication list content (no HTML/HEAD/BODY tags),
 * making it suitable for embedding inside other views like the tabbed author view.
 *
 * Input parameters:
 *   nonxrefs: map of [id=>publication] for non-crossreffed-publications
 *   xrefs: map of [id=>publication] for crossreffed-publications (optional)
 *   withlinks: 1 for showing links to aigaion in each publication, 0 for not
 *   hide_bullets: true/false - whether to hide bullet points
 *   sort: 'type' or 'year' - how to organize the publications
 *   maxyearsfromnow: filter publications by year (optional)
 */

// Definitions
$pathaigaion = AIGAION_ROOT_URL;

if (!isset($withlinks)) {
    $withlinks = '1';
}
if (!isset($sort)) {
    $sort = 'type';
}
if (!isset($maxyearsfromnow) || ($maxyearsfromnow == 'none') || empty($maxyearsfromnow)) {
    $maxyearsfromnow = 100;
}
if (!isset($hide_bullets)) {
    $hide_bullets = FALSE;
}
if (!isset($xrefs)) {
    $xrefs = array();
}

$thisYear = date("Y");
$maxyeartopublish = (intval($thisYear) - intval($maxyearsfromnow));

$typenames = array(
    'Article' => 'Journals',
    'Book' => 'Books',
    'Inbook' => 'Book Chapters',
    'Inproceedings' => 'Conferences',
    'Misc' => 'Patents',
    'Phdthesis' => 'PhD Theses',
    'Manual' => 'Manuals',
    'Mastersthesis' => 'Master Theses',
    'Techreport' => 'Technical Reports'
);

// Load helpers
$this->load->helper('export');
$this->load->helper('osbib');

$bibformat = new BIBFORMAT(APPPATH."include/OSBib/format/", TRUE);

$yearold = "";

/*=======================================================================================================*/
// Auxiliary functions (defined only if not already defined to avoid redeclaration errors)

if (!function_exists('embed_myPrintWithComma')) {
    function embed_myPrintWithComma($str) {
        if (0 != strlen($str))
            echo "$str, ";
    }
}

if (!function_exists('embed_myPrintWithPrefix')) {
    function embed_myPrintWithPrefix($p, $str) {
        if (0 != strlen($str))
            echo "$p $str, ";
    }
}

if (!function_exists('embed_isupper')) {
    function embed_isupper($i) {
        $i2 = strtoupper($i);
        return (ord($i2) >= ord('A') && ord($i2) <= ord('Z') && $i2 === $i);
    }
}

if (!function_exists('embed_myPrintFormattedFullName')) {
    function embed_myPrintFormattedFullName($nam) {
        $comPos = strpos($nam, ',');
        if (!$comPos) { echo $nam; return; }
        $firstNames = trim(substr($nam, $comPos + 1));
        $lastNames = substr($nam, 0, $comPos);
        $initials = '';
        $arr = preg_split('//', $firstNames, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($arr as $c) {
            if (embed_isupper($c)) $initials .= $c . '.';
        }
        echo $initials . ' ' . $lastNames;
    }
}

if (!function_exists('embed_myPrintNewElement')) {
    function embed_myPrintNewElement(&$nonxrefs, $pub_id, &$pathaigaion, &$withlinks, &$pubtype, $hide_bullets, &$yearold_ref) {
        // HTML element definitions
        $beginline_bullet = '<li class="aigaion_publine">' . "\n";
        $beginline_no_bullet_pub = '<li class="aigaion_nobulletpubline">' . "\n";
        $beginline_no_bullet_ctrl = '<li class="aigaion_nobulletline">' . "\n";
        $endline = "</li>\n";
        $begindivyear = '<div class="aigaion_divyear">';
        $enddivyear = "</div>\n";
        $beginpubli = '<div class="aigaion_publication">' . "\n";
        $endpubli = "</div>\n";

        $publine_start_tag = ($hide_bullets) ? $beginline_no_bullet_pub : $beginline_bullet;

        // Header for each new year
        if (intval($nonxrefs[$pub_id]->year) != intval($yearold_ref)) {
            echo $beginline_no_bullet_ctrl . $begindivyear . $nonxrefs[$pub_id]->year . $enddivyear . $endline;
            $yearold_ref = $nonxrefs[$pub_id]->year;
        }

        // Publication data
        $hasLinks = (intval($withlinks) == 1);
        $hasPDF = ((isset($nonxrefs[$pub_id]->firstattachment)) && (strlen($nonxrefs[$pub_id]->firstattachment) > 0));
        $hasURL = ((isset($nonxrefs[$pub_id]->url)) && (strlen($nonxrefs[$pub_id]->url) > 0));
        $hasNOTE = ((isset($nonxrefs[$pub_id]->note)) && (strlen($nonxrefs[$pub_id]->note) > 0));

        echo $publine_start_tag . $beginpubli;

        // 0) IMAGE
        echo "<div class='aigaion_publication_image'>" . "\n";

        $userfields_map = my_parse_pub_userfields($nonxrefs[$pub_id]);
        if (isset($userfields_map['img_url']) && $userfields_map['img_url'] != '') {
            $imgPath = $userfields_map['img_url'];
        } else {
            $imgPath = 'https://ingmec.ual.es/~ingmec/default_pub_icon.png';
        }

        if ($hasURL) {
            echo ' <a href="' . ($nonxrefs[$pub_id]->url) . '" target="_blank">';
            echo "	<img src='" . $imgPath . "'  />" . "\n";
            echo '</a>';
        } else {
            echo "	<img src='" . $imgPath . "'  />" . "\n";
        }
        echo "</div>" . "\n";

        echo "<div class='aigaion_publication_info'>" . "\n";

        // 1) AUTHOR NAMES
        echo "<p class='authors'>";
        foreach ($nonxrefs[$pub_id]->authors as $author) {
            embed_myPrintFormattedFullName($author->cleanname);
            echo ", ";
        }
        echo "</p>";

        // 2) TITLE
        echo "<p class='title'>";
        if ($hasURL)
            echo ' <a class="aigaion_enlace" href="' . ($nonxrefs[$pub_id]->url) . '" target="_blank">';
        echo strval($nonxrefs[$pub_id]->title);
        if ($hasNOTE)
            echo ' [' . $nonxrefs[$pub_id]->note . ']';
        if ($hasURL)
            echo '</a>';
        echo '</p>';

        // 3) JOURNAL-CONFERENCE
        echo "<p class='journal'>";

        if ((stripos($pubtype, 'Theses') !== FALSE) || (stripos($pubtype, 'thesis') !== FALSE))
            echo strval($nonxrefs[$pub_id]->school) . ', ';

        if ((stripos($pubtype, 'Misc') !== FALSE) || (stripos($pubtype, 'Patents') !== FALSE))
            echo strval($nonxrefs[$pub_id]->howpublished) . ', ';

        if (stripos($nonxrefs[$pub_id]->title, '(in spanish)') !== FALSE)
            echo '<img src="' . $pathaigaion . '/export_css/espania.gif" title="(in spanish)" width="16" alt="(in spanish)"> ';

        embed_myPrintWithComma($nonxrefs[$pub_id]->journal);
        embed_myPrintWithComma($nonxrefs[$pub_id]->booktitle);

        // 4) Rest of info
        embed_myPrintWithComma($nonxrefs[$pub_id]->edition);
        embed_myPrintWithComma($nonxrefs[$pub_id]->series);
        embed_myPrintWithComma($nonxrefs[$pub_id]->location);
        embed_myPrintWithComma($nonxrefs[$pub_id]->institution);
        embed_myPrintWithComma($nonxrefs[$pub_id]->organization);

        embed_myPrintWithPrefix("vol. ", $nonxrefs[$pub_id]->volume);
        embed_myPrintWithPrefix("no. ", $nonxrefs[$pub_id]->number);
        embed_myPrintWithPrefix("ch. ", $nonxrefs[$pub_id]->chapter);

        if (isset($nonxrefs[$pub_id]->firstpage) && 0 != strlen($nonxrefs[$pub_id]->firstpage)) {
            echo 'pp. ' . strval($nonxrefs[$pub_id]->firstpage) . '-' . strval($nonxrefs[$pub_id]->lastpage) . ', ';
        } else if (isset($nonxrefs[$pub_id]->pages) && 0 != strlen($nonxrefs[$pub_id]->pages)) {
            echo 'pp. ' . strval($nonxrefs[$pub_id]->pages) . ', ';
        }

        echo strval($nonxrefs[$pub_id]->year);

        // Ranking
        my_print_pub_ranking($nonxrefs[$pub_id]);

        echo '. ';
        echo '</p>';

        // 5) Downloads
        if ($hasLinks || $hasPDF || $hasURL)
            echo '   ';

        if ($hasPDF)
            echo ' <a class="aigaion_enlace" href="' . ($nonxrefs[$pub_id]->firstattachment) . '" target="_blank" > <img src="' . $pathaigaion . '/export_css/pdf_icon.gif" border="0" alt="pdf"></a>';

        if ($hasURL) {
            if ($hasPDF) echo '   ';
            echo ' <a class="aigaion_enlace" href="' . ($nonxrefs[$pub_id]->url) . '" target="_blank"><img src="' . $pathaigaion . '/export_css/url_icon.png" border="0" alt="www"></a>';
        }

        if ($hasLinks) {
            if ($hasPDF || $hasURL) echo '   ';
            echo '<a class="aigaion_enlace" href="' . $pathaigaion . '/index.php/export/publication/' . ($nonxrefs[$pub_id]->pub_id) . '/bibtex" target="_blank"><img src="' . $pathaigaion . '/export_css/bibtex_icon.gif" border="0" alt="bibtex"></a>';
        }

        if (isset($nonxrefs[$pub_id]->doi) && 0 != strlen($nonxrefs[$pub_id]->doi)) {
            if ($hasLinks || $hasPDF || $hasURL) echo '   ';
            $d = $nonxrefs[$pub_id]->doi;
            echo ' <a class="aigaion_enlace" href="http://dx.doi.org/' . strval($d) . '" target="_blank"> <img src="' . $pathaigaion . '/export_css/doi_icon.gif" border="0" alt="doi"></a>';
        }

        // 6) End - close publication div and list item
        echo $endpubli . $endline;
    }
}

/*=======================================================================================================*/

// Build index of publications by type
$myPubsIndex = array();
foreach ($typenames as $pubtypforcmp => $pubtype) {
    $myPubsIndex[$pubtype] = array();
}

foreach ($typenames as $pubtypforcmp => $pubtype) {
    foreach ($nonxrefs as $pub_id => $publication) {
        if (!strcmp($nonxrefs[$pub_id]->pub_type, $pubtypforcmp)) {
            if (intval($nonxrefs[$pub_id]->year) >= intval($maxyeartopublish)) {
                $myPubsIndex[$pubtype][$pub_id] = $pub_id;
            }
        }
    }
}

// Output the publication list (NO full HTML wrapper - just the list)
echo '<ul class="aigaion_publist">' . "\n";

if (strcmp($sort, "year") == 0) {
    // Sort by year
    $yearold = 0;
    foreach ($nonxrefs as $pub_id => $publication) {
        if (intval($nonxrefs[$pub_id]->year) >= intval($maxyeartopublish)) {
            $pubtype = $nonxrefs[$pub_id]->pub_type;
            embed_myPrintNewElement($nonxrefs, $pub_id, $pathaigaion, $withlinks, $pubtype, $hide_bullets, $yearold);
        }
    }
} else {
    // Sort by type (default)
    $begindivtype = '<div class="aigaion_divtype">';
    $beginline_no_bullet = '<li class="aigaion_nobulletline">' . "\n";
    $enddivtype = "</div>\n";
    $endline = "</li>\n";

    foreach ($typenames as $pubtypforcmp => $pubtype) {
        $pubscount = count($myPubsIndex[$pubtype]);
        if ($pubscount == 0)
            continue;

        // Header of this publication type
        echo $beginline_no_bullet . $begindivtype . "<h2>";
        echo "$pubtype (" . strval($pubscount) . ")" . "</h2>" . $enddivtype . $endline;

        $yearold = 0;
        foreach ($myPubsIndex[$pubtype] as $pub_id) {
            embed_myPrintNewElement($nonxrefs, $pub_id, $pathaigaion, $withlinks, $pubtype, $hide_bullets, $yearold);
        }
    }
}

echo "</ul>\n";

?>
