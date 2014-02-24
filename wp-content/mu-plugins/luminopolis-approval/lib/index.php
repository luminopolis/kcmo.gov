<?php

namespace luminopolis_approval;

/*
*	render template in views/
*	can filter any piece of rendered content with 'luminopolis-approval-{$template}'
*	@param string path within views to template file, no file extention, or leading / trailing slashes
*	@param array 
*	@return string
*/
function approval_render( $template, $vars = array() ){
	extract( (array) $vars, EXTR_SKIP );
	
	ob_start();
		require __DIR__.'/../views/'.$template.'.php';
		$html = ob_get_contents();
	ob_end_clean();
	
	$html = apply_filters( 'luminopolis-approval-'.$template, $html, $vars );
	
	return $html;
}

/*
*	registers custom post status for rejected content
*	attached to `init` action
*/
function post_status(){
	register_post_status( 'needs-reapproval', array(
		'exclude_from_search' => TRUE,
		'label_count' => _n_noop( 'Needs Reapproval <span class="count">(%s)</span>', 
								  'Needs Reapproval <span class="count">(%s)</span>' ),
		'protected' => TRUE,
		'public' => FALSE
	) );
}
add_action( 'init', __NAMESPACE__.'\post_status' );

/*
*	registers custom post type to handle rejection feedback
*	attached to `init` action
*/
function register_post_type(){
	if( current_user_can('publish_posts') ){
		\register_post_type( 'rejected-content', array(
			//'capabilities' => array('publish_post'),
			'exclude_from_search' => TRUE,
			'labels' => array('add_new_item' => 'Content Rejected',
							  'name' => 'Rejected Content'),
			'public' => FALSE,
			'publicly_queryable' => FALSE,
			'show_in_admin_bar' => FALSE,
			'show_in_nav_menus' => FALSE,
			'show_in_menu' => 'tools.php',
			'show_ui' => TRUE
		) );
	} 
}
add_action( 'init', __NAMESPACE__.'\register_post_type' );