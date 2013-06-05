<?php

namespace Message\Cog\Form;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;

/**
 * Class EventListener
 * @package Message\Cog\Form
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class EventListener implements SubscriberInterface, ContainerAwareInterface
{
	protected $_services;

	static public function getSubscribedEvents()
	{
		return array(
			'modules.load.success' => array(
				array('mountRoutes'),
			)
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setContainer(ContainerInterface $services)
	{
		$this->_services = $services;
	}

//	/**
//	 * Compile the routes from the CollectionManager and add them to the
//	 * router, ready to be matched against a request.
//	 */
//	public function mountRoutes()
//	{
//		$compiledRoutes = $this->_services['routes']->compileRoutes()->getRouteCollection();
//		$this->_services['router']->setRouteCollection($compiledRoutes);
//	}
}