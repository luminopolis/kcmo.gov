<?php

/*
*	use same plugins / plugin config on all sites
*	attached to `muplugins_loaded` action
*	
*/
function kcmo_load_global_options(){
	global $blog_id, $wpdb;
	
	$opts = wp_cache_get( 'alloptions', 'options' );
		
	switch_to_blog( 1 );
	
	$main_user_role_table = $wpdb->get_blog_prefix() . 'user_roles';
	
	$included = array( 'approval-workflow', 'current_theme',
					   'dbug_log_path', 'link_manager_enabled', 
					   'timezone_string', $main_user_role_table );
	
	$included = array_map( 'wrap_in_quotes', $included );
	$included = implode( ', ', $included );
	
	$sql = "SELECT option_name, option_value 
			FROM $wpdb->options 
			WHERE option_name IN( $included )";
		
	$res = $wpdb->get_results( $sql );
	
	foreach( $res as $k=>$v )
		$opts[$v->option_name] = $v->option_value;
	
	restore_current_blog();
	
	wp_cache_set( 'alloptions', $opts, 'options' );
}
add_action( 'muplugins_loaded', 'kcmo_load_global_options' );

/*
*	clears twitter cache so new feed will be rendered immediately 
*	@param mixed
*	@param mixed
*/
function kcmo_twitter_api_clear_transients( $old_value, $value ){
	global $wpdb;
	$sql = "DELETE FROM $wpdb->options 
			WHERE `option_name` LIKE '_transient_latest_tweets_%' 
			OR `option_name` LIKE '_transient_timeout_latest_tweets_%' ";
			
	$wpdb->query( $sql );
}
add_action( 'update_option_twitter_api_consumer_key', 'kcmo_twitter_api_clear_transients', 10, 2 );