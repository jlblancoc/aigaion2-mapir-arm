<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Compatibility layer for the removed ext/mysql functions.
| -------------------------------------------------------------------
|
|   The ext/mysql functions do not exist since PHP 7, but some parts of the
|   code still call them. They are emulated here on top of the mysqli
|   connection that CodeIgniter already holds.
*/

if (!function_exists('mysql_error')) {
    function mysql_error() {
        $CI = &get_instance();
        if (!is_object($CI->db->conn_id)) {
            return '';
        }
        return mysqli_error($CI->db->conn_id);
    }
}

if (!function_exists('mysql_query')) {
    function mysql_query($query) {
        $CI = &get_instance();
        return mysqli_query($CI->db->conn_id, $query);
    }
}

if (!function_exists('mysql_num_rows')) {
    function mysql_num_rows($result) {
        return mysqli_num_rows($result);
    }
}

if (!function_exists('mysql_fetch_array')) {
    function mysql_fetch_array($result) {
        return mysqli_fetch_array($result);
    }
}

if (!function_exists('mysql_real_escape_string')) {
    function mysql_real_escape_string($string) {
        $CI = &get_instance();
        return mysqli_real_escape_string($CI->db->conn_id, $string);
    }
}
