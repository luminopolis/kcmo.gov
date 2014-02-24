<?php

namespace luminopolis_approval;

if( is_admin() )
	require __DIR__.'/admin.php';

require __DIR__.'/lib/index.php';
require __DIR__.'/lib/functions.php';

/*
*
*/
function activate(){
	$role = get_role( 'editor' );
	$role->add_cap( 'luminopolis_approval_level_01' );
	$role->add_cap( 'luminopolis_approval_level_02' );
	
	/*
	// legacy
	$role = get_role( 'administrator' );
	$role->remove_cap( 'luminopolis_approval_level_01' );
	$role->remove_cap( 'luminopolis_approval_level_02' );
	*/
}
register_activation_hook( __FILE__, __NAMESPACE__.'\activate' );
	
// revisions ajax
add_action( 'wp_ajax_get-revision-diffs', __NAMESPACE__.'\revisions_ajax', 0 );

/*
*	should be exactly the same as wp_ajax_get_revision_diffs() except wp_get_revision_ui_diff is changed
*
*/
function revisions_ajax(){
	remove_action( 'wp_ajax_get-revision-diffs', 'wp_ajax_get_revision_diffs', 1 );
	
	require __DIR__.'/admin-revision.php';
	get_revision_diffs();
}

/*
*
*/
function load_textdomain(){
	load_plugin_textdomain( 'luminopolis-approval', false, __DIR__.'/lang/' );
}
add_action( 'plugins_loaded', __NAMESPACE__.'\load_textdomain' );