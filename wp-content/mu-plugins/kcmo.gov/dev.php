<?php

/*
*	better debugging for depricated functions on dev
*	attached to `deprecated_function_run` action
*/
function kcmo_deprecated_function_run( $function, $replacement, $version ){
	ddbug( $replacement, $function, 1000 );
}

if( WP_DEBUG && function_exists('ddbug') )
	add_filter( 'deprecated_function_run', 'kcmo_deprecated_function_run', 10, 3 );

/*
*	attached to `wp_die_handler` filter
*/
function kcmo_die_handler( $default = '_default_wp_die_handler' ){
	return function_exists('ddbug') ? 'kcmo_die_handler_output' : $default;
}
add_filter( 'wp_die_handler', 'kcmo_die_handler' );

/*
*
*/
function kcmo_die_handler_output( $message, $title = '', $args = array() ){
	ddbug( func_get_args(), '', 100 );
}

/*
*	mail logger
*	attached to `phpmailer_init` action
*	@param PHPMailer
*/
function kcmo_phpmailer_init( PHPMailer &$phpmailer ){
	$phpmailer->action_function = 'kcmo_phpmailer_log';
}
add_action( 'phpmailer_init', 'kcmo_phpmailer_init' );

/*
*	logs all outgoing email
*	callback for `action_function` in PHPMailer which all wp_mail uses by default
*	@param int
*	@param string
*	@param array
*	@param array
*	@param string
*	@param string
*/
function kcmo_phpmailer_log( $is_sent, $to, array $cc, array $bcc, $subject, $body ){
	if( function_exists('dlog') )
		dlog( $body, $subject."\n\n".$to, 'mail_log' );
	else
		error_log( print_r(func_get_args(), TRUE), 0, 'mail_log' );
}

/*
*	echoes current template as comment in head
*	@param string
*/
function kcmo_debug_template_include( $template ){
	global $wp_query;
	$wp_query->set( 'debug_template', '<!-- template: '.str_replace( WP_CONTENT_DIR, '', $template ).'-->' );
	
	add_action( 'wp_head', 'kcmo_template_include_display' );
	
	return $template;
}
add_filter( 'template_include', 'kcmo_debug_template_include' );

function kcmo_template_include_display(){
	global $wp_query; 
	echo $wp_query->get( 'debug_template' );
}