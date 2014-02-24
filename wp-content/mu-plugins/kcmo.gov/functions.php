<?php

/*
*	converts <br/> to \n
*	@param string
*	@return string
*/
function br2nl( $string ){
    $string = preg_replace( '/\<br(\s*)?\/?\>/i', "\n", $string );
    
    return $string;
}

/*
*
*	@param string
*	@param string
*	@param int
*	@return string
*/
function kcmo_default_hero_content( $title, $content, $words = 60 ){
	global $blog_id;
	$original_blog = $blog_id;
	
	if( !trim($content) ){
		switch_to_blog( 1 );
		
		$post_id = get_option( 'page_on_front' );
		$post = get_post( $post_id );
		
		$content = apply_filters( 'the_content', $post->post_content );
		
		$title = get_the_title( $post_id );
	} else {
		$post_id = get_the_ID();
	}
		
	$permalink = get_permalink( $post_id );
	
	$return = '<h3>'.$title.'</h3>';
	
	$return .= wp_trim_words( $content, $words, ' […]<p class="more"><a href="' . $permalink . '">Read More</a></p>' );
	
	$edit_link = get_edit_post_link( $post_id );
	if( $edit_link )
		$return .= '<a class="post-edit-link" href="' . $edit_link . '">Edit This</a>';
		
	switch_to_blog( $original_blog );
	
	return $return;
}

/*
*	array map callback
*	@param object
*	@return int
*/
function ids( $r ){
	return (int) $r->ID;
}

/*
*	array map callback
*	@param object
*	@return int
*/
function term_taxonomy_ids( $r ){
	return (int) $r->term_taxonomy_id;
}

/*
*	array map callback
*	@param object
*	@return int
*/
function term_ids( $r ){
	return (int) $r->term_id;
}

/*
*	array map callback
*	@param array
*	@return string
*/
function titles( $r ){
	return $r['title'];
}

/*
*
*	@param string
*	@return string
*/
function wrap_in_quotes( $s ){
	return "'$s'";
}