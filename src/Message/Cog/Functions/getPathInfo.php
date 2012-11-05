<?php


/****************************************************************
*
* LOOKS FOR URLS THAT MAKE USE OF PATH INFO SUCH AS 
* /personnel/display/index.php/1234/career/ AND PARSES
* THEM, PLACING THE VALUES AFTER THE FILENAME INTO AN ARRAY  
* 
* RETURNS AN ARRAY
*
*****************************************************************/


function getPathInfo() {
	$paths = array();
	if (preg_match('/^.+\.php(.+)$/', $_SERVER['PHP_SELF'], $matches)) {
		$paths = explode('/', trim($matches[1], '/'));
	}
	return $paths;
}


?>