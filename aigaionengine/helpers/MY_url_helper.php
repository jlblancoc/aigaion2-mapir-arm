<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
Aigaion: Extension of the prep_url function...
*/


/**
 * Prep URL
 *
 * Simply adds the http:// part if missing
 *
 * @access	public
 * @param	string	the URL
 * @return	string
 */
function prep_url($str = '')
{
//	if ($str == 'http://' OR $str == '')

	//cga-> If str already contains 'http://' do nothing.
	if (stripos($str,'http://') !== FALSE)
	{
		return $str;
	}
	
	
	//mod by PDM, for Aigaion 2.0
	if (stristr('^[a-z]+://', $str) == FALSE)
	{
		$str = 'http://'.$str;
	}
	
	return $str;
}
	
?>