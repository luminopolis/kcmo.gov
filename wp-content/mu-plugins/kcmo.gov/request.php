<?php

define( 'LUMINOPOLIS_REQUEST_HELPER', '1.0' );

/*
*	use get( $index ) to get unslashed global $_GET if it is set
*	@param string
*	@return mixed
*/
function get( $index ){
	static $get = NULL;
	
	if( $get === NULL )
		$get = stripslashes_deep( $_GET );
	
	return isset( $get[$index] ) ? $get[$index] : NULL;
}

/*
*
*	@param string
*	@param mixed
*/
function flash( $index, $val = NULL ){
	if( $val ){
		$_SESSION['flash'][$index] = $val;
	} elseif( isset($_SESSION['flash'][$index]) ){
		$val = $_SESSION['flash'][$index];
		unset($_SESSION['flash'][$index] );
	} else {
		$val = NULL;
	}
	
	return $val;
}

/*
*	use post( $index ) to get unslashed global $_POST if it is set
*	@param string
*	@return mixed
*/
function post( $index ){
	static $post = NULL;
	
	if( $post === NULL )
		$post = stripslashes_deep( $_POST );
	
	return isset( $post[$index] ) ? $post[$index] : NULL;
}