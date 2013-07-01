<?php

namespace Message\Cog\HTTP\EventListener;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\Service\ContainerInterface;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

/**
 * Event listener for core functionality for dealing with requests.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Request implements SubscriberInterface, ContainerAwareInterface
{
	protected $_services;

	static public function getSubscribedEvents()
	{
		return array(KernelEvents::REQUEST => array(
			array('addRequestToServices', 9999),
			array('validateRequestedFormats'),
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function setContainer(ContainerInterface $services)
	{
		$this->_services = $services;
	}

	/**
	 * Adds the current request to the service container with the key `request`.
	 *
	 * @param GetResponseEvent $event     The HttpKernel request event instance
	 */
	public function addRequestToServices(GetResponseEvent $event)
	{
		$this->_services['request'] = $this->_services->share(function() use ($event) {
			return $event->getRequest();
		});

		$this->_services['http.fragment_handler']->setRequest($this->_services['request']);
	}

	/**
	 * Validates that the requested formats are allowed by the route, and
	 * tells the request which requested formats can be used. This is then used
	 * when building the response.
	 *
	 * @param GetResponseEvent $event     The HttpKernel request event instance
	 *
	 * @throws NotAcceptableHttpException If none of the requested content type(s) are acceptable
	 */
	public function validateRequestedFormats(GetResponseEvent $event)
	{
		// If this request's route has specific format(s) set
		if ('ANY' !== $event->getRequest()->attributes->get('_format')) {
			// Determine the content type to return based on what's allowed and what's requested
			$allowedFormats         = explode('|', $event->getRequest()->attributes->get('_format'));
			$requestedContentTypes  = $event->getRequest()->getAcceptableContentTypes();
			$allowedContentTypes    = array();

			// Loop through requested content types
			foreach ($requestedContentTypes as $mimeType) {
				// If the request allows any content type, and hasn't defined any
				// preferences, skip this (the system will fall back on the
				// requested content types)
				if ('*/*' === $mimeType && empty($allowedContentTypes)) {
					return true;
				}
				// Get format from the mime type
				$formatType = $event->getRequest()->getFormat($mimeType);
				// If this content type is available, add the mimetype to the accepted list
				if (in_array($formatType, $allowedFormats)) {
					$allowedContentTypes[] = $mimeType;
				}
			}

			// If none of the requested content types were acceptable, throw exception
			if (empty($allowedContentTypes)) {
				throw new NotAcceptableHttpException(sprintf(
					'Unacceptable content type(s) requested: `%s`',
					implode(', ', $requestedContentTypes)
				));
			}
			// Otherwise, set the list of acceptable content types on the request for later use
			else {
				$event->getRequest()->attributes->set('_allowedContentTypes', $allowedContentTypes);
			}
		}
	}
}