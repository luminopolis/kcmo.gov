<?php

namespace network_queries;

/*
*	setup query sql filters on WP_Query
*	use `network_wide` = 1 to enable
*	use `network_in_blogs` = array(blog ids) to limit to specific blogs
*	use `network_wide_cache` = (int) seconds for wp native caching
*	@param WP_Query
*	@return WP_Query
*/
function pre_get_posts( \WP_Query $wp_query ){
	switch( TRUE ){
		case isset( $wp_query->query_vars['network_wide']) && $wp_query->query_vars['network_wide']:
		case $wp_query->is_search() && !is_admin():
			add_filter( 'posts_groupby', __NAMESPACE__.'\posts_groupby', 10, 2 );
			add_filter( 'posts_join', __NAMESPACE__.'\posts_join', 10, 2 );
			add_filter( 'posts_orderby', __NAMESPACE__.'\posts_orderby', 10, 2 );
			add_filter( 'posts_request', __NAMESPACE__.'\posts_request', 1, 2 );
			break;
			
		default:
			remove_filter( 'posts_groupby', __NAMESPACE__.'\posts_groupby', 10, 2 );
			remove_filter( 'posts_join', __NAMESPACE__.'\posts_join', 10, 2 );
			remove_filter( 'posts_orderby', __NAMESPACE__.'\posts_orderby', 10, 2 );
			remove_filter( 'posts_request', __NAMESPACE__.'\posts_request', 1, 2 );
			break;
	}
	
	return $wp_query;
}
add_filter( 'pre_get_posts', __NAMESPACE__.'\pre_get_posts' );

/*
*	
*	@param string
*	@param WP_Query
*	@return string
*/
function posts_groupby( $sql, \WP_Query $wp_query ){
	global $wpdb;
	
	// why is sql blank??
	$sql = trim( $sql ) ? "$sql, $wpdb->posts.ID, $wpdb->posts.blog_id" : 
						  "$wpdb->posts.ID, $wpdb->posts.blog_id";

	return $sql;
}

/*
*	adds blog_id to term_relationships join
*	@param string
*	@param WP_Query
*	@return string
*/
function posts_join( $sql, \WP_Query $wp_query ){
	global $wpdb;
	
	// @TODO cleanup regex
	$sql = preg_replace( "/$wpdb->posts.ID = $wpdb->term_relationships.object_id/", 
						 "$wpdb->posts.ID = $wpdb->term_relationships.object_id 
						 	AND $wpdb->term_relationships.blog_id = $wpdb->posts.blog_id
						 	/* network_queries posts_join */", 
						 $sql );

	return $sql;
}

/*
*	adds extra supported params to orderby clause 
*	supported params
*	`orderby`
*		blog_id			order by blog id
*		current_blog	shows current blog first, blog #1 second, all others 3rd
*	@param string
*	@param WP_Query
*	@return string
*/
function posts_orderby( $sql, \WP_Query $wp_query ){
	if( !isset($wp_query->query_vars['orderby']) ) 
		return $sql;
	
	global $blog_id, $wpdb;
	
	switch( $wp_query->query_vars['orderby'] ){
		// blog id descending
		case 'blog_id':
			$sql = 'blog_id '.$wp_query->query_vars['order'].', '.$sql;
			break;
		
		// in current blog first, then post date site wide
		case 'current_blog':
			$sql = $wpdb->prepare( "{$wpdb->posts}.blog_id = %d {$wp_query->query_vars['order']}, 
									{$wpdb->posts}.blog_id = 1 DESC, /* show main site blogs next? */
									$sql", $blog_id );
			break;
			
		default:
			break;
	}

	return $sql;
}

/*
*	replaces select from blog with network wide select union
*	overrides with cache if `network_wide_cache` is present
*	@param string
*	@param WP_Query
*	@return string
*/
function posts_request( $sql, \WP_Query $wp_query ){
	global $wpdb;
	
	$in_blogs = $wp_query->get( 'network_in_blogs' );
	$use_cache = $wp_query->get( 'network_wide_cache' );
	
	$sites = wp_get_sites();
	
	$union = array();
	
	foreach( $sites as $site ){
		$blog_id = $site['blog_id'];
		
		if( $in_blogs && !in_array($blog_id, $in_blogs) )
			continue;
		
		$table = $wpdb->get_blog_prefix( $blog_id )."posts" ;
		$union[] = "SELECT *, $blog_id AS blog_id FROM $table";
	}
	
	$union = implode( ' UNION ', $union );
	$union = "FROM ( $union ) AS $wpdb->posts /* network_queries posts_request */";
	$sql = preg_replace( "/FROM\s+$wpdb->posts/", $union, $sql );
	
	if( $use_cache ){
		$key = "network-query-".md5( serialize($wp_query->query_vars) );
		$key = substr( $key, 0, 40 );
		
		$posts = get_transient( $key );
		
		if( !$posts ){
			$posts = $wpdb->get_results( $sql );
			$posts = array_map( 'get_post', $posts );
			
			set_transient( $key, $posts, $use_cache );
		}
		
		add_filter( 'posts_results', function($p, $wp_query) use($key, $posts){
			if( isset($p[0]) && ($key == $p[0]->network_query_transient) )
				return $posts;
			
			return $p;
		}, 10, 2 );
		
		return "SELECT '$key' AS `network_query_transient`";
	}
	
	return $sql;
}