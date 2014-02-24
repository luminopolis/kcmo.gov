<?php

/*
*	array_map callback
*	gets the relevant info back from mailchimp lists api
*/
function luminopolis_mailchimp_filter_list( $r ){
	return (object) array(
		'id' => $r['id'],
		'name' => $r['name']
	);
}

/*
*	creates or updates mailchimp campaign on post save
*	attached to `save_post_mailing_list` action
*	@param int
*	@param WP_Post
*	@param bool
*/
function luminopolis_mailchimp_save( $post_id, WP_Post $post, $update ){
	require_once dirname( __FILE__ ).'/lib/src/Mailchimp.php';
	$mc = luminopolis_mailchimp_api();
	
	$campaign_id = trim( get_post_meta($post_id, '_mc_campaign_id', TRUE) );
	$list_id = isset( $_POST['mc_list_id'] ) ? $_POST['mc_list_id'] : NULL;
	
	update_post_meta( $post_id, '_mc_list_id', $list_id );
	
	// save tweet
	$tweet_text = isset( $_POST['mc_tweet_text'] ) ? $_POST['mc_tweet_text'] : NULL;
	update_post_meta( $post_id, '_mc_tweet_text', $tweet_text );
	
	delete_transient( 'mailchimp_list_'.$campaign_id );
	
	if( !$list_id )
		return FALSE;
	
	$from_email = get_option( 'mailchimp_from_email' );
	$from_name = get_option( 'mailchimp_from_name' );
	
	$options = array(
		'list_id' => $list_id,
		'subject' => $post->post_title,
		'from_email' => $from_email,
		'from_name' => $from_name,
		'to_name' => 'Eric'
	);
	
	$content = array(
		'html' => $post->post_content,
		'sections' => '',
		'text' => strip_tags($post->post_content),
		//'url' => '',
	);
	
	$errors = array( 'mailchimp' => array(), 'twitter' => array() );
	
	if( !$campaign_id ){
		// new campaign
		try{
			$ok = $mc->campaigns->create( 'regular', $options, $content );
			$campaign_id = $ok['id'];
			
			update_post_meta( $post_id, '_mc_campaign_id', $campaign_id );
		} catch (Exception $e){
			$errors['mailchimp'][] = $e->getMessage();
		}
	} else {
		// update campaign
		try{
			$ok = $mc->campaigns->update( $campaign_id, 'options', $options );
		} catch (Exception $e){
			$errors['mailchimp'][] = $e->getMessage();
		}
		
		try{
			$ok = $mc->campaigns->update( $campaign_id, 'content', $content );
		} catch (Exception $e){
			$errors['mailchimp'][] = $e->getMessage();
		}
	}
	
	// tweet if check and no errors
	if( !count($errors['mailchimp']) && isset($_POST['mailchimp_tweet_do']) ){
		$twitter = array(
			'status' => get_post_meta( $post_id, '_mc_tweet_text', TRUE )
		);

		try{
			$res = twitter_api_post( 'statuses/update', $twitter );
			
			if( isset($res['id_str']) )
				update_post_meta( $post_id, '_mc_tweet_status', $res );
			elseif( $res['error'] )
				$errors['twitter'][] = $res['error'];
		} catch( Exception $e ){
			$errors['twitter'][] = $e->getMessage();
		}
	}
	
	flash( 'luminopolis_mailchimp_errors', $errors );
}	
add_action( 'save_post_mailing_list' , 'luminopolis_mailchimp_save', 10, 3 );

/*
*	render mailchimp settings in new/edit publish box
*	attached to `post_submitbox_misc_actions` action
*/
function luminopolis_mailchimp_submitbox(){
	global $post;
	
	if( $post->post_type != 'mailing_list' )
		return;
		
	$mc = luminopolis_mailchimp_api();
	
	$campaign_id = get_post_meta( $post->ID, '_mc_campaign_id', TRUE );
	$list = luminopolis_mailchimp_getlist( $campaign_id );
	
	// get all mailing lists
	$lists = luminopolis_mailchimp_getlists();
			
	// get sent/scheduled status
	if( count($list['data']) && $campaign_id ){
		$status = $list['data'][0]['status'];
		$status_msg = luminopolis_mailchimp_campaign_status( $list );
	} else {
		$status = '';
		$status_msg = 'Not saved to Mailchimp';
	}
	
	// make sure latest tweets is active and get api config
	if( function_exists( 'twitter_api_post') )
		$twitter_integration = luminopolis_mailchimp_twitter_config();
	else 
		$twitter_integration = false;
	
	$tweet = get_post_meta( $post->ID, '_mc_tweet_text', TRUE );
	$tweet_status = get_post_meta( $post->ID, '_mc_tweet_status', TRUE );
	
	$vars = array(
		'campaign_id' => $campaign_id,
		'errors' => (array) flash('luminopolis_mailchimp_errors'),
		'lists' => array_map( 'luminopolis_mailchimp_filter_list' , (array) $lists['data'] ),
		'selected_list' => get_post_meta( $post->ID, '_mc_list_id', TRUE ),
		'status' => $status,
		'status_msg' => $status_msg,
		'tweet' => $tweet ? $tweet : get_permalink( $post->ID ),
		'tweet_link' => isset($tweet_status['id_str']) ? ' - <a target="_blank" href="https://twitter.com/'.$tweet_status['user']['screen_name'].'/status/'.$tweet_status['id_str'].'">View Tweet</a>' : '',
		'tweeted' => isset($tweet_status['id_str']) ? 'checked="checked" disabled="disabled"' : '',
		'twitter_integration' => $twitter_integration
	);
	
	try{
		$me = twitter_api_get('account/verify_credentials' );
		$vars['twitter_name'] = 'as @'.$me['screen_name'];
	} catch( Exception $e ){
		$vars['twitter_name'] = FALSE;
	}
	
	wp_register_style( 'mailchimp-admin-style', plugins_url('public/admin/post.css', __FILE__) );
	wp_enqueue_style( 'mailchimp-admin-style' );
		
	echo luminopolis_mailchimp_render( 'views/admin/post-submitbox', $vars );
}
add_action( 'post_submitbox_misc_actions', 'luminopolis_mailchimp_submitbox' );

/*
*	gets info from the twitter help api
*	currently only used to get the number of characters in a new short url
*	@return object
*/
function luminopolis_mailchimp_twitter_config(){
	$config = get_transient( 'mailchimp_list_twitter_config' );
	if( !$config ){
		try{
			$config = twitter_api_get( 'help/configuration' );
			set_transient( 'mailchimp_list_twitter_config', $config, 86400 );
		} catch( Exception $e ){
			return FALSE;
		}
	}
	
	$data = (object) array(
		'short_url_length' => $config['short_url_length']
	);
	
	return $data;
}