<?php

/*
*	renders a php template file with varaibles
*	currently only used in admin
*/
function kcmo_blog_render( $template, $vars = array() ){
	extract( (array) $vars, EXTR_SKIP );
	require dirname( __FILE__ ).'/'.$template.'.php';
}

/*
*
*/
function kcmo_blog_submitbox(){
	global $post;
	
	if( !kcmo_blog_post_supports_author($post) )
		return;
	
	$checked = get_post_meta( $post->ID, '_author_in_url', TRUE );
	
	$vars = array(
		'checked' => $checked ? 'checked="checked"' : ''
	);
	echo kcmo_blog_render( 'admin-submitbox', $vars );
}
add_action( 'post_submitbox_misc_actions', 'kcmo_blog_submitbox' );

/*
*
*	@param int
*	@param WP_Post
*/
function kcmo_blog_save_post( $post_id, $post ){
	if( !kcmo_blog_post_supports_author($post) )
		return;
		
	$author_in_url = isset( $_POST['author_in_url'] ) ? $_POST['author_in_url'] : 0;
	update_post_meta( $post->ID, '_author_in_url', $author_in_url );
}	
add_action( 'save_post', 'kcmo_blog_save_post', 10, 2 );