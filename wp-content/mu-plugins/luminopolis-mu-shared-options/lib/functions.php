<?php

namespace mu_shared_options;

/*
*	taken from http://www.php.net/manual/en/function.array-filter.php#87581
*	@param array
*	@return array
*/
function array_filter_recursive( $input ){ 
	foreach( $input as &$value ){ 
		if( is_array($value) ){ 
			$value = array_filter_recursive( $value ); 
		} 
	} 
	
	return array_filter( $input ); 
} 