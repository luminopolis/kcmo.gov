<?php
/*
Plugin Name: KCMO.gov MU Loader
Plugin URI: 
Description: 
Author: Eric Eaglstun
Version: 1.2.1
Author URI: 
*/

// wpengine does not support sessions! @TODO remove anything that refs sessions
if( !session_id() && !headers_sent() )
	@session_start();

// multisite not setup
if( !function_exists('switch_to_blog') )
	return FALSE;
	
$_path = ABSPATH.'wp-content/mu-plugins/';
$_plugins = array( 
					// core functionality loads first
					'kcmo.gov/index.php',				
					
					// other peoples plugins
					'bannerspace/bannerspace.php',
					'latest-tweets-widget/latest-tweets.php',
					'redirection/redirection.php',
					'user-role-editor/user-role-editor.php',
					
					// published by eric eaglstun
					'aitch-ref/index.php',
					'taxonomy-taxi/index.php',
					'uri-command/index.php',
					
					// site specific
					'kcmo-bannerspace/index.php',
					'kcmo-blogs/index.php',
					'kcmo-children-in-tabs/index.php',
					'kcmo-order-links/index.php',
					'kcmo-problem-report/index.php',
					'kcmo-sitemap/_plugin.php',
					'kcmo-youtube-widget/index.php',
					
					// to be released by luminopolis
					'luminopolis-approval/_plugin.php',
					'luminopolis-departments-widget/_plugin.php',
					'luminopolis-filename/_plugin.php',
					'luminopolis-google-calendar/index.php',
					'luminopolis-mailchimp/index.php',
					'luminopolis-mu-shared-options/index.php',
					'luminopolis-network-queries/index.php',
					'luminopolis-network-users/index.php'
				 );

if( WP_DEBUG )
	$_plugins[] = 'dbug/dbug.php';
			   
foreach( $_plugins as $_file ){
	if( file_exists($_path.$_file) )
		require $_path.$_file;
	//elseif( WP_DEBUG )
	//	die( "plugin not found: $_path$_file" );
}

unset( $_file );
unset( $_path );
unset( $_plugins );