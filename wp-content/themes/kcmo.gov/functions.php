<?php

define( 'KCMO_THEME_VER', '0.9.2' );

add_image_size( 'banner-top', 980, 399, TRUE );
add_image_size( 'banner-side', 258, 274, TRUE );

add_image_size( 'front-column', 187, 126, TRUE );

// strip generator
remove_action( 'wp_head', 'wp_generator' );

/*
*
*	attached to `wp_enqueue_scripts` action
*/
function kcmo_add_scripts(){
	global $wp_query;
	$bootstrap = WP_DEBUG ? '/dist/js/bootstrap.js' : '/dist/js/bootstrap.min.js';
	$plugins = WP_DEBUG ? '/_ui/js/plugins.js' : '/_ui/js/plugins.min.js';
	
	$stylesheet_directory = $wp_query->get( 'stylesheet_directory' );
	
	wp_register_script( 'bootstrap', $stylesheet_directory.$bootstrap, 
						array(), KCMO_THEME_VER, TRUE );
	wp_enqueue_script( 'bootstrap' );
	
	wp_register_script( 'plugins', $stylesheet_directory.$plugins, 
						array('bootstrap'), KCMO_THEME_VER, TRUE );
	wp_enqueue_script( 'plugins' );
	
	wp_register_script( 'main', $stylesheet_directory.'/_ui/js/main.js', 
						array('bootstrap'), KCMO_THEME_VER, TRUE );
	wp_enqueue_script( 'main' );
	
	wp_register_script( 'main', $stylesheet_directory.'/_ui/js/jquery-1.10.2.min.js', 
						array(), KCMO_THEME_VER, TRUE );
	wp_enqueue_script('jquery' );
}
add_action( 'wp_enqueue_scripts', 'kcmo_add_scripts' );

/*
*
*	attached to `wp_enqueue_scripts` action
*/
function kcmo_bannerspace_styles(){
	wp_dequeue_style( 'bannerspace-styles' );
	wp_enqueue_style( 'bannerspace-styles-custom',	get_bloginfo('stylesheet_directory').'/_ui/css/bannerspace.css' );
}
add_action( 'wp_enqueue_scripts', 'kcmo_bannerspace_styles', 11 );

/*
*	per site settings for appearance/customize in admin
*	@param WP_Customize_Manager
*	@return
*/
function kcmo_customize_register( WP_Customize_Manager $wp_customize ) {
	// theme heading color
	$wp_customize->add_setting( 'kcmo_theme_options[heading_color]',
		array(
			'default' => '#38BFC3', 
			'type' => 'option',						// 'option' or 'theme_mod'
			'capability' => 'edit_theme_options', 	// special permissions for accessing this setting.
			'transport' => 'refresh', 			// triggers a refresh of the setting 'refresh' or 'postMessage'
		) 
	);  
      
	$wp_customize->add_control( new WP_Customize_Color_Control( 
		$wp_customize, 	
		'kcmo_theme_link_textcolor', 				// set a unique ID for the control
		array(
			'label' => 'Heading Color', 
			'section' => 'colors', 					// ID of the section this control should render in
			'settings' => 'kcmo_theme_options[heading_color]', // which setting to load and manipulate (serialized is okay)
			'priority' => 10, 						// determines the order this control appears in for the specified section
		) 
	) );
}
add_action( 'customize_register', 'kcmo_customize_register' );

/*
*	enqueues main style and custom site colors
*	attached to `wp_enqueue_scripts` action
*/
function kcmo_customize_output(){
	static $theme_options = NULL;
	
	if( !$theme_options )
		$theme_options = get_option( 'kcmo_theme_options' );
	
	$heading_color = isset( $theme_options['heading_color'] ) ? $theme_options['heading_color'] : '#38BFC3';
	
	wp_enqueue_style(
		'main',
		get_template_directory_uri() . '/_ui/css/main.css'
	);

	$custom_css = "
.custom_bk{ background-color: $heading_color !important; }
#flag.custom_bk:after{ border-color: transparent transparent transparent $heading_color !important; }
.custom_heading{ color: $heading_color !important; }
	";
	wp_add_inline_style( 'main', trim( $custom_css ));
}
add_action( 'wp_enqueue_scripts', 'kcmo_customize_output' );

/*
*	add color classes and sortable ids to footer bookmark li elements
*	attached to `wp_list_bookmarks` action
*	@param string
*	@return string
*/
function kcmo_footer_links_callout_colors( $html ){
	$html = explode( "</li>", $html );
	
	foreach( $html as $k => &$li ){
		preg_match( '/data-link-id="([0-9]+)"/', $li, $link_id );
		
		if( isset($link_id[1]) ){
			// remove data-link-id from anchor
			$li = preg_replace( '/data-link-id="([0-9]+)"/', '', $li );
			
			// add data-sortable-id to list
			$li = str_replace( '<li>', '<li data-sortable-id="'.$link_id[1].'" class="color-'.( $k + 1 ).'">', $li );
		}
	}
	
	$html = implode( "</li>", $html );
	
	return $html;
}

/*
*	force links in bottom right footer callout to open in new tab
*	hack link_id into anchor attribute
*	attached to `get_bookmarks` filter
*	@param array
*	@param 
*	@return
*/
function kcmo_footer_links_callout_targets( $bookmarks, $args ){
	
	foreach( $bookmarks as &$bookmark )
		// awful hack to get link ids in anchor element
		$bookmark->link_target = '_blank" data-link-id="'.$bookmark->link_id ;
	
	return $bookmarks;
}

/*
*	add color classes to footer bookmark li elements
*	attached to `wp_list_bookmarks` action
*	@param string
*	@return string
*/
function kcmo_footer_links_main( $html ){
	if( !trim($html) )
		return $html;
		
	$html = explode( "</li>", $html );
	$html = array_filter( $html, 'trim' ); 
	
	foreach( $html as $k => &$li ){
		preg_match( '/data-link-id="([0-9]+)"/', $li, $link_id );
		
		if( isset($link_id[1]) ){
			// remove data-link-id from anchor
			$li = preg_replace( '/data-link-id="([0-9]+)"/', '', $li );
			
			// add data-sortable-id to list
			$li = str_replace( '<li>', '<li data-sortable-id="'.$link_id[1].'" class="color-'.( $k + 1 ).'">', $li );
		}
	}
	
	// put into 3 columns
	$items_in_col = ceil( count($html) / 3 );
	
	$columns = array_chunk( $html, $items_in_col );
	
	foreach( $columns as &$column ){
		$column = '<ul>'.implode( '</li>', $column ).'</li></ul>';
	}
	
	$html = implode( '', $columns );
	
	return $html;
}

/*
*
*	attached to `latest_tweets_render_tweet` filter
*	@param string the tweet, with markup
*	@param string <time> element
*	@param string url
*	@param array raw data
*	@return string html
*/
function kcmo_latest_tweets_render_tweet( $html, $date, $link, array $tweet ){
	$pic = $tweet['user']['profile_image_url_https'];
	return '<div class="ico"></div>
			<h5>'.$tweet['user']['screen_name'].'</h5>
			<p>'.$html.'</p>';
}
add_filter( 'latest_tweets_render_tweet', 'kcmo_latest_tweets_render_tweet', 10, 4 );

/*
*	remove default title markup in latest tweets widget
*	attahed to `widget_title` filter
*	@param string
*	@param array
*	@param string
*	@return string
*/
function kcmo_latest_tweets_title( $title, $instance, $id_base ){
	if( $id_base != 'latest_tweets_widget' )
		return $title;
	
	$screen_name = isset( $instance['screen_name'] ) ? $instance['screen_name'] : 'kcmo';
	$title = '<a class="btn-follow" href="https://twitter.com/intent/user?screen_name='.$screen_name.'">Follow</a>
			  <h4>Latest Tweets</h4>';
	
	return $title;
}
add_filter( 'widget_title', 'kcmo_latest_tweets_title', 10, 3 );

/*
*
*	@return string html
*/
function kcmo_menu_footer(){
	switch_to_blog( 1 );
	
	$html = wp_nav_menu( array(
		'container' => '',
		'echo' => false,
		'items_wrap' => '%3$s',
		'menu' => 'Footer',
		'walker' => new KCMO_Walker_Footer_Menu
	) );
	
	if( current_user_can('manage_links') ){
		global $wp_query;
		$admin_url_main = $wp_query->get( 'admin_url_main' );
		$menu = get_term_by( 'name', 'Footer', 'nav_menu' );
		
		if( $menu )
			$html .= '</p><p><a class="edit_links" href="'.$admin_url_main.'nav-menus.php?action=edit&menu='.$menu->term_id.'">Manage Footer Menu</a>';
	}
	
	restore_current_blog();
	
	return $html;
}

/*
*	workaround for getting current site url in nav menu after switching to blog #1
*	@param
*	@return string
*/
function kcmo_menu_home( $url = NULL ){
	static $menu_home;
	if( $url )
		$menu_home = $url;
		
	return $menu_home;
}

/*
*
*	@param string
*	@return
*/
function kcmo_menu_links( $category_name ){
	wp_list_bookmarks( array(
		'categorize' => FALSE,
		'category_name' => $category_name,
		'orderby' => 'rating',
		'title_li' => NULL
	) );
}

/*
*	generates the main menu - dropdowns are stored in kcmo_menu_secondary
*	@return string
*/
function kcmo_menu_main(){
	$nav_walker = kcmo_menu_nav();
	
	kcmo_menu_home( site_url('/') );
	
	switch_to_blog( 1 );
	
	$html = wp_nav_menu( array(
		'container_class' => 'container',
		'echo' => false,
		'items_wrap' => '<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse"> 
							 <span class="icon-bar"></span>
							 <span class="icon-bar"></span>
							 <span class="icon-bar"></span>
						 </button>
						 
						 <div class="search">
						 	
							<form action="'.home_url( '/' ).'">
								<input type="submit" value="">
								<input type="text" name="s" id="main-search" value="'.stripslashes(get_search_query()).'">
							</form>'.
							
							/* '<div class="gcse-searchbox-only" data-resultsUrl="'.home_url( '/' ).'" data-newWindow="false" data-queryParameterName="s"></div>'. */
							
						 '</div>
						 <a class="navbar-brand" href="'.home_url( '/' ).'">CITY OF KANSAS CITY HOME</a>
						 <div class="navbar-collapse collapse">
						 	<ul id="%1$s" class="%2$s">
						 		%3$s
						 		
						 		<!-- new line -->
						 		<div class="search">
							 		<form action="'.home_url( '/' ).'">
										<input type="submit" value="">
										<input type="text" name="s" id="secondary-search" value="'.stripslashes(get_search_query()).'">
									</form>
								</div>
						 	</ul>',
		'menu' => 'HeadMenu',
		'menu_class' => 'nav navbar-nav',
		'theme_location' => 'header-menu',
		'walker' => $nav_walker
	) );
	
	restore_current_blog();
	
	return $html;
}

/*
*
*/
function kcmo_menu_secondary(){
	$menu_nav = kcmo_menu_nav();
	
	return $menu_nav->secondary_output;
}

/*
*	gets class indicating color of selected item in main menu
*	@return string
*/
function kcmo_menu_color(){
	$menu_nav = kcmo_menu_nav();
	$color = $menu_nav->selected_color ? $menu_nav->selected_color : 0;
	
	return 'color-'.$color;
}

/*
*	holds static instance of navigation walker
*	@return KCMO_Walker_Nav_Menu
*/
function kcmo_menu_nav(){
	static $menu_nav;
	
	if( !$menu_nav )
		$menu_nav = new KCMO_Walker_Nav_Menu;
	
	return $menu_nav;
}

/*
*	fix for links on main menu linking to wrong subsite 
*	using blog 1 for all sites, breaks because of wp using globals for blog id 
*	and conflicts with luminopolis_network_home_url()
*	@param WP_Post
*	@return WP_Post
*/
function kcmo_menu_setup( $menu_item ){
	if( $menu_item->object != 'page' )
		return $menu_item;
		
	$post = get_post($menu_item->object_id);
	
	$menu_item->url = trailingslashit( get_home_url(1, $post->post_name) );
	
	return $menu_item;
}
add_filter( 'wp_setup_nav_menu_item', 'kcmo_menu_setup' );

/*
*	registers department contact widget
*	registers twitter feed widget
*	attached to `widgets_init` action
*/
function kcmo_widgets_init(){
	register_sidebar( array(
		'name' => 'Department Contact',
		'id' => 'department-contact',
		'before_widget' => '',
		'after_widget' => '',
		'before_title' => '',
		'after_title' => '',
	) );
	
	register_sidebar( array(
		'name' => 'Home Page Twitter Feed',
		'id' => 'home-twitter',
		'before_widget' => '',
		'after_widget' => '',
		'before_title' => '',
		'after_title' => '',
	) );
	
	register_sidebar( array(
		'name' => 'Footer Video',
		'id' => 'footer-video',
		'before_widget' => '',
		'after_widget' => '',
		'before_title' => '',
		'after_title' => '',
	) );
}
add_action( 'widgets_init', 'kcmo_widgets_init' );
