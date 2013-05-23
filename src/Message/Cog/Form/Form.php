<?php

namespace Message\Cog\Form;

use Symfony\Component\Form\Form as SymfonyForm;
use Symfony\Component\Form\ResolvedFormTypeInterface;
use Symfony\Component\Templating\Helper\HelperInterface;

class Form extends SymfonyForm
{
	protected $_helper;

	public function __toString()
	{
		try {
			if (!$this->_helper) {
				throw new \Exception('Helper not set');
			}

			$view = $this->createView();
			return $this->_helper->row($view);

		}
		catch (\Exception $e) {
			return $e->xdebug_message;
		}
	}

	public function setConfigType(ResolvedFormTypeInterface $type)
	{
		$this->getConfig()->setType($type);
	}

	public function setHelper(HelperInterface $helper) {
		$this->_helper = $helper;

		return $this;
	}

}