<?php 
/*
Template Name: 3 Column / Department Home
*/
get_header(); 
?>
		<div id="body" class="withtabs"><!-- qdi478t -->
			<div class="slider">
				<?php get_template_part( 'parts/head-logo' ); ?>
				
				<?php echo do_shortcode( '[kcmo_bannerspace size=banner-top location=home show_title=0 show_content=0]' ); ?>
			</div>
			<div class="left-main">
				<div class="search custom_bk">
					<form action="<?php echo home_url( '/' ); ?>">
						<label>How may we serve you today? </label>
						<input type="submit" value="">
						<input type="text" name="s" value="">
					</form>
				</div>
				<section class="services">
					<?php if( kcmo_query_about()->have_posts() ): ?>
						<?php while( kcmo_query_about()->have_posts() ): kcmo_query_about()->the_post(); ?>
						<article>
							<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'front-column' ); ?></a>
							<h3 class="custom_heading one"><?php the_title(); ?></h3>
							<p><?php echo get_the_excerpt(); ?></p>
							<?php edit_post_link(); ?>
							<p class="more"><a href="<?php the_permalink(); ?>">Read More</a></p>
							<div class="contacts">
								<?php get_template_part( 'parts/social_mini' ); ?>
							</div>
						</article>
						<?php endwhile; ?>
					<?php else: ?>
						<article>
							<div class="contacts">
								<?php get_template_part( 'parts/social_mini' ); ?>
							</div>
						</article>
					<?php endif; ?>
					
					<?php while( kcmo_query_news()->have_posts() ): kcmo_query_news()->the_post(); ?>
					<article>
						<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'front-column' ); ?></a>
						<h3 class="custom_heading"><?php the_title(); ?></h3>
						<p><?php echo get_the_excerpt(); ?></p>
						<?php edit_post_link(); ?>
						<p class="more"><a href="<?php the_permalink(); ?>">Read More</a></p>
					</article>
					<?php endwhile; ?>
					
					
				</section>
			</div>
			<div class="homesidebar">
				<div class="text-box custom_bk">
					<?php if( $wp_query->have_posts() ): while( $wp_query->have_posts() ): ?>
						<?php $wp_query->the_post(); ?>
						<?php echo kcmo_default_hero_content( get_the_title(), get_the_content(), 60 ); ?>
					<?php endwhile; else: ?>
						<?php echo kcmo_default_hero_content( '', '', 60 ); ?>
					<?php endif; ?>
				</div>
				<div class="tweets">
					<script type="text/javascript" src="//platform.twitter.com/widgets.js"></script>
					
					<?php dynamic_sidebar( 'home-twitter' ); ?>
				</div>
			</div>
			
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