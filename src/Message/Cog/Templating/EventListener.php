<?php

namespace Message\Cog\Templating;

use Message\Cog\Event\SubscriberInterface;
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
				array('addTwigGlobals'),
			)
		);
	}

	public function addTwigGlobals()
	{
		$this->_services['templating.twig.environment']
			->addGlobal('flashes', $this->_services['http.session']->getFlashBag()->all());

		$this->_services['templating.twig.environment']
			->addGlobal('cfg', $this->_services['cfg']);

	}
}