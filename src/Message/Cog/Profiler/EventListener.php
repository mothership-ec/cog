<?php

namespace Message\Cog\Profiler;

use Message\Cog\HTTP\Event\Event;
use Message\Cog\HTTP\Event\FilterResponseEvent;
use Message\Cog\Environment;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener for core functionality to deal with exceptions.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class EventListener implements EventSubscriberInterface
{
	protected $_profiler;
	protected $_environment;

	static public function getSubscribedEvents()
	{
		return array(Event::RESPONSE => array(
			array('renderProfiler'),
		));
	}

	/**
	 * Constructor.
	 *
	 * @param Profiler    $profiler    Instance of the profiler
	 * @param Environment $environment Instance of the application environment
	 */
	public function __construct(Profiler $profiler, Environment $environment)
	{
		$this->_profiler    = $profiler;
		$this->_environment = $environment;
	}

	/**
	 * Render the profiler to the output.
	 *
	 * @param  FilterResponseEvent $event [description]
	 * @return [type]                     [description]
	 *
	 * @todo Ideally this would just append the HTML to the response content rather than just echo it out?
	 * @todo This should only append the HTML if the response type is actually HTML. Sort this out once we are setting response type on the response.
	 */
	public function renderProfiler(FilterResponseEvent $event)
	{
		if ($event->getRequest()->isExternal()) {
			if ($this->_environment->isLocal()
			&& $this->_environment->context() != 'console') {
				echo $this->_profiler->renderHtml();
			}
		}
	}
}