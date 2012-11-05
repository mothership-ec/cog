<?php

// ADAPTED AND TIDIED UP FROM HERE:
// http://stackoverflow.com/questions/2516599/php-2d-array-output-all-combinations/2516779#2516779

function arrayCartesian() {
	
	$_ = func_get_args();
	
	if(count($_) == 0) {
		return array(array());
	}
	
	$a = array_shift($_);
	$c = call_user_func_array(__FUNCTION__, $_);
	
	$return = array();
	
	foreach($a as $v) {
		foreach($c as $p) {
			$return[] = array_merge(array($v), $p);
		}
	}

	return $return;

}
