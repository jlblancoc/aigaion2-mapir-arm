<?php

/*
	Page for exporting aigaion data to other sites.

	Input data (get):

	'mode' => 'bytopic', 'byauthor', 'byid'
	'idparm' => id of the topic or of the author
	['withlinks'] => '1' if we want links in each publication to the aigaion site; '0' (default) if not
	'css' => style sheet file, that must be in /_utils/aigaion/export_css/
	['filtertype'] => 'none' (default), the name of the type of publication to filter 'Article', 'Book', etc.
	['formattype'] => 'mapir_formatted_list' (default) standard list of publications, 'mapir_formatted_image_list' add a small image for each publication
	['orderby'] => 'year' (default), 'type' (journals, conferences, etc)
	['maxyearsfromnow'] => A limit of years to list only recent publications
	['iframe'] => '0' (default) if want the result in a HTML page; a number of pixels to indicate the height of the IFRAME to contain the result
	
*/

	function parmexists($n)
	{
		//echo 'parmexists';
		//echo $_GET[$n];
		if (!isset($_GET[$n])) return(FALSE);
		if (strlen($_GET[$n])<=0) return(FALSE);
		return(TRUE);
	}

	function withiframe()
	{
		if ((parmexists('iframe'))&&(intval($_GET['iframe'])>0)) return(TRUE);
		return(FALSE);
	}

	function errorandend($e)
	{
		if (!withiframe()) echo '<html><body>';
		echo 'Error in the aigaion exporting URL: '.$e;
		if (!withiframe()) echo '</body></html>';
		exit(0);
	}

	
	if (!parmexists('mode')) errorandend('MODE not set');
	if (!parmexists('idparm')) errorandend('ID not set');
	if (!parmexists('css')) errorandend('CSS file not specified');
	
	if (strcmp($_GET['mode'],'bytopic')==0) $mode='bytopic';
	else if (strcmp($_GET['mode'],'byauthor')==0) $mode='byauthor';
	else if (strcmp($_GET['mode'],'byid')==0) $mode='byid';
	else if (strcmp($_GET['mode'],'all')==0) $mode='all';
	else errorandend('MODE invalid');

	if (intval($_GET['idparm'])<=0) errorandend('ID invalid');
	$id=$_GET['idparm'];

	if (parmexists('withlinks'))
	{
		if (intval($_GET['withlinks'])==1) $withlinks='1';
		else $withlinks='0';
	}
	else $withlinks='0';

	$css=$_GET['css'];

	//Filter Type
	if (parmexists('filtertype'))
		$filtertype = $_GET['filtertype'];	
	else $filtertype='none';
	
	// Format Type
	if (parmexists('formattype'))
		$formattype = $_GET['formattype'];
	else $formattype='mapir_formatted_list';
	
	// OrderBy
	if (parmexists('orderby'))
		$orderby = $_GET['orderby'];
	else $orderby='type';
	
	// maxyearsfromnow
	if (parmexists('maxyearsfromnow'))
		$maxyearsfromnow = $_GET['maxyearsfromnow'];
	else $maxyearsfromnow='none';
	
	//$urlcall='http://mapir.isa.uma.es/_utils/aigaion/index.php/export/'.$mode.'/'.$id.'/'.$withlinks.'/'.$css;
	$urlcall='http://mapir.isa.uma.es/mapirpubsite/index.php/export/'.$mode.'/'.$id.'/'.$withlinks.'/'.$css.'/'.$filtertype.'/'.$formattype.'/'.$orderby.'/'.$maxyearsfromnow;
?>

<?php if (withiframe()): ?>
	<iframe src="<?php echo $urlcall; ?>" width="100%" frameborder="0" height="<?php echo $_GET['iframe']; ?>">
		If you are seeing this line of text, then your browser is incapable of showing the list of publications. You can list them <a href="<?php echo $urlcall; ?>" target="_blank">here</a> instead.
	</iframe>
<?php else: ?>
	<html>
	<head>
	<meta http-equiv="refresh" content="0;url=<?php echo $urlcall; ?>">
	</head>
	<body>
		Loading list of publications...
	</body>
	</html>
<?php endif; ?>
