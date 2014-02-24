<?php

namespace luminopolis_sitemap;

class Sitemap_Walker extends \Walker{
	public $db_fields = array(
		'id' => 'id',
		'parent' => 'parent'
	);
	
	public function start_lvl( &$output, $depth = 0, $args = array() ){
		$output .= '<ul>';
	}
	
	public function end_lvl( &$output, $depth = 0, $args = array() ){
		$output .= '</ul>';
	}
	
	public function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ){
		if( $object->post_title )
			$output .= '<li><a href="'.$object->permalink.'">'.$object->post_title.'</a>';
	}
	
	public function end_el( &$output, $object, $depth = 0, $args = array() ){
		$output .= '</li>';
	}
}