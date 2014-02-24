<?php
/*
*	logic that reorders items on ajax call
*/
function kcmo_frontend_order_ajax(){
	global $blog_id, $wpdb;
	$res = (object) array(
		'success' => FALSE
	);
	
	$order = (array) post( 'order' );
	
	switch( post('id') ){
		// post ids
		case 'footer_bottom':
		case 'page-tabs':
			$res->user_can_edit_posts = current_user_can( 'edit_posts' );
			if( !current_user_can('manage_links') )
				continue;
				
			foreach( $order as $i => $post_id ){
				$sql = $wpdb->prepare( "UPDATE $wpdb->posts SET menu_order = %d 
										WHERE ID = %d LIMIT 1", $i, $post_id );
				$wpdb->query( $sql );
			}
			
			$res->blog_id = (int) $blog_id;
			$res->success = TRUE;
			break;
		
		// links, using 'rating' as order
		case 'footer_links_main':
		case 'footer_side':
			$res->user_can_manage_links = current_user_can( 'manage_links' );
			if( !current_user_can('manage_links') )
				continue;
				
			switch_to_blog( 1 );
			
			foreach( $order as $i => $link_id ){
				$sql = $wpdb->prepare( "UPDATE $wpdb->links SET link_rating = %d 
										WHERE link_id = %d LIMIT 1", $i, $link_id );
				$wpdb->query( $sql );
			}
			
			$res->blog_id = 1;
			$res->success = TRUE;
			break;
			
		default:
			break;
	}
	
	header( 'Content-Type: application/json' );
	echo json_encode( $res );
	die();
}
add_action( 'wp_ajax_update-menu-order', 'kcmo_frontend_order_ajax' );