<?php

namespace Message\Cog\Event;

use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;

/**
 * Base event listener that is container aware.
 *
 * @author James Moss <james@message.co.uk>
 */
abstract class EventListener implements ContainerAwareInterface
{
	protected $_services;

	/**
	 * {@inheritdoc}
	 */
	public function setContainer(ContainerInterface $services)
	{
		$this->_services = $services;
	}
}