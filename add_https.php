<?php
/*
  Plugin Name: Add httpS
  Description: Заменяет http на https по умному. Делает редирект с http на https. Меняет в option "siteurl", "home" http на https. В functions.php активной темы менять http на https.
  Author: Andrey Morkovin
  Version: 3.6
 */

add_action( 'init', 'https_morkovin' );
function https_morkovin() {
	if ( !is_ssl() ) {
		$https_url = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		wp_redirect( $https_url, 301 );
		exit;
	}

	$siteurl = get_option('siteurl');
	$home = get_option('home');

	if ( strpos($siteurl, 'ttp://') ) {
		$siteurl = str_replace('ttp://', 'ttps://', $siteurl);
		update_option( 'siteurl', $siteurl );
	}
	if ( strpos($home, 'ttp://') ) {
		$home = str_replace('ttp://', 'ttps://', $home);
		update_option( 'home', $home );
	}

	if ( !get_option ('morkovin_functions_replace_http') ) {
		$function_path = get_template_directory().'/functions.php';
		$file = file_get_contents($function_path);
		$file = str_replace('http://', 'https://', $file);
		file_put_contents($function_path, $file);
		update_option( 'morkovin_functions_replace_http', 1 );
	}
}

function add_https_buffer_callback($buffer) {
	if ( is_admin() ) {
		return $buffer;
	}

	if ( !strpos( $_SERVER['REQUEST_URI'], '.xml' ) ) {		
		$domen_name = $_SERVER['HTTP_HOST'];
		if ( strpos($_SERVER['HTTP_HOST'], 'www') !== false ) {
			$domen_name = str_replace('www.', '', $domen_name);
		}

		$buffer = str_replace('href="http://'.$domen_name, 'href="https://'.$domen_name, $buffer);
		$buffer = str_replace('href="http://www.'.$domen_name, 'href="https://www.'.$domen_name, $buffer);

		$buffer = str_replace('src="http://', 'src="//', $buffer);
		$buffer = str_replace('action="http://', 'action="//', $buffer);
		$buffer = str_replace('data="http://', 'data="//', $buffer);
    	$buffer = str_replace('httpss://', '//', $buffer);
	}
    return $buffer; 
}

function morkovin_add_https_buffer_start() { ob_start("add_https_buffer_callback"); } 
function morkovin_add_https_buffer_end() { ob_end_flush(); }

add_action('after_setup_theme', 'morkovin_add_https_buffer_start');
add_action('shutdown', 'morkovin_add_https_buffer_end');

register_deactivation_hook( __FILE__, 'morkovin_add_https_uninstall');
function morkovin_add_https_uninstall() {
	$siteurl = get_option('siteurl');
	$home = get_option('home');

	if ( strpos($siteurl, 'ttps://') ) {
		$siteurl = str_replace('ttps://', 'ttp://', $siteurl);
		update_option( 'siteurl', $siteurl );
	}
	if ( strpos($home, 'ttps://') ) {
		$home = str_replace('ttps://', 'ttp://', $home);
		update_option( 'home', $home );
	}

	$function_path = get_template_directory().'/functions.php';
	$file = file_get_contents($function_path);
	$file = str_replace('https://', 'http://', $file);
	file_put_contents($function_path, $file);
	update_option( 'morkovin_functions_replace_http', false );
}