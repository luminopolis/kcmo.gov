<?php 

namespace luminopolis_approval;

/*
*	@TODO remove the 'Add New' link in edit.php
*/
function add_new(){
	$post_type = isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : 'post';
	
	add_filter( 'views_edit-'.$post_type, __NAMESPACE__.'\display_rejected', 10, 1 );
}
add_action( 'load-edit.php', __NAMESPACE__.'\add_new' );

/*
*	give information about who approved or rejected posts in edit table
*	@param array
*	@param WP_Post
*	@return
*/
function display_post_states( $post_states, \WP_Post $post ){
	
	if( $post->post_status == 'needs-reapproval' )
		$post_states = array( 'Needs Reapproval' );
	
	if( !in_array($post->post_status, array('pending')) )
		return $post_states;
	
	$approvals = approval_get_history( $post->ID );
	
	if( !count($approvals) )
		return $post_states;
		
	$display = display_date_and_name( $approvals[0] );
	
	$post_states = array( $display );
	
	return $post_states;
}
add_filter( 'display_post_states', __NAMESPACE__.'\display_post_states', 10, 2 );

/*
*	attached to `views_edit-{post_type}` filter to show link with total number of rejected posts
*	@param array
*	@return array
*/
function display_rejected( $views ){
	$post_type = isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : 'post';
	
	$q = new \WP_Query(
		array(
			'meta_key' => '_luminopolis_publish_status',
			'meta_value' => 'reject',
			'post_status' => 'pending',
			'post_type' => $post_type
		)
	);
	
	if( !$q->found_posts )
		return $views;
	
	$class = '';
	
	if( isset($_REQUEST['publish_status']) && 'rejected' == $_REQUEST['publish_status'] ){
		$views['pending'] = str_replace('class="current"', '', $views['pending'] );
		$class = ' class="current"';
	}
	
	$views['rejected'] = "<a href='edit.php?post_status=pending&amp;publish_status=rejected&amp;post_type=$post_type'$class>" . 
								   sprintf( 
								   		translate_nooped_plural( array(
								   			'domain' => '',
								   			'context' => '',
								   			'singular' => 'Rejected <span class="count">(%s)</span> ',
								   			'plural' => 'Rejected <span class="count">(%s)</span> ',
								   		), 'rejected' ), 
								   		number_format_i18n( $q->found_posts) ) . 
								   '</a>';
	
	return $views;
}

/*
*	sets up where clause to list needs reapproval posts in main edit table
*	attached to `load-edit` action
*/
function sql_filters(){
	add_filter( 'posts_join', __NAMESPACE__.'\posts_join', 10, 2 );	
	add_filter( 'posts_request', __NAMESPACE__.'\posts_request', 10, 2 );	
	add_filter( 'posts_where', __NAMESPACE__.'\posts_where', 10, 2 );	
	
	add_filter( 'posts_results', __NAMESPACE__.'\posts_results', 10, 2 );	
}
add_action( 'load-edit.php', __NAMESPACE__.'\sql_filters' );

/*
*	
*	@param string
*	@param WP_Query
*	@return string
*/
function posts_join( $sql, \WP_Query $wp_query ){
	global $wpdb;
	$sql .= " LEFT JOIN $wpdb->postmeta PM 
				ON $wpdb->posts.ID = PM.post_id 
				AND PM.meta_key = '_luminopolis_publish_status' ";					
	return $sql;
}

/*
*	just for debugging
*	@param string
*	@param WP_Query
*	@return string
*/
function posts_request( $sql, \WP_Query $wp_query ){
	//dbug( $wp_query->query_vars, '', 100 );						
	return $sql;
}

/*
*	where clause to list needs reapproval posts in main edit table
*	@param string
*	@param WP_Query
*	@return string
*/
function posts_where( $sql, \WP_Query $wp_query ){
	global $wpdb;
	
	if( !isset($wp_query->query_vars['post_status']) )
		$wp_query->query_vars['post_status'] = NULL;
	
	if( !isset($_REQUEST['publish_status']) )
		$_REQUEST['publish_status'] = NULL;
	
	$sql = " /* luminopolis_approval_posts_where */ ".$sql;
	
	if( !is_admin() && $wp_query->query_vars['post_status'] != 'pending' )
		$sql .= $wpdb->prepare( " OR (post_status = 'needs-reapproval' AND post_type = %s ) /* 1 */", 
								$wp_query->query_vars['post_type'] );
	elseif( $wp_query->query_vars['post_status'] == 'pending' && $_REQUEST['publish_status'] == 'rejected' )
		$sql .= " AND ( PM.meta_value IN('reject') ) /* 2 */";
	elseif( $wp_query->query_vars['post_status'] == 'pending' && $wp_query->query_vars['meta_key'] != '_luminopolis_publish_status' )
		$sql .= " AND ( PM.meta_value IS NULL OR PM.meta_value NOT IN('reject') ) /* 3 */";
	
	$sql .= " /* luminopolis_approval_posts_where */ ";
		
	return $sql;
}

/*
*	remove parent pages from pages which require reapproval
*	do prevent page duplicates in edit listing
*	@param array
*	@param WP_Query
*	@return array
*/
function posts_results( $posts, \WP_Query $wp_query ){
	if( !$wp_query->is_main_query() )
		return $posts;
	
	$return = array();
	
	foreach( $posts as $k=>$post ){
		$return[$post->ID] = $post;
	
		if( $post->post_status == 'needs-reapproval' ){
			$parent_id = $post->post_parent;
			
			if( isset($return[$parent_id]) )
				$post->post_parent = $return[$parent_id]->post_parent;
				
			//unset( $return[$parent_id] );
		}
	}
	
	return $return;
}

/*
*	modify counts above table for pending to not show rejected
*	attached to `wp_count_posts` filter
*	@param object
*	@param string
*	@param string
*	@return object
*/
function wp_count_posts( $counts, $post_type, $perm ){
	$q = new \WP_Query(
		array(
			'post_status' => 'pending',
			'post_type' => $post_type,
		)
	);
	
	$counts->pending = $q->found_posts;
	
	return $counts;
}
add_filter( 'wp_count_posts', __NAMESPACE__.'\wp_count_posts', 10, 3 );