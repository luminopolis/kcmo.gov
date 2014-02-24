<?php
/*
Plugin Name:	KCMO Google Calendar
Plugin URI: 
Description:	Queries Google Calendar and renders events in template.
Author:			Luminopolis / Eric Eaglstun
Text Domain: 	luminopolis-google-calendar
Domain Path:	/lang
Version: 1.1
Author URI: 
*/

if( is_admin() )
	require dirname( __FILE__ ).'/admin.php';

define( 'GOOGLE_CALENDAR_DEFAULT_URL', 'https://www.google.com/calendar/feeds/kcmocco%40gmail.com/public/full' );
define( 'GOOGLE_CALENDAR_DEFAULT_RESULTS', 4 );

/*
*	gets calendar feed from cache or from google feed
*	stores in cache for 5 minutes
*	@return array
*/
function luminopolis_google_calendar_data(){
	$data = get_transient( 'google-calendar-data' );
	
	if( !$data ){
		$url = get_option( 'google-calendar-url', GOOGLE_CALENDAR_DEFAULT_URL );
		$max_results = (int) get_option( 'google-calendar-max-results', GOOGLE_CALENDAR_DEFAULT_RESULTS );
		
		$params = array(
			'alt' => 'json',
			'futureevents' => 'true',
			'max-results' => $max_results,
			'orderby' => 'starttime',
			'singleevents' => 'true',
			'sortorder' => 'ascending'
		);
		
		$query = http_build_query( $params );
		$url .= '?'.$query;
		
		$response = wp_remote_get( $url );
		
		if( !is_wp_error($response) && $body = json_decode($response['body']) ){
			$data = isset( $body->feed->entry ) ? $body->feed->entry : array();
			set_transient( 'google-calendar-data', $data, 300 );
		}
	}
	
	return $data;
}

/*
*	render the html that shows calendar data
*	attached to `parse_query` action
*	html is available as $google_calendar in template
*	@param WP_Query
*	@return WP_Query
*/
function luminopolis_google_calendar_html( WP_Query &$wp_query ){
	$events_html = get_transient( 'google-calendar-html' );
	
	if( !$events_html && ($data = luminopolis_google_calendar_data()) ){
		$events = array();
		
		$offset = get_option( 'gmt_offset' );
		$tz = timezone_name_from_abbr( '', $offset*3600, FALSE );
		date_default_timezone_set( $tz );
		
		foreach( $data as $event ){
			// group events by day
			if( !isset($event->{'gd$when'}) )
				continue;
			
			$start = strtotime( $event->{'gd$when'}[0]->startTime );
			$start = date( 'F j', $start );
			
			if( !isset($events[$start]) )
				$events[$start] = array();
			
			$events[$start][] = '<li><a href="'.$event->link[0]->href.'" target="_blank">'.$event->title->{'$t'}.'</a></li>';
		}
		
		$events_html = '';
		
		foreach( $events as $date => $day_events ){
			$events_html .= '<h4>'.$date.'</h4><ul>';
			$events_html .= implode( '', $day_events );
			$events_html .= '</ul>';
		}
		
		set_transient( 'google-calendar-html', $events_html, 300 );
	} elseif( !$events_html ) {
		$events_html = '<h4>No upcoming events</h4>';
		set_transient( 'google-calendar-html', $events_html, 300 );
	}
	
	$wp_query->set( 'google_calendar', $events_html );
	return $wp_query;
}
add_action( 'parse_query', 'luminopolis_google_calendar_html' );
