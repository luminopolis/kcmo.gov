<?php

namespace luminopolis_approval;

require dirname(__FILE__).'/admin-post.php';

/*
*	do not allow new rejection posts without a post they are referencing
*	attached to `load-post-new.php` action
*/
function post_new_setup(){
	if( isset($_GET['post_type']) && $_GET['post_type'] == 'rejected-content' && !isset($_GET['reject-id']) ){
		wp_redirect( admin_url() );
		die();
	}
}
add_action( 'load-post-new.php', __NAMESPACE__.'\post_new_setup' );