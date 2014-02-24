<?php 
/*
Template Name: Search Results
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
			<div id="content" class="full-width">
				<gcse:searchresults-only queryParameterName="s">
				<?php if( $wp_query->have_posts() ): ?>
					<?php while( $wp_query->have_posts() ): $wp_query->the_post(); ?>
					<div class="hide-if-js">
						<div class="entry-content left-col">
							<?php switch_to_blog( $post->blog_id ); ?>
							<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<?php the_excerpt(); ?>
							<?php edit_post_link(); ?>
						</div>
					</div>
					<?php endwhile; ?>
					
					<?php restore_current_blog(); ?>
					
				<?php endif; ?>
				</gcse:searchresults-only>
			</div>
			<!-- <aside id="sidebar">
				<div class="details">
					<?php echo do_shortcode( '[kcmo_bannerspace category_name=sidebar size=banner-side class=image show_arrows=0]' ); ?>
				</div>
			</aside>
			/ sidebar -->
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
