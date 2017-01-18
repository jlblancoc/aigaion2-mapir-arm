<?php

// Recognized data fields:
$USERFIELDS_DEFINITIONS = [ 
    [ 'name' => 'img_url', 'desc' => 'URL a imagen en miniatura' ],
    [ 'name' => 'rank_indexname',  'desc' => 'Nombre del índice (Vacío, defecto=`JCR`)' ],
    [ 'name' => 'rank_pos_in_category',  'desc' => 'Posición de la revista en la categoría' ],
    [ 'name' => 'rank_num_in_category',  'desc' => 'Número de revistas en la categoría' ],
    [ 'name' => 'rank_cat_name' , 'desc' => 'Nombre de la categoría' ],
    [ 'name' => 'impact_factor' , 'desc' => 'Impact factor de la revista' ]
 ];

/** Given a publication object, returns a map key=>value for the keys in $USERFIELDS_DEFINITIONS */
function my_parse_pub_userfields(&$pub)
{
    $userfields = $pub->userfields;
    // backwards compatibility: if userfields is an URI, assume it's an image link:
    if (substr($userfields,0,4)=="http") {
        $userfields = "img_url=".$userfields;
    }

    preg_match_all("/([^,= ]+)=([^,= ]+)/",$userfields , $r); // See: http://stackoverflow.com/a/4924004/1631514
    
    // URI decode
    foreach ($r[2] as $i => $v) {
        $v = urldecode($v);
        $r[2][$i] = $v;
    }
    
    $userfields_map = array_combine($r[1], $r[2]);
    
    return $userfields_map;
}

/** Returns a map with quartile & tercile as: 'Q'-> int , 'T'-> int , or an 
 * empty array if not applicable or no DB data. 
 */
function pub_get_ranking(&$userfields_map)
{
    if (isset($userfields_map['rank_pos_in_category']) && isset($userfields_map['rank_num_in_category']))
    {
        // We can compute journal ranking:
        $rank_pos = $userfields_map['rank_pos_in_category'];
        $rank_count = $userfields_map['rank_num_in_category'];

        $ranking_ratio = floatval($rank_pos)/floatval($rank_count);

        $quartil = ceil($ranking_ratio/0.25);
        $tercil  = ceil($ranking_ratio/0.333333);

        $rank_cat = isset($userfields_map['rank_cat_name']) ? ' in '.$userfields_map['rank_cat_name'] : '';
        $ranking_name = isset($userfields_map['rank_indexname']) ? ' ('.$userfields_map['rank_indexname'].')' : ' (JCR)';
        
        return array(
            'Q' => intval($quartil),
            'T' => intval($tercil),
            'rank_pos'=>$rank_pos,
            'rank_count'=>$rank_count,
            'rank_cat' => $rank_cat,
            'ranking_name' => $ranking_name
        );
    }
    else {
        return array();
    }    
}

/** Given a publication object, echo its ranking as HTML, if it is an article, 
 * and the DB is populated via `userfields` fields. */
function my_print_pub_ranking(&$pub)
{
    if ($pub->pub_type != "Article") {
        return;
    }

    $userfields_map = my_parse_pub_userfields($pub);
    $ranking_data = pub_get_ranking($userfields_map);
    
    if (empty($ranking_data)) {
        return;
    }

    $mouse_over_text = 'Ranking '.$ranking_data['rank_pos'].'/'.$ranking_data['rank_count'].$ranking_data['rank_cat'].$ranking_data['ranking_name'];

    echo ', <span style="border-bottom: 1px dotted #000;" title="'.$mouse_over_text.'">'.
            '(Q'.$ranking_data['Q'].
            ',&nbsp;T'.$ranking_data['T'].
            ')</span>';
}

?>
