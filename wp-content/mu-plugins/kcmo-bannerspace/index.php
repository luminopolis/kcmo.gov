<?php
/*
Plugin Name:	KCMO Custom Bannerspace Shortcode
Plugin URI: 
Description:	Allows for for parameters to be passed into the [kcmo_bannerspace] shortcode.
Author:			Luminopolis / Eric Eaglstun
Version:		1.0
Author URI: 	http://luminopolis.com/
*/

// remove inline styles, recreate inline js
remove_action( 'wp_head', 'bannerspace_wp_headers', 10 );

/*
*	callback for 'kcmo_bannerspace' shortcode
*	@param array
*	@return
*/
function kcmo_bannerspace_shortcode( $atts ){
	global $blog_id, $post;
	$current_blog = $blog_id;
	
	$preload = array();
	
	$options = get_option( 'bs_options' );
	
	extract( shortcode_atts(array(
		'class' => '',
		'location' => '',
		'show_arrows' => 1,
		'show_content' => 1,
		'show_title' => 1,
		'size' => 'bannerspace'
	), $atts) );
	
	$bannerspace_wp_plugin_path = plugins_url( '', __FILE__ );
	
	$output_buffer = '';
	
	if( $show_arrows )
		$output_buffer .= '<img style="display:none; visibility:hidden; " src="'.$bannerspace_wp_plugin_path.'/public/l_arrow.png" />
						   <img style="display:none; visibility:hidden; " src="'.$bannerspace_wp_plugin_path.'/public/r_arrow.png" />';
	
	$output_buffer .= '<div id="bannerspace_wrap">
						<div id="bannerspace">';
		
	$sx = 0;
	
	$args = array( 'network_wide' => TRUE,
				   'network_wide_cache' => 60,
				   'post_type' => 'bannerspace_post', 
				   'orderby' => 'rand', 
				   //'orderby' => 'current_blog',
				   'posts_per_page' => -1,
				   'tax_query' => array(
						array(
							'taxonomy' => 'content-location',
							'field' => 'slug',
							'terms' => $location
						)
				  ) );
				   
	$loop = new WP_Query( $args );
						 
	while( $loop->have_posts() ) :
		$sx++;
		
		$loop->the_post();
		
		switch_to_blog( $post->blog_id );
						
		$link = get_post_meta( $post->ID, 'link', true );
			
		$output_buffer .='				
			<div class="slide s'.$sx.'">
				';
				if( !empty($link) ) : 
					$output_buffer .='<a href="'.$link.'">';
				endif;
				
				$image = get_the_post_thumbnail( $post->ID, $size, array(
					'class' => $class
				) );
				
				$preload[$sx] = $image;
				
				// show first one inline, load the rest when page is loaded
				if( $sx != 1 )
					$image = '';
			
					
				$output_buffer .='		
					<!--image slider-->
					<div class="imageWrapper">'.$image.'</div>
					
					<div class="content">';
				
				if( $show_title )
					$output_buffer .= '<h2>' . get_the_title('') . '</h2>';
				
				if( $show_content )	
					$output_buffer .= '<p>' . get_the_content('') . '</p>';
				
				$output_buffer .= '</div>';
				
				if( !empty($link) ) : 
					$output_buffer .='</a>';
				endif;
			
		$output_buffer .='
			</div>';
	
	endwhile; 
	
	$output_buffer .= '</div>';
	
	if( $show_arrows ){
		$output_buffer .= '<a href="javascript: void(0);" id="bs_l_arrow" class="bs_arrow"></a>
						   <a href="javascript: void(0);" id="bs_r_arrow" class="bs_arrow"></a>';
	}
		
	
	$output_buffer .= '</div><div class="bs_clear"></div>';
	
	wp_reset_postdata();
	
	switch_to_blog( $current_blog );
	
	switch( $options['auto_play'] ){
		case 3:
			$autoplay = 'resume';
			break;
			
		default:
			$autoplay = 'pause';
			break;
	}
	
	wp_register_script( 'bannerspace', plugins_url('public/index.js', __FILE__), array(), '', TRUE );
	wp_enqueue_script( 'bannerspace' );
	
	wp_localize_script( 'bannerspace', 'banner_preload', $preload );
	wp_localize_script( 'bannerspace', 'banner_autoplay', $autoplay );
	
	return $output_buffer;
}

add_shortcode( 'kcmo_bannerspace', 'kcmo_bannerspace_shortcode' );