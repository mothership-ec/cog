<?php

namespace Message\Cog\Functions;

use stdClass;

class Iterable
{
	/**
	 * Recursively converts an array to an object.
	 *
	 * @param array $array                  The array to convert
	 * @param bool  $maintainNumericIndices If true, numeric indecies are retained
	 * @param string|null $className        Class to create for the returned object,
	 *                                      if null, stdClass is used
	 *
	 * @return object                       The array as an object instance
	 */
	public static function toObject($array, $maintainNumericIndices = false, $className = null)
	{
		if (!is_array($array)) {
			return $array;
		}

		$className         = $className ?: 'stdClass';
		$hasNumericIndices = (array_values($array) === $array) && $maintainNumericIndices;
		$new               = $hasNumericIndices ? array() : new $className;

		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$value = self::toObject($value, $maintainNumericIndices);
			}

			if ($hasNumericIndices) {
				$new[$key] = $value;
			}
			else {
				$new->{Text::toCamelCaps($key)} = $value;
			}
		}

		return $new;
	}

	/**
	 * Gets all the keys in a multidimension array
	 *
	 * @param  array  $array The array to extract keys from
	 *
	 * @return array        A single dimension array containing all keys
	 */
	public static function arrayKeysMultidimensional(array $array)
	{ 
		$keys = array();
		foreach ($array as $key => $value) {
			$keys[] = $key; 
			if (is_array($array[$key])) {
				$keys = array_merge($keys, self::arrayKeysMultidimensional($array[$key])); 
			}
		}

		return $keys; 
	}

	/**
	 * Gets all parent keys up the tree from a specific needle
	 *
	 * @param  string $needle 	The key name to search for
	 * @param  array  $haystack    The array to search
	 *
	 * @return array|null       A single dimension array containing all 
	 *                          parent keys (or null if $needle was not found)
	 */
	public static function getParentsFromKey($needle, array $haystack)
	{
		foreach ($haystack as $key => $value) {
			if (!is_array($value)) {
				continue;
			}

			if (in_array($needle, array_keys($value))) {
				return array($key);
			}

			$chain = self::getParentsFromKey($needle, $value);

			if (!is_null($chain)) {
				return array_merge(array($key), $chain);
			}
		}

		return null;
	}

	/**
	 * Turns a single dimension array into a multidimensions array.
	 *
	 * The original array must be in key => value format where the child is
	 * the key and the parent is the value.
	 *
	 * @param  array  $array The array to transform
	 *
	 * @return array         The tree represented as a multidimensional array
	 */
	public static function toTree(array $array)
	{
	    $flat = array();
	    $tree = array();

	    foreach ($array as $child => $parent) {
	        if (!isset($flat[$child])) {
	            $flat[$child] = array();
	        }
	        if (!empty($parent)) {
	            $flat[$parent][$child] =& $flat[$child];
	        } else {
	            $tree[$child] =& $flat[$child];
	        }
	    }

	    return $tree;
	}
}