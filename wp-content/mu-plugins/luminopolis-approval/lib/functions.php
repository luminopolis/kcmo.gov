<?php

namespace luminopolis_approval;

/*
*	taken from http://www.php.net/manual/en/function.array-filter.php#87581
*	@param array
*	@return array
*/
function array_filter_recursive( $input ){ 
	foreach( $input as &$value ){ 
		if( is_array($value) ){ 
			$value = array_filter_recursive( $value ); 
		} 
	} 
	
	return array_filter( $input ); 
} 

// http://ch1.php.net/manual/en/function.array-replace-recursive.php#92574
if( !function_exists('array_replace_recursive') ):
	function array_replace_recursive( $array, $array1 ){
		function recurse( $array, $array1 ){
			foreach( $array1 as $key => $value ){
				// create new key in $array, if it is empty or not an array
				if( !isset($array[$key]) || (isset($array[$key]) && !is_array($array[$key])) ){
					$array[$key] = array();
				}
			
				// overwrite the value in the base array
				if( is_array($value) ){
					$value = recurse( $array[$key], $value );
				}
				$array[$key] = $value;
			}
			return $array;
		}
	
		// handle the arguments, merge one by one
		$args = func_get_args();
		$array = $args[0];
		if( !is_array($array) ){
			return $array;
		}
		
		for( $i = 1; $i < count($args); $i++ ){
			if( is_array($args[$i]) ){
				$array = recurse($array, $args[$i] );
			}
		}
	
		return $array;
	}
endif;

/*
*	gets a nicely formatted html bit of information about approval history
*	@param array
*	@return string
*/
function display_date_and_name( $action ){
	$date = date( "D, M jS, g:i a", $action['stamp'] );
	$user = get_userdata( $action['user_id'] );
	
	$html = '<span class="action '.$action['action'].'">'.
				ucwords($action['action']).'
				<span class="on-date">
					on '.$date.'
				</span>
			 </span> ';
	
	if( $user )
		$html .= '<span class="author">by '.$user->user_nicename.'</span>';
		
	return $html;
}

/*
*	this is incredibly hacky. ideally this email should be sent
*	after the post is changed from a revision to its own post
*	@param int
*	@param string
*	@return string
*/
function approval_email_link( $post_id, $context = 'url' ){
	$link = get_edit_post_link( $post_id, $context );
	$link = str_replace('revision.php?revision=', 'post.php?action=edit&post=', $link );
	
	return $link;
}

/*
*	gets an array of emails for users with specified approval level
*	@param string use leading 0's ! and not an int
*	@return array
*/
function get_emails_by_level( $level = '01' ){
	$all_users = get_users();
	$cap ='luminopolis_approval_level_'.$level;
	$to = array();

	foreach( $all_users as $user ){
		if( $user->has_cap($cap) && !is_super_admin($user->ID) )
			$to[] = $user->data->user_email;
	}
	
	return $to;
}

/*
*	gets all submission/approval history ordered by time descending
*	@param int
*	@return array
*/
function approval_get_history( $post_id ){
	$approvals = get_post_meta( $post_id, '_luminopolis_approval' );
	usort( $approvals, __NAMESPACE__.'\get_history_sort' );
	
	return $approvals;
}

/*
*	usort callback to sort approvals by time descending
*	@param array
*	@param array
*	@return int
*/
function get_history_sort( $a, $b ){
	return $a['stamp'] < $b['stamp'] ? 1 : -1;
}

/*
*	wrapper for wp_mail
*	@param string
*	@param string
*	@param string
*	@param mixed
*	@param array
*	@return bool
*/
function approval_mail( $to, $title, $message, $headers = '', $attachments = array() ){
	add_filter( 'wp_mail_from', __NAMESPACE__.'\wp_mail_from' );
	
	$ok = wp_mail( $to, $title, $message, $headers );
	
	remove_filter( 'wp_mail_from', __NAMESPACE__.'\wp_mail_from' );
	
	return $ok;
}

/*
*	attached to `wp_mail_from` filter
*	@param string
*	@return string
*/
function wp_mail_from( $from ){
	$options = (array) get_option( 'luminopolis_approval_options' );
	if( isset($options['misc']['send_from']) && trim($options['misc']['send_from']) )
		$from = $options['misc']['send_from'];
		
	return $from;
}

/*
*	mail logger
*	attached to `phpmailer_init` action
*	@TODO make this only log approval emails
*	@param PHPMailer
*/
function phpmailer_init( \PHPMailer &$phpmailer ){
	$options = (array) get_option('luminopolis_approval_options' );
	
	if( isset($options['misc']['log_mail']) && $options['misc']['log_mail'] )
		$phpmailer->action_function = __NAMESPACE__.'\mail_log';
}
add_action( 'phpmailer_init', __NAMESPACE__.'\phpmailer_init' );

/*
*	logs all outgoing email
*	callback for `action_function` in PHPMailer which all wp_mail uses by default
*	@param int
*	@param string
*	@param array
*	@param array
*	@param string
*	@param string
*/
function mail_log( $is_sent, $to, array $cc, array $bcc, $subject, $body ){
	if( function_exists('dlog') )
		dlog( func_get_args(), 'approval_mail_log', 'mail_log' );
	else
		error_log( print_r(func_get_args(), TRUE), 0,'mail_log' );
}

/*
*	makes array for tracking approval state in post meta
*	@param string approved, edited, published, rejected, resubmitted, submitted
*	@param int
*	@return array
*/
function approval_meta( $action = '', $user_id ){
	$meta = array( 'action' => $action,
				   'stamp' => time(), 
				   'user_id' => $user_id );
	return $meta;
} 

/*
*
*	@param string
*	@return bool
*/
function post_supports_approval( $post_type ){
	static $options = NULL;
	
	if( !$options) 
		$options = (array) get_option( 'luminopolis_approval_options' );
		
	$excluded = isset( $options['post_types'] ) && array_key_exists( $post_type, $options['post_types'] );
	return !$excluded;
}

/*
*
*	@return bool
*/
function user_needs_approval(){
	static $options = NULL;
	static $user = NULL;
	
	if( !$options) 
		$options = (array) get_option( 'luminopolis_approval_options' );
	
	if( !$user )
		$user = wp_get_current_user();
	
	$excluded = array_intersect_key( $user->caps, array_keys($options['caps']) );
	
	return !count($excluded);
}