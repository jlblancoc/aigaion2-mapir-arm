<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
include_once('aigaionengine/helpers/my_userfields.php');

/*
---------------- JLBC JAN/2017 ------------------
-- Use new userfields data to generate stats for journal rankings

---------------- MLOPEZ 13/10/2016 ------------------
-- note" field is shown in parentheses

--------------- JGMONROY 11/05/2015 -----------------
-- Change reference format based on user options.
-- New configuration options to choose the order of publications, and format.

--------------- JAFMA 16/05/2008 --------------------
-- JLBC 1/02/2010: A few changes to avoid calling the Aigaion slow formatting functions

views/export/mapir_formatted_image_list.php
(NOTE: FILENAME MODIFIED TO IMAGE_LIST FOR CLARITY, ORIGINAL WAS mapir_formatted)

displays osbib formatted data for given publications as a simple list.

input parameters:
nonxref: map of [id=>publication] for non-crossreffed-publications
xref: map of [id=>publication] for crossreffed-publications
header: not used here.
style: APA | IEEETRANS, etc (available OSBib styles)
withlinks: 1 for showing links to aigaion in each publication, 0 for not showing them.
cssfile: CSS file name (with extension, without path), that must be in http://babel.isa.uma.es/_utils/aigaion/export_css
*/

// Definitions
$pathaigaion = AIGAION_ROOT_URL ;     // Removed http://xxxx/ to make more generic...
if (!isset($header) || ($header == null)) {
    $header = array();
}

// *** NEW: Initialize $hide_bullets ***
if (!isset($hide_bullets)) {
    $hide_bullets = FALSE;
}

$typenames = get_pub_types();
$maxyeartopublish = 0; // Show all years unless limited by URL parameter
if (isset($maxyearsfromnow) && strcmp($maxyearsfromnow,'none') != 0) {
    $maxyeartopublish = date('Y') - intval($maxyearsfromnow);
}
if (!isset($sort)) {
    $sort = "type"; // default sort: type
}


// --- START: REMOVED HTML HEADER WRAPPER (DOCTYPE, HTML, HEAD, BODY tags) ---


/** NEW SIGNATURE: Added $hide_bullets parameter */
function myPrintNewElement(&$nonxrefs,$pub_id,&$pathaigaion,&$withlinks,&$pubtype, $hide_bullets)
{
	//Some definitions
    // Original aigaion_publine is used for standard list-style markers
	$beginline_bullet = '<li class="aigaion_publine">'."\n"; 
    // New class used to explicitly remove list-style markers
	$beginline_no_bullet_pub = '<li class="aigaion_nobulletpubline">'."\n"; 
    // Existing class used for control lines (Year/Type headers)
	$beginline_no_bullet_ctrl = '<li class="aigaion_nobulletline">'."\n"; 
	$endline = "</li>\n";
	$begindivyear = '<div class="aigaion_divyear">';
	$enddivyear = "</div>\n";
	$beginpubli = '<div class="aigaion_publication">'."\n"; 
	$endpubli = "</div>\n";

    // *** NEW LOGIC: Choose the correct start tag for a publication entry ***
    $publine_start_tag = ($hide_bullets) ? $beginline_no_bullet_pub : $beginline_bullet;
    // ---------------------------------------------------------------------

	global $yearold;

	// Header for each new year --------------
	if (intval($nonxrefs[$pub_id]->year)!=intval($yearold))
	{
		// Always use the existing no-bullet class for the year header (control line)
		echo $beginline_no_bullet_ctrl.$begindivyear.$nonxrefs[$pub_id]->year.$enddivyear.$endline;
		$yearold=$nonxrefs[$pub_id]->year;
	}

	// Public. data -------------------------
	$hasLinks= (intval($withlinks)==1);

	// Output the list item using the chosen tag
	echo $publine_start_tag.$beginpubli; 

	// 1. Image
	if (!empty($nonxrefs[$pub_id]->firstattachment))
	{
	    $href = $nonxrefs[$pub_id]->firstattachment;
	    $width="64"; $height="64";
	    
	    $userfields_array = my_userfields_get_array ($nonxrefs[$pub_id]->userfields);
	    if (array_key_exists('image_width',$userfields_array)) 
	    	$width = $userfields_array['image_width'];
	    if (array_key_exists('image_height',$userfields_array))
	    	$height = $userfields_array['image_height'];

        $image_base = basename( $href );
        $image_dir  = dirname( $href );
	    $image_rel_path = $image_dir."/small_".$image_base;
	    
	    echo '<a class="aigaion_image" href="'.$href.'" target="_blank" ><img src="'.$pathaigaion.$image_rel_path.'" width="'.$width.'" height="'.$height.'" alt="image"></a>';
	}
	
	echo '<div class="aigaion_publication_info">';

	// 2. Authors
	echo '<p class="authors">';
	echo $nonxrefs[$pub_id]->authors_formatted;
	echo '</p>';
	
	// 3. Title
	echo '<p class="title">';
	echo $nonxrefs[$pub_id]->title;
	echo '</p>';

	// 4. Journal/Booktitle/Misc
	echo '<p class="journal">';
	// Check for quartile/ranking and show icon
	$userfields_array = my_userfields_get_array ($nonxrefs[$pub_id]->userfields);
	if (array_key_exists('journal_quartile',$userfields_array)) {
	    $q = $userfields_array['journal_quartile'];
	    if ($q == 'Q1') $q_img = 'Q1.gif';
	    else if ($q == 'Q2') $q_img = 'Q2.gif';
	    else if ($q == 'Q3') $q_img = 'Q3.gif';
	    else if ($q == 'Q4') $q_img = 'Q4.gif';
	    else $q_img = 'Qna.gif';
	    
	    echo '<img src="'.$pathaigaion.'export_css/'.$q_img.'" title="Journal Quartile '.$q.'" width="16" alt="Q"> ';
	}
	
	// Check for language icon
	if (array_key_exists('language',$userfields_array)) {
	    $lang = $userfields_array['language'];
	    $lang_img = strtolower(substr($lang, 0, 2)).'.gif'; // e.g., 'es' for Spanish
	    echo '<img src="'.$pathaigaion.'export_css/'.$lang_img.'" title="(in '.$lang.')" width="16" alt="(in '.$lang.')"> ';
	}

	$osbib = new OSBibFormatted($nonxrefs[$pub_id], $xrefpubs);
	$osbib->style = $style;
	$osbib->format = $format;
	$osbib->useurl = False; // URL link is added manually later

	echo $osbib->getFormatted('journal');
	
	if (!empty($nonxrefs[$pub_id]->note)) {
	    echo ' ('.$nonxrefs[$pub_id]->note.')';
	}
	
	echo '</p>';

	// 5. Links
	if ($hasLinks) {
	    // PDF link
	    if (!empty($nonxrefs[$pub_id]->firstattachment)) {
            echo ' <a class="aigaion_enlace" href="'.$nonxrefs[$pub_id]->firstattachment.'" target="_blank" > <img src="'.$pathaigaion.'export_css/pdf_icon.gif" border="0" alt="pdf"></a> ';
	    }
	    
	    // BibTeX link
        echo ' <a class="aigaion_enlace" href="'.$pathaigaion.'index.php/export/publication/'.$pub_id.'/bibtex" target="_blank"><img src="'.$pathaigaion.'export_css/bibtex_icon.gif" border="0" alt="bibtex"></a>';
	}
	
	echo '</div>'; // aigaion_publication_info
	
	echo '</div>'; // aigaion_publication
	
	echo $endline; // </li>
}


// --- START: List Generation ---

// Check if crossrefs are available, if so, merge them with the non-crossreffed ones
if (isset($xrefpubs) && is_array($xrefpubs)) {
    $nonxrefs = array_merge($nonxrefs, $xrefpubs);
}


// Generate publication list By.....

// Index by type for non-year sorts
$myPubsIndex = array();
foreach($typenames as $pubtypforcmp=>$pubtype)
	$myPubsIndex[$pubtype]=array();

foreach($typenames as $pubtypforcmp=>$pubtype)
{
	foreach ($nonxrefs as $pub_id=>$publication)
	{
		if (!strcmp($nonxrefs[$pub_id]->pub_type,$pubtypforcmp))
		{
			if ( intval($nonxrefs[$pub_id]->year) >= intval($maxyeartopublish) )
				$myPubsIndex[$pubtype][$pub_id]=$pub_id; 
		}
	}
}

// Start the main publication list container
// The opening <ul> or <ol> tag is required here. Use <ul> as it is an unordered list.
echo '<ul class="aigaion_publist">';


if (strcmp($sort,"year") == 0)
{
	$yearold=0;
	foreach($nonxrefs as $pub_id=>$publication)
	{
		if ( intval($nonxrefs[$pub_id]->year) >= intval($maxyeartopublish) )
		{
			$pubtype = $nonxrefs[$pub_id]->pub_type;
			// *** MODIFIED CALL: Passed $hide_bullets ***
			myPrintNewElement($nonxrefs,$pub_id,$pathaigaion,$withlinks,$pubtype, $hide_bullets); 
		}
	}
}
else // Sort by type
{
	$begindivtype = '<div class="aigaion_divtype">';
	$beginline_no_bullet = '<li class="aigaion_nobulletline">'."\n";
	$enddivtype = "</div>\n";
	$endline = "</li>\n";
	
	foreach($typenames as $pubtypforcmp=>$pubtype)
	{
		// Check if there are pubs of this type
		if (count($myPubsIndex[$pubtype]) > 0)
		{
			// Output the Type header (e.g., "Journals (X)")
			echo $beginline_no_bullet.$begindivtype.'<h2>'.ucwords($pubtype).' ('.count($myPubsIndex[$pubtype]).')</h2>'.$enddivtype.$endline;
			
			foreach($myPubsIndex[$pubtype] as $pub_id)
			{
				// *** MODIFIED CALL: Passed $hide_bullets ***
				myPrintNewElement($nonxrefs,$pub_id,$pathaigaion,$withlinks,$pubtype, $hide_bullets);
			}
		}
	}
}

// Close the main publication list container
echo '</ul>';

// --- END: REMOVED HTML FOOTER WRAPPER (BODY and HTML closing tags) ---