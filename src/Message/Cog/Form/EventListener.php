<?php

namespace Message\Cog\Form;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\Extension\Templating\TemplatingRendererEngine;

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
				array('setupFormHelper'),
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

	public function setupFormHelper()
	{
		$this->_services['templating.engine.php']->addHelpers(array(
			$this->_services['form.helper.php']
		));
	}
}