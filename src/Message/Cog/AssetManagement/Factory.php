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
}