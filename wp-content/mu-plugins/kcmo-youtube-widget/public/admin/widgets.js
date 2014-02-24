jQuery(document).ready( function(){
	kcmo_youtube_form();
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
	
	if( request.id_base == 'kcmo_youtube' )
		kcmo_youtube_form();
});

function kcmo_youtube_form(){
	regex = /^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/;
	
	jQuery( 'div.kcmo_youtube' ).each( function(i, el){
		var $el = jQuery( el );
		
		url = $el.find('input[type=text]').val();
		
		id = url.match(regex);
		//console.log( id );
		
		if( id ){
			id = id[1];
			
			info_url = 'https://gdata.youtube.com/feeds/api/videos/'+id+'?v=2&alt=json';
			
			jQuery.getJSON( info_url, function(data){
				$el.find('img').attr( 'src', data.entry.media$group.media$thumbnail[2].url );
				$el.find('span').html( data.entry.title.$t );
			} );
		}
	} );
};