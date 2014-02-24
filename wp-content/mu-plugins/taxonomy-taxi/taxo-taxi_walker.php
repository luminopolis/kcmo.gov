<?php
/*
*	builds nested drop down selects in wp-admin/edit.php
*	sets value to be term slug rather than term id
*	Version: .77
*/
class Walker_Taxo_Taxi extends Walker_CategoryDropdown{
	
	/*
	*	
	*	@param string
	*	@param object
	*	@param int
	*	@param array
	*	@param int
	*	@return
	*/
	public function start_el( &$output, $category, $depth = 0, $args = array(), $current_object_id = 0 ){
		$pad = str_repeat( '&nbsp;', $depth * 2 );
		$cat_name = apply_filters('list_cats', $category->name, $category);
		
		if( !isset($args['value']) ){
			$args['value'] = ( $category->taxonomy != 'category' ? 'slug' : 'id' );
		}
		
		$output .= "<option class=\"level-$depth\" value=\"".$category->slug."\"";
		
		if( $category->slug === $args['selected'] )
			$output .= ' selected="selected"';
		
		$output .= '>';
		$output .= $pad.$cat_name;
		
		if( $args['show_count'] )
			$output .= '&nbsp;&nbsp;('. $category->count .')';
	
		$output .= "</option>\n";
	}
}