<?php

/*
*	insert Mailchimp column heading into posts edit main table
*	@param array
*	@return array
*/
function luminopolis_mailchimp_edit_column_head( $defaults ){
	$new = array_splice( $defaults, 0, 2 );
	$new = array_merge( $new, array('mailchimp_status' => 'Mailchimp Status'), $defaults );
	
	return $new;
}
add_filter( 'manage_mailing_list_posts_columns', 'luminopolis_mailchimp_edit_column_head' );  

/*
*	display Mailchimp status in post edit main table column
*	@param string
*	@param int
*/
function luminopolis_mailchimp_edit_column_content( $column_name, $post_id ){
	if( $column_name != 'mailchimp_status' )
		return;
		
	$campaign_id = get_post_meta( $post_id, '_mc_campaign_id', TRUE );
	if( $campaign_id ){
		$list = luminopolis_mailchimp_getlist( $campaign_id );
		echo luminopolis_mailchimp_campaign_status( $list );
	} else
		echo '&nbsp;';
}
add_action( 'manage_mailing_list_posts_custom_column', 'luminopolis_mailchimp_edit_column_content', 10, 2 );  