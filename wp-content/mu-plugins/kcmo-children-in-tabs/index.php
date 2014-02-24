<?php
/*
Plugin Name: KCMO Children posts in tabs
Plugin URI: 
Description: Options to show child pages in tabs on parent page
Author: Luminopolis / Eric Eaglstun
Version: 1.0
Author URI: 
*/

if( !defined('LUMINOPOLIS_REQUEST_HELPER') )
	return;

if( is_admin() )
	require dirname( __FILE__ ).'/admin.php';

/*
*	adds the correct anchor to permalinks for pages in parents tab
*	@param string
*	@param int
*	@param bool
*	@return string
*/
function kcmo_children_permalink( $link, $post_id, $sample ){
	if( get_post_meta($post_id, '_show_in_tab', TRUE) ){
		$post = get_post( $post_id );
		
		if( !$post->post_parent )
			return $link;
			
		$link = get_permalink( $post->post_parent );
		
		$args = array(
			'meta_query' => array(
				array(
					'key' => '_show_in_tab',
					'value' => 1,
           			'compare' => '=',
				)
			),
			'order' => 'ASC',
			'orderby' => 'menu_order',
			'post_parent' => $post->post_parent,
			'post_type' => 'any',
			'posts_per_page' => -1
		);
		
		$r = new WP_Query( $args );
		//dbug( $r->request );
		
		$index = array_search( $post_id, array_map('ids', $r->posts) );
		
		// add one takes care of main tab which is not included in above query
		$link .= '#'.kcmo_children_permalink_tab( $index + 1 );
	}
	
	return $link;
}
add_filter( 'page_link', 'kcmo_children_permalink', 10, 3 );

/*
*	
*	@param int
*	@return string html
*/
function kcmo_children_permalink_tab( $index ){
	return 'tab-firs'.( $index + 1 );
}

/*
*	
*	@param WP_Post
*	@return string
*/
function kcmo_children_title( $post ){
	if( $title = get_post_meta($post->ID, '_tab_title', TRUE) ){
		//$title = 'In Tab';
	} else {
		$title = get_the_title( $post );
	}
	
	return $title;
}

/*
*	set post has_data property for queries with tabbed children posts
*	@param string
*	@param WP_Query
*	@return string
*/
function kcmo_children_sql_fields( $sql, $wp_query ){
	global $wpdb;
	if( !empty($wp_query->queried_object) && is_post_type_hierarchical($wp_query->queried_object->post_type) ){
		$sql .= ", 1 as `has_tabs`";
	} else {
		$sql .= ", 0 as `has_tabs`";
	}
	
	return $sql;
}
add_filter( 'posts_fields', 'kcmo_children_sql_fields', 10, 2 );

/*
*
*	@param string
*	@param WP_Query
*	@return string
*/
function kcmo_children_sql_join( $sql, $wp_query ){
	global $wpdb;
	if( !empty($wp_query->queried_object) && is_post_type_hierarchical($wp_query->queried_object->post_type) ){
		$sql .= " LEFT JOIN {$wpdb->postmeta} PM 
					ON {$wpdb->posts}.ID = PM.post_id 
					AND PM.meta_key = '_show_in_tab' ";
	}
	
	return $sql;
}
add_filter( 'posts_join', 'kcmo_children_sql_join', 10, 2 );

/*
*
*	@param string
*	@param WP_Query
*	@return string
*/
function kcmo_children_sql_orderby( $sql, $wp_query ){
	global $wpdb;
	if( !empty($wp_query->queried_object) && is_post_type_hierarchical($wp_query->queried_object->post_type) ){
		$sql = $wpdb->prepare( "{$wpdb->posts}.ID = %d DESC, menu_order ASC, $sql", 
								$wp_query->queried_object->ID );
	}
	
	return $sql;
}
add_filter( 'posts_orderby', 'kcmo_children_sql_orderby', 10, 2 );

/*
*	for debugging
*	@param string
*	@param WP_Query
*	@return string
*/
function kcmo_children_sql_request( $sql, $wp_query ){
	//dbug($sql);
	
	return $sql;
}

add_filter( 'posts_request', 'kcmo_children_sql_request', 10, 2 );

/*
*
*	@param string
*	@param WP_Query
*	@return string
*/
function kcmo_children_sql_where( $sql, $wp_query ){
	global $wpdb;
	
	if( !empty($wp_query->queried_object) && is_post_type_hierarchical($wp_query->queried_object->post_type) ){
		
		$read_private_cap = $read_private_cap = 'read_private_' . $wp_query->queried_object->post_type . 's';;
		
		$cap = "$wpdb->posts.post_status = 'publish'";
		
		if( is_user_logged_in() )
			if( $wp_query->is_preview() ){
				$cap .= " OR 1 = 1 ";
			} elseif( current_user_can($read_private_cap) ){
				$cap .= " OR $wpdb->posts.post_status = 'private' ";
			} else {
				$cap .= $wpdb->prepare( " OR ($wpdb->posts.post_author = %d 
											  AND $wpdb->posts.post_status = 'private') ", get_current_user_id() );
			}
					
		$sql = $wpdb->prepare( " AND ( (1 = 1 $sql) OR (post_parent = %d AND PM.meta_value = 1) ) 
								 AND ( $cap )", 
								$wp_query->queried_object->ID );
	}
	
	return $sql;
}

/*
*	gets the template used for a tab fart, used for hiding side slideshow in certain places
*	@param int
*	@return string template filename without extension
*/
function kcmo_children_tab_template( $post_id ){
	$template = get_post_meta( $post_id, '_wp_page_template', TRUE );
	$template = basename( $template, '.php' );
	
	return $template;
}

add_filter( 'posts_where', 'kcmo_children_sql_where', 10, 2 );