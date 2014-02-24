jQuery( document ).ready( function($){
	"use strict";

	var $lists = $( ".sortable-list" );
	
	$lists.each( function(index, el){
		var $parent = $(el);
		var $this = $(this);
		var data = $this.data( 'sortable' );
		
		$this.sortable( {
			//'axis': 'y',
			//'helper': fixHelper,
			'items': data ? data.items : '',
			'update' : function(e, ui){
				var items = $parent.data( 'sortable' ).items;
				
				var order = $parent.find( items ).map( function(index, el){
					return $(el).data( 'sortable-id' );
				} );
				
				var post_data = {
					action: 'update-menu-order',
					id: data.id,
					order: order.toArray(),
				};
				
				$.post( ajaxurl, post_data, function(data, textStatus, jqXHR){
					if( !data.success ){
						alert( 'There was a problem saving the order.' );
					}
				} );
			}	
		} );
	} );
} );