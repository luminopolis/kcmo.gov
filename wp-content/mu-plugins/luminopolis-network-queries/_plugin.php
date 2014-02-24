<?php
/*
Plugin Name: Luminopolis MU Network-wide Queries
Plugin URI: 
Description: Allows querying posts network wide with network_wide=1, supports taxonomy queries but not post meta
Author: Luminopolis / Eric Eaglstun
Version: 1.5
Author URI: 
*/

// require php 5.3+
register_activation_hook( __FILE__, create_function("", '$ver = "5.3"; if( version_compare(phpversion(), $ver, "<") ) die( "This plugin requires PHP version $ver or greater be installed." );') );

// add db column
register_activation_hook( __FILE__, __NAMESPACE__.'\taxonomy_activation' );

require __DIR__.'/index.php';