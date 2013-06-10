<?php

namespace Message\Cog\Form;

use Message\Cog\Event\SubscriberInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\Extension\Templating\TemplatingRendererEngine;
use Message\Cog\Event\EventListener as BaseListener;

/**
 * Class EventListener
 * @package Message\Cog\Form
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class EventListener extends BaseListener implements SubscriberInterface
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

	public function setupFormHelper()
	{
		$this->_services['templating.engine.php']->addHelpers(array(
			$this->_services['form.helper.php']
		));

		$this->_services['templating.engine.twig']->addHelpers(
			$this->_services['form.helper.twig']
		);
	}
}