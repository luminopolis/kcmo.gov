<?php

namespace luminopolis_filename;

/*
*
*	@param array
*	@return array
*/
function wp_handle_upload_prefilter( $file ){
	$file['name'] = preg_replace( "/[^A-Za-z0-9.\-]/", '', $file['name'] );
	
	return $file;
}
add_filter( 'wp_handle_upload_prefilter', __NAMESPACE__.'\wp_handle_upload_prefilter' );