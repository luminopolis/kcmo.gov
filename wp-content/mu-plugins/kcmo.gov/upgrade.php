<?php

/*
*
*	@param string
*	@return
*/
function kcmo_do_upgrade( $current_version ){
	global $wpdb;
	
	// handy to have
	$sites = wp_get_sites();
	
	switch( TRUE ){	
		// delete default posts in each site
		case version_compare( $current_version, '0.9', '<' ):
			foreach( $sites as $site ){
				$blog_id = $site['blog_id'];
				$table = $wpdb->get_blog_prefix( $blog_id )."posts" ;
				
				$sql = "DELETE FROM $table WHERE `post_title` IN 
						( 'Sample Page', 'Auto Draft', 'Hello world!' )";
				$res = $wpdb->query( $sql );
				var_dump( $res, $sql );
			}
		
		// set pages with missing parent to top level
		case version_compare( $current_version, '0.9.1', '<' ):
			foreach( $sites as $site ){
				$blog_id = $site['blog_id'];
				$table = $wpdb->get_blog_prefix( $blog_id )."posts" ;
				$sql = "UPDATE $table P 
						LEFT JOIN $table P2 ON P.post_parent = P2.ID
						SET P.post_parent = 0 
						WHERE P2.ID IS NULL";
				$res = $wpdb->query( $sql );
				var_dump( $res, $sql );
			}
			
		// add default plugins back in
		case version_compare( $current_version, '0.9.2', '<' ):
			foreach( $sites as $site ){
				$blog_id = $site['blog_id'];
				$table = $wpdb->get_blog_prefix( $blog_id )."options" ;
				
				// update_option is not working here.  old option seems to come back from blog #1 always
				$plugins = serialize( array('approval-workflow/approval-workflow.php', 'kcmo-approval/index.php') );
				$sql = $wpdb->prepare( "UPDATE $table 
										SET option_value = %s 
										WHERE option_name = 'active_plugins' LIMIT 1", $plugins );
				$wpdb->query( $sql );
			}
		
		// add default plugins back in
		case version_compare( $current_version, '0.9.3', '<' ):
			foreach( $sites as $site ){
				$blog_id = $site['blog_id'];
				
				if( $blog_id == 1 )
					continue;
					
				$table = $wpdb->get_blog_prefix( $blog_id )."options" ;
				
				$sql = "DELETE FROM $table 
						WHERE option_name IN( 'stylesheet', 'template' )";
				
				$wpdb->query( $sql );
				var_dump( $sql );
			}
		
		// rename kcmo_approval options to luminopolis
		case version_compare( $current_version, '0.9.6', '<' ):
			// update user meta
			$table = $wpdb->get_blog_prefix( 1 )."usermeta";
			$sql = "SELECT * FROM $table WHERE meta_value LIKE '%kcmo_approval_%'";
			$res = $wpdb->get_results( $sql );
			foreach( $res as $r ){
				$v = unserialize( $r->meta_value );
				$v = array_keys( $v );
				
				foreach( $v as &$_v )
					$_v = str_replace( 'kcmo_approval', 'luminopolis_approval', $_v );
				
				$v = array_combine( $v, array_fill(0, count($v), TRUE) );
				
				$sql = $wpdb->prepare( "UPDATE $table SET meta_value = %s
										WHERE umeta_id = %d 
										LIMIT 1", serialize($v), $r->umeta_id );
				$res = $wpdb->query( $sql );
			}
			
			// update site options
			$table = $wpdb->get_blog_prefix( 1 )."options";
			$sql = "SELECT * FROM $table WHERE option_name = 'kcmosand_user_roles'";
			$res = $wpdb->get_row( $sql );
			$v = unserialize( $res->option_value );
			
			unset( $v['administrator']['capabilities']['kcmo_approval_level_01'] );
			unset( $v['administrator']['capabilities']['kcmo_approval_level_02'] );
			
			$v['administrator']['capabilities']['luminopolis_approval_level_01'] = TRUE;
			$v['administrator']['capabilities']['luminopolis_approval_level_02'] = TRUE;
			
			$sql = $wpdb->prepare( "UPDATE $table SET option_value = %s 
									WHERE option_name = 'kcmosand_user_roles'", serialize($v) );
			$res = $wpdb->query( $sql );
			
			foreach( $sites as $site ){
				$blog_id = $site['blog_id'];
				
				$table = $wpdb->get_blog_prefix( $blog_id )."postmeta";
				
				$sql = "UPDATE $table 
						SET meta_key = 'luminopolis_approval'
						WHERE meta_key = 'kcmo_approval'";
				
				$wpdb->query( $sql );
				var_dump( $sql );
				
				$sql = "UPDATE $table 
						SET meta_key = 'luminopolis_publish_status'
						WHERE meta_key = 'kcmo_publish_status'";
				
				$wpdb->query( $sql );
				var_dump( $sql );
			}
			
		// update meta keys for approval workflow
		case version_compare( $current_version, '0.9.7', '<' ):
			foreach( $sites as $site ){
				$blog_id = $site['blog_id'];
				
				$table = $wpdb->get_blog_prefix( $blog_id )."postmeta";
				$sql = "UPDATE $table 
						SET meta_key = '_luminopolis_approval'
						WHERE meta_key = 'luminopolis_approval'";
				
				$wpdb->query( $sql );
				var_dump( $sql );
				
				$sql = "UPDATE $table 
						SET meta_key = '_luminopolis_publish_status'
						WHERE meta_key = 'luminopolis_publish_status'";
				
				$wpdb->query( $sql );
				var_dump( $sql );
			}
		
		// change /archive/ to /news/
		case version_compare( $current_version, '0.9.8', '<' ):
			foreach( $sites as $site ){
				$blog_id = $site['blog_id'];
				
				$table = $wpdb->get_blog_prefix( $blog_id )."options";
				$sql = "DELETE FROM $table
						WHERE option_name = 'rewrite_rules'";
						
				$wpdb->query( $sql );
				var_dump( $sql );
			}
		
		// Redirection 'URL Monitoring' bug
		case version_compare( $current_version, '0.9.9.1', '<' ):
			foreach( $sites as $site ){
				$blog_id = $site['blog_id'];
				
				if( $blog_id == 1 )
					continue;
					
				$table = $wpdb->get_blog_prefix( $blog_id )."options";
				$sql = "DELETE FROM $table
						WHERE option_name IN( 'redirection_options', 'redirection_version' )";
						
				$wpdb->query( $sql );
				var_dump( $sql );
			}
		
		// move inacative depts widget back to sidebar
		case version_compare( $current_version, '1.0', '<' ):
			foreach( $sites as $site ){
				$blog_id = $site['blog_id'];
				switch_to_blog( $blog_id );
				
				$widgets = get_option( 'sidebars_widgets' );
				foreach( $widgets['wp_inactive_widgets'] as $k=>$inactive ){
					if( strpos($inactive, 'kcmo_dept') === 0 ){
						array_unshift( $widgets['department-contact'], $inactive );
						unset( $widgets['wp_inactive_widgets'][$k] );
					}
				}
				
				update_option( 'sidebars_widgets', $widgets );
			}
			
			break;
		
		// move inacative youtube widget back to sidebar
		case version_compare( $current_version, '1.0.1', '<' ):
			foreach( $sites as $site ){
				$blog_id = $site['blog_id'];
				switch_to_blog( $blog_id );
				
				$widgets = get_option( 'sidebars_widgets' );
				foreach( $widgets['wp_inactive_widgets'] as $k=>$inactive ){
					if( strpos($inactive, 'kcmo_youtube') === 0 ){
						array_unshift( $widgets['footer-video'], $inactive );
						unset( $widgets['wp_inactive_widgets'][$k] );
					}
				}
				
				update_option( 'sidebars_widgets', $widgets );
			}
			
			break;
		
		// move widget areas that are overfilled back to inactive
		case version_compare( $current_version, '1.0.2', '<' ):
			foreach( $sites as $site ){
				$blog_id = $site['blog_id'];
				switch_to_blog( $blog_id );
				
				$widgets = get_option( 'sidebars_widgets' );
				
				foreach( $widgets['department-contact'] as $k=>$inactive ){
					if( $k > 0 ){
						array_unshift( $widgets['wp_inactive_widgets'], $inactive );
						unset( $widgets['department-contact'][$k] );
					}
				}
				
				foreach( $widgets['footer-video'] as $k=>$inactive ){
					if( $k > 0 ){
						array_unshift( $widgets['wp_inactive_widgets'], $inactive );
						unset( $widgets['footer-video'][$k] );
					}
				}
				
				update_option( 'sidebars_widgets', $widgets );
			}
			
			break;
		
		// update stylesheet and templates that have switched to 2014
		case version_compare( $current_version, '1.0.3', '<' ):
			foreach( $sites as $site ){
				$blog_id = $site['blog_id'];
				
				$table = $wpdb->get_blog_prefix( $blog_id )."options";
				$sql = "UPDATE $table SET option_value = 'kcmo.gov'
						WHERE option_name IN( 'stylesheet', 'template' ) and option_value = 'twentyfourteen'";
						
				$wpdb->query( $sql );
			}
			
			break;
		
		// move widget areas that are overfilled back to inactive
		case version_compare( $current_version, '1.0.4', '<' ):
			foreach( $sites as $site ){
				$blog_id = $site['blog_id'];
				switch_to_blog( $blog_id );
				
				$widgets = get_option( 'sidebars_widgets' );
				
				foreach( $widgets['wp_inactive_widgets'] as $k=>$inactive ){
					// move youtube
					if( strpos($inactive, 'kcmo_youtube') === 0 ){
						array_unshift( $widgets['footer-video'], $inactive );
						unset( $widgets['wp_inactive_widgets'][$k] );
					}
					
					// move depts 
					if( strpos($inactive, 'kcmo_dept') === 0 ){
						array_unshift( $widgets['department-contact'], $inactive );
						unset( $widgets['wp_inactive_widgets'][$k] );
					}
					
					// move tweets 
					if( strpos($inactive, 'latest_tweets') === 0 ){
						array_unshift( $widgets['home-twitter'], $inactive );
						unset( $widgets['wp_inactive_widgets'][$k] );
					}
				}
				
				foreach( $widgets as $index => &$section ){
					if( is_array($section) )
						rsort( $section );
					
					$extras = array();
					
					if( in_array($index, array('footer-video','department-contact','home-twitter')) ){
						$extras = array_slice( $section, 1 );
						$section = array_slice( $section, 0, 1 );
						
						$widgets['wp_inactive_widgets'] = array_merge( $widgets['wp_inactive_widgets'], $extras );
					}
				}
				
				update_option( 'sidebars_widgets', $widgets );
			}
			
			break;
								
		// do this every time
		default:
			// flush_rewrite_rules(); does not work at this point
			foreach( $sites as $site ){
				$blog_id = $site['blog_id'];
				
				$table = $wpdb->get_blog_prefix( $blog_id )."options";
				$sql = "DELETE FROM $table
						WHERE option_name = 'rewrite_rules'";
						
				$wpdb->query( $sql );
				var_dump( $sql );
			}
			break;
	}
	
	update_site_option( 'kcmo_mu_version', KCMO_MU_VERSION );
	var_dump( KCMO_MU_VERSION, "Site Updated" ); die();
}

add_action( 'plugins_loaded', create_function( '', 'return kcmo_do_upgrade("'.$current_version.'");') );


