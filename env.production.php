<?php

// @see https://github.com/x-team/config-driven-wp/
return array(
	'DB_HOST' => '127.0.0.1',
	'DB_HOST_SLAVE' => 'localhost',
	'DB_NAME' => 'DB NAME HERE',
	'DB_PASSWORD' => 'DB PASS HERE',
	'DB_USER' => 'DB USER HERE',
	'DB_CHARSET' => 'utf8',
	'DB_COLLATE' => 'utf8_unicode_ci',
	
	'table_prefix' => 'table_prefix_',	//'wp_'

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
