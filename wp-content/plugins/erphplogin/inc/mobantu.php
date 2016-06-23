<?php 
/*
author: mobantu.com
date: 2014.05.22
*/
$var = $wpdb->query("SELECT qqid FROM $wpdb->users");
if(!$var){
	$wpdb->query("ALTER TABLE $wpdb->users ADD qqid varchar(50)");
}
$var1 = $wpdb->query("SELECT sinaid FROM $wpdb->users");
if(!$var1){
 $wpdb->query("ALTER TABLE $wpdb->users ADD sinaid varchar(50)");
}
function erphplogin_selfURL(){  
    $pageURL = 'http';
    $pageURL .= (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on")?"s":"";
    $pageURL .= "://";
    $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    return $pageURL;      
}
function erphplogin_post($url, $data) {
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, TRUE );
	curl_setopt ( $ch, CURLOPT_POST, TRUE );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	$ret = curl_exec ( $ch );
	curl_close ( $ch );
	return $ret;
}
function get_url_contents($url) {
	//if (ini_get ( "allow_url_fopen" ) == "1")
		//return file_get_contents ( $url );
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, TRUE );
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	curl_setopt ( $ch, CURLOPT_URL, $url );
	$result = curl_exec ( $ch );
	curl_close ( $ch );
	return $result;
}

function erphploginFormButton()
{
    echo '<div style="display:block">
    <a href="'.constant("erphplogin").'auth/qq.php?erphploginurl='.get_option('erphplogin_url').'"><img src="'.constant("erphplogin").'img/login_qq.png"></a>
    <a href="'.constant("erphplogin").'auth/sina.php?erphploginurl='.get_option('erphplogin_url').'"><img src="'.constant("erphplogin").'img/login_sina.png"></a></div>';
}
add_filter('login_form', 'erphploginFormButton');


?>