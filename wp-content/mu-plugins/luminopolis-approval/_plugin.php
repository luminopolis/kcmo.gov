<?php
/*
Plugin Name:	Luminopolis Approval Workflow
Plugin URI: 
Description: 
Author:			Luminopolis / Eric Eaglstun
Text Domain: 	luminopolis-approval
Domain Path:	/lang
Version: 		1.0
Author URI: 	http://luminopolis.com/
*/

register_activation_hook( __FILE__, create_function("", '$ver = "5.3"; if( version_compare(phpversion(), $ver, "<") ) die( "This plugin requires PHP version $ver or greater be installed." );') );

if( !defined('LUMINOPOLIS_APPROVAL_PLUGINS_URL') )
	define( 'LUMINOPOLIS_APPROVAL_PLUGINS_URL', plugins_url('', __FILE__) );

require __DIR__.'/index.php';