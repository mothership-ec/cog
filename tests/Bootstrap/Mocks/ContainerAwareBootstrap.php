<?php

namespace Message\Cog\Test\Bootstrap\Mocks;

use Message\Cog\Bootstrap\BootstrapInterface;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;

/**
 * This bootstrap implements the `Service\ContainerAwareInterface`, so we can
 * test that the `Bootstrap\Loader` sets the container on these bootstraps.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class ContainerAwareBootstrap implements BootstrapInterface, ContainerAwareInterface
{
	protected $_container;

	public function setContainer(ContainerInterface $container)
	{
		$this->_container = $container;
	}

	public function getContainer()
	{
		return $this->_container;
	}
}