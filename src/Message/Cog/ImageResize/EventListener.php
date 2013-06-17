<?php

namespace Message\Cog\ImageResize;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Console\Command\Event\Status;

/**
 * Event listener for the ImageResize component.
 *
 * @author James Moss <james@message.co.uk>
 */
class EventListener extends BaseListener implements SubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'modules.load.success' => array(
				array('loadTemplatingHelpers'),
			),
			'console.status.check' => array(
				array('checkStatus'),
			),
		);
	}

	public function loadTemplatingHelpers()
	{
		$this->_services['templating.php.engine']->addHelpers(array(
			new Templating\PhpHelper($this->_services['image.resize'])
		));

		$this->_services['templating.twig.environment']->addExtension(
			new Templating\TwigExtension($this->_services['image.resize'])
		);
	}

	public function checkStatus(Status $event)
	{
		$event
			->header('Message\\Cog\\ImageResize')
			->checkPath('Cache directory', $this->_services['image.resize']->getCachePath())
		;
	}
}