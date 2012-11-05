<?php


//PRINT OUT A STRING OR ARRAY AND EXIT (DEBUGGING)
function dump($var, $setHeader = false) {
	if ($setHeader) {
		//ensure we have a utf-8 header for dumping non english text
		header('Content-type: text/html; charset=utf-8'); 
	}
	echo '<pre>';
	if (is_array($var) || is_object($var)) {
		print_r($var);
	} else {
		var_dump($var);
	}
	echo '</pre>';
	exit;
}



?>