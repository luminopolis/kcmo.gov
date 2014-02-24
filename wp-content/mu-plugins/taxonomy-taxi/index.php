<?php
/*
Plugin Name: Taxonomy Taxi
Plugin URI: 
Description: Show custom taxonomies in /wp-admin/edit.php automatically
Version: .77
Author: Eric Eaglstun
Author URI: 
Photo Credit: http://www.flickr.com/photos/photos_mweber/
Photo URL: http://www.flickr.com/photos/photos_mweber/540970484/
Photo License: Attribution-NonCommercial 2.0 Generic (CC BY-NC 2.0)
*/

if( is_admin() )
	require dirname( __FILE__ ).'/admin.php';