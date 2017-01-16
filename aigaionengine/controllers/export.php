<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Export extends Controller {

	function Export()
	{
		parent::Controller();	
	}
	
	/** Pass control to the export/all/ */
	function index()
	{
		$this->all();
	}
	

    /** 
    export/all
    
    Export all (accessible) entries in the database
    
	Fails with error message when one of:
	    never
	    
	Parameters passed via URL segments:
	    3rd: type (bibtex|ris)
	         
    Returns:
        A clean text page with exported publications
    */
    function all() {
	    $type = $this->uri->segment(3,'');
	    if (!in_array($type,array('bibtex','ris','formatted'))) {
            $header ['title']       = "Select export format";
            $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js');
            
            //get output
            $output  = $this->load->view('header',        $header,  true);
            $output .= $this->load->view('export/chooseformat',  array('header'=>'Export all publications','exportCommand'=>'export/all/'), true);
            $output .= $this->load->view('footer',        '',       true);
            
            //set output
            $this->output->set_output($output);
            return;
	    }
	    $exportdata = array();
        $userlogin = getUserLogin();
        //for export, bibtex should NOT merge crossrefs; ris and formatted SHOULD merge crossrefs
        switch ($type) {
            case 'bibtex':
                $this->publication_db->suppressMerge = True;
                break;
            case 'ris':
                $this->publication_db->enforceMerge = True;
                break;
            case 'formatted':
                $this->publication_db->enforceMerge = True;
                $exportdata['format'] = $this->input->post('format');
                $exportdata['sort'] = $this->input->post('sort');
                $exportdata['style'] = $this->input->post('style');
                break;
            default:
                break;
                
        }
        #collect to-be-exported publications 
        $publicationMap = $this->publication_db->getAllPublicationsAsMap();
        #split into publications and crossreffed publications, adding crossreffed publications as needed
        $splitpubs = $this->publication_db->resolveXref($publicationMap,false);
        $pubs = $splitpubs[0];
        $xrefpubs = $splitpubs[1];
        
        #send to right export view
        $exportdata['nonxrefs'] = $pubs;
        $exportdata['xrefs']    = $xrefpubs;
        $exportdata['header']   = 'All publications';

        $output = $this->load->view('export/'.$type, $exportdata, True);

        //set output
        $this->output->set_output($output);        

    }    
    /** 
    export/topic
    
    Export all (accessible) entries from one topic
    
	Fails with error message when one of:
	    non existing topic_id requested
	    
	Parameters passed via URL segments:
	    3rd: topic_id
	    4rth: type (bibtex|ris)
	         
    Returns:
        A clean text page with exported publications
    */
    function topic() {
	    $topic_id = $this->uri->segment(3,-1);
	    $type = $this->uri->segment(4,'');
	    $config = array();
	    $topic = $this->topic_db->getByID($topic_id,$config);
	    if ($topic==null) {
	        appendErrorMessage('Export requested for non existing topic<br/>');
	        redirect ('');
	    }
	    if (!in_array($type,array('bibtex','ris','formatted'))) {
            $header ['title']       = "Select export format";
            $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js');
            
            //get output
            $output  = $this->load->view('header',        $header,  true);
            $output .= $this->load->view('export/chooseformat',  array('header'=>'Export all for topic '.$topic->name,'exportCommand'=>'export/topic/'.$topic->topic_id.'/'), true);
            $output .= $this->load->view('footer',        '',       true);
            
            //set output
            $this->output->set_output($output);
            return;
	    }
	    $exportdata = array();
        $userlogin = getUserLogin();
        //for export, bibtex should NOT merge crossrefs; ris SHOULD merge crossrefs
        switch ($type) {
            case 'bibtex':
                $this->publication_db->suppressMerge = True;
                break;
            case 'ris':
                $this->publication_db->enforceMerge = True;
                break;
            case 'formatted':
                $this->publication_db->enforceMerge = True;
                $exportdata['format'] = $this->input->post('format');
                $exportdata['sort'] = $this->input->post('sort');
                $exportdata['style'] = $this->input->post('style');
                break;
            default:
                break;
                
        }

        #collect to-be-exported publications 
        $publicationMap = $this->publication_db->getForTopicAsMap($topic->topic_id);
        #split into publications and crossreffed publications, adding crossreffed publications as needed
        $splitpubs = $this->publication_db->resolveXref($publicationMap,false);
        $pubs = $splitpubs[0];
        $xrefpubs = $splitpubs[1];
        
        #send to right export view
        $exportdata['nonxrefs'] = $pubs;
        $exportdata['xrefs']    = $xrefpubs;
        $exportdata['header']   = 'All publications for topic "'.$topic->name.'"';
        $output = $this->load->view('export/'.$type, $exportdata, True);

        //set output
        $this->output->set_output($output);        

    }        


    /** 	-------------------------   JAFMA 16/05/2008, JL 01/02/2010   ----------------------
    export/bytopic  -> exportacion completa por topic, ordenada por tipo y dentro por anio, y en formato IEEETRANS
    
    Export all (accessible) entries from one topic
    
	Fails with error message when one of:
	    non existing topic_id requested
	    
	Parameters passed via URL segments:
	    3rd: topic_id
		4th: 1 for showing links to individual publications, 0 for not.
		5th: css file (see aigaionengine/views/export/mapir_formatted_list.php)
		6th: type of the publications (a filter)
	    7th: format type (with or without images)
		8th: order by year or publication type
		9th: max year to list publications from now (recent publications)
    Returns:
        A clean text page with exported publications
    */
    function bytopic() {
	    $topic_id = $this->uri->segment(3,-1);
	    $withlinks = $this->uri->segment(4,'0');
	    $cssfile = $this->uri->segment(5,'');
		$filtertype = $this->uri->segment(6,'');		
		$type = $this->uri->segment(7,'mapir_formatted_list');		
	    $orderby = $this->uri->segment(8,'type');
		$maxyearsfromnow = $this->uri->segment(9,'none');
		
		if ($filtertype == 'none'){ $filtertype = ''; }
			    
	    $config = array();
	    $topic = $this->topic_db->getByID($topic_id,$config);
	    if ($topic==null) {
	        appendErrorMessage('Export requested for non existing topic<br/>');
	        redirect ('');
	    }
	    $exportdata = array();

            $this->publication_db->enforceMerge = True;
            $exportdata['format'] = 'html';        
            $exportdata['style'] = 'IEEETRANS';
            $exportdata['withlinks'] = $withlinks;
            $exportdata['cssfile'] = $cssfile;
            $exportdata['sort'] = $orderby;
            $exportdata['maxyearsfromnow'] = $maxyearsfromnow;
		
            #collect to-be-exported publications 
            $publicationMap = $this->publication_db->getForTopicAsOrderedMap($topic->topic_id,$exportdata['sort'],$filtertype);
            #split into publications and crossreffed publications, adding crossreffed publications as needed
            $splitpubs = $this->publication_db->resolveXref($publicationMap,false);
            $pubs = $splitpubs[0]; // array con un objeto publicacion (con campos iguales a los de un registro de la BBDD) por cada publicacion
            $xrefpubs = $splitpubs[1]; // Este siempre estara vacio
        
            #agrega el primer attachment, si hay
            foreach ($pubs as $pub_id=>$publication) 
            {
                $atts=$this->attachment_db->getAttachmentsForPublication($pub_id);
                if (count($atts) > 0) {
                $pubs[$pub_id]->firstattachment = $atts[0]->location;
            } else {
                $pubs[$pub_id]->firstattachment = '';
            }
        }

        #send to right export view
        $exportdata['nonxrefs'] = $pubs;
        $exportdata['xrefs']    = $xrefpubs;
        $exportdata['header']   = 'All publications for topic "'.$topic->name.'"';
        $output = $this->load->view('export/'.$type, $exportdata, True);

        //set output
        $this->output->set_output($output);        

    }         


    /** 	-------------------------   JAFMA 16/05/2008, JL 01/02/2010   ----------------------
    export/byauthor  -> exportacion completa por autor, ordenada por tipo y dentro por anio, y en formato IEEETRANS
    
    Export all (accessible) entries from one topic
    
	Fails with error message when one of:
	    non existing topic_id requested
	    
	Parameters passed via URL segments:
	    3rd: author_id
		4th: 1 for showing links to individual publications, 0 for not.
		5th: css file (see aigaionengine/views/export/mapir_formatted_list.php)
		6th: type of the publications (a filter)
	    7th: format type (with or without images)
		8th: order by year or publication type
		9th: max year to list publications from now (recent publications)
    Returns:
        A clean text page with exported publications
    */
    function byauthor() {
	    $author_id = $this->uri->segment(3,-1);
	    $withlinks = $this->uri->segment(4,'0');
	    $cssfile = $this->uri->segment(5,'');
		$filtertype = $this->uri->segment(6,'');
		$type = $this->uri->segment(7,'mapir_formatted_list');
		$orderby = $this->uri->segment(8,'type');
		$maxyearsfromnow = $this->uri->segment(9,'none');
	    
		if ($filtertype == 'none'){ $filtertype = ''; }
			
	    $author = $this->author_db->getByID($author_id);
	    if ($author==null) {
	        appendErrorMessage('Export requested for non existing author<br/>');
	        redirect ('');
	    }
	    $exportdata = array();

        $this->publication_db->enforceMerge = True;
        $exportdata['format'] = 'html';        
        $exportdata['style'] = 'IEEETRANS';
		$exportdata['withlinks'] = $withlinks;
		$exportdata['cssfile'] = $cssfile;
		$exportdata['sort'] = $orderby;
		$exportdata['maxyearsfromnow'] = $maxyearsfromnow;

        #collect to-be-exported publications 
        $publicationMap = $this->publication_db->getForAuthorAsOrderedMap($author->author_id,$exportdata['sort'],$filtertype);
        #split into publications and crossreffed publications, adding crossreffed publications as needed
        $splitpubs = $this->publication_db->resolveXref($publicationMap,false);
        $pubs = $splitpubs[0]; // array con un objeto publicacion (con campos iguales a los de un registro de la BBDD) por cada publicacion
        $xrefpubs = $splitpubs[1]; // Este siempre estara vacio
        
        #agrega el primer attachment, si hay
        foreach ($pubs as $pub_id=>$publication) 
        {
            $atts=$this->attachment_db->getAttachmentsForPublication($pub_id);
            if (count($atts) > 0) {
                $pubs[$pub_id]->firstattachment = $atts[0]->location;
            } else {
                $pubs[$pub_id]->firstattachment = '';
            }
        }

        #send to right export view
        $exportdata['nonxrefs'] = $pubs;
        $exportdata['xrefs']    = $xrefpubs;
        $exportdata['header']   = 'All publications for author '.$author->getName();
        $output = $this->load->view('export/'.$type, $exportdata, True);

        //set output
        $this->output->set_output($output);        

    }        

 

    /** 
    	-------------------------   JAFMA 28/05/2008, JL 01/02/2010, CGA 31/10/2012   ----------------------
    export/byid
    
    Export one publication
    
	Fails with error message when one of:
	    non existing pub_id requested
	    
	Parameters passed via URL segments:
	3rd: pub_id
	4th: 1 for showing links to individual publications, 0 for not.
	5th: css file (see aigaionengine/views/export/mapir_formatted_list.php)
	6th: format type	
    Returns:
        A clean text page with exported publications
    */
    function byid() {
	    $pub_id = $this->uri->segment(3,-1);
	    $withlinks = $this->uri->segment(4,'0');
	    $cssfile = $this->uri->segment(5,'');
	    $type = $this->uri->segment(6,'mapir_formatted_list');
		
		$exportdata = array();
                $this->publication_db->enforceMerge = True;
                $exportdata['format'] = 'html';
                $exportdata['sort'] = 'nothing';
                $exportdata['style'] = 'IEEETRANS';
	    $publication = $this->publication_db->getByID($pub_id);
	    if ($publication==null) {
	        appendErrorMessage('Export requested for non existing publication<br/>');
	        redirect ('');
	    }
      	$userlogin = getUserLogin();

        #collect to-be-exported publications 
        $publicationMap = array($publication->pub_id => $publication);
        #split into publications and crossreffed publications, adding crossreffed publications as needed
        $splitpubs = $this->publication_db->resolveXref($publicationMap,false);
        $pubs = $splitpubs[0];
        $xrefpubs = $splitpubs[1];
		#agrega el primer attachment, si hay
		foreach ($pubs as $pub_id=>$publication) 
		{
			$atts=$this->attachment_db->getAttachmentsForPublication($pub_id);
			if (count($atts)>0)	$pubs[$pub_id]->firstattachment=$atts[0]->location;
			else $pubs[$pub_id]->firstattachment='';
		}
        
        #send to right export view
        $exportdata['nonxrefs'] = $pubs;
        $exportdata['xrefs']    = $xrefpubs;
		$exportdata['withlinks'] = $withlinks;
		$exportdata['cssfile'] = $cssfile;

        $output = $this->load->view('export/'.$type, $exportdata, True);

        //set output
        $this->output->set_output($output);        

    }    


    /** 
    export/author
    
    Export all (accessible) entries from one author
    
	Fails with error message when one of:
	    non existing author_id requested
	    
	Parameters passed via URL segments:
	    3rd: author_id
	    4rth: type (bibtex|ris)
	         
    Returns:
        A clean text page with exported publications
    */
    function author() {
	    $author_id = $this->uri->segment(3,-1);
	    $type = $this->uri->segment(4,'');
	    $author = $this->author_db->getByID($author_id);
	    if ($author==null) {
	        appendErrorMessage('Export requested for non existing author<br/>');
	        redirect ('');
	    }
	    if (!in_array($type,array('bibtex','ris','formatted'))) {
            $header ['title']       = "Select export format";
            $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js');
            
            //get output
            $output  = $this->load->view('header',        $header,  true);
            $output .= $this->load->view('export/chooseformat',  array('header'=>'Export all for author '.$author->getName(),'exportCommand'=>'export/author/'.$author->author_id.'/'), true);
            $output .= $this->load->view('footer',        '',       true);
            
            //set output
            $this->output->set_output($output);
            return;
	    }
	    $exportdata = array();
        $userlogin = getUserLogin();
        //for export, bibtex should NOT merge crossrefs; ris SHOULD merge crossrefs
        switch ($type) {
            case 'bibtex':
                $this->publication_db->suppressMerge = True;
                break;
            case 'ris':
                $this->publication_db->enforceMerge = True;
                break;
            case 'formatted':
                $this->publication_db->enforceMerge = True;
                $exportdata['format'] = $this->input->post('format');
                $exportdata['sort'] = $this->input->post('sort');
                $exportdata['style'] = $this->input->post('style');
                break;
            default:
                break;
                
        }

        #collect to-be-exported publications 
        $publicationMap = $this->publication_db->getForAuthorAsMap($author->author_id);
        #split into publications and crossreffed publications, adding crossreffed publications as needed
        $splitpubs = $this->publication_db->resolveXref($publicationMap,false);
        $pubs = $splitpubs[0];
        $xrefpubs = $splitpubs[1];
        
        #send to right export view
        $exportdata['nonxrefs'] = $pubs;
        $exportdata['xrefs']    = $xrefpubs;
        $exportdata['header']   = 'All publications for '.$author->getName();

        $output = $this->load->view('export/'.$type, $exportdata, True);

        //set output
        $this->output->set_output($output);        

    }       
    /** 
    export/bookmarklist
    
    Export all (accessible) entries from the bookmarklist of this user
    
	Fails with error message when one of:
	    insufficient rights
	    
	Parameters passed via URL segments:
	    3rth: type (bibtex|ris)
	         
    Returns:
        A clean text page with exported publications
    */
    function bookmarklist() {
	    $type = $this->uri->segment(3,'');
	    if (!in_array($type,array('bibtex','ris','formatted'))) {
            $header ['title']       = "Select export format";
            $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js');
            
            //get output
            $output  = $this->load->view('header',        $header,  true);
            $output .= $this->load->view('export/chooseformat',  array('header'=>'Export all publications on bookmarklist','exportCommand'=>'export/bookmarklist/'), true);
            $output .= $this->load->view('footer',        '',       true);
            
            //set output
            $this->output->set_output($output);
            return;
	    }
	    $exportdata = array();
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist')) {
	        appendErrorMessage('Export: no bookmarklist rights<br/>');
	        redirect ('');
	    }
        //for export, bibtex should NOT merge crossrefs; ris SHOULD merge crossrefs
        switch ($type) {
            case 'bibtex':
                $this->publication_db->suppressMerge = True;
                break;
            case 'ris':
                $this->publication_db->enforceMerge = True;
                break;
            case 'formatted':
                $this->publication_db->enforceMerge = True;
                $exportdata['format'] = $this->input->post('format');
                $exportdata['sort'] = $this->input->post('sort');
                $exportdata['style'] = $this->input->post('style');
                break;
            default:
                break;
                
        }
	    
        #collect to-be-exported publications 
        $publicationMap = $this->publication_db->getForBookmarkListAsMap();
        #split into publications and crossreffed publications, adding crossreffed publications as needed
        $splitpubs = $this->publication_db->resolveXref($publicationMap,false);
        $pubs = $splitpubs[0];
        $xrefpubs = $splitpubs[1];
        
        #send to right export view
        $exportdata['nonxrefs'] = $pubs;
        $exportdata['xrefs']    = $xrefpubs;
        $exportdata['header']   = 'Exported from bookmarklist';

        $output = $this->load->view('export/'.$type, $exportdata, True);

        //set output
        $this->output->set_output($output);        

    }        
        
    /** 
    export/publication
    
    Export one publication
    
	Fails with error message when one of:
	    non existing pub_id requested
	    
	Parameters passed via URL segments:
	    3rd: pub_id
	    4rth: type (bibtex|ris)
	         
    Returns:
        A clean text page with exported publications
    */
    function publication() {
	    $pub_id = $this->uri->segment(3,-1);
	    $type = $this->uri->segment(4,'');
        //for export, bibtex should NOT merge crossrefs; ris SHOULD merge crossrefs
	    $exportdata = array();
        switch ($type) {
            case 'bibtex':
                $this->publication_db->suppressMerge = True;
                break;
            case 'ris':
                $this->publication_db->enforceMerge = True; //although the crossreferenced publications are STILL exported...
                break;
            case 'formatted':
                $this->publication_db->enforceMerge = True;
                $exportdata['format'] = $this->input->post('format');
                $exportdata['sort'] = $this->input->post('sort');
                $exportdata['style'] = $this->input->post('style');
                break;
            default:
                break;
                
        }
	    $publication = $this->publication_db->getByID($pub_id);
	    if ($publication==null) {
	        appendErrorMessage('Export requested for non existing publication<br/>');
	        redirect ('');
	    }
	    if (!in_array($type,array('bibtex','ris','formatted'))) {
            $header ['title']       = "Select export format";
            $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js');
            
            //get output
            $output  = $this->load->view('header',        $header,  true);
            $output .= $this->load->view('export/chooseformat',  array('header'=>'Export one publication','exportCommand'=>'export/publication/'.$publication->pub_id.'/'), true);
            $output .= $this->load->view('footer',        '',       true);
            
            //set output
            $this->output->set_output($output);
            return;
	    }
      $userlogin = getUserLogin();

        #collect to-be-exported publications 
        $publicationMap = array($publication->pub_id=>$publication);
        #split into publications and crossreffed publications, adding crossreffed publications as needed
        $splitpubs = $this->publication_db->resolveXref($publicationMap,false);
        $pubs = $splitpubs[0];
        $xrefpubs = $splitpubs[1];
        
        #send to right export view
        $exportdata['nonxrefs'] = $pubs;
        $exportdata['xrefs']    = $xrefpubs;

        $output = $this->load->view('export/'.$type, $exportdata, True);

        //set output
        $this->output->set_output($output);        

    }    
}
?>
