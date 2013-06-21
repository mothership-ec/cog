<?php

namespace Message\Cog\Validation;

abstract class OtherCollectionAbstract implements CollectionInterface
{
	/**
	 * @var Loader
	 */
	protected $_loader;

	/**
	 * Method to retrieve data submitted to form, to be parsed to callbacks
	 *
	 * @throws \Exception       Throws exception if loader or validator cannot be found
	 *
	 * @return array
	 */
	protected function _getSubmittedData()
	{
		if (!$this->_loader) {
			throw new \Exception('Could not find loader, it should be set in the register() method');
		}

		$validator = $this->_loader->getValidator();

		if (!$this->_loader->getValidator()) {
			throw new \Exception('Validator not assigned to Loader');
		}

		return $this->_loader->getValidator()->getData();
	}
}