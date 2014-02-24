<?php
/*
Plugin Name:	KCMO Mailchimp Integration
Plugin URI: 
Description: 	Adds options to save to mailchimp campaign and tweet link. Uses `mailing_list` post type. Requires 'Latest Tweets' widget for twitter integration. 
Author:			Luminopolis / Eric Eaglstun
Text Domain: 	luminopolis-mailchimp
Domain Path:	/lang
Version:		0.9
Author URI: 	http://luminopolis.com/
*/

register_activation_hook( __FILE__, create_function("", '$ver = "5.3"; if( version_compare(phpversion(), $ver, "<") ) die( "This plugin requires PHP version $ver or greater be installed." );') );

require __DIR__.'/index.php';