<?php

namespace Message\Cog\Test\Event;

use Message\Cog\Event\DispatcherInterface;
use Message\Cog\Event\SubscriberInterface;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FauxDispatcher implements DispatcherInterface
{
	protected $_subscribers = array();
	protected $_listeners = array();

	public function dispatch($eventName, Event $event = null)
	{

	}

	public function addSubscriber(EventSubscriberInterface $subscriber)
	{
		$this->_subscribers[] = $subscriber;
	}

	/**
	 * Checks if a given event subscriber has been registered to this dispatcher.
	 *
	 * @param  string  $className Class name of the subscriber to check for
	 * @return boolean            Result of the check
	 */
	public function isSubscriberRegistered($className)
	{
		foreach ($this->_subscribers as $subscriber) {
			if ($className === get_class($subscriber)) {
				return true;
			}
		}

		return false;
	}

	public function addListener($eventName, $listener, $priority = 0)
	{
		if (!isset($this->_listeners[$eventName])) {
			$this->_listeners[$eventName] = array();
		}

		$this->_listeners[$eventName][] = $listener;
	}

	public function getListeners($eventName = null)
	{
		if (null !== $eventName) {
			return $this->_listeners[$eventName] ?: null;
		}

		return $this->_listeners;
	}

	public function hasListeners($eventName = null)
	{
		return isset($this->_listeners[$eventName]) && !empty($this->_listeners[$eventName]);
	}

	public function removeListener($eventName, $listener)
	{

	}

	public function removeSubscriber(EventSubscriberInterface $subscriber)
	{

	}
}