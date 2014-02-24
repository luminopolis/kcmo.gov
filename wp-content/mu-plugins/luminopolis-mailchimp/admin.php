<?php

/*
*	loads php file admin-{pagenow} for per page specific actions/filters
*/
function luminopolis_mailchimp_require(){
	global $pagenow;
	
	$file = dirname(__FILE__).'/admin-'.$pagenow;

	if( file_exists($file) )
		require $file;
}
add_action( 'admin_menu', 'luminopolis_mailchimp_require', 9 );

/*
*	gets static instance of Mailchimp api
*	@return Mailchimp
*/
function luminopolis_mailchimp_api(){
	static $mc = NULL;
	
	if( $mc === NULL ){
		require_once dirname( __FILE__ ).'/lib/src/Mailchimp.php';
		$api_key = get_option( 'mailchimp_api_key' );
		
		try{
			$mc = new Mailchimp( $api_key );
			$mc->connected = TRUE;
		} catch( Exception $e ){
			$mc = (object) array( 'connected' => FALSE );
		}
	}
	
	return $mc;
}

/*
*	register menu in admin
*	attached to `admin_menu` action
*/
function luminopolis_mailchimp_admin(){
	add_options_page( 'Mailchimp Settings', 'Mailchimp', 
					  'publish_posts', 'luminopolis_mailchimp', 'luminopolis_mailchimp_settings_page' );
}
add_action( 'admin_menu', 'luminopolis_mailchimp_admin', 10 );

/*
*	friendly text and link to published campaigns on mailchimp.com
*	@param array
*	@return string
*/
function luminopolis_mailchimp_campaign_status( $list ){
	switch( $list['data'][0]['status'] ){
		case 'sent':
			$status_msg = '<strong>Sent</strong> - '.$list['data'][0]['send_time'];
			$status_msg .= '<br/><a target="_blank" href="https://us3.admin.mailchimp.com/reports/summary?id='.$list['data'][0]['web_id'].'">Summary in Mailchimp</a>';
			break;
		case 'save':
			$status_msg = '<strong>Saved</strong> - ';
			$status_msg .= '<a target="_blank" href="https://us3.admin.mailchimp.com/campaigns/wizard/confirm?id='.$list['data'][0]['web_id'].'">View in Mailchimp</a>';
			break;
		default:
			$status_msg = ucfirst( $list['data'][0]['status'] );
			break;
	}
	
	return $status_msg;
}

/*
*
*	@param int
*	@return array
*/
function luminopolis_mailchimp_getlist( $campaign_id ){
	$mc = luminopolis_mailchimp_api();
	
	$list = get_transient( 'mailchimp_list_'.$campaign_id );
	
	if( $mc->connected && !$list && $campaign_id ){
		try{
			$list = $mc->campaigns->getList( array('campaign_id' => $campaign_id) );
			set_transient( 'mailchimp_list_'.$campaign_id, $list, 600 );
		} catch ( Exception	 $e ){
			$mc->connected = FALSE;
		}
	}
	
	return $list;
}

/*
*
*	@return array
*/
function luminopolis_mailchimp_getlists(){
	$mc = luminopolis_mailchimp_api();
	
	$lists = get_transient( 'mailchimp_lists' );
	
	if( $mc->connected && !$lists ){
		try{
			$lists = $mc->lists->getList();
			set_transient( 'mailchimp_lists', $lists, 600 );
		} catch ( Exception	 $e ){
			$mc->connected = FALSE;
		}
	}
	
	return $lists;
}

/*
*	renders a php template file with varaibles
*	used for settings and submitbox
*	@param string
*	@param array
*/
function luminopolis_mailchimp_render( $template, $vars = array() ){
	extract( (array) $vars, EXTR_SKIP );
	require dirname( __FILE__ ).'/'.$template.'.php';
}