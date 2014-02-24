<?php
/*
Plugin Name:	KCMO Departments Widget
Plugin URI:		Customizable contact information and social networks per department
Description: 
Author:			Luminopolis / Eric Eaglstun
Text Domain: 	departments-widget
Domain Path:	/lang
Version: 1.1
Author URI: 
*/

// require php 5.3+
register_activation_hook( __FILE__, create_function("", '$ver = "5.3"; if( version_compare(phpversion(), $ver, "<") ) die( "This plugin requires PHP version $ver or greater be installed." );') );

require __DIR__.'/index.php';