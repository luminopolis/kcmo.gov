<?php
/*
Plugin Name: URI COMMAND
Plugin URI: 
Description: 
Author: 
Version: 0.0.5
Author URI: 
*/

define( 'URICOMMAND_VERSION', '0.0.5' );

// no trailing slash
if( !defined('URICOMMAND_PLUGIN_DIR') )
	define( 'URICOMMAND_PLUGIN_DIR', dirname(__FILE__) );
	
if( !defined('URICOMMAND_PLUGIN_URL') )
	define( 'URICOMMAND_PLUGIN_URL', plugins_url('', __FILE__) );
	
if( is_admin() )
	require URICOMMAND_PLUGIN_DIR.'/admin.php';

// testing only
function dynamic_test( $post ){
	// wp://dynamic_test/post/?category=$term&blog_title=$blog_title&secret_of_everything=42&json_data=[{"blog_id":"5"},{"dynamic_blog_id":"$blog_id"},{"$blog_id":"blog_id"}]&devo=potato&get_array[]=1&get_array[color]=blue&get_array[5] = five&get_array[$blog_id]=blog_id&get_array[blog_id]=$blog_id&get_array[post]=$post&pagename=$pagename&$pagename=$pagename
	
	if( $post )
		return get_permalink( $post->ID );
}

function dynamic_test_label( $post ){
	$permalink =  get_the_title( $post->ID );
	
	return $permalink;
}

/*
*	parse the uri and return permalink
*	attached to 'clean_url' filter
*	@param string
*	@param string
*	@param string
*	@return string
*/
function dynamic_nav_menu_clean_url( $good_protocol_url, $original_url, $_context = '' ){
	if( $_context != 'display' || strpos($original_url, 'wp://') !== 0 )
		return $good_protocol_url; 
		
	$good_protocol_url = dynamic_nav_parse( $original_url );
	
	return $good_protocol_url;
}
add_filter( 'clean_url', 'dynamic_nav_menu_clean_url', 10, 3 );

/*
*
*/
function dynamic_nav_menu_esc_html( $safe_text, $text ){
	if( strpos($text, 'wp://') !== 0 )
		return $safe_text; 
	
	$good_protocol_url = dynamic_nav_parse( $text );
	
	return $good_protocol_url;
}
add_filter( 'esc_html', 'dynamic_nav_menu_esc_html', 10, 2 );

/*
*
*/
function dynamic_nav_menu_the_title( $title, $post_id ){
	if( strpos($title, 'wp://') !== 0 )
		return $title; 
	
	$good_protocol_url = dynamic_nav_parse( $title );
	
	return $good_protocol_url;
}
add_filter( 'the_title', 'dynamic_nav_menu_the_title', 10, 2 );

/*
*
*/
function dynamic_nav_parse( $original_url ){
	$parsed = parse_url( $original_url );
	$function = $parsed['host'];
	
	// @TODO make an option whehter to show wp:// in html, maybe for dev?
	if( !is_callable($function) )
		return 'http://fail/';
	
	// this could all change
	
	// currently path is being used
	$path = isset( $parsed['path'] ) ? array_filter( explode('/', $parsed['path']) ) : array();
	isset( $parsed['query'] ) ? parse_str( $parsed['query'], $query ) : $query = array();
	
	// parse query variables into function arguments
	$query = dynamic_nav_parse_r( $query, $path );
	
	$good_protocol_url = call_user_func_array( $function, $query );
	return $good_protocol_url;
}

/*
*	recursive function that checks for dynamic variables in url query
*	@param
*	@return
*/
function dynamic_nav_parse_r( $mixed ){
	if( is_array($mixed) || is_object($mixed) ){
		$parsed = array();
		foreach( $mixed as $k=>$v ){
			$key = dynamic_nav_parse_r( $k );
			$val = dynamic_nav_parse_r( $v );
			
			// @TODO check that key is not array
			$parsed[$key] = $val;
		}
	} elseif( is_string($mixed) && $json = json_decode($mixed) ){
		$parsed = dynamic_nav_parse_r( $json );
	} elseif( is_string($mixed) && (strpos($mixed, '$') === 0) && ($index = substr($mixed, 1)) && isset($GLOBALS[$index]) ){
		$parsed = $GLOBALS[$index];
	}  else {
		$parsed = $mixed;
	}
		
	return $parsed;
}

/*
*	attached to `kses_allowed_protocols` filter
*	@param array allowed protocols
*	@return array
*/
function dynamic_nav_menu_protocols( $protocols ){
	$protocols[] = 'wp';
	
	return $protocols;
}
add_filter( 'kses_allowed_protocols', 'dynamic_nav_menu_protocols' );
