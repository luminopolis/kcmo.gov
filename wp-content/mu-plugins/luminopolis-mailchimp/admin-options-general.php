<?php
/*
*	functions required for settings screen
*/

// @TODO wrap this in a function/action ?
if( isset($_POST['_wp_nonce']) && wp_verify_nonce($_POST['_wp_nonce'], 'mailchimp-settings')  )
	luminopolis_mailchimp_settings_save();
		
/*
*	render the settings page
*
*/
function luminopolis_mailchimp_settings_page(){
	wp_register_style( 'mailchimp-admin-style', plugins_url('public/admin/options-general.css', __FILE__) );
	wp_enqueue_style( 'mailchimp-admin-style' );
	
	$vars = array(
		'mailchimp_api_key' => get_option( 'mailchimp_api_key' ),
		'mailchimp_from_email' => get_option( 'mailchimp_from_email' ),
		'mailchimp_from_name' => get_option( 'mailchimp_from_name' )
	);
	
	echo luminopolis_mailchimp_render( 'views/admin/options-general', $vars );
}

/*
*	process settings form and save
*/
function luminopolis_mailchimp_settings_save(){
	foreach( array('api_key', 'from_email', 'from_name') as $form_val )
		update_option( 'mailchimp_'.$form_val, $_POST['mailchimp_'.$form_val] );
}