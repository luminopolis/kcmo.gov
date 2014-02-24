<?php 
/*
Template Name: Email Archive
*/
get_header(); 
?>	
		<div id="body" class="withtabs">
			<div class="head-sub">
				<div class="contacts">
					<?php get_template_part( 'parts/social_mini' ); ?>
				</div>
				<?php if( $wp_query->post_count > 1 && $wp_query->posts[0]->has_tabs ): ?>
				<div class="tabs">
					<ul class="sortable-list" data-sortable='{"items":"li:not(:first)","id":"page-tabs"}'>
						<?php foreach( $wp_query->posts as $k=>$v ): ?>
						<li data-sortable-id="<?php echo $v->ID; ?>"><a href="#<?php echo kcmo_children_permalink_tab( $k ); ?>"><span><?php echo kcmo_children_title( $v ); ?></span></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php endif; ?>
				<?php get_template_part( 'parts/head-logo' ); ?>
				<div class="image"><img src="<?php echo $stylesheet_directory; ?>/_media/images/3.jpg" alt=""></div>
			</div>
			<div id="content">
				<?php while( $wp_query->have_posts() ): $wp_query->the_post(); ?>
				
					<div id="">
						<div class="entry-content left-col">
							<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						
							<?php edit_post_link(); ?>
						</div>
					</div>
				
				<?php endwhile; ?>
			</div>
			<aside id="sidebar">
				<div class="details">
					<?php echo do_shortcode( '[kcmo_bannerspace location=sidebar size=banner-side class=image show_arrows=0 show_paging=0]' ); ?>
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
