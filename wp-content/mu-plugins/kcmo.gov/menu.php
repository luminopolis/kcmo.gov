<?php

/*
*
*/
class KCMO_Walker_Footer_Menu extends Walker_Nav_Menu{
	/*
	*	markup is simply anchor links
	*	@param string
	*/
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ){
		// @TODO when there are no items in 'Footer Menu' this returns posts where title is not set??
		if( trim($item->title) )
			$output .= '<a class="link" data-sortable-id="'.$item->ID.'" href="'.$item->url.'">'.$item->title.'</a>';
	}
}	

/*
*
*/
class KCMO_Walker_Nav_Menu extends Walker_Nav_Menu{
	public $secondary_output = '';
	public $selected_color = '';
	
	private $current_parent = 0;
	private $secondary_items = array();		// output the drop down menus in a secondary content block
	
	/*
	*
	*	@param string
	*/
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ){
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
		
		$class_names = $value = '';

		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;
		
		if( in_array('menu-item-has-children', $classes) ){
			$this->current_parent = $item->ID;
			$classes[] = 'dropdown';
		}
			
		// add the color to each new top level link
		if( $depth == 0 )
			$classes[] = 'color-'.kcmo_nav_current_color( TRUE );
		
		// keep track of current color for logo color
		if( in_array('current-menu-item', $classes) )
			$this->selected_color = kcmo_nav_current_color();
			
		$classes[] = 'depth-'.$depth;
		
		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args) );
		$class_names = ' class="' . esc_attr( $class_names ) . '"';

		$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';
		
		$atts = array();
		$atts['title'] = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target ) ? $item->target	 : '';
		$atts['rel'] = ! empty( $item->xfn ) ? $item->xfn		: '';
		$atts['href'] = ! empty( $item->url ) ? $item->url		: '';
		
		$data = '';
		
		// add data toggle
		if( $depth < 1 && in_array('menu-item-has-children', $classes) )
			$atts['data-toggle'] = 'dropdown';
			
		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args );
		
		$attributes = '';
		foreach( $atts as $attr => $value ){
			if( !empty($value) ){
				$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}
		
		// why is this sometimes not an object?
		if( !is_object($args) )
			$args = (object) $args;
			
		$item_output = $args->before;
		$item_output .= $indent . '<li data-menu-id="'.$item->ID.'" ' . $id . $class_names .'>';
		$item_output .= '<a'. $attributes .'>';
		
		//duplicate_hook
		$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
		$item_output .= '</a>';
		$item_output .= $args->after;
		
		$html = apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		
		switch( $depth ){
			case 0:
				$output .= $html;
				break;
				
			case 1:
				$this->secondary_items[$item->ID] = $html;
				break;
		}
	}
	
	/*
	*
	*	@param string
	*	@param int
	*	@param array
	*
	*/
	public function start_lvl( &$output, $depth = 0, $args = array() ){
		$indent = "\n".str_repeat("\t", $depth);
		
		//$html = $indent.'<ul data-menu-id="'.$this->current_parent.'" class="dropdown-menu color-'.kcmo_nav_current_color().' depth-'.$depth.'">';
		
		switch( $depth ){
			case 0:
				$this->secondary_output .= '<div data-menu-id="'.$this->current_parent.'" class="item dropdown-menu color-'.kcmo_nav_current_color().' depth-'.$depth.'">'; 
				break;
				
			//default:
			//	ddbug( func_get_args() );
			//	break;
		}
	}
	
	/*
	*
	*	@param string
	*	@param
	*	@param int
	*	@param array
	*/
	public function end_el( &$output, $item, $depth = 0, $args = array() ){
		$html = "</li>\n";
		
		switch( $depth ){
			case 0:
				$output .= $html;
				break;
			case 1:
				//$this->secondary_output .= $html;
				break;
				
			//default:
			//	ddbug( $item->post_name );
			//	break;
		}
	}
	
	/*
	*
	*	@param string
	*	@param int
	*	@param array
	*
	*/
	public function end_lvl( &$output, $depth = 0, $args = array() ){
		$indent = str_repeat("\t", $depth);
		$html = '';
		
		switch( $depth ){
			case 0:
				// put into 4 columns
				$items_in_col = ceil( count($this->secondary_items) / 4 );
				
				$columns = array_chunk( $this->secondary_items, $items_in_col );
				foreach( $columns as $column ){
					$html .= '<ul>'.implode( '', $column ).'</ul>';
				}
				
				$this->secondary_output .= $html.$indent."</div> <!-- end col -->\n";
				$this->secondary_items = array();
				break;
				
			//default:
			//	ddbug( func_get_args() );
			//	break;
		}
	}
}

/*
*
*	@parm bool increment color count (true for top level items only)
*	@return int
*/
function kcmo_nav_current_color( $inc = FALSE ){
	static $color = 0;
	
	if( $inc )
		$color++;
		
	return $color;
}