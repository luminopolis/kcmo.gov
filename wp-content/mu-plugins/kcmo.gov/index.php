<?php
/*
Plugin Name: KCMO.gov Must Use
Plugin URI: 
Description: Core functionality for KCMO.gov
Author: Luminopolis / Eric Eaglstun
Version: 1.0
Author URI: 
*/

// testing git hooks
		
define( 'KCMO_MU_VERSION', '1.0.4' );

$current_dir = dirname( __FILE__ );

require $current_dir.'/functions.php';
require $current_dir.'/menu.php';
require $current_dir.'/plugins.php';
require $current_dir.'/posts.php';
require $current_dir.'/request.php';
require $current_dir.'/sql.php';
require $current_dir.'/view.php';
require $current_dir.'/widgets.php';

// debugging
if( defined('ENVIRONMENT') && in_array(ENVIRONMENT, array('DEV', 'STAGE')) )
	require $current_dir.'/dev.php';
	
$current_version = get_site_option( 'kcmo_mu_version' );

// to force a re-upgrade
//$current_version = '1.0.3';
if( version_compare($current_version, KCMO_MU_VERSION, '<') )
	require $current_dir.'/upgrade.php';	
	
unset( $current_version );

// db upgrade
if( 0 && isset($_GET['migrate']) && file_exists($current_dir.'/migrate.php') )
	require $current_dir.'/migrate.php';

unset( $current_dir );

/*	
*	register main menu
*	attached to `init` action
*/
function kcmo_register_main_menu(){
	register_nav_menu( 'header-menu', 'Header Menu' );
}
add_action( 'init', 'kcmo_register_main_menu' );

/*
*	remove default bannerspace post type and replace with one limited to edit&above
*/
function kcmo_register_bannerspace_post_type(){
	remove_action( 'init', 'create_bannerspace_post_type' );
	//return;
	
	register_post_type( 'bannerspace_post',
		array(
			'labels' => array( 
				'name' => __( 'Banner Slides' ), 	
				'singular_name' => __( 'slide' )
		),
		'public' => true,
		'exclude_from_search' => true,
		'hierarchical' => true,
		'capability_type' => 'editor',
		'map_meta_cap' => true,
		'supports' => array('title', 'editor', 'thumbnail', 'custom-fields', 'page-attributes'),
		'taxonomies' => array('category') 
		)
	);
}
add_action( 'init', 'kcmo_register_bannerspace_post_type', 1 );

/*
*	allow authors to edit/publish pages
*	@TODO this may make a database call every time.. 
*	look into doing this on upgrade or activation possibly
*/
function kcmo_role_permissions(){
	$role = get_role( 'author' );
	$role->add_cap( 'edit_pages' );
}
add_action( 'init', 'kcmo_role_permissions', 20 );

/*
*	enables the Link Manager that existed in WordPress until version 3.5.
*	@see http://core.trac.wordpress.org/ticket/21307
*/
add_filter( 'pre_option_link_manager_enabled', '__return_true' );

/*
*	set DOING_AJAX constant for requests that arent routed through admin-ajax.php
*/
if( !defined('DOING_AJAX') && 
	 isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
	 strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'XMLHttpRequest') === 0 )
	 define( 'DOING_AJAX', TRUE );
	 


	
