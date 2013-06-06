<?php

namespace Message\Cog\Filesystem;

use Message\Cog\Event\SubscriberInterface;
use Message\Cog\Event\Event;
use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Service\ContainerInterface;

/**
 * Event listener for the Filesystem component.
 *
 * @author James Moss <james@message.co.uk>
 */
class EventListener extends BaseListener implements SubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array('cog.load.success' => array(
			array('registerStreamWrapper'),
		));
	}

	/**
	 * Sets up the cog:// stream 
	 *
	 * @param Event $event The event instance
	 */
	public function registerStreamWrapper(Event $event)
	{
		$manager = $this->_services['filesystem.stream_wrapper_manager'];
		$services = $this->_services;
		$manager->register('cog', function() use ($services){
			return $services['filesystem.stream_wrapper'];
		});
	}
}