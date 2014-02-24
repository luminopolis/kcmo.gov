<?php

/*
*	copies settings from blog #1 contact widget on new department creation
*	@param int
*	@param int
*	@param string
*	@param string
*	@param int
*	@param array
*/
function kcmo_departments_duplicate_default( $blog_id, $user_id, $domain, $path, $site_id, $meta ){
	switch_to_blog( 1 );
	$depts = get_option( 'widget_departments_widget' );
	
	switch_to_blog( $blog_id );
	update_option( 'widget_departments_widget', $depts );
	
	restore_current_blog();
}
add_action( 'wpmu_new_blog', 'kcmo_departments_duplicate_default', 10, 6 );