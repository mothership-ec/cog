<?php

namespace Message\Cog\HTTP\EventListener;

use Message\Cog\Services;
use Message\Cog\HTTP\Event\Event;
use Message\Cog\HTTP\Event\BuildResponseFromExceptionEvent;
use Message\Cog\HTTP\StatusException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener for core functionality to deal with exceptions.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Exception implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(Event::EXCEPTION => array(
			array('renderStandardErrorPage'),
			array('dumper'),
		));
	}

	/**
	 * This listener attempts to turn StatusException's into Response's by
	 * looking for an internal error route for the HTTP error code.
	 *
	 * If the route evaluates, a sub request is performed and the Request is
	 * set on the event.
	 *
	 * @param BuildResponseFromExceptionEvent $event The exception event
	 */
	public function renderStandardErrorPage(BuildResponseFromExceptionEvent $event)
	{
		// This event listener only looks at StatusException's
		if (!$event->getException() instanceof StatusException) {
			return false;
		}
		// This event listener only needs to look at the master (external) request
		if ($event->getRequest()->isInternal()) {
			return false;
		}

		try {
			// Execute the sub-request for the error page
			$routeName = 'error.' . $event->getException()->getCode();
			$response  = $event->getDispatcher()->forward(
				$routeName,
				array(), // Attributes
				array(), // Query
				false    // Do not catch exceptions
			);
			// Set the response
			$event->setResponse($response);
		}
		catch (StatusException $e) {
			// I know empty catch blocks are bad, but here we just want to ignore
			// any StatusExceptions thrown by the subrequest. Likely to be a 404
			// because the route or view doesn't exist.
		}
	}

	/**
	 * Temporary method to dump any uncaught exceptions.
	 *
	 * @param BuildResponseFromExceptionEvent $event The exception event
	 */
	public function dumper(BuildResponseFromExceptionEvent $event)
	{
		var_dump($event->getException());
		exit;
	}
}