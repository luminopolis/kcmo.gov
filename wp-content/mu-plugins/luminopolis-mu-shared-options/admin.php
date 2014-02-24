<?php

namespace mu_shared_options;

/*
*	registers settings menu link in admin
*	attached to `admin_menu` action
*/
function admin_menu(){
	add_options_page( __('Mulisite Shared Options', 'mu-shared-options'), __('Mulisite Shared Options', 'mu-shared-options'), 
					  'manage_options', 'luminopolis-mu-shared-settings', __NAMESPACE__.'\admin_page_render' );
}
add_action( 'admin_menu', __NAMESPACE__.'\admin_menu' );

/*
*	callback for add_options_page() to render settings screen
*/
function admin_page_render(){	
	$options = get_site_option( 'luminopolis-mu-shared-options' );
	$options = (array) json_decode( $options );
	
	sort( $options );
	
	$options = array_map( __NAMESPACE__.'\admin_format', $options );
	$options = implode( '', $options );
	
	$vars = array(
		'shared_options' => trim( $options ),
		'success' => admin_messages( 'success' )
	);
	
	if( !defined('LUMINOPOLIS_MU_SHARED_PLUGINS_URL') )
		define( 'LUMINOPOLIS_MU_SHARED_PLUGINS_URL', plugins_url('', __FILE__) );
		
	wp_register_style( 'kcmo-shared-settings', LUMINOPOLIS_MU_SHARED_PLUGINS_URL.'/public/admin/options-general.css' );
	wp_enqueue_style( 'kcmo-shared-settings' );
	
	echo render( 'views/admin/options-general', $vars );
}

/*
*	alphabetize grouping first letter or first word on new line, then by comma
*	@param string
*	@return string
*/
function admin_format( $r ){
	static $alpha = '';
	
	$sort = preg_split( '/[_-]+/', $r );
	$sort = count( $sort ) > 1 ? $sort[0] : $r[0];
	
	if( $alpha != $sort ){
		$alpha = $sort;
		$r = "\n\n".$r;
	} else {
		$r = ", ".$r;
	}
	
	return $r;
}

/*
*
*	@param string
*	@param string
*	@return array
*/
function admin_messages( $group, $message = NULL ){
	static $storage;
	
	if( is_null($storage) )
		$storage = array();
		
	if( !isset($storage[$group]) )
		$storage[$group] = array();
		
	if( !is_null($message) )
		$storage[$group][] = $message;
		
	return $storage[$group];
}

/*
*	update settings from form data
*	attached to `load-settings_page_gcal-settings` action
*/
function admin_update(){
	if( isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'luminopolis-mu-shared-options') ){
		$options = $_POST['mu-shared-options'];
		
		$options = str_replace( "\n", ', ', $options );
		$options = explode( ',', $options );
		$options = array_unique( array_filter(array_map('trim', $options) ));
		
		// serialize is overkill here
		$options = json_encode( $options );
		
		update_site_option( 'luminopolis-mu-shared-options', $options );
		
		admin_messages( 'success', __('Settings saved.', 'mu-shared-options') );
	}
}
add_action( 'load-settings_page_luminopolis-mu-shared-settings', __NAMESPACE__.'\admin_update' );

/*
*	renders a php template file with variables
*	currently only used in admin
*/
function render( $template, $vars = array() ){
	extract( (array) $vars, EXTR_SKIP );
	require dirname( __FILE__ ).'/'.$template.'.php';
}