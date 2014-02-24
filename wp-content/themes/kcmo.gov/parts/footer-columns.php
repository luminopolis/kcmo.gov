<div class="events">
	<h3>UPCOMING EVENTS</h3>
	<?php 
	if( isset( $google_calendar ) && trim( $google_calendar ))
		echo $google_calendar;
	?>
</div>

<div class="search">
	<h3>TOP SEARCHES</h3>
	<ol>
		<li><a href="<?php echo get_blog_option( 1, 'siteurl' ); ?>/?s=snow information">Snow information</a></li>
		<li><a href="<?php echo get_blog_option( 1, 'siteurl' ); ?>/?s=tax information">Tax information</a></li>
		<li><a href="<?php echo get_blog_option( 1, 'siteurl' ); ?>/?s=ntdf information">NTDF information</a></li>
		<li><a href="<?php echo get_blog_option( 1, 'siteurl' ); ?>/?s=finance government ethics committee">Finance Government Ethics Committee</a></li>
		<li><a href="<?php echo get_blog_option( 1, 'siteurl' ); ?>/?s=streetcar route">Streetcar route</a></li>
		<li><a href="<?php echo get_blog_option( 1, 'siteurl' ); ?>/?s=report a pot hole">Reporting a pot hole</a></li>
		<li><a href="<?php echo get_blog_option( 1, 'siteurl' ); ?>/?s=barking dog">Barking dog</a></li>
		<li><a href="<?php echo get_blog_option( 1, 'siteurl' ); ?>/?s=city hall hours">City Hall hours</a></li>
		<li><a href="<?php echo get_blog_option( 1, 'siteurl' ); ?>/?s=police reports">Police reports</a></li>
		<li><a href="<?php echo get_blog_option( 1, 'siteurl' ); ?>/?s=apply for a program">Apply for a program</a></li>
	</ol>
</div>

<div class="video">
	<?php dynamic_sidebar( 'footer-video' ); ?>
</div>