<?php

namespace mu_shared_options;

if( is_admin() )
	require __DIR__.'/admin.php';

require __DIR__.'/lib/functions.php';

/*
*
*/
class MU_Shared_Options{
	private static $instance = NULL;
	private static $options = array();	// 0 => blog #1, 1 => current blog
	private static $shared_option_names = array();
	
	/*
	*	called on `init` action to register filters
	*/
	static public function init(){
		// can't use __callStatic in php 5.2
		if( !self::$instance )
			self::$instance = new MU_Shared_Options;
		
		global $blog_id, $wpdb;
		
		if( $blog_id == 1 )
			return;
			
		$options = get_site_option( 'luminopolis-mu-shared-options' );
		self::$shared_option_names = (array) json_decode( $options );
			
		$elements = array_map( 'esc_sql', self::$shared_option_names );
		$elements = array_map( create_function('$e', 'return "\'$e\'";'), $elements );
		$elements = count( $elements ) ? implode( ', ', $elements ) : "''";
		
		foreach( array(1, $blog_id) as $key => $_blog_id ){
			$prefix = $wpdb->get_blog_prefix( $_blog_id );
			$table = $prefix.'options';
			
			$sql = "SELECT option_name, option_value 
					FROM $table 
					WHERE option_name IN( $elements )
					AND option_value != ''";
			$res = $wpdb->get_results( $sql, OBJECT_K );
			
			$val = array_filter_recursive( array_map(array(self::$instance, 'option_values'), $res ) );
			
			self::$options[$key] = $val;				
		}
		
		//ddbug(self::$options);
		
		foreach( array_keys( array_merge(self::$options[0], self::$options[1]) ) as $option_name ){
			add_filter( 'pre_option_'.$option_name, 
						array(self::$instance, 'pre_option_'.$option_name) );
			
			add_filter( 'sanitize_option_'.$option_name, 
						array(self::$instance, 'sanitize_option_'.$option_name) );
						
			add_filter( 'update_option_'.$option_name, 
						array(self::$instance, 'update_option_'.$option_name) );
		}
	}
	
	/*
	*	all *_option_* filters attached in the init() method are called here
	*	@param string
	*	@param array
	*	@return mixed
	*/
	public function __call( $filter, $args ){
		if( strpos($filter, 'pre_option_') === 0 )
			return self::pre_option( substr($filter, 11) );
			
		elseif( strpos($filter, 'sanitize_option_') === 0 )
			return self::sanitize_option( substr($filter, 16), $args[0] );
		
		elseif( strpos($filter, 'update_option_') === 0 )
			return self::update_option( substr($filter, 14), $args[0] );
	}
	
	/*
	*	gets the option from current blog options if exists, else falls back to blog 1's options
	*	called from `pre_option_*` filter
	*	@param string
	*	@return mixed
	*/
	private static function pre_option( $option_name, $option_value = NULL ){
		$return = isset( self::$options[1][$option_name] ) ? self::$options[1][$option_name] : self::$options[0][$option_name];
			
		if( is_array($return) && isset($return['_multiwidget']) && count($return) == 1 )
			$return = self::$options[0][$option_name];
			
		return $return; 
	}
	
	/*
	*	default option in sanitize_filter, to make updating work.
	*	update_option() needs to check old and new values are different,
	*	and using get_option with our pre_option_ filter throws off $old_value
	*	called from `sanitize_option_*` filter
	*	@param string
	*	@param mixed 
	*	@return mixed
	*/
	private static function sanitize_option( $option_name, $option_value ){
		remove_filter( 'pre_option_'.$option_name, 
					   array(self::$instance, 'pre_option_'.$option_name) );
						   
		return $option_value;
	}
	
	/*
	*	sets the option in internal data storage for use on same page request
	*	called from `update_option_*` filter
	*	@param string
	*	@param mixed
	*	@return NULL
	*/
	private static function update_option( $option_name, $option_value ){
		self::$options[1][$option_name] = $option_value;
	}
	
	/*
	*	array_map callback to reduce db result to key => value pairs
	*	@param object
	*	@return string
	*/
	public function option_values( $r ){
		return maybe_unserialize( $r->option_value );
	}
}

add_action( 'muplugins_loaded', array(__NAMESPACE__.'\MU_Shared_Options','init') );
