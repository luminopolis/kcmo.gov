<?php
/*
Plugin Name:	Alphanumeric Filenames
Plugin URI: 
Description: 
Author:			Luminopolis / Eric Eaglstun
Version: 		1.0
Author URI: 	http://luminopolis.com/
*/

register_activation_hook( __FILE__, create_function("", '$ver = "5.3"; if( version_compare(phpversion(), $ver, "<") ) die( "This plugin requires PHP version $ver or greater be installed." );') );

require __DIR__.'/index.php';