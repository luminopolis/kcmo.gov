<?php

namespace luminopolis_sitemap;

/*
*
*	@param array
*	@return array
*/
function rewrite_rules_array( $rewrite ){
	$new = array(
		'sitemap.xml/?$' => 'index.php?action=kcmo_sitemap&format=xml',
		'sitemap/?$' => 'index.php?action=kcmo_sitemap&format=html'
	);
	
	$rewrite = array_merge( $new, $rewrite );
	
	return $rewrite;
}
add_filter( 'rewrite_rules_array', __NAMESPACE__.'\rewrite_rules_array' );

/*
*
*	attached to `query_vars` filter
*	@param array
*	@return array
*/
function query_vars( $vars ){
    $vars = array_merge( $vars, array('action', 'format') );
      
    return $vars;
}
add_filter( 'query_vars', __NAMESPACE__.'\query_vars' );

/*
*
*	@param WP_Query
*	@return WP_Query
*/
function pre_get_posts( \WP_Query &$wp_query ){
	if( isset($wp_query->query_vars['action']) && $wp_query->query_vars['action'] == 'kcmo_sitemap' ){
		global $blog_id;
		
		if( $blog_id > 1 )
			$wp_query->query_vars['network_in_blogs'] = array($blog_id);
		
		$wp_query->query_vars['network_wide'] = TRUE;
		$wp_query->query_vars['network_wide_cache'] = 300;
		
		$wp_query->query_vars['order'] = 'ASC';
		$wp_query->query_vars['orderby'] = 'blog_id';
		$wp_query->query_vars['post_type'] = 'any';
		$wp_query->query_vars['posts_per_page'] = -1;
		
		add_filter( 'posts_results', __NAMESPACE__.'\posts_results', 100, 2 );
		add_filter( 'posts_where', __NAMESPACE__.'\posts_where', 10, 2 );
		
		switch( $wp_query->query_vars['format'] ){
			case 'html':
				add_filter( 'template_include', __NAMESPACE__.'\template_html' ); 
				break;
				
			case 'xml':
				add_filter( 'template_include', __NAMESPACE__.'\template_xml' ); 
				break;
		}
	}
	
	return $wp_query;
}
add_filter( 'pre_get_posts', __NAMESPACE__.'\pre_get_posts', 1 );

/*
*
*	@param string
*	@param WP_Query
*	@return string
*/
function posts_where( $sql, \WP_Query &$wp_query ){
	if( isset($wp_query->query_vars['action']) && $wp_query->query_vars['action'] == 'kcmo_sitemap' ){
		$sql .= " OR ( post_type = 'attachment' 
					   AND post_status = 'inherit' 
					   AND post_mime_type NOT LIKE 'image%' )";
	}
	
	return $sql;
}

/*
*	attached to `posts_results` filter
*	@param array
*	@param WP_Query
*	@return array
*/
function posts_results( $res, \WP_Query &$wp_query ){
	if( !isset($wp_query->query_vars['action']) || $wp_query->query_vars['action'] != 'kcmo_sitemap' )
		return $res;
	
	$items = array();
	
	$blog_id = 0;
	$current_blog_id = get_current_blog_id();
	$public = TRUE;
	
	foreach( $res as $r ){
		if( $r->blog_id != $blog_id ){
			$blog_id = $r->blog_id;
			
			switch_to_blog( $blog_id );
			$public = get_blog_option( $blog_id, 'blog_public' );
		}
		
		if( $public ){
			switch( $r->post_type ){
				case 'attachment':
					$permalink = wp_get_attachment_url( $r->ID );
					break;
					
				default:
					$permalink = get_permalink( $r );
					break;
			}
			
			$items[] = (object) array(
				'blog_id' => $r->blog_id,
				'id' => $r->blog_id.'-'.$r->ID,
				'lastmod' => date( "Y-m-d", strtotime($r->post_modified) ),
				'parent' => $r->blog_id.'-'.$r->post_parent,
				'permalink' => $permalink,
				'post_id' => $r->ID,
				'post_title' => $r->post_title
			);
		}
	}
	
	// not using restore_current_blog() since it needs to be called after every switch_to_blog()
	switch_to_blog( $current_blog_id );
	
	usort( $items, __NAMESPACE__.'\display_sort' );
	
	require_once __DIR__.'/lib/class-sitemap-walker.php';
	
	$wp_query->set( 'sitemap', new Sitemap_Walker );
	$wp_query->set( 'sitemap_items', $items );
	
	remove_filter( 'posts_results', __NAMESPACE__.'\posts_results', 100, 2 );
	
	return $res;
}

/*
*	usort callback sorts by permalink
*	@param object
*	@param object
*	@return
*/
function display_sort( $a, $b ){
	return strcmp( $a->permalink, $b->permalink );
}

/*
*
*	attached to `template_include` filter
*	@param string
*	@return string
*/
function template_html( $template ){
	$template = __DIR__.'/views/sitemap.html.php';
	
	return $template;
}

/*
*
*	attached to `template_include` filter
*	@param string
*	@return string
*/
function kcmo_sitemap_template_xml( $template ){
	$template = __DIR__.'/views/sitemap.xml.php';
	
	return $template;
}