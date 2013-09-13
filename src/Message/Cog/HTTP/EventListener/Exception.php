<?php

namespace Message\Cog\HTTP\EventListener;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\HTTP\RedirectResponse;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Event listener for core functionality for any last actions on an Exception
 * before it is rendered.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Exception implements SubscriberInterface
{

	static public function getSubscribedEvents()
	{
		return array(KernelEvents::EXCEPTION => array(
			array('redirectNotFound')
		));
	}

	public function redirectNotFound(GetResponseEvent $event)
	{
		$response = new RedirectResponse('404', 404);

		$event->setResponse($response);
	}
}