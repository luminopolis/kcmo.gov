<?php

remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );

/*
*	couldnt find an htaccess solution - 
*	redirect pages with /cms/ in url to their non cms location - only happened in admin.
*	attached to `init` action
*/
function kcmo_init_url_check(){
	if( strpos($_SERVER['REQUEST_URI'], '/cms/') === 0 ){
		$url = str_replace( '/cms/', '/', $_SERVER['REQUEST_URI'] );
		
		wp_redirect( $url );
		die();
	}
}
kcmo_init_url_check( 'init', 'init_url_check' );

/*
*	allows to access the page selected to show in the the hero bucket at its own url
*	@param
*	@param
*	@param
*	@return string
*/
function kcmo_page_link( $link, $post_id, $sample ){
	global $pagenow;
	
	if( $link != home_url('/') )
		return $link;
		
	$leavename = is_admin() && in_array( $pagenow, array('post.php','post-new.php') );
	
	$link = _get_page_link( $post_id, $leavename, $sample );
	
	return $link;
}
add_filter( 'page_link', 'kcmo_page_link', 10, 3 );

/*
*	attached to `option_sidebars_widgets` filter
*/
function kcmo_option_sidebars_widgets( $sidebars_widgets ){
	return $sidebars_widgets;
}
add_filter( 'option_sidebars_widgets', 'kcmo_option_sidebars_widgets', 1000, 2 );

/*
*	default thumbnails in home page columns if no fetured image exists
*	@param string
*	@param int
*	@param string
*	@param string
*	@param array
*	@return string
*/
function kcmo_post_thumbnail_html( $html, $post_id, $post_thumbnail_id, $size, $attr ){
	if( trim($html) || $size != 'front-column' )
		return $html;
		
	static $thumb_id = 4;
	$thumb_id++;
	
	if( $thumb_id > 7 )
		$thumb_id = 5;
		
	return '<img src="'.site_url('/wp-content/themes/kcmo.gov/_media/images/'.$thumb_id.'.jpg').'"/>';
}
add_filter( 'post_thumbnail_html', 'kcmo_post_thumbnail_html', 10, 5 );

/*
*	remove the redirect when accessing the page which has been selected to display on home
*	@param string
*	@param string
*	@return string
*/
function kcmo_redirect_canonical( $redirect_url, $requested_url ){
	global $wp_query;
	
	if( isset($wp_query->queried_object) && $wp_query->queried_object->ID == get_option('page_on_front') ){
		$wp_query->set( 'home_read_more', TRUE );
		
		$url = parse_url( $requested_url );
		$redirect_url = get_site_url( 1, trailingslashit($url['path']) );
		
		if( isset($url['query']) )
			$redirect_url .= '?'.$url['query'];
	} else {
		$wp_query->set( 'home_read_more', FALSE );
	}
	
	return $redirect_url;
}
add_filter( 'redirect_canonical', 'kcmo_redirect_canonical', 10, 2 );

/*
*	force 3 column page on front page of each site
*	@param string
*	@return string
*/
function kcmo_template_include( $template ){
	global $wp_query;
	
	if( $wp_query->get('home_read_more') && ($template == TEMPLATEPATH.'/home.php') ){
		// disallow 3 column for any other page than home, since content will be cut off in hero bucket
		$template = TEMPLATEPATH.'/index.php';
	} elseif( !$wp_query->get('home_read_more') && isset($wp_query->queried_object) && $wp_query->queried_object->ID == get_option('page_on_front') ){
		$template = TEMPLATEPATH.'/home.php';	
	}
	
	return $template;
}
add_filter( 'template_include', 'kcmo_template_include' );

/*
*	sets variables available in template
*	attached to `parse_query` filter
*	@param WP_Query
*	@return WP_Query
*/
function kcmo_template_vars( &$wp_query ){
	if( !$wp_query->is_main_query() )
		return $wp_query;
	
	switch_to_blog( 1 );
	
	$wp_query->set( 'admin_url_main', admin_url() );
	
	$wp_query->set( 'blog_charset', get_bloginfo('charset') );
	$wp_query->set( 'blog_name', get_bloginfo('name') );
	$wp_query->set( 'blog_title', get_bloginfo('title') );
	$wp_query->set( 'blog_url', get_bloginfo('url') );

	$wp_query->set( 'stylesheet_directory', get_bloginfo('stylesheet_directory') );
	$wp_query->set( 'stylesheet_url', get_bloginfo('stylesheet_url') );
	$wp_query->set( 'template_url', get_bloginfo('template_url') );
	
	restore_current_blog();
	
	return $wp_query;
}

/*
*
*	@param int
*	@return int
*/
function kcmo_tweets_default( $has_items = 0 ){
	static $displayed = 0;
	
	if( $has_items )
		$displayed = $has_items;
		
	return $displayed;
}

/*
*	attached to `latest_tweets_render_list` to keep track of if tweets have been rendered
*	@param array
*	@param
*	@return array
*/
function kcmo_tweets_widget( $items, $screen_name ){
	if( count($items) )
		kcmo_tweets_default( 1 );
	
	return $items;
}

add_filter( 'latest_tweets_render_list', 'kcmo_tweets_widget', 10, 2 );
add_filter( 'parse_query', 'kcmo_template_vars', 20 );