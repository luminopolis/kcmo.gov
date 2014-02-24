<?php

/*
*	get posts for 'About' content bucket
*	@return WP_Query
*/
function kcmo_query_about(){
	static $about_query = NULL;
	
	if( !$about_query ){
		global $blog_id;
		$args = array(
				'network_wide_cache' => 60,
				'network_wide' => TRUE,
				'post_type' => array('post', 'page', 'attachment', 'mailing_list', 'blog'),
				'posts_per_page' => 1,
				'tax_query' => array(
					array(
						'taxonomy' => 'content-bucket',
						'field' => 'slug',
						'terms' => 'about'
					)
				)
		);
		
		// second phase, allow posts to be promoted to home page from subsites
		$args['orderby'] = 'current_blog';
			
		$about_query = new WP_Query( $args );
	}	
	
	return $about_query;
}

/*
*	get posts for 'News' content bucket
*	@return WP_Query
*/
function kcmo_query_news(){
	static $news_query = NULL;
	
	if( !$news_query ){
		global $blog_id;
		$args = array(
				'network_wide' => TRUE,
				'network_wide_cache' => 60,
				'post_type' => 'any',
				'posts_per_page' => 2,
				'tax_query' => array(
					array(
						'taxonomy' => 'content-bucket',
						'field' => 'slug',
						'terms' => 'news'
					)
				)
		);
		
		// second phase, allow posts to be promoted to home page from subsites
		$args['orderby'] = 'current_blog';
		
		$news_query = new WP_Query( $args );
	}
	
	return $news_query;
}

/*
*
*/
function kcmo_mail_post_link( $post_link, $post, $leavename, $sample ){
	$year = date( 'Y', strtotime($post->post_date) );
	$post_link = str_replace( '%year%', $year, $post_link );
	
	return $post_link;
}

/*
*	registers mailing list archive custom post type
*	attached to `init` action
*/
function kcmo_mail_post_register(){
	register_post_type( 'mailing_list', array(
		'capability_type' => 'post',
		'has_archive' => TRUE,
		'hierarchical' => FALSE,
		'label' => 'Email Archive',
		'labels' => array(),
		'map_meta_cap' => true,
		'public' => TRUE,
		'rewrite' => array(
			'slug' => 'news/%year%',
			'with_front' => FALSE,
			'ep_mask'=> EP_YEAR
		),
		'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
	) );
	
	add_filter( 'mailing_list_rewrite_rules', 'kcmo_mail_post_rewrite' );
	add_filter( 'post_type_link', 'kcmo_mail_post_link', 10, 4 );
}
add_action( 'init', 'kcmo_mail_post_register' );

/*
*	@param array
*	@return array
*/
function kcmo_mail_post_rewrite( $rules ){
	// need year/page/2/ to match before single page
	unset( $rules['news/([0-9]{4})/([^/]+)(/[0-9]+)?/?$'] );
	$rules['news/([0-9]{4})/([^/]+)(/[0-9]+)?/?$'] = 'index.php?year=$matches[1]&mailing_list=$matches[2]&page=$matches[3]';
	
	// add back archives without year
	$rules['news/page/?([0-9]{1,})/?$'] = 'index.php?paged=$matches[1]&post_type=mailing_list';
	$rules['news/?$'] = 'index.php?post_type=mailing_list';
	
	unset( $rules['news/[0-9]{4}/[^/]+/([^/]+)/?$'] );
	
	foreach( $rules as &$rule ){
		if( !strpos($rule, 'mailing_list') && !strpos($rule, 'attachment') )
			$rule .= "&post_type=mailing_list";
	}
	
	return $rules;
}

/*
*	allow numeric slugs
*	attached to `wp_unique_post_slug` filter
*	@param string
*	@param int
*	@param string
*	@param string
*	@param int
*	@param string
*	@return string
*/
function kcmo_numeric_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ){
	$slug = is_numeric( $original_slug ) ? $original_slug : $slug;
	return $slug;
}
add_filter( 'wp_unique_post_slug', 'kcmo_numeric_slug', 10, 6 );

/*
*	registers taxonomy for content bucket positions
*	attached to `init` action
*/
function kcmo_taxonomy_content_buckets(){
	register_taxonomy( 'content-bucket', array('blog', 'page', 'post'), array(
		'hierarchical' => TRUE,
		'labels' => array('name' => 'Content Buckets',
						  'singular_name' => 'Content Bucket')
	) );
}
add_action( 'init', 'kcmo_taxonomy_content_buckets', 0 );

/*
*	registers taxonomy for bannerspace slideshow positions
*	attached to `init` action
*/
function kcmo_taxonomy_content_location(){
	register_taxonomy( 'content-location', array('bannerspace_post'), array(
		'hierarchical' => TRUE,
		'labels' => array('name' => 'Content Location',
						  'singular_name' => 'Content Location')
	) );
}
add_action( 'init', 'kcmo_taxonomy_content_location', 0 );


