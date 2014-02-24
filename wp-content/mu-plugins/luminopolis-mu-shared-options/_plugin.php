<?php
/*
Plugin Name:	Multisite Shared Options
Plugin URI: 
Description:	Allows options to be set on a per blog basis but fallback to blog 1 if empty
Author:			Luminopolis / Eric Eaglstun
Text Domain: 	mu-shared-options
Domain Path:	/lang
Version:		1.0
Author URI: 
*/

register_activation_hook( __FILE__, create_function("", '$ver = "5.3"; if( version_compare(phpversion(), $ver, "<") ) die( "This plugin requires PHP version $ver or greater be installed." );') );

require __DIR__.'/index.php';