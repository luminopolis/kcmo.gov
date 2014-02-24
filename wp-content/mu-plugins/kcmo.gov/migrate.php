<?php

function kcmo_migrate(){
	global $table_prefix, $wpdb;
	
	$wpdb = new wpdb( DB_USER, DB_PASSWORD, 'kcmo_stage_merge', DB_HOST );
	$wpdb->set_prefix( $table_prefix, false ); 
	
	$wpdb_prod = new wpdb( DB_USER, DB_PASSWORD, 'kcmo_prod_merge', DB_HOST );
	
	$prefix = $wpdb->get_blog_prefix( 1 );
	
	foreach( range(1, 35) as $blog_id ){
		
		//$blog_id = $site['blog_id'];
		
		$prefix = $wpdb->get_blog_prefix( $blog_id );
		
		// 
		$sql = "SELECT option_value FROM {$prefix}options WHERE option_name = 'widget_kcmo_dept';";
		$res = $wpdb->get_var( $sql );
		
		$res = unserialize( $res );
		
		replace_ip( $res );
		
		$res = serialize( $res );
		
		$sql = $wpdb->prepare( "REPLACE INTO {$prefix}options 
								( option_name, option_value ) 
								VALUES 
								( 'widget_kcmo_dept', %s );", $res );
		echo( $sql );
	}
	
	die('migrate complete');
}

function replace_ip( &$r ){
	if( is_object($r) || is_array($r) )
		foreach( $r as &$_r )
			replace_ip( $_r );
		
	$r = str_replace( '184.168.86.20/~kcstage/cms', 'kcmo.gov', $r );
}

add_action( 'init', 'kcmo_migrate' );