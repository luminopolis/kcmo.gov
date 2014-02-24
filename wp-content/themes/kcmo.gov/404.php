<?php 
/*
Template Name: 404
*/
get_header(); 
?>
		<div id="body" class="withtabs">
			<div class="head-sub">
				<div class="contacts">
					<?php get_template_part( 'parts/social_mini' ); ?>
				</div>
				<?php get_template_part( 'parts/head-logo' ); ?>
				<div class="image"><img src="<?php echo $stylesheet_directory; ?>/_media/images/3.jpg" alt=""></div>
			</div>
			<div id="content">
				<div id="">
					<div class="entry-content left-col">
						<h2>404</h2>
						<p>Sorry, we could not find the page you are looking for, please use the search box above.</p>
					</div>
				</div>
			</div>
			<aside id="sidebar">
				<div class="details">
					<?php echo do_shortcode( '[kcmo_bannerspace category_name=sidebar size=banner-side class=image show_arrows=0]' ); ?>
				</div>
			</aside>
			<!-- / sidebar -->
			<?php get_template_part( 'parts/feedback' ); ?>
			<footer id="footer">
				<div class="widgets">
					<?php get_template_part( 'parts/footer-columns' ); ?>
				</div>
				<div class="bottom">
					<div class="left-col">
						<?php get_template_part( 'parts/skycast' ); ?>
					</div>
					
					<?php get_template_part( 'parts/footer-links-main' ); ?>
					
					<div class="media">
						<?php get_template_part( 'parts/footer-links-callout' ); ?>
						
						<?php get_template_part( 'parts/social' ); ?>
					</div>
				</div>
			</footer>
			<!-- / footer -->
		</div>
	

<?php get_footer(); ?>
