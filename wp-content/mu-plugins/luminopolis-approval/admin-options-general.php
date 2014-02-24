<?php

namespace luminopolis_approval;

/*
*
*	@param string
*	@return array
*/
function admin_messages( $message = NULL ){
	static $display;
	
	if( !$display )
		$display = array();
		
	if( $message )
		$display[] = $message;
		
	return $display;
}

/*
*
*	@return array
*/
function admin_default_misc(){
	$options = array(
		'enabled', 'log_mail', 'rejection_email', 'rejection_from', 'send_from'
	);
	
	return array_combine( $options, array_fill(0, count($options), NULL) );
}

/*
*	render the options page
*
*/
function admin_page(){
	$vars = array();
	
	$defaults = array(
		'post_types' => array(),
		'caps' => array(),
		'misc' => array(),
	);
	
	// get all the post types
	$post_types = array_keys( get_post_types(array(
		'public' => TRUE
	), 'names') );
	
	$defaults['post_types'] = array_combine( $post_types, array_fill(0, count($post_types), NULL) );
	
	$caps = array();
	
	// get all the capabilities
	global $wp_roles;
	foreach( $wp_roles->roles as $role ){
		$caps = array_merge( $caps, array_keys($role['capabilities']) );
	}
	
	$caps = array_unique( $caps );
	sort( $caps );
	
	$defaults['caps'] = array_combine( $caps, array_fill(0, count($caps), NULL) );
	
	// get the misc options
	$defaults['misc'] = admin_default_misc();
	
	$selected = (array) get_option('luminopolis_approval_options' );
	$selected = array_replace_recursive( $defaults, $selected );
	
	$vars['options'] = $selected;
	
	// success message
	$vars['message'] = implode( '', admin_messages() );
	
	wp_register_style( 'luminopolis-approval', LUMINOPOLIS_APPROVAL_PLUGINS_URL.'/public/admin/options-general.css' );
	wp_enqueue_style( 'luminopolis-approval' );
	
	echo approval_render( 'admin/options-general', $vars );
}

/*
*	update settings from form data
*	attached to `load-settings_page_approval-settings` action
*/
function admin_update(){
	if( isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'luminopolis-approval-settings') ){
		$options = array(
			'post_types' => array(), 'caps' => array(), 'misc' => array()
		);
		
		foreach( $options as $option => $val ){
			if( isset($_POST[$option]) )
				$options[$option] = ( $_POST[$option] );
		}
		
		admin_messages( '<div class="updated">Settings saved.</div>' );
		
		update_option( 'luminopolis_approval_options', $options );
	}
}
add_action( 'load-settings_page_approval-settings', __NAMESPACE__.'\admin_update' );