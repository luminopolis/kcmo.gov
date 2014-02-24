<?php

add_action( 'admin_menu', 'kcmo_problem_report_admin' );

/*
*	attached to `admin_menu` action
*/	
function kcmo_problem_report_admin(){
	require dirname( __FILE__ ).'/model.php';
	
	// notifications
	$problems = kcmo_problem_report_model_get_all( TRUE );
	$problems = count( $problems );
	
	$title = 'Problem Reports';
	
	if( $problems )
		$title .= '<span class="update-plugins"><span class="update-count">'.$problems.'</span></span>';
	
	add_menu_page( 'Problem Reports', $title, 
				   'administrator', 'kcmo-problem-reports', 'kcmo_problem_report_route' );
}

/*
*	callback for add_menu_page displays correct page
*/
function kcmo_problem_report_route(){
	switch( TRUE ){
		case isset( $_POST['_wpnonce']) && wp_verify_nonce( $_POST['_wpnonce'], 'problem-report-'.$_POST['key_id'] );
			kcmo_problem_report_model_update_admin( stripslashes_deep($_POST) );
			
		case isset( $_GET['key_id'] ):
			kcmo_problem_report_single( $_GET['key_id'] );
			break;
			
		default:
			kcmo_problem_report_table();
			break;
	}
}

/*
*	view single problem report
*	@param int
*/
function kcmo_problem_report_single( $key_id ){
	$vars['problem'] = kcmo_problem_report_model_get_one( $key_id );
	$vars['wpnonce'] = wp_create_nonce( 'problem-report-'.$key_id );
	
	kcmo_problem_render( 'admin-single', $vars );
}

/*
*	view list of all reported problems
*/		   
function kcmo_problem_report_table(){
	$vars['problems'] = kcmo_problem_report_model_get_all();
	
	kcmo_problem_render( 'admin-table', $vars );
}