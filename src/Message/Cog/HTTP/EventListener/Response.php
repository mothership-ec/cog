<?php

namespace Message\Cog\HTTP\EventListener;

use Message\Cog\HTTP\Event\Event;
use Message\Cog\HTTP\Event\FilterResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener for core functionality for any last actions on a Response
 * before it is rendered.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Response implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(Event::RESPONSE => array(
			array('prepareResponse'),
		));
	}

	/**
	 * Prepare the Response.
	 *
	 * @param  FilterResponseEvent $event The filter response event
	 */
	public function prepareResponse(FilterResponseEvent $event)
	{
		$event->getResponse()->prepare($event->getRequest());
	}
}