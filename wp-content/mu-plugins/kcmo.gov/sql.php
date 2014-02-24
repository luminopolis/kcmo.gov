<?php

/*
*	for debugging
*	@param string
*	@param WP_Query
*	@return string
*/
function kcmo_posts_request( $sql, WP_Query $wp_query ){
		
	return $sql;
}
add_filter( 'posts_request', 'kcmo_posts_request', 10, 2 );