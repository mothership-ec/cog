<?php

namespace Message\Cog\Config;

use Message\Cog\Functions\Iterable;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException as YamlParseException;

/**
 * Configuration compiler.
 *
 * Responsible for compiling YAML data in to native PHP data types in an object,
 * then stacking each of these on top of eachother.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Compiler
{
	protected $_dataSets = array();

	/**
	 * Add a configuration data set to be compiled.
	 *
	 * @param string $data Configuration data set as un-parsed YAML string
	 *
	 * @return Compiler    Returns $this for chainability
	 */
	public function add($data)
	{
		$this->_dataSets[] = $data;

		return $this;
	}

	/**
	 * Clear all configuration data sets from the internal array, turning this
	 * instance back into an empty container.
	 */
	public function clear()
	{
		$this->_dataSets = array();
	}

	/**
	 * Compile the configuration sets that have been set on this instance in the
	 * order they were added, and return the compiled configuration set as an
	 * instance of `Group`.
	 *
	 * @see _stack
	 *
	 * @return Group     The compiled configuration set
	 *
	 * @throws Exception If no data sets have been added to be compiled
	 * @throws Exception If YAML parsing throws a ParseException
	 * @throws Exception If YAML parsed result is not an array
	 */
	public function compile()
	{
		if (empty($this->_dataSets)) {
			throw new Exception('Cannot compile configuration: there\'s nothing to compile');
		}

		$compiled = array();

		foreach ($this->_dataSets as $data) {
			try {
				$parsed = Yaml::parse($data, true);
			}
			catch (YamlParseException $e) {
				throw new Exception(sprintf(
					'Cannot compile configuration: YAML parsing failed with message `%s`',
					$e->getMessage()
				), null, $e);
			}

			// Yaml::parse returns null if the file has nothing to parse in it
			// (e.g. empty file or just comments etc.)
			if(is_null($parsed)) {
				continue;
			}

			if (!is_array($parsed)) {
				throw new Exception(sprintf(
					'Cannot compile configuration: parsed result was not an array for YAML `%s`',
					$data
				));
			}

			$compiled = $this->_stack($compiled, $parsed);
		}

		return Iterable::toObject($compiled, true, 'Message\Cog\Config\Group');
	}

	/**
	 * Stack one array on top of another recursively.
	 *
	 * Values are replaced in the base array where they exist in the overlay
	 * array.
	 *
	 * If the key for the value in question exists in the base array, the value
	 * is an array with sequential numeric indexes (0, 1, 2, 3 etc), then the
	 * value is stacked by calling this method again.
	 *
	 * The base array is returned with it's values replaced from the overlay
	 * array as appropriate.
	 *
	 * Example base array:
	 *
	 * [name]  => Message
	 * [email] => info@message.co.uk
	 * [staff] => [
	 * 		[0] => Jamie
	 *   	[1] => Joe
	 *    	[2] => Danny
	 * ]
	 *
	 * Example overlay array:
	 *
	 * [name]  => Bob's Socks
	 * [staff] => [
	 * 		[0] => Bob
	 *   	[1] => Jeff
	 * ]
	 *
	 * Returned array:
	 *
	 * [name]  => Bob's Socks
	 * [email] => info@message.co.uk
	 * [staff] => [
	 * 		[0] => Bob
	 *   	[1] => Jeff
	 * ]
	 *
	 * @param  array  $base    Base array
	 * @param  array  $overlay Overlay array
	 *
	 * @return array           The stacked array
	 */
	protected function _stack(array $base, array $overlay)
	{
		foreach ($overlay as $key => $val) {
			if (isset($base[$key]) && (is_array($val) && $val !== array_values($val))) {
				$base[$key] = $this->_stack($base[$key], $val);
			}
			else {
				$base[$key] = $val;
			}
		}

		return $base;
	}
}