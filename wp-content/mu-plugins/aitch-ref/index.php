<?php
/*
Plugin Name: aitch-ref!
Plugin URI: http://wordpress.org/extend/plugins/aitch-ref/
Description: href junk. Requires PHP >= 5.2 and Wordpress >= 3.0
Version: 0.86
Author: Eric Eaglstun
Author URI: http://ericeaglstun.com
*/

class AitchRef{
	// these will be overwritten in setup()
	private static $baseurl = 'http://';						// is_ssl()
	private static $blog_id = 1;								// multiuser support
	private static $cwd = '/var/www/plugins/aitch-ref';			// full server path to current directory
	private static $path = '/wp-content/plugins/aitch-ref/';	// web accessible path to current to current directory, w trailing slash
	
	private static $possible = array();							// a list of the possible base urls that 
																// can be replaced
	
	// only used in admin
	private static $messages = array();							// error / success messages to user
	private static $render = '';								// path to view being rendered (currently only admin.php)
	 
	/*
	*	runs once when plugin has loaded, sets up vars and adds filters/actions
	*	@return NULL
	*/
	public static function setup(){
		$pathinfo = pathinfo(__FILE__);
		
		global $blog_id;
		self::$blog_id = $blog_id;
		self::$possible = self::getUrls( TRUE );
		
		self::$baseurl = is_ssl() ? 'https://'.$_SERVER['HTTP_HOST'] : 'http://'.$_SERVER['HTTP_HOST'];
		self::$cwd = $pathinfo['dirname'];
		
		// these can return back urls starting with /
		add_filter( 'bloginfo', 'AitchRef::_site_url' );
		add_filter( 'bloginfo_url', 'AitchRef::_site_url' );
		add_filter( 'content_url', 'AitchRef::_site_url' );
		add_filter( 'get_pagenum_link', 'AitchRef::_site_url' );
		add_filter( 'option_url', 'AitchRef::_site_url' );
		add_filter( 'plugins_url', 'AitchRef::_site_url' );
		add_filter( 'pre_post_link', 'AitchRef::_site_url' );
		add_filter( 'script_loader_src', 'AitchRef::_site_url' );
		add_filter( 'style_loader_src', 'AitchRef::_site_url' );
		add_filter( 'term_link', 'AitchRef::_site_url' );
		add_filter( 'the_content', 'AitchRef::_site_url' );
		add_filter( 'upload_dir', 'AitchRef::_site_url' );
		add_filter( 'url', 'AitchRef::_site_url' );
		add_filter( 'wp_list_pages', 'AitchRef::_site_url' );
		
		// these need to return back with leading http://
		add_filter( 'admin_url', 'AitchRef::_site_url_absolute' );
		add_filter( 'get_permalink', 'AitchRef::_site_url_absolute' ); 
		add_filter( 'home_url', 'AitchRef::_site_url_absolute' );
		add_filter( 'login_url', 'AitchRef::_site_url_absolute' );
		add_filter( 'option_home', 'AitchRef::_site_url_absolute' );
		add_filter( 'option_siteurl', 'AitchRef::_site_url_absolute' );
		add_filter( 'page_link', 'AitchRef::_site_url_absolute' ); 
		add_filter( 'post_link', 'AitchRef::_site_url_absolute' );
		add_filter( 'siteurl', 'AitchRef::_site_url_absolute' );	// ಠ_ಠ DEPRECATED
		add_filter( 'site_url', 'AitchRef::_site_url_absolute' );
		add_filter( 'stylesheet_uri', 'AitchRef::_site_url_absolute' );
		add_filter( 'template_directory_uri', 'AitchRef::_site_url_absolute' );	
		add_filter( 'wp_get_attachment_url', 'AitchRef::_site_url_absolute' );
		
		// admin
		if( !is_admin() )
			return;
		
		self::$path = plugins_url( '', __FILE__ );
		
		add_action( 'admin_menu', 'AitchRef::_admin_menu' );
		add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'AitchRef::_admin_plugins' );
	}
	
	/*
	*	add_filter callback
	*	@param string
	*	@return string
	*/
	public static function _site_url( $url ){
		if( is_array($url) ){
			// this is to fix a bug in 'upload_dir' filter, 
			// $url[error] needs to be a boolean but str_replace casts to string
			$url2 = str_replace( self::$possible, '', array_filter($url) );
			$url2 = array_merge( $url, $url2 );
		} else {
			$url2 = str_replace( self::$possible, '', $url );
		}
		
		//$url2 = str_replace( '//', '/', $url2 );
			
		return $url2;		
	}
	
	/*
	*	add_filter callback
	*	@param mixed
	*	@return string
	*/
	public static function _site_url_absolute( $url ){
		
		if( is_array($url) ){
			// this is to fix a bug in 'upload_dir' filter, 
			// $url[error] needs to be a boolean but str_replace casts to string
			$url2 = str_replace( self::$possible, self::$baseurl, array_filter($url) );
			$url2 = array_merge( $url, $url2 );
		} else {
			$url2 = str_replace( self::$possible, self::$baseurl, $url );
		}
			
		// what is this??
		if( strpos($url2, self::$baseurl) !== 0 && strpos($url2, 'http://') !== 0 ){
			//var_dump($url2);
			//die($url2);
			$url2 = self::$baseurl.$url2;
		}
		return $url2;
	}
	
	/*
	*	show link to admin options in 'settings' sidebar
	*
	*/
	public static function _admin_menu(){
		add_options_page( 'aitch ref! Settings', 'aitch ref!', 'manage_options', 'aitch-ref', 'AitchRef::_options_page' );
	}
	
	/*
	*	add 'settings' link in main plugins page
	*	attached to plugin_action_links_* action
	*	@param array
	*	@return array
	*/
	public static function _admin_plugins( $links ){
		$settings_link = '<a href="options-general.php?page=aitch-ref">Settings</a>';  
		array_unshift( $links, $settings_link );
		return $links;
	}
	
	/*
	*	callback for add_options_page() to render options page in admin 
	*
	*/
	public static function _options_page(){
		if( isset($_POST['urls']) ){
			self::updateUrls( $_POST['urls'] );
		}
		
		$vars = (object) array();
		$vars->messages = implode( "\n", self::$messages );
		$vars->path = self::$path;
		$vars->urls = esc_textarea( self::getUrls() );
		
		self::render( 'admin/options', $vars );
	}
	
	/*
	*	db interaction
	*	@param bool
	*	@return string | array
	*/
	private static function getUrls( $as_array = FALSE ){
		$urls = self::get_option( 'aitchref_urls' );
		
		// backwards compat, now storing this option as a json encoded string cuz im a maverick
		if( !is_array($urls) ){
			$urls = (array) json_decode( $urls );
		}
		
		if( $as_array ){
			return $urls;
		} else {
			$str = implode( "\n", $urls );
			return $str;
		}
	}
	
	/*
	*
	*	@param string
	*	@return
	*/
	private static function updateUrls( $str ){
		$urls = preg_split ("/\s+/", $str);
		$urls = array_map( 'trim', $urls );
		$urls = array_unique( $urls );
		sort( $urls );
		foreach( $urls as $k=>$url ){
			// no trailing slash!
			if( strrpos($url, '/') == (strlen($url)-1) ){
				$urls[$k] = substr( $url, 0, -1 );
			}
		}
		
		$urls = json_encode( $urls );
		self::update_option( 'aitchref_urls', $urls );
		
		array_push( self::$messages, '<div class="updated fade"><p>aitch-ref! updated</p></div>' );
	}
	
	/*
	*	render a page into wherever
	*	(only used in admin screen)
	*	@param string
	*	@param object|array
	*	@return
	*/
	private static function render( $filename, $vars = array() ){
		self::$render = self::$cwd.'/views/'.$filename.'.php';
		if( file_exists(self::$render) ){
			extract( (array) $vars, EXTR_SKIP );
			include self::$render;
		}
	}
	
	// wrappers for get_option, MU / single blog installs
	
	/*
	*
	*	@param
	*	@return
	*/
	private static function get_option( $key ){
		return is_multisite() ? get_blog_option( self::$blog_id, $key ) : get_option( $key );
	}
	
	/*
	*
	*	@param
	*	@param
	*	@return
	*/
	private static function update_option( $key, $val ){
		return is_multisite() ? update_blog_option( self::$blog_id, $key, $val ) : update_option( $key, $val );
	}
	
	/*
	*
	*	@param
	*	@return
	*/
	private static function delete_option( $key ){
		return is_multisite() ? delete_blog_option( self::$blog_id, $key ) : delete_option( $key );
	}
}

if( !function_exists('aitch') ){
	/*
	*	helper for AitchRef to use directly in templates
	*	@param string the url
	*	@param bool to use absolute or not
	*	@return string
	*/
	function aitch( $href, $absolute = FALSE ){
		if( $absolute )
			return AitchRef::_site_url_absolute( $href );
		else
			return AitchRef::_site_url( $href );
	}
}

AitchRef::setup();