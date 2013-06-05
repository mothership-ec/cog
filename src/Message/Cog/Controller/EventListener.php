<?php

namespace Message\Cog\Controller;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\HTTP\RequestAwareInterface;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Event listener for the Controller component.
 *
 * @author James Moss <james@message.co.uk>
 */
class EventListener implements SubscriberInterface, ContainerAwareInterface
{
	protected $_services;

	static public function getSubscribedEvents()
	{
		return array(
			KernelEvents::CONTROLLER => array(
				array('setContainerOnController'),
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

	public function setContainerOnController(FilterControllerEvent $event)
	{
		$controller      = $event->getController();
		$controllerClass = array_shift($controller);

		if ($controllerClass instanceof ContainerAwareInterface) {
			$controllerClass->setContainer($this->_services);
		}
		if ($controllerClass instanceof RequestAwareInterface) {
			$controllerClass->setRequest($this->_services['request']);
		}
	}
}