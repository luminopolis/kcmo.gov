<?php
/*
Template Name: List post links
*/

switch_to_blog( get_current_blog_id() );
query_posts('post_type=page&posts_per_page=-1');
if (have_posts()) :
   while (have_posts()) : the_post();
      the_permalink(); ?><br /><?php
   endwhile;
endif;
?>