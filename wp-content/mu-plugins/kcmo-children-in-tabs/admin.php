<?php

/*
*	setup admin actions and filters
*	attached to `load-post.php` action to only load plugin on post edit screen
*	@return NULL
*/
function kcmo_children_admin_bootstrap(){
	add_action( 'save_post', 'kcmo_children_admin_save' );
	add_filter( 'wp_enqueue_media', 'kcmo_children_admin_init' );	
}
add_action( 'load-post.php', 'kcmo_children_admin_bootstrap' );

/*
*	enqueues javascript and filter for tab checkbox
*	attached to `wp_enqueue_media` action - earliest $post seems available
*	@return NULL
*/
function kcmo_children_admin_init(){
	global $post;
	
	if( is_post_type_hierarchical($post->post_type) && !in_array($post->post_type, array('bannerspace_post')) ){
		add_action( 'admin_enqueue_scripts', 'kcmo_children_admin_js' );
		
		add_filter( 'wp_dropdown_pages', 'kcmo_children_admin_checkbox' );
	}
}

/*
*	
*	attached to `wp_dropdown_pages` action
*	@param string html
*	@return string html
*/
function kcmo_children_admin_checkbox( $html ){
	global $post;
	
	$checked = $post->post_parent > 0 && get_post_meta( $post->ID, '_show_in_tab', TRUE ) ? 'checked="checked"' : '';
	$title = get_post_meta( $post->ID, '_tab_title', TRUE );

	$html .= '<p id="kcmo-show-tab"><strong>Show in tab on parent page?</strong>
			  <input type="checkbox" name="_show_in_tab" value="1" '.$checked.'/></p>

			  <p id="kcmo-tab-title"><strong>Tab Title</strong>
			  <input type="text" name="_tab_title" value="'.esc_attr( $title ).'" placeholder=""/></p>';

	return $html;
}

/*
*	adds javascript to post edit screen
*	attached to `admin_enqueue_scripts` action
*	@return NULL
*/
function kcmo_children_admin_js(){
	wp_register_script( 'kcmo-children', plugins_url('/admin.js', __FILE__), array('jquery') );
	wp_enqueue_script( 'kcmo-children' );
}

/*
*	saves show in tab option as post meta
*	attached to `save_post` action
*	@return NULL
*/
function kcmo_children_admin_save( $post_id ){
	update_post_meta( $post_id, '_show_in_tab', post('_show_in_tab') );
	update_post_meta( $post_id, '_tab_title', post('_tab_title') );
}