<?php

/*
*	gets registered sidebar widgets from blog #1 where defaults are not set
*	attached to `pre_option_sidebars_widgets` filter
*	@param array
*	@return array
*/
function kcmo_sidebars_widgets( $sidebars ){
	global $blog_id;
	
	if( ($blog_id != 1) && isset($sidebars['wp_inactive_widgets']) ){
		$default = get_blog_option( 1, 'sidebars_widgets' );
		
		foreach( array('department-contact', 'footer-video', 'home-twitter') as $key ){
			if( !isset($sidebars[$key]) || !count($sidebars[$key]) ){
				$sidebars[$key] = $default[$key];	
			}
		}
	}
	
	return $sidebars;
}

/*
*	hide rss feed if no items
*	@param string html
*	@param array
*	@return string
*/
function kcmo_widget_hide_feed( $html, $widget ){
	if( $widget['id'] == 'rss' ){
		
		$Feed = new WP_Query( array(
			'feed' => 'atom'
		) );
		
		if( !$Feed->have_posts() )
			$html = '<!-- widget: no rss posts -->';
	}
	
	return $html;
}
add_filter( 'departments_widget', 'kcmo_widget_hide_feed', 10, 2 );

if( !is_admin() ){
	add_filter( 'pre_option_sidebars_widgets', 'kcmo_sidebars_widgets' );
	add_filter( 'option_sidebars_widgets', 'kcmo_sidebars_widgets' );
}