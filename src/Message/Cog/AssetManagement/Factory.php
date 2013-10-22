<?php

namespace Message\Cog\AssetManagement;

use Assetic\Factory\AssetFactory;

class Factory extends AssetFactory
{
	protected $_referenceParser;

	public function setReferenceParser($referenceParser)
	{
		$this->_referenceParser = $referenceParser;
	}

    public function createAsset($inputs = array(), $filters = array(), array $options = array())
    {
        if (!is_array($inputs)) {
            $inputs = array($inputs);
        }

        $namespaces = array();

        foreach ($inputs as $key => $input) {
            // Parse the input
            $parsed = $this->_referenceParser->parse($input);

            // Update the input to the real full path
        	$inputs[$key] = $parsed->getFullPath();

            // Get the module name
            $namespaces[$inputs[$key]] = str_replace('\\', ':', $parsed->getModuleName());
        }

        $collection = parent::createAsset($inputs, $filters, $options);

        // Store the cog namespace against each asset for use in the cogule filter
        foreach ($collection as $asset) {
            $asset->cogNamespace = $namespaces[$asset->getSourceRoot() . '/' .$asset->getSourcePath()];
        }

        return $collection;
    }

    public function generateAssetName($inputs, $filters, $options = array())
    {
        $name = parent::generateAssetName($inputs, $filters, $options);

        // Cache busting
        $hash = hash_init('sha1');

        foreach ($inputs as $input) {
            // Parse the input
            $parsed = $this->_referenceParser->parse($input);

            // Update the input to the real full path
            $path = $parsed->getFullPath();

            hash_update($hash, filemtime($path));
        }

        // Return a combination of the two name parts
        return $name . substr(hash_final($hash), 0, 7);
    }
}