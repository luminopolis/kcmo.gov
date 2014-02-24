jQuery(document).ready( function($){
			
	// All options - http://jquery.malsup.com/cycle/options.html
	$('#bannerspace').after('<div id=bannerspace_nav>').cycle( {				
		before: function( currSlideElement, nextSlideElement, options, forwardFlag ){
			var slide = options.nextSlide+1;
			jQuery( '.slide.s'+slide+' div.imageWrapper' ).html( banner_preload[slide] );
		},
		cleartypeNoBg: true,
		containerResize: 0,
		fx:     'fade',	//Effects - http://jquery.malsup.com/cycle/browser.html
		next:   '#bs_r_arrow',
		pager:  '#bannerspace_nav',
		pagerAnchorBuilder: function(idx, slide ){
			var title =  $($(slide).find('.title').get(0)).html();
			
			return '<span><a href=\'javascript:void(0);\' title=\' ' +title+ ' \'></a></span>';
		},
		prev:   '#bs_l_arrow',
		requeueOnImageNotLoaded: 1,
		slideResize: 0,
		speed:  '1000',
		sync: 'checkbox',	
		timeout: '7500'
	} ).cycle( banner_autoplay );
	
});

jQuery( window ).load( function($){									
	jQuery('.bs_arrow').fadeIn();
	jQuery('#bannerspace_nav').fadeIn();
	jQuery('#bannerspace .content').fadeIn();
	
	for( i in banner_preload ){
	  	//jQuery( '.slide.s'+i+' div.imageWrapper' ).html( banner_preload[i] );
	}
} );