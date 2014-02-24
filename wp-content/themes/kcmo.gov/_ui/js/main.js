/*******************************************************************************

	CSS on Sails Framework
	Title: Skinny Alligator
	Author: luminopolis
	Date: September 2013	

*******************************************************************************/

if( typeof String.prototype.trim !== 'function' ){
	String.prototype.trim = function() {
		return this.replace( /^\s+|\s+$/g, '' ); 
	}
}

var current_menu = 0;

(function($){
	
	var App, $searchBox;
	
	App = {

		/**
		 * Init Function
		 */
		init: function(){
			App.tabs();
		},
		
		/**
		 * tabs
		 */
		tabs: function(){
			$(document).scrollTop(0);
			
			$(".withtabs").tabs( { 
				beforeActivate: function( event, ui ){
					var new_template = ui.newPanel.data( 'template' );
	
					if( new_template == 'index-no-slideshow' )
						$('aside#sidebar').fadeOut( 'fast' );
					else
						$('aside#sidebar').fadeIn( 'fast' );
				},
				hide: {
					effect: "fadeOut",
					duration: "fast"
				},
				show: {
					effect: "fadeIn",
					duration: "fast"
				}
			} );

			if(/*@cc_on !@*/false && (
				   document.documentMode === 9 || document.documentMode === 10)
			  ){
				// IE 9 or 10 (not 8 or 11!)
				document.documentElement.className += ' ie9 ie10';
			}
		}	
	};
	
	$( function(){
		App.init();
	} );
	
	// toggle sidebar on tab show/hide
	$("#content div").on( 'shown.bs.tab', function(){
		//alert();
	});
	
	// widen search box on focus
	function activateSearch( selector ){
		$searchBox = $( selector );
		
		if( ($searchBox.length > 0) && ($searchBox.val().trim() != '') )
			$searchBox.css( 'background-image', 'none' );
			
		$searchBox.focus( function(){	
			$searchBox.css( 'background-image', 'none' );
			$searchBox.animate( {width:'200px'} );
		} ).blur( function(){
			if( $searchBox.val().trim() == '' ){
				$searchBox.css( 'background-image', 'url("http://www.google.com/cse/intl/en/images/google_custom_search_watermark.gif")' );
				$searchBox.animate( {width:'140px'} );
			}
		});
	};
	activateSearch( '.navbar .search input#main-search' );
	
	// custom dropdown
	$('li.dropdown').on('show.bs.dropdown', function(el){
		$el = $(el.currentTarget );
		$el.filter( 'a' ).addClass( 'open' );
		
		if( $( '.subs .dropdown-menu:visible' ).length ){
			//el.preventDefault();
		}
	} );
	
	// menu slide down
	$('li.dropdown').on('shown.bs.dropdown', function(el){
		$el = $( el.currentTarget );
		menu_id = $el.data( 'menu-id' );
		
		$el.parents('ul').addClass('disable-current-menu-item' );
		
		if( !current_menu ){
			$('div.subs div[data-menu-id='+menu_id+']').slideDown( function(){
				current_menu = menu_id;
			} );
		}
	} );
	
	// menu slide up
	$('li.dropdown').on('hide.bs.dropdown', function(el){
		$el = $(el.currentTarget );
		
		$el.parents('ul').removeClass('disable-current-menu-item' );
		
		if( $('.subs .dropdown-menu:visible').length ){
			//el.preventDefault();
		}
	} );
	
	// menu slide up
	$('li.dropdown').on('hidden.bs.dropdown', function(el){
		$el = $( el.currentTarget );
		menu_id = $el.data( 'menu-id' );
		
		$('div.subs div[data-menu-id='+menu_id+']').slideUp( function(){
			do_show = menu_id != current_menu;
			current_menu = 0;
			
			if( do_show )
				$el.trigger( 'shown.bs.dropdown' );
			
		} );
	} );
	
	// feedback form
	$('.wrong a').click( function(e){
		// remove dotted focus only on click, not for keyboard tab/enter
		if( e.timeStamp )
			$( this ).blur();
			
		$( '#feedback' ).slideToggle();
		e.preventDefault();
		
		return false;
	} );
	
	$('body').addClass('js');
	
	// google custom search
    var cx = '016919805973101078730:ivlnctldhuu';
    var gcse = document.createElement( 'script' );
    gcse.type = 'text/javascript';
    gcse.async = true;
    gcse.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') + '//www.google.com/cse/cse.js?cx=' + cx;
    gcse.onready = function(){
    	activateSearch( '#gsc-i-id1' );
    };
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore( gcse, s );
	
	//
	$( document ).off('click.bs.dropdown.data-api', ':not([data-toggle=dropdown])' );
})(jQuery);