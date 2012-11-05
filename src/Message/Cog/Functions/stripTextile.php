<?php

function stripTextile($string) {

/* 	$string = html_entity_decode($string); */
	$string = preg_replace(array('/^h([\(\)\#A-z1-9]+)\.(.*)/m', '/(\!.*\!)/Ums'), '', str_replace(array('*', '#', '_', '%', '|', 'http://', '<'), '', $string));
	$string = preg_replace('/"([^:]*)"\:([.\:\=\(\!\?\'\)\/\\&\-\_A-z0-9]+)/ms', '$1', $string); // make links plain text
	$string = trim(str_replace(array('h1.', 'h2.', 'h3.', 'h4.', 'h5.'), '', $string)); // removes any uncaught headers and trims
	return $string;

}
?>