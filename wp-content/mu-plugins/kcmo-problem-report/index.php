<?php
/*
Plugin Name: KCMO Problem Report
Plugin URI: 
Description: 
Author: Luminopolis / Eric Eaglstun
Version: 0.8
Author URI: 
*/

if( is_admin() )
	require dirname( __FILE__ ).'/admin.php';

if( !defined('LUMINOPOLIS_REPORTER_PLUGINS_URL') )
	define( 'LUMINOPOLIS_REPORTER_PLUGINS_URL', plugins_url('', __FILE__) );
	
/*
*	handles checking for feedback form post
*	echos json if ajax, sets vars if not
*	attached to `parse_query` action
*	@param WP_Query
*	@return WP_Query
*/
function kcmo_problem_init( WP_Query &$wp_query ){
	if( !$wp_query->is_main_query() )
		return $wp_query;
		
	if( isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'feedback-form') )
		$feedback = kcmo_problem_process( stripslashes_deep($_POST) );
	else
		$feedback = (object) array(
			'error' => '',
			'user_data' => array( 'activity' => '', 'issue' => '' )
		);	
	
	$wp_query->set( 'feedback', $feedback );
	
	return $wp_query;
}
add_action( 'parse_query', 'kcmo_problem_init', 10, 1 );

/*
*
*	@param array post data, slashes stripped
*	@return object
*/
function kcmo_problem_process( $data ){
	$errors = array();
	$return = (object) array();
	
	if( !isset($data['activity']) || !trim($data['activity']) || !isset($data['issue']) || !trim($data['issue']) ){
		$return->error = 'Please complete both fields. We look forward to your feedback!';
	} else {
		require dirname( __FILE__ ).'/model.php';
		$data['report_date'] = current_time( 'mysql' );
		$data['URL'] = kcmo_problem_current_page_url();
		
		kcmo_problem_report_model_insert_frontend( $data );
		$return->success = 'Thanks for your feedback! We are always striving to make KCMO.gov the best site it can be. The City&#39;s Web Team will look into this.';
	}
	
	if( defined('DOING_AJAX') && DOING_AJAX ){
		header( 'Content-type: application/json' );
		echo json_encode( $return );
		die();
	}
	
	// extra data do be used for accessible form (no js)		
	$return->user_data = array( 'activity' => $data['activity'],
							 	'issue' => $data['issue'] );
		
	return $return;
}

/*
*	
*	attached to `wp_enqueue_scripts` action
*/
function kcmo_problem_scripts(){
	wp_register_script( 'kcmo-report', LUMINOPOLIS_REPORTER_PLUGINS_URL.'/index.js', array('jquery') );
	wp_enqueue_script( 'kcmo-report' );
}
add_action( 'wp_enqueue_scripts', 'kcmo_problem_scripts' );

/*
*
*	@return string
*/
function kcmo_problem_current_page_url(){
	$page_url = 'http';
	if( isset($_SERVER["HTTPS"]) ){
		if( $_SERVER["HTTPS"] == "on" )
			$page_url .= "s";
	}
	$page_url .= "://";
	
	if( $_SERVER["SERVER_PORT"] != "80" )
		$page_url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	else
		$page_url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		
	return $page_url;
}

/*
*	creates problem_report table on plugin activation
*	
*/
function kcmo_problem_activation(){
	global $wpdb;
	
	$sql = "SHOW TABLES LIKE 'problem_report'";
	$res = $wpdb->get_row( $sql );
	
	if( $res )
		return;
		
	$schema = "CREATE TABLE `problem_report` (
				   `key_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				   `URL` varchar(255) DEFAULT NULL,
				   `report_date` datetime DEFAULT NULL,
				   `activity` text,
				   `issue` text,
				   `resolution` text,
				   `resolution_date` datetime DEFAULT NULL,
				   `other_notes` text,
				   PRIMARY KEY (`key_id`)
			   ) 
			   ENGINE=InnoDB 
			   DEFAULT CHARSET=latin1";
			   
	$res = $wpdb->query( $schema );
}
register_activation_hook( __FILE__, 'kcmo_problem_activation' );

/*
*	renders a php template file with varaibles
*	currently only used in admin
*/
function kcmo_problem_render( $template, $vars = array() ){
	extract( (array) $vars, EXTR_SKIP );
	require dirname( __FILE__ ).'/'.$template.'.php';
}