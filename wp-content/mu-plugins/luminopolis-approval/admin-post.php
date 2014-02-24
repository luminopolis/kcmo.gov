<?php

namespace luminopolis_approval;

/*
*	shows a visual history of the workflow in the post edit screen
*	@param string
*	@param WP_Post
*/
function approval_metabox_history( $post_type, \WP_Post $post ){
	if( !current_user_can('publish_posts') )
		return;
	
	if( in_array($post_type, array('rejected-content')) )
		return;
	
	if( !post_supports_approval($post_type) )
		return;
			
    add_meta_box( 
        'approval-history',
        'Approval History',
        __NAMESPACE__.'\approval_metabox_history_render',
        $post_type,
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', __NAMESPACE__.'\approval_metabox_history', 10, 2 );

/*
*	shows meta box with history of approvals / submissions
*	@param WP_Post
*	@param array
*/
function approval_metabox_history_render( $post, $attrs ){
	$post_id = $post->post_status == 'needs-reapproval' ? $post->post_parent : $post->ID;
	$approvals = approval_get_history( $post_id );
	
	$vars = array(
		'approvals' => $approvals
	);
	
	echo approval_render( 'admin/post-meta-history', $vars );
}

/*
*	run on final post repproval, sets the post id and nonce back to its parent 
*	
*/
function approval_republished(){
	if( !isset($_POST['luminopolis_publish']) || !isset($_POST['luminopolis_publish']['final']) )
		return;
		
	if( !wp_verify_nonce($_POST['luminopolis_publish']['final'], 'final') )
		return;
		
	global $wpdb;
	$sql = $wpdb->prepare( "SELECT post_parent 
							FROM $wpdb->posts 
							WHERE ID = %d LIMIT 1", $_POST['post_ID'] );
	$parent = $wpdb->get_var( $sql );
	
	if( !$parent )
		return;
		
	$_REQUEST['_wpnonce'] = wp_create_nonce( 'update-post_'.$parent );
	$_POST['post_ID'] = $parent;
}
add_action( 'load-post.php', __NAMESPACE__.'\approval_republished' );

/*
*	renders custom publish buttons
*	attached to `post_submitbox_start` action
*/
function post_ui_inputs(){
	global $post;
	
	switch( TRUE ){
		case !post_supports_approval( $post->post_type );
		//case !user_needs_approval():
			break;
			
		// reapproval for published posts
		case in_array( $post->post_status, array('pending', 'publish') ) && !current_user_can('publish_posts'):
			$vars = array();
			echo approval_render( 'admin/post-submitbox-author', $vars );
			break;
		
		// approval / rejection buttons	
		case in_array( $post->post_status, array('pending', 'needs-reapproval') ) && current_user_can('publish_posts'):
			$vars = array(
				'can_approve_final' => current_user_can('luminopolis_approval_level_02'),
				'can_approve_initial' => current_user_can('luminopolis_approval_level_01')
			);
			
			echo approval_render( 'admin/post-submitbox', $vars );
			break;
	}
}
add_action( 'post_submitbox_start', __NAMESPACE__.'\post_ui_inputs' );

/*
*	registers styleshhet and starts output buffer to remove default publish button
*	attached to `load-post.php` and `load-post-new.php` actions
*/
function post_ui(){
	wp_register_style( 'luminopolis-approval', LUMINOPOLIS_APPROVAL_PLUGINS_URL.'/public/admin/post.css' );
	wp_enqueue_style( 'luminopolis-approval' );
	
	// dont show on save post
	if( !count($_POST) )
		ob_start();
}
add_action( 'load-post.php', __NAMESPACE__.'\post_ui' );
add_action( 'load-post-new.php', __NAMESPACE__.'\post_ui' );

/*
*	gets the output buffer contents and removes default publishing action
*	attached to `admin_footer-load-post.php` and `admin_footer-load-post-new.php` actions
*/
function post_ui_end(){
	$html = ob_get_contents();
	ob_end_clean();
	
	global $post;
	
	// <div id="publishing-action">
	switch( TRUE ){
		//
		//case !user_needs_approval():
		case !post_supports_approval( $post->post_type ):
			break;
			
		// author, replace with update ui
		case !current_user_can('publish_posts') && in_array( $post->post_status, array('pending', 'publish') ):
		// admin user, replace publish button with approve/reject ui
		case current_user_can('publish_posts') && in_array($post->post_status, array('pending', 'needs-reapproval') ):
			$html = preg_replace( '/<div id="publishing-action">(.+?)<\/div>/s', '', $html );
			$html = str_replace( '<div id="OB_REMOVEpublishing-action">', '<div id="publishing-action">', $html );
			break;
	}
		
	echo $html;
}
add_action( 'admin_footer-post.php', __NAMESPACE__.'\post_ui_end' );
add_action( 'admin_footer-post-new.php', __NAMESPACE__.'\post_ui_end' );


add_filter( 'pre_get_posts', __NAMESPACE__.'\pre_get_posts', 10, 2 );

/*
*	sets up default title and content for rejection form
*	attached to `edit_form_top` action
*	uses filter 'luminopolis-approval/rejection-template'
*	uses filter 'luminopolis-approval/rejection-title'
*	@param WP_Post
*/
function post_ui_rejection_setup( \WP_Post $post ){
	if( $post->post_type != 'rejected-content' )
		return;
	
	if( in_array($post->post_status, array('draft','publish')) )
		$reject_id = (int) get_post_meta( $post->ID, '_reject-id', TRUE );
	elseif( isset($_GET['reject-id']) )
		$reject_id = (int) $_GET['reject-id'];
		
	$post->_reject_id = $reject_id;
		
	$rejected_content = get_post( $reject_id );
	$original_content = get_post( $rejected_content->post_parent );
	
	remove_meta_box( 'submitdiv', 'rejected-content', 'side' );
	add_meta_box( 'feedback-div', 'Send Feedback', __NAMESPACE__.'\post_ui_rejection_render', 
				  null, 'side', 'core', (object) array('reject_id' => $reject_id) ); 
	
	$current_user = wp_get_current_user();
	$current_user_meta = array_filter_recursive( get_user_meta($current_user->ID) );
	
	$options = (array) get_option( 'luminopolis_approval_options' );
	
	$vars = array(
		'current_user' => $current_user,
		'current_user_meta' => $current_user_meta,
		'edit_url' => get_edit_post_link( $rejected_content->ID ),
		'original_content' => $original_content,
		'rejected_content' => $rejected_content
	);
	
	if( isset($options['misc']['rejection_email']) && trim($options['misc']['rejection_email']) )
		$vars['rejection_email'] = $options['misc']['rejection_email'];
	else
		$vars['rejection_email'] = $current_user->data->user_email;
	
	if( isset($options['misc']['rejection_from']) && trim($options['misc']['rejection_from']) )
		$vars['rejection_from'] = $options['misc']['rejection_from'];
	elseif( isset($current_user_meta['first_name']) && isset($current_user_meta['last_name']) )
		$vars['rejection_from'] = $current_user_meta['first_name'][0].' '.$current_user_meta['last_name'][0];
	else
		$vars['rejection_from'] = $current_user_meta['nickname'][0];
		
	if( !trim($post->post_content) )
		$post->post_content = apply_filters( 'luminopolis-approval/rejection-template',
											 approval_render('admin/rejection-template', $vars),
											 $vars );	  
	
	if( !trim($post->post_title) )
		$post->post_title = apply_filters( 'luminopolis-approval/rejection-title', 
										   'Your content, "'.$rejected_content->post_title.'" was rejected.', 
										   $vars );
}
add_action( 'edit_form_top', __NAMESPACE__.'\post_ui_rejection_setup' );

/*
*	attached to `save_post_rejected-content` action
*	@param int
*	@param WP_Post
*	@param bool
*	@return
*/
function save_rejected_content( $post_id, \WP_Post $post, $update = FALSE ){
	if( !$update || !isset($_POST['reject-id']) )
		return;
		
	update_post_meta( $post_id, '_reject-id', $_POST['reject-id'] );
	
	if( isset($_POST['publish_and_send']) ){
		$rejected_content = get_post( $_POST['reject-id'] );
		
		$author_email = get_the_author_meta( 'email', $rejected_content->post_author );
		$editor_email = get_the_author_meta( 'email', $post->post_author );
		
		$headers = array( "Reply-To: ".$editor_email, "Content-type: text/html" );
		
		approval_mail( $author_email, $post->post_title, wpautop($post->post_content), $headers );
		
		// dont use wp_update_post here, causes loop
		global $wpdb;
		$wpdb->update( $wpdb->posts, array('post_status' => 'publish'), array('ID' => $post_id) );
	}
}
add_action( 'save_post_rejected-content', __NAMESPACE__.'\save_rejected_content', 10, 3 );

/*
*	renders the 'Send Feedback' meta box in place of stock 'Publish' 
*	@param WP_Post
*	@param array
*	@return
*/
function post_ui_rejection_render( \WP_Post $post, array $callback ){
	// the rejected post
	$rejected_content = get_post( $post->_reject_id );
	
	$vars = array(
		'author_email' => get_the_author_meta( 'email', $rejected_content->post_author ),
		'reject_id' => $callback['args']->reject_id,
		'reject' => get_post( $callback['args']->reject_id )
	);
	
	echo approval_render( 'admin/feedback-div', $vars );
}

/*
*	redirects user to new version of post that needs approval on edit/resubmit
*	attached to `redirect_post_location` filter
*	@param string
*	@param int 
*	@return string
*/
function redirect_reapproval( $location, $post_id ){
	global $wpdb;
		
	$sql = $wpdb->prepare( "SELECT ID 
							FROM $wpdb->posts 
							WHERE post_parent = %d
							AND post_status = 'needs-reapproval'
							ORDER BY ID DESC
							LIMIT 1", $post_id );
	$post_id = $wpdb->get_var( $sql );
	$location = get_edit_post_link( $post_id, 'url' );
	
	return $location;
}

/*
*	redirects user to feedback form on rejection
*	attached to `redirect_post_location` filter
*	@param string
*	@param int 
*	@return string
*/
function redirect_reject( $location, $post_id ){
	$location = admin_url( 'post-new.php?post_type=rejected-content&reject-id='.$post_id.'&_nonce='.wp_create_nonce('reject-'.$post_id) );
	
	return $location;
}

