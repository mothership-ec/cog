<?php

namespace Message\Cog\AssetManagement;

use Assetic\Factory\AssetFactory;

class Factory extends AssetFactory
{
	protected $_referenceParser;
	protected $_cacheBustingEnabled = false;
	protected $_parsed = array();

	public function setReferenceParser($referenceParser)
	{
		$this->_referenceParser = $referenceParser;
	}

	public function enableCacheBusting()
	{
		$this->_cacheBustingEnabled = true;
	}

	public function disableCacheBusting()
	{
		$this->_cacheBustingEnabled = false;
	}

	/**
	 * Create the combined assets from a set of inputs and store the cog
	 * namespace for later use.
	 *
	 * @param  array  $inputs
	 * @param  array  $filters
	 * @param  array  $options
	 * @return \Assetic\Asset\AssetCollection
	 */
	public function createAsset($inputs = array(), $filters = array(), array $options = array())
	{
		if (!is_array($inputs)) {
			$inputs = array($inputs);
		}

		$paths      = $this->_getFullPaths($inputs);
		$namespaces = $this->_getNamespaces($inputs);

		$collection = parent::createAsset($paths, $filters, $options);

		// Store the cog namespace against each asset for use in the cogule filter
		foreach ($collection as $asset) {
			$asset->cogNamespace = $namespaces[$asset->getSourceRoot() . '/' .$asset->getSourcePath()];
		}

		return $collection;
	}

	/**
	 * Generate the asset name as a hash of the input file modified times.
	 *
	 * @param  array  $inputs
	 * @param  array  $filters
	 * @param  array  $options
	 * @return string
	 */
	public function generateAssetName($inputs, $filters, $options = array())
	{
		$name = parent::generateAssetName($inputs, $filters, $options);

		if ($this->_cacheBustingEnabled) {
			$hash = hash_init('sha1');

			hash_update($hash, $name);

	        $paths = $this->_getFullPaths($inputs);

			foreach ($paths as $path) {
				hash_update($hash, file_get_contents($path));
			}

			// Return the final hash
			$name = hash_final($hash);
		}

		return $name;
	}

	/**
	 * Get the real full path for a set of inputs.
	 *
	 * @param  array $inputs
	 * @return array
	 */
	protected function _getFullPaths($inputs)
	{
		$parsedInputs = $this->_getParsedInputs($inputs);

		foreach ($parsedInputs as $input => $parsed) {
			// Update the input to the real full path
			$inputs[array_search($input, $inputs)] = $parsed->getFullPath();
		}

		return $inputs;
	}

	/**
	 * Get the namespaces for a set of inputs.
	 *
	 * @param  array $inputs
	 * @return array
	 */
	protected function _getNamespaces($inputs)
	{
		$parsedInputs = $this->_getParsedInputs($inputs);

		$namespaces = array();

		foreach ($parsedInputs as $key => $parsed) {
			// Get the module name
			$namespaces[$parsed->getFullPath()] = str_replace('\\', ':', $parsed->getModuleName());
		}

		return $namespaces;
	}

	/**
	 * Get the parsed references for a set of inputs.
	 *
	 * @param  array $inputs
	 * @return array
	 */
	protected function _getParsedInputs($inputs)
	{
		$parsed = array();

		foreach ($inputs as $key => $input) {
			if (! isset($this->_parsed[$input])) {
				// Parse the input, has to be cloned else the last parsed reference
				// will override all previous
				$this->_parsed[$input] = clone $this->_referenceParser->parse($input);
			}

			$parsed[$input] = $this->_parsed[$input];
		}

		return $parsed;
	}
}