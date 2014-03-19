<?php

namespace Message\Cog\HTTP\EventListener;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\HTTP\CookieCollection;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Event listener for core functionality for any last actions on a Response
 * before it is rendered.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Response implements SubscriberInterface
{
	protected $_cookieCollection;

	static public function getSubscribedEvents()
	{
		return array(KernelEvents::RESPONSE => array(
			array('setCookies', -100),
			array('setRefererSession'),
		));
	}

	/**
	 * Constructor
	 *
	 * @param CookieCollection $cookieCollection The cookie collection
	 */
	public function __construct(CookieCollection $cookieCollection)
	{
		$this->_cookieCollection = $cookieCollection;
	}

	/**
	 * Set cookies in the collection on the master response.
	 *
	 * @param FilterResponseEvent $event The event instance
	 */
	public function setCookies(FilterResponseEvent $event)
	{
		// Skip if this isn't the master request
		if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
			return false;
		}

		foreach ($this->_cookieCollection as $cookie) {
			$event->getResponse()->headers->setCookie($cookie);
		}
	}

	/**
	 * If the response is a redirect, set a referer session.
	 *
	 * @param FilterResponseEvent $event
	 */
	public function setRefererSession(FilterResponseEvent $event)
	{
		// Skip if this isn't the master request
		if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
			return false;
		}

		$response = $event->getResponse();
		$request = $event->getRequest();

		if ($response->isRedirect()) {
			$request->getSession()->set('referer', $request->getUri());
		}
	}
}