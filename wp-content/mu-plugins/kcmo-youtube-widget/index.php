<?php
/*
Plugin Name: KCMO YouTube widget
Plugin URI: Footer video with fallback to default
Description: 
Author: Luminopolis / Eric Eaglstun
Version: 1.1
Author URI: 
*/

/*
*
*/
class Youtube_Widget extends WP_Widget {
	/*
	*
	*/
	public function __construct(){
		parent::__construct(
			'kcmo_youtube', // base id
			'YouTube',
			array( 'class' => 'departments_widget', 
				   'description' => 'Simple YouTube video embed widget.' )
		);
	}

	/*
	*	outputs the content of the widget
	*	@param array
	*	@param array
	*/
	public function widget( $args, $instance ){
		$url = $instance['url'];
		
		$instance['html'] = wp_oembed_get( $url, array('height' => 225, 'width' => 300) );
		
		$this->render( 'views/widgets', $instance );
	}

	/*
	*	outputs the options form on admin
	*	@param array
	*/
 	public function form( $instance ){
		$defaults = array(
			'url' => ''
		);
		
		$vars = array_merge( $defaults, $instance );
		
		if( !defined('LUMINOPOLIS_YOUTUBE_PLUGINS_URL') )
			define( 'LUMINOPOLIS_YOUTUBE_PLUGINS_URL', plugins_url('', __FILE__) );
			
		wp_register_script( 'kcmo-youtube', LUMINOPOLIS_YOUTUBE_PLUGINS_URL.'/public/admin/widgets.js' );
		wp_enqueue_script( 'kcmo-youtube' );
	
		$this->render( 'views/admin/form', $vars );
	}
	
	/*
	*	processes widget options to be saved
	*	@param array
	*	@param array
	*	@return array
	*/
	public function update( $new_instance, $old_instance ){
		return $new_instance;
	}
	
	/*
	*
	*	@param string
	*	@param array
	*/
	private function render( $template, $vars = array() ){
		extract( (array) $vars );
		require dirname( __FILE__ ).'/'.$template.'.php';
	}
}

add_action( 'widgets_init',
     create_function( '', 'return register_widget("Youtube_Widget");' )
);