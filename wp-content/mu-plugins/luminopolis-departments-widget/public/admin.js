"use strict";

jQuery( document ).ready( function($){
	kcmo_dept_form();
} );

/*
*	reactivate javascript listeners after ajax submit
*/
jQuery( document ).ajaxSuccess( function(e, xhr, settings){
	if( !settings.data )
		return;
		
	var request = {}, pairs = settings.data.split('&'), i, split, widget;

	for( i in pairs ){
		split = pairs[i].split('=' );
		request[decodeURIComponent(split[0])] = decodeURIComponent(split[1] );
	}
	
	if( request.id_base == 'kcmo_dept' )
		kcmo_dept_form();
});

/*
*
*/
function kcmo_dept_form(){
	var $addnew, blank_gif, $container, $delete, $edit, $edits, $heading, $inputs, $li, $ul;
	
	blank_gif = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
	$container = jQuery( '.kcmo_dept' );
	$container.find( 'ul.social_networks' ).sortable();
	
	// edit icon and title
	$edit = $container.find( 'ul.social_networks li h5 span.action a.edit' );
	$edit.click( function(e){
		$heading = jQuery(this).parents('h5' );
		$edits = $heading.next( 'div.hide-if-js' );
		
		if( $edits.is(":visible") ){
			$inputs = $edits.find('input' );
			
			$heading.find( 'img' ).attr( 'src', $inputs.eq(0).val() || blank_gif );
			$heading.find( 'span.title' ).html( $inputs.eq(1).val() );
		} 
		
		$edits.toggle();
		return false;
	} );
	
	// delete social network
	$delete = $container.find( 'ul.social_networks li h5 span.action a.delete' );
	$delete.click( function(e){
		$li = jQuery(this).parents('li' );
		$li.fadeOut( 'slow', function(){
			jQuery(this).remove();
		} );
		return false;
	} );
	
	// add new social network
	$addnew = $container.find( 'a.addnew' );
	$addnew.click( function(e){
		var $parent = jQuery(this).parents( '.kcmo_dept' );
		
		var length = $parent.find( 'li' ).length;
		$li = $parent.find( 'li' ).eq(0).clone( true );
		
		$li.find( 'input' ).map( function(){
			var $this = jQuery(this);
			var name = $this.attr('name' );
			
			name = name.replace( /\[social\]\[([a-z0-9\-])+\]/, '[social][new-'+length+']' );
			$this.attr( 'name', name );
		} );
		
		// show all 4 fields
		$li.find( '.hide-if-js' ).show();
		
		// clear values
		$li.find( 'input[type=text]' ).val('' );
		$li.find( 'h5 img' ).attr( 'src', blank_gif );
		$li.find( 'h5 span.title' ).html('New Social Network' );
		
		// set order
		$li.find( 'h5 input' ).val( length );
		
		$container.find( 'ul' ).append( $li );
		
		return false;
	} );
}