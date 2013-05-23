<?php

namespace Message\Cog\HTTP\EventListener;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\HTTP\Event\Event;
use Message\Cog\HTTP\Event\FilterResponseEvent;
use Message\Cog\HTTP\CookieCollection;

/**
 * Event listener for core functionality for any last actions on a Response
 * before it is rendered.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Response implements SubscriberInterface
{
	protected $_cookieCollection;

	public function __construct(CookieCollection $cookieCollection)
	{
		$this->_cookieCollection = $cookieCollection;
	}

	static public function getSubscribedEvents()
	{
		return array(Event::RESPONSE => array(
			array('setResponseCookies'),
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
	
	public function setResponseCookies(FilterResponseEvent $event)
	{
		foreach ($this->_cookieCollection as $cookie) {
			$event->getResponse()->headers->setCookie($cookie);
		}

	}
}