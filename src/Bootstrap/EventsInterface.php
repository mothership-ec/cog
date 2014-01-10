<?php

namespace Message\Cog\Bootstrap;

/**
 * Bootstrap interface for registering events to the event dispatcher.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface EventsInterface extends BootstrapInterface
{
	/**
	 * Register event listeners / subscribers to the given event dispatcher.
	 *
	 * The event dispatcher is not type hinted because this would mean we would
	 * have to type hint it in every class that uses this interface which is
	 * unmanageable.
	 *
	 * We can assume that `$eventDispatcher` is an instance of
	 * `\Message\Cog\Event\DispatcherInterface`.
	 *
	 * @param object $eventDispatcher The event dispatcher
	 */
	public function registerEvents($eventDispatcher);
}