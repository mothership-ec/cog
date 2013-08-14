<?php

namespace Message\Cog\Debug;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Event\Event;
use Message\Cog\Application\Environment;

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
	protected $_profiler;
	protected $_environment;

	static public function getSubscribedEvents()
	{
		return array(
			'terminate' => array(
				array('renderProfiler'),
			),
			'cog.load.success' => array(
				array('registerWhoops'),
			),
		);
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
	 * @param Event $event The "filter response" event instance
	 *
	 * @todo Ideally this would just append the HTML to the response content rather than just echo it out?
	 * @todo This should only append the HTML if the response type is actually HTML. Sort this out once we are setting response type on the response.
	 */
	public function renderProfiler(Event $event)
	{
		if ($this->_environment->isLocal()
		 && $this->_environment->context() != 'console') {
			echo $this->_profiler->renderHtml();
		}
	}

	/**
	 * Register the "Whoops" error page when NOT in the live or staging
	 * environment.
	 *
	 * @param  Event  $event The event object
	 */
	public function registerWhoops(Event $event)
	{
		if (!in_array($this->_environment->get(), array('live', 'staging'))) {
			$this->_services['whoops']->register();
		}
	}
}