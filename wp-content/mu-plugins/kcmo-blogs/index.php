<?php
/*
Plugin Name: KCMO Blog Posts
Plugin URI: 
Description: Shows author username in url for posts written by Authors and Conributors
Author: Luminopolis / Eric Eaglstun
Version: 0.8
Author URI: 
*/

if( is_admin() )
	require dirname( __FILE__ ).'/admin.php';
	
/*
*	shows author name in blog post urls
*	attached to `post_type_link` filter
*	@param strng
*	@param WP_Post
*	@param bool
*	@param bool
*	@return string
*/
function kcmo_blog_get_permalink( $post_link, WP_Post $post, $leavename = FALSE, $sample = FALSE ){
	if( !kcmo_blog_post_supports_author($post) )
		return $post_link;
		
	$author = get_userdata( $post->post_author );
	
	$uses_slug = get_post_meta( $post->ID, '_author_in_url', TRUE );
	$slug = $uses_slug ? $author->data->display_name.'/' : '';
	
	$post_link = str_replace( '%author%/', $slug, $post_link );
	
	return $post_link;
}
add_filter( 'post_type_link', 'kcmo_blog_get_permalink', 10, 4 );

/*
*	show archive pages for authors
*	attached to `pre_get_posts` filter
*	@param WP_Query
*	@return WP_Query
*/
function kcmo_blog_pre_get_posts( &$wp_query ){
	if( $wp_query->is_main_query() ){
		if( $wp_query->query_vars['author'] && !isset($wp_query->query_vars['post_type']) )
			$wp_query->query_vars['post_type'] = 'blog';
	}
	
	if( $wp_query->query_vars['feed'] == 'atom' )
		$wp_query->query_vars['post_type'] = array( 'blog', 'post' );
		
	return $wp_query;
}
add_filter( 'pre_get_posts', 'kcmo_blog_pre_get_posts' );

/*
*	registers 'blog' custom post type
*	attached to `init` action
*/
function kcmo_blog_post_register(){
	register_post_type( 'blog', array(
		'author_in_slug' => TRUE,
		'hierarchical' => FALSE,
		'label' => 'Blog Posts',
		'labels' => array(),
		'public' => TRUE,
		'rewrite' => array(
			'slug' => '%author%/blog',
			'with_front' => FALSE
		),
		'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
		'taxonomies' => array( 'category' )
	) );
}
add_action( 'init', 'kcmo_blog_post_register' );

/*
*
*	@param WP_Post or global $post
*	@return bool
*/
function kcmo_blog_post_supports_author( $post = NULL ){
	global $wp_post_types;
	
	if( !$post )
		$post = $GLOBALS['post'];
		
	return isset( $wp_post_types[$post->post_type]->author_in_slug) && $wp_post_types[$post->post_type]->author_in_slug;
}

/*
*	something in this post register is screwing up single page links :(
*	@param array
*	@return array
*/
function kcmo_blog_all_rewrite_rules( array $rewrite ){
	unset( $rewrite['([^/]+)/?$'] );
	
	return $rewrite;
}
add_filter( 'rewrite_rules_array', 'kcmo_blog_all_rewrite_rules' );

/*
*	make a copy of the blog post rules that are accessible withour author in url
*	@param array
*	@return array
*/
function kcmo_blog_rewrite_rules( array $rewrite ){
	$new_rules = array();
	
	// take each rule, remove leading author slug, shift matches back one number
	foreach( $rewrite as $regex => $path ){
		$url = parse_url( $path );
		parse_str( $url['query'], $query );

		if( isset($query['author_name']) ){
			unset( $query['author_name'] );
			$query['kcmo_blog'] = 1;

			$i = 2;
			while( $key = array_search( "\$matches[$i]", $query) ){ 
				$query[$key] = "\$matches[".( $i - 1 )."]";
				$i++;
			}

			$path = 'index.php?'.urldecode( http_build_query($query) );
			
			$regex = str_replace( array('[^/]+/blog', '([^/]+)/blog'), 'blog', $regex );
			$new_rules[$regex] = $path;
		}
	}
	
	$rewrite = array_merge( $rewrite, $new_rules );
	
	return $rewrite;
}
add_filter( 'blog_rewrite_rules', 'kcmo_blog_rewrite_rules' );

/*
*	flush rewrite rules on plugin activation
*	callback for register_activation_hook
*/
function kcmo_blog_post_rewrite(){
	kcmo_blog_post_register();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'kcmo_blog_post_rewrite' );
