<?php
/*
Plugin Name: KCMO Sortable Front-end 
Plugin URI: 
Description: 
Author: Luminopolis / Eric Eaglstun
Version: 1.0
Author URI: 
*/

if( !defined('LUMINOPOLIS_REQUEST_HELPER') )
	return;
	
if( is_admin() )
	require dirname( __FILE__ ).'/admin.php';

if( !defined('LUMINOPOLIS_SORTABLE_PLUGINS_URL') )
	define( 'LUMINOPOLIS_SORTABLE_PLUGINS_URL', plugins_url('', __FILE__) );
	
/*
*	sets up admin url for global javascript
*	
*/
function kcmo_frontend_order_ajaxurl(){
	echo '<script type="text/javascript">
			var ajaxurl = "'.admin_url( 'admin-ajax.php' ).'";
		  </script>';
}

/*
*	queues scripts in frontend for admins that can edit posts
*	attached to `init` action
*/
function kcmo_frontend_order_scripts(){
	if( !current_user_can('edit_posts') )
		return;
	
	add_action( 'wp_head', 'kcmo_frontend_order_ajaxurl' );

	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'kcmo-order-links', LUMINOPOLIS_SORTABLE_PLUGINS_URL.'/public/index.js', 
					   array('jquery-ui-sortable'), NULL, TRUE );
}
add_action( 'init', 'kcmo_frontend_order_scripts' );