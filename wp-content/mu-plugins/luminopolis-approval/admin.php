<?php

namespace luminopolis_approval;

/*
*	loads php file admin-{pagenow}.php for per page specific actions/filters
*/
function admin_require(){
	global $pagenow;
	
	$file = dirname(__FILE__).'/admin-'.$pagenow;

	if( file_exists($file) )
		require $file;
}
add_action( 'admin_menu', __NAMESPACE__.'\admin_require', 9 );

/*
*	registers settings menu link in admin
*	attached to `admin_menu` action
*/
function admin_menu(){
	add_options_page( 'Approval Workflow Settings', 'Approval Workflow', 
					  'manage_options', 'approval-settings', __NAMESPACE__.'\admin_page' );
}
add_action( 'admin_menu', __NAMESPACE__.'\admin_menu' );

/*
*	sets or gets the editable post slug.  needed for the hidden accessible ui which can override
*	the slug on post reapproval
*	called witihin luminopolis_approval_post_slug() as a setter
*	attached to `editable_slug` action as a getter
*	@param string
*	@param bool not passed on `editable_slug` action
*	@return
*/
function editable_slug( $slug, $set = FALSE ){
	static $original_slug = NULL;
	if( $set )
		$original_slug = $slug;
		
	return $original_slug ? $original_slug : $slug;
}
add_filter( 'editable_slug', __NAMESPACE__.'\editable_slug' );

/*
*	emails orginal post author once editor approves and publishes to site
*	attached to `wp_insert_post` action
*	@param int
*	@param WP_Post
*	@param bool
*	@reurn
*/
function mail_on_publish( $post_id, $post, $update ){
	$author = get_userdata( $post->post_author );
	
	if( !user_can($author->ID, 'publish_posts') ){
		approval_mail( $author->data->user_email,
					   'Your post "'.$post->post_title.'" was published.', 
					   'You can view this post at '.get_permalink($post_id) );
	}
}

/*
*	shows the original permalink in post edit screen
*	@param string
*	@param WP_Post
*	@param bool
*	@return string
*/
function post_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ){
	if( $post_status == 'needs-reapproval' && !isset($_POST['samplepermalinknonce']) ){
		$post = get_post( $post_parent );
		$slug = $post->post_name;
		
		editable_slug( $slug, TRUE );
	}
	
	return $slug;
}
add_filter( 'wp_unique_post_slug', __NAMESPACE__.'\post_slug', 10, 6 );

/*
*	adds resubmitted posts into revision history since it is a new post
*	@param WP_Query
*	@return WP_Query
*/
function pre_get_posts( \WP_Query $wp_query ){
	if( $wp_query->query_vars['post_type'] == 'revision' ){
		$parent = get_post( $wp_query->query_vars['post_parent'] );
		
		if( $parent->post_status == 'needs-reapproval' )
			$in = array( $parent->post_parent, $wp_query->query_vars['post_parent'] );
		else {
			$in = array( $wp_query->query_vars['post_parent'] );
		}
			
		$in = array_filter( $in );
		
		$wp_query->query_vars['post_parent__in'] = $in;
		
		unset( $wp_query->query_vars['post_parent'] );
	}
	
	return $wp_query;
}

/*
*	handles the actions from the 3 custom buttons for approval
*	these are actions that only a user with approval levels 1 or 2 will be applied to
*	@param array info for WP_Post
*	@param array data from $_POST
*	@return array
*/
function publish_status( $wp_post, array $post_data ){
	if( !isset($post_data['luminopolis_publish']) || count($post_data['luminopolis_publish']) != 1 )
		return $wp_post;
	
	global $current_user, $wpdb;
	$nonce = array_values( $post_data['luminopolis_publish'] );
	
	switch( TRUE ){
		// a level_02 publishes post to site
		case wp_verify_nonce( $nonce[0], 'final' ):
			update_post_meta( $post_data['post_ID'], '_luminopolis_publish_status', 'publish' );
			
			$meta = approval_meta( 'published', $current_user->data->ID );
			add_post_meta( $post_data['post_ID'], '_luminopolis_approval', $meta, FALSE );
			
			$wp_post['post_status'] = 'publish';
			
			if( $post_data['original_post_status'] == 'needs-reapproval' ){
				$wp_post['ID'] = $post_data['post_ID'];
				
				$sql = $wpdb->prepare( "DELETE FROM $wpdb->posts 
										WHERE post_parent = %d
										AND post_status = 'needs-reapproval'", $wp_post['ID'] );
				$wpdb->query( $sql );
			} else {
				$post_id = parse_url( $post_data['_wp_http_referer'] );
				parse_str( $post_id['query'], $post_id );
				$post_id = $post_id['post'];
				
				$wpdb->update( $wpdb->posts, array('post_status' => 'inherit', 'post_type' => 'revision'), array('ID' => $post_id) );
			}	
			
			// email original author
			add_action( 'wp_insert_post', __NAMESPACE__.'\mail_on_publish', 10, 3 );
			
			break;
		
		// a level_01 approves post
		case wp_verify_nonce( $nonce[0], 'pending' ):
			
			update_post_meta( $post_data['post_ID'], '_luminopolis_publish_status', 'pending' );
			
			$meta = approval_meta( 'approved', $current_user->data->ID );
			add_post_meta( $post_data['post_ID'], '_luminopolis_approval', $meta, FALSE );
			
			$author = get_userdata( $wp_post['post_author'] );
			
			// email level 02's
			$to = get_emails_by_level( '02' );
			
			if( $post_data['original_post_status'] == 'needs-reapproval' )
				$wp_post['post_status'] = 'needs-reapproval';
			
			if( $post_data['original_post_status'] == 'needs-reapproval' ){
				$post_data['post_ID'] = $post_data['post_parent'];
				$post_data['post_parent'] = 0;
			}
			
			approval_mail( $to, 
						   'A new post from '.$author->data->user_nicename.' is awaiting approval', 
						   'You can view this post at '.get_edit_post_link($post_data['post_ID']) );
			
			break;
		
		// a level_01 or level_02 rejects the post	
		case wp_verify_nonce( $nonce[0], 'reject' ):
			update_post_meta( $post_data['post_ID'], '_luminopolis_publish_status', 'reject' );
			
			$meta = approval_meta( 'rejected', $current_user->data->ID );
			add_post_meta( $post_data['post_ID'], '_luminopolis_approval', $meta, FALSE );
			
			// show ui for editor -> author feedback email
			add_filter( 'redirect_post_location', __NAMESPACE__.'\redirect_reject', 10, 2 );
			
			break;
	}
	
	return $wp_post;
}
add_action( 'wp_insert_post_data', __NAMESPACE__.'\publish_status', 10, 2 );

/*
*	saves updated post as custom status, marked as needing revision
*	attached to `_wp_put_post_revision` action via wp_insert_post_data()
*	@param int
*/
function revised_approval( $revision_id ){
	if( $_POST['action'] != 'editpost' )
		return;
	
	// infinite loop
	remove_action( '_wp_put_post_revision', __NAMESPACE__.'\revised_approval' );
	
	wp_update_post( array(
		'ID' => $revision_id,
		'post_status' => 'needs-reapproval',
		'post_type' => $_POST['post_type'],
		'tax_input' => $_POST['tax_input']
	) );
	
	global $wpdb;
	$sql = $wpdb->prepare( "UPDATE $wpdb->posts 
							SET post_status = 'inherit' 
							WHERE post_parent = %d
							AND post_type = 'revision'
							AND ID != %d", $_POST['post_ID'], $revision_id );
	$wpdb->query( $sql );
}

/*
*	sends an email to the approvers on updating / submitting a post
*	@param string
*	@param string
*	@param WP_Post
*	@return
*/
function transition_post_status( $new_status, $old_status, $wp_post ){
	$author = get_userdata( $wp_post->post_author );
	$user = wp_get_current_user();
	
	if( $wp_post->post_type == 'rejected-content' )
		return;
		
	// it will do this twice for some reason.
	remove_action( 'transition_post_status', __NAMESPACE__.'\transition_post_status', 10, 3 );
	
	// email level 01's when a new post is submitted with pending status
	if( $new_status == 'pending' && in_array($old_status, array('draft', 'needs-reapproval')) && !user_can($author->ID, 'publish_posts') ){
		$to = get_emails_by_level( '01' );
		
		approval_mail( $to, 
					   'A new post "'.get_the_title($wp_post->ID).'" by '.$author->data->user_nicename.' has been submitted for review', 
					   'You can edit this at '.get_edit_post_link($wp_post->ID, 'url') );
		
		$meta = approval_meta( 'submitted', $author->data->ID );
		add_post_meta( $wp_post->ID, '_luminopolis_approval', $meta, FALSE );
			  
		return;
	}
	
	// email level 01's when a published post is edited by an author
	if( $new_status == 'inherit' && in_array($old_status, array('new')) && !user_can($author->ID, 'publish_posts') ){
		$to = get_emails_by_level( '01' );
		
		$link = approval_email_link( $wp_post->ID );
		
		approval_mail( $to, 
					   'The post "'.get_the_title($wp_post->ID).'" by '.$author->data->user_nicename.' has been edited and needs review.', 
					   'You can edit this at '.$link );
		
		$meta = approval_meta( 'edited', $author->data->ID );
		add_post_meta( $wp_post->ID, '_luminopolis_approval', $meta, FALSE );
		
		update_post_meta( $wp_post->ID, '_luminopolis_publish_status', 'resubmitted' );
		
		add_filter( 'redirect_post_location', __NAMESPACE__.'\redirect_reapproval', 10, 2 );
	}
	
	// email level 01's when a rejected post is edited by an author
	if( $new_status == 'pending' && in_array($old_status, array('pending')) && !user_can($user->ID, 'publish_posts') ){
		$to = get_emails_by_level( '01' );
		
		$link = approval_email_link( $wp_post->ID );
		
		approval_mail( $to, 
					  'The post "'.get_the_title($wp_post->ID).'" by '.$author->data->user_nicename.' has been re-submitted and needs review.', 
					  'You can edit this at '.$link );
		
		$meta = approval_meta( 'resubmitted', $author->data->ID );
		add_post_meta( $wp_post->ID, '_luminopolis_approval', $meta, FALSE );
		
		delete_post_meta( $wp_post->ID, '_luminopolis_publish_status' );
		
		add_filter( 'redirect_post_location', __NAMESPACE__.'\redirect_reapproval', 10, 2 );
	}
}
add_action( 'transition_post_status', __NAMESPACE__.'\transition_post_status', 10, 3 );

/*
*	force author edits to go through approval again
*	attached to `wp_insert_post_data` filter
*	@param array
*	@param array
*	@return array
*/
function wp_insert_post_data( $data, $postarr ){
	$user = wp_get_current_user();
	
	if( user_can($user->ID, 'publish_posts') )
		return $data;
	
	//dbug( $data, '$data' );
	//ddbug( $postarr, '$postarr' );
	
	switch( $data['post_status'] ){
		case 'pending':
			break;
			
		case 'publish':
			$data['ID'] = $postarr['ID'];
			$data = stripslashes_deep( $data );
			
			add_action( '_wp_put_post_revision', __NAMESPACE__.'\revised_approval' );
			
			_wp_put_post_revision( $data );
			break;
			
		case 'inherit':
			break;
	}
	
	return $data;
}
add_filter( 'wp_insert_post_data', __NAMESPACE__.'\wp_insert_post_data', 10, 2 );