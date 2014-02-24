<?php

namespace luminopolis_approval;

add_filter( 'pre_get_posts', __NAMESPACE__.'\pre_get_posts', 10, 2 );

/*
*
*/
function get_revision_diffs(){
	require ABSPATH.'wp-admin/includes/revision.php';

	if( !$post = get_post( (int) $_REQUEST['post_id']) )
		wp_send_json_error();

	if( !current_user_can('read_post', $post->ID) )
		wp_send_json_error();

	// Really just pre-loading the cache here.
	if( !$revisions = wp_get_post_revisions( $post->ID, array( 'check_enabled' => false)) )
		wp_send_json_error();

	$return = array();
	@set_time_limit( 0 );

	foreach( $_REQUEST['compare'] as $compare_key ){
		list( $compare_from, $compare_to ) = explode( ':', $compare_key ); // from:to

		$return[] = array(
			'id' => $compare_key,
			'fields' => get_revision_ui_diff( $post, $compare_from, $compare_to ),
		);
	}
	wp_send_json_success( $return );
}

/*
*	modified from wp_get_revision_ui_diff()
*	@param
*	@param
*	@param
*	@return array
*/
function get_revision_ui_diff( $post, $compare_from, $compare_to ){
	if( !$post = get_post($post) )
		return false;

	if( $compare_from ){
		if( !$compare_from = get_post( $compare_from) )
			return false;
	} else {
		// If we're dealing with the first revision...
		$compare_from = false;
	}
	
	if( !$compare_to = get_post($compare_to) )
		return false;
	
	// If comparing revisions, make sure we're dealing with the right post parent.
	// The parent post may be a 'revision' when revisions are disabled and we're looking at autosaves.
	//if( $compare_from && $compare_from->post_parent !== $post->ID && $compare_from->ID !== $post->ID )
	//	return false;
	//if( $compare_to->post_parent !== $post->ID && $compare_to->ID !== $post->ID )
	//	return false;

	if( $compare_from && strtotime( $compare_from->post_date_gmt ) > strtotime( $compare_to->post_date_gmt) ){
		$temp = $compare_from;
		$compare_from = $compare_to;
		$compare_to = $temp;
	}
	
	// Add default title if title field is empty
	if( $compare_from && empty($compare_from->post_title) )
		$compare_from->post_title = __( '(no title)' );
	if( empty($compare_to->post_title) )
		$compare_to->post_title = __( '(no title)' );

	$return = array();
	
	foreach( _wp_post_revision_fields() as $field => $name ){
		/**
		 * Contextually filter a post revision field.
		 *
		 * The dynamic portion of the hook name, $field, corresponds to each of the post
		 * fields of the revision object being iterated over in a foreach statement.
		 *
		 * @since 3.6.0
		 *
		 * @param string  $compare_from->$field The current revision field to compare to or from.
		 * @param string  $field                The current revision field.
		 * @param WP_Post $compare_from         The revision post object to compare to or from.
		 * @param string  null                  The context of whether the current revision is the old or the new one. Values are 'to' or 'from'.
		 */
		$content_from = $compare_from ? apply_filters( "_wp_post_revision_field_$field", $compare_from->$field, $field, $compare_from, 'from' ) : '';

		/** This filter is documented in wp-admin/includes/revision.php */
		$content_to = apply_filters( "_wp_post_revision_field_$field", $compare_to->$field, $field, $compare_to, 'to' );

		$diff = wp_text_diff( $content_from, $content_to, array( 'show_split_view' => true) );
		
		if( !$diff && 'post_title' === $field ){
			// It's a better user experience to still show the Title, even if it didn't change.
			// No, you didn't see this.
			$diff = '<table class="diff"><colgroup><col class="content diffsplit left"><col class="content diffsplit middle"><col class="content diffsplit right"></colgroup><tbody><tr>';
			$diff .= '<td>' . esc_html( $compare_from->post_title ) . '</td><td></td><td>' . esc_html( $compare_to->post_title ) . '</td>';
			$diff .= '</tr></tbody>';
			$diff .= '</table>';
		}

		if( $diff ){
			$return[] = array(
				'id' => $field,
				'name' => $name,
				'diff' => $diff,
			);
		}
	}
	
	return $return;
}

/*
*
*
*/
function footer_js(){
	global $wp_scripts;
	
	// good thing this is all public :-/
	$data = $wp_scripts->registered['revisions']->extra['data'];
	
	$data = str_replace( 'var _wpRevisionsSettings = ', '', $data );
	$data = str_replace( "\n", ' ', $data );
	
	// trailing semicolon
	$data = substr( $data, 0, -1 );
	
	$data = json_decode( $data );
	
	$fields = get_revision_ui_diff( $data->from, $data->from, $data->to );
	$data->diffData = array( array(
		'id' => $data->from . ':' . $data->to,
		'fields' => $fields,
	) );
	
	$data = json_encode( $data );
	$wp_scripts->registered['revisions']->extra['data'] = "var _wpRevisionsSettings = $data;";
}
add_action( 'admin_footer', __NAMESPACE__.'\footer_js' );