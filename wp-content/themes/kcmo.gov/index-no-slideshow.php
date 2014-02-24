<?php 
/*
Template Name: No Side Slideshow
*/
get_header(); 
?>
		<div id="body" class="withtabs">
			<div class="head-sub">
				<div class="contacts">
					<?php get_template_part( 'parts/social_mini' ); ?>
				</div>
				<?php if( $wp_query->post_count > 1 ): ?>
				<div class="tabs">
					<ul class="sortable-list" data-sortable='{"items":"li","id":"tabs"}'>
						<?php foreach( $wp_query->posts as $k=>$v ): ?>
						<li data-sortable-id="<?php echo $v->ID; ?>"><a href="#<?php echo kcmo_children_permalink_tab( $k ); ?>"><span><?php echo kcmo_children_title( $v ); ?></span></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php endif; ?>
				<?php get_template_part( 'parts/head-logo' ); ?>
				<div class="image"><img src="<?php echo $stylesheet_directory; ?>/_media/images/3.jpg" alt=""></div>
			</div>
			<div id="content" class="full-width">
				<?php while( $wp_query->have_posts() ): $wp_query->the_post(); ?>
				<div id="<?php echo kcmo_children_permalink_tab( $wp_query->current_post ); ?>">
					<div class="entry-content left-col">
						<h2 class="custom_heading"><?php the_title(); ?></h2>
						<?php the_content(); ?>
						<?php edit_post_link(); ?>
					</div>
				</div>
				<?php endwhile; ?>
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
