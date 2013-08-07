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

        foreach ($inputs as $key => $input) {
        	// $inputs[$key] = $this->_referenceParser->parse($input)->getFullPath('View');
        	$inputs[$key] = $this->_referenceParser->parse($input)->getFullPath();
        }

        return parent::createAsset($inputs, $filters, $options);
    }
}