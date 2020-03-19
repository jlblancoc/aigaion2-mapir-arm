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

views/export/mapir_formatted

displays osbib formatted data for given publications

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
    $header = '';
}
if (!isset($withlinks)) {
    $withlinks = '1';
}
if (!isset($sort)) {
    $sort = 'type';
}
if (!isset($maxyearsfromnow) || ($maxyearsfromnow == 'none')) {
    $maxyearsfromnow = 100;
}
$thisYear = date("Y");
$maxyeartopublish = (intval($thisYear) - intval($maxyearsfromnow));
$typenames = array(	'Article' => 'Journals',
					'Book' => 'Books',
					'Inbook' => 'Book Chapters',
					'Inproceedings' => 'Conferences',
					'Misc' => 'Patents',
					'Phdthesis' => 'PhD Theses',
					'Manual' => 'Manuals',
					'Mastersthesis' => 'Master Theses',
					'Techreport' => 'Technical Reports' );
// Start composing the page
$ext = "html";
$mime = "text/html";
$pre = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0//EN"
   "http://www.w3.org/TR/REC-html40/strict.dtd">'."\n";
$pre.="<html>\n<head>\n<title>".$header."</title>\n";
if (strlen($cssfile)>0)
	$pre.='<link rel="stylesheet" href="'.$pathaigaion.'/export_css/'.$cssfile.'" type="text/css">'."\n";
$pre.="</head>\n<body>\n";
if (strlen($cssfile)>0)
{
	$pi=pathinfo($cssfile);
	$nf='export_css/'.$pi['filename'].'.head';
	if (file_exists($nf))
	{
        if (false === $fh = fopen($nf, 'rb', FALSE)) $txt='';
		else
		{
          clearstatcache();
          if ($fsize = @filesize($nf)) $txt = fread($fh, $fsize);
		  else
		  {
              $txt = '';
              while (!feof($fh)) $txt .= fread($fh, 8192);
          }
          fclose($fh);
		}
		//$txt=file_get_contents($pi['filename'].'.head');
		$pre.=$txt;
	}
}
$pre.='<ul class="aigaion_publist">'."\n";
header("Content-Type: text/html; charset=UTF-8");
echo $pre;

// Call helpers
$this->load->helper('export');
$this->load->helper('osbib');

$bibformat = new BIBFORMAT(APPPATH."include/OSBib/format/", TRUE);

$t0=microtime(TRUE);
$tformatting=0.0;
$typeold="";
$yearold="";
$pubsact="";
$pubscount=0;


/*=======================================================================================================*/
// Auxiliary functions:
function myPrintWithComma($str)
{
	if (0!=strlen($str))
		echo "$str, ";
}

function myPrintWithPrefix($p, $str)
{
	if (0!=strlen($str))
		echo "$p $str, ";
}

function isupper($i)
{
	$i2 = strtoupper($i);
	return (ord($i2)>=ord('A') && ord($i2)<=ord('Z') && $i2 === $i);
}

function myPrintFormattedFullName($nam)
{
	// nam is: "Last Names, First Names"
	// Output is: "F.N. Last Names"
	$comPos=strpos($nam,',');
	if (!$comPos) { echo $nam; return; }  // No comma...
	$firstNames= trim(substr($nam,$comPos+1));
	$lastNames=substr($nam,0,$comPos);
	$initials='';
	$arr = preg_split('//', $firstNames, -1, PREG_SPLIT_NO_EMPTY);
	foreach($arr as $c)
	{
		if (isupper($c)) $initials.=$c.'.';
	}
	echo $initials.' '.$lastNames;
}

function myPrintNewElement(&$nonxrefs,$pub_id,&$pathaigaion,&$withlinks,&$pubtype)
{
	//Some definitions
	$beginline = '<li class="aigaion_publine">'."\n";
	$beginline_no_bullet = '<li class="aigaion_nobulletline">'."\n";
	$endline = "</li>\n";
	$begindivyear = '<div class="aigaion_divyear">';
	$enddivyear = "</div>\n";
	$beginpubli = '<div class="aigaion_publication">'."\n"; //  &#9642;  ";  // Black square (it's easier than CSS's ul/li with the mess of years, etc.)
	$endpubli = "</div>\n";

	global $yearold;

	// Header for each new year --------------
	if (intval($nonxrefs[$pub_id]->year)!=intval($yearold))
	{
		echo $beginline_no_bullet.$begindivyear.$nonxrefs[$pub_id]->year.$enddivyear.$endline;
		$yearold=$nonxrefs[$pub_id]->year;
	}

	// Public. data -------------------------
	$hasLinks= (intval($withlinks)==1);
	$hasPDF=((isset($nonxrefs[$pub_id]->firstattachment))&&(strlen($nonxrefs[$pub_id]->firstattachment)>0));
	$hasURL=((isset($nonxrefs[$pub_id]->url))&&(strlen($nonxrefs[$pub_id]->url)>0));
    $hasNOTE=((isset($nonxrefs[$pub_id]->note))&&(strlen($nonxrefs[$pub_id]->note)>0));

	echo $beginline.$beginpubli;

	// 0) IMAGE
	echo "<div class='aigaion_publication_image'>"."\n";

        $userfields_map = my_parse_pub_userfields($nonxrefs[$pub_id]);
	if (isset($userfields_map['img_url']) && $userfields_map['img_url'] != '') {
            $imgPath = $userfields_map['img_url'];
        } else {
            //Default image
            $imgPath = 'http://mapir.isa.uma.es/jgmonroy/images/papers/logo_pub_list.png';
        }

	if ($hasURL)
	{
		echo ' <a href="'.($nonxrefs[$pub_id]->url).'" target="_blank">';
		echo "	<img src='".$imgPath."'  />"."\n";
		echo '</a>';
	}
	else
		echo "	<img src='".$imgPath."'  />"."\n";

	echo "</div>"."\n";


	echo "<div class='aigaion_publication_info'>"."\n";
	// 1) AUTHOR NAMES
	echo "<p class='authors'>";
	foreach ($nonxrefs[$pub_id]->authors as $author)
	{
		myPrintFormattedFullName($author->cleanname);
		echo ", ";
	}
	echo "</p>";


	// 2) TITLE
	echo "<p class='title'>";
	if ($hasURL)
		echo ' <a class="aigaion_enlace" href="'.($nonxrefs[$pub_id]->url).'" target="_blank">';
	echo strval($nonxrefs[$pub_id]->title);
    // Note:
	if ($hasNOTE)
	    echo ' ['.$nonxrefs[$pub_id]->note.']';
	if ($hasURL)
		echo '</a>';
	echo '</p>';


	// 3) JOURNAL-CONFERENCE:
	echo "<p class='journal'>";

	//cga Add additional info for theses
	if ( (stripos($pubtype,'Theses')!==FALSE) || (stripos($pubtype,'thesis')!==FALSE) )
		echo strval($nonxrefs[$pub_id]->school). ', ';

	//cga Add additional info for Patents
	if ( (stripos($pubtype,'Misc')!==FALSE) || (stripos($pubtype,'Patents')!==FALSE) )
		echo strval($nonxrefs[$pub_id]->howpublished). ', ';

	if (stripos($nonxrefs[$pub_id]->title,'(in spanish)')!==FALSE)
		echo '<img src="'.$pathaigaion.'/export_css/espania.gif" title="(in spanish)" width="16" alt="(in spanish)"> ';

	//Standard data
	myPrintWithComma($nonxrefs[$pub_id]->journal);
	myPrintWithComma($nonxrefs[$pub_id]->booktitle);

	// 4) Rest of info, if available:
	myPrintWithComma($nonxrefs[$pub_id]->edition);
	myPrintWithComma($nonxrefs[$pub_id]->series);
	myPrintWithComma($nonxrefs[$pub_id]->location);
	myPrintWithComma($nonxrefs[$pub_id]->institution);
	myPrintWithComma($nonxrefs[$pub_id]->organization);

	myPrintWithPrefix("vol. ",$nonxrefs[$pub_id]->volume);
	myPrintWithPrefix("no. ",$nonxrefs[$pub_id]->number);
	myPrintWithPrefix("ch. ",$nonxrefs[$pub_id]->chapter);
	// pages?
	if (isset($nonxrefs[$pub_id]->firstpage) && 0!=strlen($nonxrefs[$pub_id]->firstpage))
	{
		echo 'pp. '.strval($nonxrefs[$pub_id]->firstpage).'-'.strval($nonxrefs[$pub_id]->lastpage).', ';
	}
	else if (isset($nonxrefs[$pub_id]->pages) && 0!=strlen($nonxrefs[$pub_id]->pages))
	{
		echo 'pp. '.strval($nonxrefs[$pub_id]->pages).', ';
	}

	// Year:
	echo strval($nonxrefs[$pub_id]->year);

        // Ranking:
        my_print_pub_ranking($nonxrefs[$pub_id]);

        // end of pub entry:
        echo '. ';
	echo '</p>';

	// 5) Downloads
	if ($hasLinks || $hasPDF || $hasURL)
		echo '   ';
	//PDF
	if ($hasPDF)
		echo ' <a class="aigaion_enlace" href="'.($nonxrefs[$pub_id]->firstattachment).'" target="_blank" > <img src="'.$pathaigaion.'/export_css/pdf_icon.gif" border="0" alt="pdf"></a>';

	//URL
	if ($hasURL)
	{
		if ($hasPDF) echo '   ';
		echo ' <a class="aigaion_enlace" href="'.($nonxrefs[$pub_id]->url).'" target="_blank"><img src="'.$pathaigaion.'/export_css/url_icon.png" border="0" alt="www"></a>';
	}
	//BibTex
	if ($hasLinks)
	{
		if ($hasPDF || $hasURL) echo '   ';
		echo '<a class="aigaion_enlace" href="'.$pathaigaion.'/index.php/export/publication/'.($nonxrefs[$pub_id]->pub_id).'/bibtex" target="_blank"><img src="'.$pathaigaion.'/export_css/bibtex_icon.gif" border="0" alt="bibtex"></a>';
	}
	// DOI?
	if (isset($nonxrefs[$pub_id]->doi) && 0!=strlen($nonxrefs[$pub_id]->doi))
	{
		if ($hasLinks || $hasPDF || $hasURL)	echo '   ';
		$d = $nonxrefs[$pub_id]->doi;
		echo ' <a class="aigaion_enlace" href="http://dx.doi.org/'.strval($d).'" target="_blank"> <img src="'.$pathaigaion.'/export_css/doi_icon.gif" border="0" alt="doi"></a>';
	}
	// 6) End:
	echo $endpubli.$endline;

}

/*=======================================================================================================*/

// For each publication type, make a list of the pub_id's included in that category:
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
				$myPubsIndex[$pubtype][$pub_id]=$pub_id;  // Is there something like std::vector<> in PHP, or only std::map<>???
		}
	}
}

//Generate publication list By.....
if (strcmp($sort,"year") == 0)
{
	$yearold=0;
	foreach($nonxrefs as $pub_id=>$publication)
	{
		if ( intval($nonxrefs[$pub_id]->year) >= intval($maxyeartopublish) )
		{
			$pubtype = $nonxrefs[$pub_id]->pub_type;
			myPrintNewElement($nonxrefs,$pub_id,$pathaigaion,$withlinks,$pubtype);
		}
	}
}
else
{
	$begindivtype = '<div class="aigaion_divtype">';
	$beginline_no_bullet = '<li class="aigaion_nobulletline">'."\n";
	$enddivtype = "</div>\n";
	$endline = "</li>\n";
	foreach($typenames as $pubtypforcmp=>$pubtype)
	{
		$pubscount = count($myPubsIndex[$pubtype]);
		if ($pubscount==0)
			continue;	// Skip: we don't have pubs of this type.

		// Header of this pub. type:
		echo $beginline_no_bullet.$begindivtype."<h2>";
		echo "$pubtype (".strval($pubscount).")"."</h2>".$enddivtype.$endline;

		global $yearold;
		$yearold=0;
		foreach($myPubsIndex[$pubtype] as $pub_id)
		{
			myPrintNewElement($nonxrefs,$pub_id,$pathaigaion,$withlinks,$pubtype);
		}
		flush();
	}
}

echo "\n</ul>";
echo "</body>\n</html>";

?>
