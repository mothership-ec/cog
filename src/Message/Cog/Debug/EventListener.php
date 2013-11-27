<?php

namespace Message\Cog\Debug;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Event\Event;
use Message\Cog\Application\Environment;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Event listener for the Debug component.
 *
 * * Registers event listener(s) to render the Profiler.
 * * Registers the "Whoops" error page.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class EventListener extends BaseListener implements SubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			KernelEvents::RESPONSE => array(
				array('renderProfiler', 1000),
			),
			'modules.load.success' => array(
				array('registerWhoopsHandlers'),
				array('registerMonologHandlers'),
			),
		);
	}

	/**
	 * Render the profiler to the output.
	 *
	 * @param Event $event The "filter response" event instance
	 *
	 * @todo Ideally this would just append the HTML to the response content rather than just echo it out?
	 * @todo This should only append the HTML if the response type is actually HTML. Sort this out once we are setting response type on the response.
	 */
	public function renderProfiler(FilterResponseEvent $event)
	{
		// Ignore for subrequests
		if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
			return false;
		}

		$contentType = $event->getResponse()->headers->get('Content-Type');

		if ($this->get('environment')->isLocal()
		 && $this->get('environment')->context() != 'console'
		 && (is_null($contentType) || 'text/html' === $contentType)) {
			$html = $this->get('profiler')->renderHtml();
			$event->getResponse()->setContent($event->getResponse()->getContent() . $html);
		}
	}

	/**
	 * Register the "Whoops" error page when NOT in the live environment.
	 *
	 * @param  Event  $event The event object
	 */
	public function registerWhoopsHandlers(Event $event)
	{
		if (!in_array($this->get('environment')->get(), array('live', 'staging'))
		 && 'web' === $this->get('environment')->context()) {
			$this->_services['whoops']->register();
		}
	}

	/**
	 * Register the error/exception/shutdown handlers for Monolog.
	 *
	 * @param  Event  $event The event object
	 */
	public function registerMonologHandlers(Event $event)
	{
		\Monolog\ErrorHandler::register($this->_services['log.errors']);
	}
}