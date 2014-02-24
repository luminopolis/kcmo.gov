<?php

namespace network_queries;

/*
*	@TODO get this working so the 'Undo' trash link works with blog id
*	@param string
*	@param string
*	@param
*	@return string
*/
function clean_url( $good_protocol_url, $original_url, $_context ){
	if( strpos($original_url, 'doaction=undo')){
		
		$parsed = parse_url( $original_url );
		parse_str( html_entity_decode($parsed['query']), $query );
	}
	
	return $good_protocol_url;
}

/*
*
*	attached to `load-edit.php` action
*/
function network_untrash(){
	// wip to get untrash working with network wide search
	//add_filter( 'clean_url', __NAMESPACE__.'\clean_url', 10, 3 );
}
add_action( 'load-edit.php', __NAMESPACE__.'\network_untrash' );

/*
*	adds blog_id index on activation
*	
*/
function taxonomy_activation(){
	global $wpdb;
	
	$sql = "DESCRIBE wp_term_relationships";
	$res = $wpdb->get_results( $sql, OBJECT_K );
	
	$table = $wpdb->get_blog_prefix( 1 )."term_relationships";
	
	if( !isset($res['blog_id']) ){
		$schema = "ALTER TABLE `$table` 
				   ADD `blog_id` INT(11)  NOT NULL  DEFAULT '0' 
				   AFTER `object_id`";
				   
		$res = $wpdb->query( $schema );
	}
	
	// drop object/ttid key
	$sql = "ALTER TABLE `$table` DROP PRIMARY KEY";
	$res = $wpdb->query( $sql );
	
	// add key with blog id
	$sql = "ALTER TABLE `$table` ADD PRIMARY KEY (`object_id`, `blog_id`, `term_taxonomy_id`)";
	$res = $wpdb->query( $sql );
	
	$sql = "UPDATE $table
			SET `blog_id` = 1 
			WHERE `blog_id` = 0";
	$res = $wpdb->query( $sql );
}
