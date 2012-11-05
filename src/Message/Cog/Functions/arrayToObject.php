<?php

use Mothership\Framework\Services;

/**
 * Recursively converts an array to an object
 * @param  array $array 
 * @return stdClass
 */
function arrayToObject($array, $maintainNumericIndices = false)
{
	if (!is_array($array)) {
		return $array;
	}

	$hasNumericIndices = (array_values($array) === $array) && $maintainNumericIndices;
	$new = $hasNumericIndices ? array() : new stdClass;

	foreach ($array as $key => $value) {
		if (is_array($value)) {
			$value = arrayToObject($value, $maintainNumericIndices);
		}

		if ($hasNumericIndices) {
			$new[$key] = $value;
		} else {
			$new->{Services::get('fns.text')->toCamelCaps($key)} = $value;
		}	
	}
	
	return $new;
}