<?php

function createRandomKey($length = 30) {
	
	$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	
	$return = '';
	
	for($i = 0; $i <= $length; $i++) {
		$return .= $characters{rand(0, (strlen($characters) - 1))};
	}
	
	return $return;
	
}

?>