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

        list($cog, $resource) = explode('::', str_replace('@', '', $inputs[0]));

        foreach ($inputs as $key => $input) {
        	$inputs[$key] = $this->_referenceParser->parse($input)->getFullPath();
        }

        $collection = parent::createAsset($inputs, $filters, $options);

        // Store the cog namespace against each asset for use in the cogule filter
        foreach ($collection as $asset) {
            $asset->cogNamespace = $cog;
        }

        return $collection;
    }
}