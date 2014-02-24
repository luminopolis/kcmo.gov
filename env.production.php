<?php

// @see https://github.com/x-team/config-driven-wp/
return array(
	// wp_engine prod
	'DB_HOST' => '127.0.0.1',
	'DB_HOST_SLAVE' => 'localhost',
	'DB_NAME' => 'wp_qdi478t',
	'DB_PASSWORD' => 'WLlmIQspex6n8lAUAwLf',
	'DB_USER' => 'qdi478t',
	'DB_CHARSET' => 'utf8',
	'DB_COLLATE' => 'utf8_unicode_ci',
	
	'table_prefix' => 'kcmosand_',	//'wp_'

	'WP_DEBUG' => TRUE,
	'WPLANG' => '',
	
	'ENVIRONMENT' => 'PROD',	// custom
	
	// http://strongpasswordgenerator.com/ use 40+ characters
	'AUTH_KEY'         => 'Mx9TDkGrRgHGSXE4GeOgRqIfIZgfOZoKmau7aEjnEfINfgP8mr',
	'SECURE_AUTH_KEY'  => 'e5baofWk6DsitCSVOcnkxGC3BipTlAkYOs3MWQkUzet8dZ6e4P',
	'LOGGED_IN_KEY'    => '35AM80EqH426196F2eZ4pEJ4yd18cE101mF4wua3jh8a0ie0Gf',
	'NONCE_KEY'        => 'f3ImD0Y24D0z634Y71C34UCYqd66267o4NwF83J4B234C84gdb',
	'AUTH_SALT'        => 'S6gv4ySDO18BvR8NdBR06dGpG7bHqQF143282273Y0umJO7tlZ',
	'SECURE_AUTH_SALT' => 'dV7HTiVf82OTDWckHbuEyaBs2xuyxuLijeMFRTNZgwq8I6bIuc',
	'LOGGED_IN_SALT'   => 'V75252N6f3vG2e3G4E1443nMjz734y2p26EEc5XmsWo0YY4pFs',
	'NONCE_SALT'       => '17ymMLf8ka0x5rme3s2Hdgk97Xv8G7Z78QuWD73831IQEXO3mH',
	'WP_CACHE' => FALSE,
	'WP_AUTO_UPDATE_CORE' => FALSE,
	'PWP_NAME' => 'qdi478t',

	'FS_METHOD' => 'direct',
	'FS_CHMOD_DIR' => 0775,
	'FS_CHMOD_FILE' => 0664,
	'PWP_ROOT_DIR' => '/nas/wp',
	'WPE_APIKEY' => '77cb26c233cd28562c1834a5f8f3315a7011056d',
	'WPE_FOOTER_HTML' => '',
	'WPE_CLUSTER_ID' => '1598',
	'WPE_CLUSTER_TYPE' => 'pod',
	'WPE_ISP' => TRUE,
	'WPE_BPOD' => FALSE,
	'WPE_RO_FILESYSTEM' => FALSE,
	'WPE_LARGEFS_BUCKET' => 'largefs.wpengine',
	'WPE_CDN_DISABLE_ALLOWED' => TRUE,
	'DISALLOW_FILE_EDIT' => FALSE,
	'DISALLOW_FILE_MODS' => FALSE,
	'DISABLE_WP_CRON' => FALSE,
	'WPE_FORCE_SSL_LOGIN' => FALSE,
	'FORCE_SSL_LOGIN' => FALSE,
	'WPE_EXTERNAL_URL' => FALSE,
	'WP_POST_REVISIONS' => FALSE,
	'WPE_WHITELABEL' => 'wpengine',
	'WP_TURN_OFF_ADMIN_BAR' => FALSE,
	'WPE_BETA_TESTER' => FALSE,
	//
	
	// Multisite constants
	'WP_ALLOW_MULTISITE' => TRUE,
	'MULTISITE' => TRUE,
	'SUBDOMAIN_INSTALL' => FALSE,
	'base' => '/',
	//'DOMAIN_CURRENT_SITE' => 'kcmo.gov',	
	'DOMAIN_CURRENT_SITE' => 'qdi478t.wpengine.com',
	'PATH_CURRENT_SITE' => '/',
	'SITE_ID_CURRENT_SITE' => 1,
	//'BLOG_ID_CURRENT_SITE' => 1
);
