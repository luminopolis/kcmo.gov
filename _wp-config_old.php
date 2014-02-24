<?php

// qdi478t.wpengine.com

if( !defined('ABSPATH') )
	define( 'ABSPATH', dirname(__FILE__) . '/' );

if( file_exists(ABSPATH.'env.development.php') ){
	$config = include ABSPATH.'env.development.php';
} elseif( file_exists(ABSPATH.'env.staging.php') ){
	$config = include ABSPATH.'env.staging.php';
} elseif( file_exists(ABSPATH.'env.production.php') ){
	$config = include ABSPATH.'env.production.php';
} else {
	header( 'HTTP/1.0 500 Server Misconfiguration' );
	die( 'Missing or invalid environment' );
}

if( empty($config) ){
	header( 'HTTP/1.0 500 Server Misconfiguration' );
	die( 'Missing or empty config for active-env' );
}


foreach( $config as $config_key => $config_value ){
	if( strtoupper( $config_key ) === $config_key ){
		define( $config_key, $config_value );
	} else {
		$GLOBALS[$config_key] = $config_value;
	}
}

unset( $config );

/*SSLSTART*/ if ( isset($_SERVER['HTTP_X_WPE_SSL']) && $_SERVER['HTTP_X_WPE_SSL'] ) $_SERVER['HTTPS'] = 'on'; /*SSLEND*/

umask(0002);

$wpe_cdn_uris=array ();

$wpe_no_cdn_uris=array ();

$wpe_content_regexs=array ();

$wpe_all_domains=array (  0 => 'kcmo.gov',  1 => 'qdi478t.wpengine.com',);

$wpe_varnish_servers=array (  0 => 'pod-1598',);

$wpe_special_ips=array ();

$wpe_ec_servers=array ();

$wpe_largefs=array ();

$wpe_netdna_domains=array (  0 =>   array (    'match' => 'qdi478t.wpengine.com',    'zone' => '3b6mh91it59y1qcgu6f75y09nm',    'enabled' => true,  ),);

$wpe_netdna_domains_secure=array ();

$wpe_netdna_push_domains=array ();

$wpe_domain_mappings=array ();

$memcached_servers=array (  'default' =>   array (    0 => 'unix:///tmp/memcached.sock',  ),);

require_once ABSPATH . 'wp-settings.php';

$_wpe_preamble_path = null; if(false){}

define('DB_HOST','127.0.0.1');

define('DB_HOST_SLAVE','localhost');

define('DB_NAME','wp_qdi478t');

define('DB_USER','qdi478t');

define('DB_PASSWORD','WLlmIQspex6n8lAUAwLf');

define('WP_CACHE',TRUE);

define('WP_AUTO_UPDATE_CORE',false);

define('PWP_NAME','qdi478t');

define('FS_METHOD','direct');

define('FS_CHMOD_DIR',0775);

define('FS_CHMOD_FILE',0664);

define('PWP_ROOT_DIR','/nas/wp');

define('WPE_APIKEY','77cb26c233cd28562c1834a5f8f3315a7011056d');

define('WPE_FOOTER_HTML',"");

define('WPE_CLUSTER_ID','1598');

define('WPE_CLUSTER_TYPE','pod');

define('WPE_ISP',true);

define('WPE_BPOD',false);

define('WPE_RO_FILESYSTEM',false);

define('WPE_LARGEFS_BUCKET','largefs.wpengine');

define('WPE_CDN_DISABLE_ALLOWED',true);

define('DISALLOW_FILE_EDIT',FALSE);

define('DISALLOW_FILE_MODS',FALSE);

define('DISABLE_WP_CRON',false);

define('WPE_FORCE_SSL_LOGIN',false);

define('FORCE_SSL_LOGIN',false);

define('WPE_EXTERNAL_URL',false);

define('WP_POST_REVISIONS',FALSE);

define('WPE_WHITELABEL','wpengine');

define('WP_TURN_OFF_ADMIN_BAR',false);

define('WPE_BETA_TESTER',false);
