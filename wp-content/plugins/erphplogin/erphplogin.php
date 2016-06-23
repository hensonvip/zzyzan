<?php 
/*
Plugin Name: Erphplogin
Plugin URI: http://www.22vd.com
Description: 连接腾讯QQ与新浪微博登录。
Version: 1.0.4
Author: 创客源码
Author URI: http://www.22vd.com
*/

global $wpdb;
define("erphplogin",plugin_dir_url( __FILE__ ));
if(!isset($_SESSION))
  session_start();
add_action('admin_menu', 'erphplogin_menu');
function erphplogin_menu() {
	if (function_exists('add_menu_page')) {
		add_menu_page('Erphplogin', 'Erphplogin', 'administrator', 'erphplogin/erphp.php', '','');
	}
}
include('inc/mobantu.php');
include('inc/show.php');
include('inc/qq.php');
include('inc/sina.php');
?>
