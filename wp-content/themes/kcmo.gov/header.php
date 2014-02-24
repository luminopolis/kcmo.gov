<!DOCTYPE html>
<!--[if IE 8]> <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<meta name="google-site-verification" content="u1DyD_72zV79DtxeFRF7GDq6Fje9H5ygt_GZofwxQAI" />
	<title>KCMO.gov <?php wp_title(); ?></title>
	
	<link rel="stylesheet" media="all" href="<?php echo $stylesheet_directory; ?>/_ui/css/bootstrap.css">
	<!--[if lt IE 9]>
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/assets/ico/favicon.ico" />
	
	<?php wp_head(); ?>
	<script type="text/javascript" src="//use.typekit.net/mkn1xtd.js"></script>
	<script type="text/javascript">try{Typekit.load();}catch(e){}</script>
	<script>document.documentElement.className = document.documentElement.className.replace(/(\s|^)no-js(\s|$)/, '' );</script>
	<script>
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga' );
		
		ga( 'create', 'UA-12129530-3', 'kcmo.gov' );
		ga( 'send', 'pageview' );
	</script>
	
</head>

<body class=""><!-- qdi478t -->
	<div class="navbar navbar-default">
		<div class="headbar">
			<?php echo kcmo_menu_main(); ?>
		</div>
	</div>
	
	<div class="container">
		<div class="subs">
			<?php echo kcmo_menu_secondary(); ?>
		</div>
	</div>
	
	<div class="container <?php echo kcmo_menu_color(); ?>">

		