<?php

/*
*	registers settings menu link in admin
*	attached to `admin_menu` action
*/
function luminopolis_google_calendar_admin_menu(){
	add_options_page( 'Google Calendar Settings', 'Google Calendar', 
					  'manage_options', 'gcal-settings', 'luminopolis_google_calendar_admin_page' );
}
add_action( 'admin_menu', 'luminopolis_google_calendar_admin_menu' );

/*
*	renders admin settings page
*	callback for add_options_page()
*/
function luminopolis_google_calendar_admin_page(){
	$vars = array(
		'max_results' => get_option( 'google-calendar-max-results', GOOGLE_CALENDAR_DEFAULT_RESULTS ),
		'url' => get_option( 'google-calendar-url', GOOGLE_CALENDAR_DEFAULT_URL )
	);
	
	luminopolis_google_calendar_render( 'views/admin/options-general', $vars );
}

/*
*	update settings from form data
*	attached to `load-settings_page_gcal-settings` action
*/
function luminopolis_google_calendar_admin_update(){
	if( isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'luminopolis-gcal-settings') ){
		foreach( array('max-results', 'url') as $k ){
			update_option( 'google-calendar-'.$k, $_POST[$k] );
		}
		
		delete_transient( 'google-calendar-data' );
		delete_transient( 'google-calendar-html' );
	}
}
add_action( 'load-settings_page_gcal-settings', 'luminopolis_google_calendar_admin_update' );

/*
*	renders a php template file with variables
*	currently only used in admin
*/
function luminopolis_google_calendar_render( $template, $vars = array() ){
	extract( (array) $vars, EXTR_SKIP );
	require dirname( __FILE__ ).'/'.$template.'.php';
}