<?php
/*
Plugin Name: 	Luminopolis MU Network-wide Users
Plugin URI: 
Description:	Allows users assigned to blog #1 to have the same roles and capabilities across all subsites
Author:			Luminopolis / Eric Eaglstun
Version:		1.0
Author URI:		http://luminopolis.com/
*/

class MU_Network_Users{
	private static $instance = NULL;
	
	private $user_role_key = ''; 			// user role meta key for current blog
	private $main_user_role_key = ''; 		// user role meta key for blog #1
	
	private $capability_key = '';			//
	private $main_capability_key = '';		//
	
	/*
	*
	*/
	public static function init(){
		if( !self::$instance )
			self::$instance = new self;
	}
	
	/*
	*
	*/
	public function __construct(){
		global $blog_id, $wpdb;
		
		if( $blog_id == 1 )
			return;
			
		// 
		$this->user_role_key = $wpdb->get_blog_prefix() . 'user_roles';
		$this->main_user_role_key = $wpdb->get_blog_prefix(1) . 'user_roles';
		
		// capabilities
		$this->capability_key = $wpdb->get_blog_prefix() . 'capabilities';
		$this->main_capability_key = $wpdb->get_blog_prefix(1) . 'capabilities';
		
		add_filter( 'get_blogs_of_user', array($this, 'get_blogs_of_user'), 10, 3 );
		add_filter( 'get_meta_sql', array($this, 'get_meta_sql'), 10, 6 );
		add_filter( 'get_user_metadata', array($this, 'get_user_metadata'), 10, 4 );
		add_filter( 'pre_option_'.$this->user_role_key, array($this, 'option_user_roles') );
		add_filter( 'pre_user_query', array($this, 'pre_user_query') );
	}
	
	/*
	*	allow users assigned to blog #1 to automatically have all sites admin avaialble
	*	@param array
	*	@param int
	*	@param bool
	*	@return array
	*/
	public function get_blogs_of_user( $blogs, $user_id, $all ){
		if( !isset($blogs[1]) )
			return $blogs;
			
		$sites = wp_get_sites( array() );
		
		foreach( $sites as $site ){
			
			if( isset($blogs[$site['blog_id']]) )
				continue;
			
			$blog_id = $site['blog_id'];
			$blog = get_blog_details( $blog_id );
			
			$blogs[$blog->blog_id] = (object) array(
				'userblog_id' => $blog->blog_id,
				'blogname'    => $blog->blogname,
				'domain'      => $blog->domain,
				'path'        => $blog->path,
				'site_id'     => $blog->site_id,
				'siteurl'     => $blog->siteurl,
				'archived'    => 0,
				'spam'        => 0,
				'deleted'     => 0
			);
		}
		
		return $blogs;
	}
	
	/*
	*	joins user query on blog 1 capabilities in admin/users.php
	*	@param array join and where
	*	@param array
	*	@param string
	*	@param string
	*	@param string
	*	@param WP_User_Query
	*	@return array
	*/
	public function get_meta_sql( $clauses, $queries, $type, $primary_table, $primary_id_column, $context ){
		if( !($context instanceof WP_User_Query) )
			return $clauses;
		
		if( $context->query_vars['fields'] == 'all_with_meta' ){
			$caps = array( $this->capability_key, $this->main_capability_key );
			
			// wrap in quotes and separate with commas
			$caps = implode( ', ', array_map(create_function('$r', 'return "\'$r\'";'), $caps) ); 
			
			$caps = "IN( $caps )";
			
			$clauses['where'] = str_replace( "= '$this->capability_key'", $caps, $clauses['where'] );
		}
		
		return $clauses;
	}
	
	/*
	*
	*	@param NULL
	*	@param int
	*	@param string
	*	@param bool
	*	@return
	*/
	function get_user_metadata( $null, $object_id, $meta_key, $single ){
		if( $meta_key != $this->capability_key )
			return $null;
		
		$meta_key = $this->main_capability_key;
		
		// this needs to return an array, so the calling function can decide if its single
		$value = get_metadata( 'user', $object_id, $meta_key, FALSE );
		
		if( !empty($value) )
			return $value;
	}
	
	/*
	*	attached to `option_user_roles` pre_option filter
	*	@return array
	*/
	public function option_user_roles(){
		$options = wp_load_alloptions();
		$return = unserialize( $options[$this->main_user_role_key] );
		
		return $return;
	}

	/*
	*	
	*	@param WP_User_Query
	*	@return WP_User_Query
	*/
	public function pre_user_query( WP_User_Query $user_query ){
		global $wpdb;
		$user_query->query_orderby = "GROUP BY $wpdb->users.ID ".$user_query->query_orderby;
		
		return $user_query;
	}
}

add_action( 'muplugins_loaded', array('MU_Network_Users', 'init') );
