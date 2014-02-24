jQuery(document).ready(function($){
	"use strict";
	return;
	$('#feedback form').submit( function(){
		var $form = $(this);
		
		$form.find( 'textarea[name=activity]' ).removeClass( 'has-error' );
		$form.find( 'p.help-block' ).html( '' );
		
		$.ajax( {
			data: $form.serialize(),
			method: 'post',
			success: function(data){
				if( data.success ){
					$('#feedback form').fadeOut("fast", function(){
						$(this).addClass('has-success').html('<p class="help-block">'+data.success+'</p>').fadeIn("fast");
					})
				} else if( data.error ){
					var $textarea = $('textarea[name=activity]' );
					
					$textarea.parents('fieldset').addClass( 'has-error' );
					$textarea.next('p.help-block').html( data.error );
				}
			}
		} );
		return false;
	});
})