jQuery( document ).ready( function($){
	"use strict";
	
	var $parent_id = $('#parent_id' );
	var $show = $( '#kcmo-show-tab input' );
	var $title = $( '#kcmo-tab-title input' );

	// toggle checkbox disabled on parent selector change and page load
	$parent_id.change( show_checkbox );
	show_checkbox();
	
	//
	function show_checkbox(){
		var val = $parent_id.val();
		
		if( val ){
			$show.attr( 'disabled', false );
			$title.attr( 'disabled', false );
		} else {
			$show.attr( 'disabled', true );
			$title.attr( 'disabled', true );
		}
	};
} );