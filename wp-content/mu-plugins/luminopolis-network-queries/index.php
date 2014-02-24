<?php

namespace network_queries;

if( is_admin() )
	require __DIR__.'/admin.php';

require __DIR__.'/sql.php';

/*
*
*	@param string
*	@param int
*	@param bool
*	@return string
*/
function get_delete_post_link( $url, $post_id, $force_delete ){
	$action = ( $force_delete || !EMPTY_TRASH_DAYS ) ? 'delete' : 'trash';
	
	$post = get_post( $post_id );
	
	if( $post->blog_id ){
		$post_type_object = get_post_type_object( $post->post_type );
		
		$path = add_query_arg( 'action', $action, sprintf($post_type_object->_edit_link, $post->ID) );
		$url = get_admin_url( $post->blog_id, $path );
		
		$url = wp_nonce_url( $url, "$action-post_{$post->ID}" );
		
		$delete_link = add_query_arg( 'action', $action, admin_url( sprintf($post_type_object->_edit_link, $post->ID)) );
	}
	
	return $url;
}
add_filter( 'get_delete_post_link', __NAMESPACE__.'\get_delete_post_link', 10, 3 );

/*
*	gets correct link for admin edit page when on different site
*	attached to `get_edit_post_link` filter
*	@param string
*	@param int
*	@param string
*	@return string
*/
function get_edit_post_link( $url, $post_id, $context ){
	$post = get_post( $post_id );
	
	if( $post->blog_id ){
		$post_type_object = get_post_type_object( $post->post_type );
		
		$url = get_admin_url( $post->blog_id, sprintf($post_type_object->_edit_link . '&amp;action=edit', $post->ID) );
	}
	
	return $url;
}
add_filter( 'get_edit_post_link', __NAMESPACE__.'\get_edit_post_link', 10, 3 );

/*
*
*	attached to `home_url` filter
*	@param string
*	@param
*	@param
*	@param int
*	@return string
*/
function home_url( $url, $path, $orig_scheme, $blog_id ){
	global $post, $wpdb;
	
	if( !$blog_id && $post && $post->blog_id && $post->blog_id != $GLOBALS['blog_id'] ){
		
		$homes = wp_cache_get( 'blog_homes', 'luminopolis' );
		
		if( !$homes )
			$homes = array(
				$GLOBALS['blog_id'] => untrailingslashit( network_option('home', $GLOBALS['blog_id']) )
			);
		
		if( !isset($homes[$post->blog_id]) ){
			$homes[$post->blog_id] = untrailingslashit( network_option('home', $post->blog_id) );
			wp_cache_add( 'blog_homes', $homes, 'luminopolis' );
		}
		
		$url = str_replace( $homes[$GLOBALS['blog_id']], $homes[$post->blog_id], $url );
	}
	
	return $url;
}
add_filter( 'home_url', __NAMESPACE__.'\home_url', 10 , 4 );

/*
*
*	@param string
*	@param int
*	@return string
*/
function network_option( $option_name, $blog_id ){
	global $wpdb;
	
	$table = $wpdb->get_blog_prefix( $blog_id );
	$table .= "options";
	
	$sql = "SELECT option_value 
			FROM $table 
			WHERE `option_name` = 'home' LIMIT 1";
	$res = $wpdb->get_var( $sql );
	return $res;
}

/*
*	before term associations are deleted, cache rows where object_id matched other blog associations,
*	these will be reinserted after the delete
*	@param int
*	@param array
*	return 
*/
function delete_term_relationships( $object_id, $term_taxonomy_ids ){
	global $blog_id, $wpdb;
	
	$term_taxonomy_ids = array_map( 'intval', $term_taxonomy_ids );
	$term_taxonomy_ids = implode( ', ', $term_taxonomy_ids );
	
	$sql = $wpdb->prepare( "SELECT * FROM $wpdb->term_relationships
							WHERE object_id = %d
							AND term_taxonomy_id IN ( $term_taxonomy_ids )
							AND blog_id != %d", $object_id, $blog_id );
	$res = $wpdb->get_results( $sql );
	wp_cache_add( 'object_terms_keep', $res, 'luminopolis' );
}
add_filter( 'delete_term_relationships', __NAMESPACE__.'\delete_term_relationships', 10, 2 );

/*
*	put deleted rows where object_id matched other blogs back into database
*	@param int
*	@param array
*	return 
*/
function deleted_term_relationships( $object_id, $term_taxonomy_ids ){
	global $wpdb;
	
	$data = wp_cache_get( 'object_terms_keep', 'luminopolis' );
	foreach( $data as $row ){
		$wpdb->insert( $wpdb->term_relationships, (array) $row );
	}
}
add_filter( 'deleted_term_relationships', __NAMESPACE__.'\deleted_term_relationships', 10, 2 );

/*
*	array_filter callback
*	@param int
*	@return int
*/
function filter_object_term_ids( $term ){
	static $object_terms = NULL;
	
	if( !$object_terms ){
		$object_terms = wp_cache_get( 'object_terms', 'luminopolis' );
		$object_terms = array_map( 'term_ids', $object_terms );
	}
	
	if( in_array($term, $object_terms) )
		return $term;
}

/*
*	array_filter callback
*	@param int
*	@return int
*/
function filter_object_term_taxonomy_ids( $term_taxonomy_id ){
	static $object_terms = NULL;
	
	if( !$object_terms ){
		$object_terms = wp_cache_get( 'object_terms', 'luminopolis' );
		$object_terms = array_map( 'term_taxonomy_ids', $object_terms );
	}
	
	if( in_array($term_taxonomy_id, $object_terms) )
		return $term_taxonomy_id;
}

/*
*	array_filter callback
*	@param object
*	@return object
*/
function filter_object_term_objects( $term ){
	static $object_terms = NULL;
	
	if( !$object_terms ){
		$object_terms = wp_cache_get( 'object_terms', 'luminopolis' );
		$object_terms = array_map( 'term_taxonomy_ids', $object_terms );
	}
	
	if( in_array($term->term_taxonomy_id, $object_terms) )
		return $term;
}

/*
*
*	attached to `wp_get_object_terms` filter
*	@param array
*	@param int
*	@param string
*	@param array
*	@return array
*/
function wp_get_object_terms( $terms, $object_ids, $taxonomies, $args ){
	global $blog_id, $wpdb;
	
	if( is_numeric($object_ids) )
		$xsql = $wpdb->prepare( "AND TR.object_id = %d", $object_ids );
	else
		$xsql = "AND 1 = 1";
		
	$sql = $wpdb->prepare( "SELECT TX.term_id, TR.term_taxonomy_id 
							FROM $wpdb->term_relationships TR 
							LEFT JOIN $wpdb->term_taxonomy TX 
								ON TR.term_taxonomy_id = TX.term_taxonomy_id
							WHERE TR.blog_id = %d
							$xsql", $blog_id );
	$object_terms = $wpdb->get_results( $sql );
		
	wp_cache_add( 'object_terms', $object_terms, 'luminopolis' );
	
	switch( $args['fields'] ){
		case 'ids':
			$terms = array_filter( $terms, __NAMESPACE__.'\filter_object_term_ids' );
			break;
			
		case 'tt_ids':
			$terms = array_filter( $terms, __NAMESPACE__.'\filter_object_term_taxonomy_ids' );
			break;
			
		default:
			$terms = array_filter( $terms, __NAMESPACE__.'\filter_object_term_objects' );
			break;
	}
	
	return $terms;
}
add_filter( 'wp_get_object_terms', __NAMESPACE__.'\wp_get_object_terms', 10, 4 );

/*
*
*	attached to `set_object_terms` action
*	@param int
*	@param array
*	@param array term taxonomy ids
*	@param string
*	@param bool
*	@param array
*/
function set_object_terms( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ){
	global $blog_id, $wpdb;
	
	foreach( $tt_ids as $tt_id ){
		$sql = $wpdb->prepare( "SELECT term_taxonomy_id 
								FROM $wpdb->term_relationships 
								WHERE object_id = %d 
								AND term_taxonomy_id = %d
								AND blog_id IN( 0, %d )", $object_id, $tt_id, $blog_id );
		$res = $wpdb->get_var( $sql );
		
		if( $res ){
			$sql = $wpdb->prepare( "UPDATE $wpdb->term_relationships 
									SET blog_id = %d
									WHERE object_id = %d 
									AND term_taxonomy_id = %d
									AND blog_id = 0", $blog_id, $object_id, $tt_id );
			$wpdb->query( $sql );
		} else {
			$args = array( 'blog_id' => $blog_id,
						   'object_id' => $object_id,
						   'term_taxonomy_id' => $tt_id );
			$wpdb->insert( $wpdb->term_relationships, $args );
		}
	}
}
add_action( 'set_object_terms', __NAMESPACE__.'\set_object_terms', 10, 6 );

/*
*	overriders per blog taxonomy tables
*	attached to `switch_blog` action
*	@param int
*/
function taxonomy_tables( $blog_id ){
	global $wpdb;
	
	// not overriding $wpdb $tables/$global_tables because docs say private
	$prefix = $wpdb->get_blog_prefix( 1 );
	$wpdb->term_relationships = "{$prefix}term_relationships";
	$wpdb->term_taxonomy = "{$prefix}term_taxonomy";
	$wpdb->terms = "{$prefix}terms";
}
add_action( 'switch_blog', __NAMESPACE__.'\taxonomy_tables' );