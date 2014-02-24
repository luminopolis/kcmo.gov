<?php

// Version: .77

add_action( 'load-edit.php', 'TaxoTaxi::setup' );

/*
*
*/	
class TaxoTaxi{
	private static $wpdb;						// pretend that $wpdb is not global
	
	private static $post_type = '';				// single custom post type we are working with
	private static $taxonomies = array();		// the taxonomies associated with current post type
												// populated by get_object_taxonomies, 
												// with default categories and post tags removed
	
	/*
	*	called on `load-edit.php` action
	*	sets up class variables and the rest of the actions / filters
	*/
	public static function setup(){
		global $wpdb;
		self::$wpdb = &$wpdb;
		
		require dirname( __FILE__ ).'/taxo-taxi_walker.php';
		
		add_filter( 'query_vars', 'TaxoTaxi::query_vars' );
		
		$post_type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : 'post';
		add_filter( 'manage_edit-'.$post_type.'_sortable_columns', 'TaxoTaxi::register_sortable_columns' );
		
		add_filter( 'manage_pages_columns', 'TaxoTaxi::manage_posts_columns' );
		add_filter( 'manage_posts_columns', 'TaxoTaxi::manage_posts_columns' );
		
		add_action( 'manage_pages_custom_column', 'TaxoTaxi::manage_posts_custom_column', 10, 2 );
		add_action( 'manage_posts_custom_column', 'TaxoTaxi::manage_posts_custom_column', 10, 2 );
		
		add_filter( 'posts_fields', 'TaxoTaxi::posts_fields' );
		add_filter( 'posts_groupby', 'TaxoTaxi::posts_groupby' );
		add_filter( 'posts_join', 'TaxoTaxi::posts_join' );
		add_filter( 'posts_orderby', 'TaxoTaxi::posts_orderby' );
		add_filter( 'posts_request', 'TaxoTaxi::posts_request' );
		add_filter( 'posts_results', 'TaxoTaxi::posts_results' );

		add_filter( 'request', 'TaxoTaxi::request' );	
		add_action( 'restrict_manage_posts', 'TaxoTaxi::restrict_manage_posts' );
	}
	
	/*
	*	setup class variables as soon as posssible, once $post_type is available
	*	attached to `query_vars` filter
	*	@param array not used
	*	@return array 
	*/
	public static function query_vars( $query_vars ){
		global $post_type;
		self::$post_type = $post_type;
		
		self::$taxonomies = get_object_taxonomies( $post_type, 'objects' );
		
		// don't show default taxonomies twice
		unset( self::$taxonomies['category'] );
		unset( self::$taxonomies['post_tag'] );
		
		return $query_vars;
	}
	
	/*
	*	attached to `manage_posts_columns` filter
	*	adds columns for custom taxonomies in Edit table
	*	@param array $headings
	*	@return array $headings
	*/
	public static function manage_posts_columns( $headings ){
		// default is to show before Categories
		$keys = array_keys( $headings );
		$key = array_search( 'categories', $keys );
		
		// arbitary placement in table
		if( !$key )
			$key = 2;
		
		// display the extra taxonomies after standard Categories
		$a = array_slice( $headings, 0, $key );
		$b = array_map( 'TaxoTaxi::_array_map_taxonomies', self::$taxonomies );
		$c = array_slice( $headings, $key );
		
		$headings = array_merge( $a, $b, $c );
		
		return $headings;
	}
	
	/*
	*	attached to `manage_posts_custom_column` action
	*	echos column data inside each table cell
	*	@param string 
	*	@param int
	*	@return NULL
	*/
	public static function manage_posts_custom_column( $column_name, $post_id ){
		global $post;
		
		if( !isset($post->$column_name) || !count($post->$column_name) )
			return print '&nbsp;';

		$links = array_map( 'TaxoTaxi::_array_map_buildLinks', $post->$column_name );
		
		// array_unique is needed because of duplicates when sorting by categories or post tags( beheader )
		echo implode( ', ', array_unique($links) );
	}
	
	/*
	*	filter for `posts_fields` to select joined taxonomy data into the main query
	*	@param string 
	*	@return string
	*/
	public static function posts_fields( $sql ){
		foreach( self::$taxonomies as $tax ){
			$tax = esc_sql( $tax->name );
			
			$sql .= ", GROUP_CONCAT( DISTINCT(IF(TX_AUTO.taxonomy = '{$tax}', T_AUTO.name, NULL)) 
							ORDER BY T_AUTO.name ASC ) 
							AS `{$tax}_names`,
					   GROUP_CONCAT( DISTINCT(IF(TX_AUTO.taxonomy = '{$tax}', T_AUTO.slug, NULL)) 
					   		ORDER BY T_AUTO.name ASC ) 
					   		AS `{$tax}_slugs`";
		}
		
		// @TODO: this should be unnecessary with the above sql.  
		// refactor TaxoTaxi::posts_results to not need this 
		$sql .= ", GROUP_CONCAT( (TX_AUTO.taxonomy) ORDER BY T_AUTO.name ASC ) AS `concat_taxonomy`
				 , GROUP_CONCAT( (T_AUTO.slug) ORDER BY T_AUTO.name ASC ) AS `concat_slug`
				 , GROUP_CONCAT( (T_AUTO.name) ORDER BY T_AUTO.name ASC ) AS `concat_name`";
				 
		return $sql;
	}
	
	/*
	*	filter for `posts_groupby` to group query by post id
	*	@param string 
	*	@return string
	*/
	public static function posts_groupby( $sql ){
		$sql = self::$wpdb->posts.".ID";
		return $sql;
	}
	
	/*
	*	filter for `posts_join` to join taxonomy data into the main query
	*	@param string 
	*	@return string
	*/
	public static function posts_join( $sql ){
		$sql .= " LEFT JOIN ".self::$wpdb->term_relationships." TR_AUTO 
					ON ".self::$wpdb->posts.".ID = TR_AUTO.object_id
				  LEFT JOIN ".self::$wpdb->term_taxonomy." TX_AUTO 
				  	ON TR_AUTO.term_taxonomy_id = TX_AUTO.term_taxonomy_id 
				  	AND TX_AUTO.taxonomy NOT IN( 'category', 'post_tag' )
				  LEFT JOIN ".self::$wpdb->terms." T_AUTO 
				  	ON TX_AUTO.term_id = T_AUTO.term_id ";
				  	
		return $sql;
	}
	
	/*
	*	handy if you have Behaeder installed
	*	@param string 
	*	@return string
	*/
	public static function posts_orderby( $sql ){
		if( !isset($_GET['orderby']) || !array_key_exists($_GET['orderby'], self::$taxonomies) )
			return $sql;
		
		$order = isset( $_GET['order']) && $_GET['order'] == 'asc' ? 'asc' : 'desc';
		$orderby = esc_sql( $_GET['orderby'] );
		
		$sql = "`{$orderby}_names` $order ";
		return $sql;
	}
	
	/*
	*	just for debugging, view the sql query that populates the Edit table
	*	@param string 
	*	@return string
	*/
	public static function posts_request( $sql ){
		return $sql;
	}
	
	/*
	*	filter for `posts_results` to parse taxonomy data from each $post into array for later display 
	*	@param array WP_Post
	*	@return array
	*/
	public static function posts_results( $posts ){
		foreach( $posts as &$post ){
			// TODO: refactor this to not need the extra sql fields in TaxoTaxi::posts_fields
			
			// if this is NULL, then no custom taxonomies were found for the post
			if( !$post->concat_taxonomy )
				continue;
				
			// get the unique taxonomies, use as array keys with empty array as value
			$order = explode( ',', $post->concat_taxonomy );
			
			$keys = array_keys( array_flip($order) );
			$taxonomies = array_fill_keys( $keys, array() );
			
			$slugs = explode( ',', $post->concat_slug );
			$names = explode( ',', $post->concat_name );
		
			foreach( $names as $k=>$name ){
				// there seems to be a problem with the length limit from group_concat
				// refactoring this method as above should get rid of the problem
				if( isset($order[$k]) )
					$taxonomies[ $order[$k] ][] = array( 'name' => $name,
														 'post_type' => $post->post_type,
														 'slug' => $slugs[$k], 
														 'taxonomy' => $order[$k] );
			}
			
			$props = array_merge( $post->to_array(), $taxonomies );
			$post = new WP_Post( (object) $props );
		}
		
		return $posts;
	}
	
	/*
	*	register custom taxonomies for sortable columns
	*	attached to `manage_edit-{$post_type}_sortable_columns` filter
	*	@param array
	*	@return array
	*/
	public static function register_sortable_columns( $columns ){
		if( empty(self::$taxonomies) )
			return $columns;
			
		// add key value pairs with both values as the taxonomy slug
		$keys = array_keys( self::$taxonomies );
		$keys = array_combine( $keys, $keys );
		
		$columns = array_merge( $columns, $keys ); 
		
		return $columns;
	}
	
	/*
	*	fix bug in setting post_format query varaible
	*	wp-includes/post.php function _post_format_request()
	*		$tax = get_taxonomy( 'post_format' );
	*		$qvs['post_type'] = $tax->object_type;
	*		sets global $post_type to an array
	*	@param array
	*	@return array
	*/
	public static function request( $qvs ){
		if( isset($qvs['post_type']) && is_array($qvs['post_type']) )
			$qvs['post_type'] = $qvs['post_type'][0];
			
		return $qvs;
	}
	
	/*
	*	action for `restrict_manage_posts` 
	*	to display drop down selects for custom taxonomies
	*	return string
	*/
	public static function restrict_manage_posts(){
		foreach( self::$taxonomies as $taxonomy => $props ){
			$html = wp_dropdown_categories( array(
				'echo' => 0,
				'hierarchical' => TRUE,
				'name' => $taxonomy,
				'selected' => isset( $_GET[$taxonomy] ) ? $_GET[$taxonomy] : FALSE,
				'show_option_all' => 'View All '.$props->labels->all_items,
				'taxonomy' => $taxonomy,
				'walker' => new Walker_Taxo_Taxi
			) );
			
			echo $html;
		}
	}
	
	/*
	*	array map callback to build the link in the Edit table
	*	@param array
	*	@return string
	*/
	public static function _array_map_buildLinks( $array ){
		return '<a href="?post_type='.$array['post_type'].'&'.$array['taxonomy'].'='.$array['slug'].'">'.$array['name'].'</a>';
	}
	
	/*
	*	array map callback
	*	@param object
	*	@return string
	*/
	public static function _array_map_taxonomies( $object ){
		return $object->labels->name;
	}
}