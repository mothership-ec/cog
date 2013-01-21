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
}