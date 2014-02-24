<?php

/*
*
*	@param bool set to true to get only unresolved
*	@return array
*/
function kcmo_problem_report_model_get_all( $unresolved = FALSE ){
	global $wpdb;
	
	$where = $unresolved ? "WHERE resolution_date IS NULL" : "";
	
	$sql = "SELECT * FROM problem_report
			$where
			ORDER BY report_date DESC";
	$res = $wpdb->get_results( $sql );
	
	return $res;
}

/*
*
*	@param int
*	@return object
*/
function kcmo_problem_report_model_get_one( $key_id ){
	global $wpdb;
	$sql = $wpdb->prepare( "SELECT * FROM problem_report
							WHERE key_id = %d
							LIMIT 1", $key_id );
	$res = $wpdb->get_row( $sql );
	
	return $res;
}

/*
*
*	@param array post data, slashes stripped
*	@return
*/
function kcmo_problem_report_model_insert_frontend( $data ){
	global $wpdb;
	
	$sql = $wpdb->prepare( "INSERT INTO problem_report
							( `activity`, `issue`, `report_date`, `URL` )
							VALUES
							( %s, %s, %s, %s )",
							$data['activity'], $data['issue'], $data['report_date'], $data['URL'] );
	$res = $wpdb->query( $sql );
	return $res;
}

/*
*
*	@param array post data, slashes stripped
*	@return
*/
function kcmo_problem_report_model_update_admin( $data ){
	global $wpdb;
	
	$sql = $wpdb->prepare( "UPDATE problem_report
							SET resolution = %s,
								resolution_date = NOW(),
								other_notes = %s
							WHERE key_id = %d
							LIMIT 1", $data['resolution'], $data['other_notes'], $data['key_id'] );
	$res = $wpdb->query( $sql );
	return $res;
}
