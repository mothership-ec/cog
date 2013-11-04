<?php

namespace Message\Cog\HTTP\EventListener;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\Service\ContainerInterface;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernelInterface;
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
			array('prepareRequest', 9999),
			array('addRequestToServices', 9998),
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
	 * Prepare the request and give it anything it needs. This currently just
	 * sets the session on the request.
	 *
	 * @param GetResponseEvent $event     The HttpKernel request event instance
	 */
	public function prepareRequest(GetResponseEvent $event)
	{
		$event->getRequest()->setSession($this->_services['http.session']);
	}

	/**
	 * Adds the current request to the service container with the key `request`.
	 *
	 * If the request is a master request, it is also saved with the key
	 * `http.request.master`. This is because when using Symfony's reverse proxy
	 * for caching the master request changes to a sub-request for the master
	 * content.
	 *
	 * @param GetResponseEvent $event     The HttpKernel request event instance
	 */
	public function addRequestToServices(GetResponseEvent $event)
	{
		$this->_services['request'] = $this->_services->share(function() use ($event) {
			return $event->getRequest();
		});

		if (HttpKernelInterface::MASTER_REQUEST == $event->getRequestType()) {
			$this->_services['http.request.master'] = $this->_services->share(function() use ($event) {
				return $event->getRequest();
			});
		}

		$this->_services['http.fragment_handler']->setRequest($this->_services['request']);
	}

	/**
	 * Validates that the requested formats are allowed by the route, and
	 * tells the request which requested formats can be used. This is then used
	 * when building the response.
	 *
	 * If the request is a sub-request, the allowed formats are set to whatever
	 * is defined in the `_format` attribute. This will either be the allowed
	 * content type(s) for the master route, or whatever was set as `_format`
	 * for the sub-request specifically.
	 *
	 * This allows the developer to sub-request for HTML within a JSON request,
	 * for example.
	 *
	 * @param GetResponseEvent $event     The HttpKernel request event instance
	 *
	 * @throws NotAcceptableHttpException If none of the requested content type(s) are acceptable
	 */
	public function validateRequestedFormats(GetResponseEvent $event)
	{
		$request = $event->getRequest();

		if ('ANY' === $request->attributes->get('_format')) {
			return;
		}

		$allowedContentTypes = array();
		$allowedFormats      = explode('|', $request->attributes->get('_format'));

		// If this is a subrequest, set the allowed content types to whatever is in _format
		if (HttpKernelInterface::SUB_REQUEST === $event->getRequestType()) {
			foreach ($allowedFormats as $format) {
				$allowedContentTypes[] = $request->getMimeType($format);
			}
		}
		// Otherwise, only allow requested & available content types
		else {
			// Determine the content type to return based on what's allowed and what's requested
			$requestedContentTypes = $request->getAcceptableContentTypes();

			// Loop through requested content types
			foreach ($requestedContentTypes as $mimeType) {
				// If the request allows any content type, and hasn't defined any
				// preferences, skip this (the system will fall back on the
				// requested content types)
				if ('*/*' === $mimeType && empty($allowedContentTypes)) {
					return true;
				}
				// Get format from the mime type
				$formatType = $request->getFormat($mimeType);
				// If this content type is available, add the mimetype to the accepted list
				if (in_array($formatType, $allowedFormats)) {
					$allowedContentTypes[] = $mimeType;
				}
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
		$request->attributes->set('_allowedContentTypes', $allowedContentTypes);
	}
}