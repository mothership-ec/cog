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
	 *
	 * @return stdClass                     The array in object form
	 */
	public static function toObject($array, $maintainNumericIndices = false)
	{
		if (!is_array($array)) {
			return $array;
		}

		$hasNumericIndices = (array_values($array) === $array) && $maintainNumericIndices;
		$new = $hasNumericIndices ? array() : new stdClass;

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