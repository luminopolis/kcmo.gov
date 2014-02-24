<?php get_header(); ?>

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
						<h2 class="custom_heading">KCMO.GOV Sitemap</h2>
						<ul id="sitemap">
							<?php
							echo $sitemap->walk( $sitemap_items, 0 );
							?>
						</ul>
					
					</div>
				</div>
			</div>
			

<?php get_footer(); ?>