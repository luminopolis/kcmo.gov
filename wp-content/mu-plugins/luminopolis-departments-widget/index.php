<?php

if( is_admin() )
	require dirname( __FILE__ ).'/admin.php';
	
/*
*
*/
class Departments_Widget extends WP_Widget {
	/*
	*
	*/
	public function __construct(){
		parent::__construct(
			'kcmo_dept', // base id
			'Departments Contact',
			array( 'class' => 'departments_widget', 
				   'description' => 'Contact Information and Social Widgets' )
		);
	}
	
	/*
	*	formats contact block
	*	auto links email address to mailto
	*	converts new lines to html line breaks
	*	@param string
	*	@return string
	*/
	private function format( $text ){
		$text = nl2br( $text );
		$text = preg_replace( '/(\S+@\S+\.\S+)/', '<a href="mailto:$1">$1</a>', $text );
		return $text;
	}

	/*
	*	outputs the content of the widget
	*	uses `departments_widget` filter 
	*	@param array
	*	@param array
	*/
	public function widget( $args, $instance ){
		$instance['contact'] = $this->format( $instance['contact'] );
		
		foreach( $instance['social'] as $k=>&$v ){
			$html = '<li><a href="'.esc_url( $v['href'] ).'"><img src="'.$v['img'].'" alt="'.$v['alt'].'"></a></li>';
			
			$v['html'] = apply_filters( 'departments_widget', $html, $v );
		}
		
		$this->render( 'frontend', $instance );
	}

	/*
	*	outputs the options form on admin
	*	@param array
	*/
 	public function form( $instance ){
		$vars = array();
		
		$stylesheet_directory = get_bloginfo( 'stylesheet_directory' );
		
		$defaults = array(
			'title' => '311 Call Center',
			'contact' => "City Hall, first floor \n414 E. 12th St.\nKansas City, MO 64106\nPhone: 816-513-1313\n311@kcmo.org",
			
			'social' => array(
				array( 'id' => 'twitter',
						'img' => $stylesheet_directory.'/_media/images/ico_ss_1.png', 
						'href' => 'https://twitter.com/KCMO',
						'title' => 'Twitter',
						'alt' => 'Follow KCMO on Twitter' ),
									
				array( 'id' => 'rss',
						'img' => $stylesheet_directory.'/_media/images/ico_ss_2.png', 
						'href' => get_bloginfo('atom_url'),
						'title' => 'RSS Feed',
						'alt' => 'Atom Feed' ),
								
				array( 'id' => 'facebook',
						'img' => $stylesheet_directory.'/_media/images/ico_ss_3.png', 
						'href' => 'https://www.facebook.com/KCMOgov',
						'title' => 'Facebook',
						'alt' => 'Like KCMO.gov on Facebook' ),
									 
				array( 'id' => 'youtube',
						'img' => $stylesheet_directory.'/_media/images/ico_ss_4.png', 
						'href' => 'https://www.youtube.com/user/KCMOCCO',
						'title' => 'YouTube',
						'alt' => 'KCMO Videos on YouTube' )
			)
		);
		
		$vars = array_merge( $defaults, $instance );
		
		$plugins_url = defined('LUMINOPOLIS_DEPARTMENTS_PLUGINS_URL') ? LUMINOPOLIS_DEPARTMENTS_PLUGINS_URL : plugins_url( '', __FILE__ );
		
		wp_register_style( 'department-widget-style', $plugins_url.'/public/admin.css' );
		wp_enqueue_style( 'department-widget-style' );
		
		wp_register_script( 'department-widget-script',$plugins_url.'/public/admin.js' );
		wp_enqueue_script( 'department-widget-script' );
		 
		$this->render( 'admin/widgets', $vars );
	}
	
	/*
	*	processes widget options to be saved
	*	@param array
	*	@param array
	*	@return array
	*/
	public function update( $new_instance, $old_instance ){
		$social = array();
		foreach( $new_instance['social'] as $k => $item ){
			if( strpos($k, 'new-') === 0 )
				$k = sanitize_title( $item['title'] );
				
			$social[ $item['order'] ] = array(
				'id' => $k,
				'img' => $item['img'],
				'href' => $item['href'],
				'title' => $item['title'],
				'alt' => $item['alt']
			);
		}
		
		$social = array_values( $social );
		$new_instance['social'] = $social;
		
		return $new_instance;
	}
	
	/*
	*
	*	@param string
	*	@param array
	*/
	private function render( $template, $vars = array() ){
		extract( (array) $vars );
		require dirname( __FILE__ ).'/views/'.$template.'.php';
	}
}

add_action( 'widgets_init',
     create_function( '', 'return register_widget("Departments_Widget");' )
);